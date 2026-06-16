<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;

// (Codigo agregado) Agrego Facades necesarios para llamadas HTTP y utilidades
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatGptController extends Controller
{
    public function index()
    {
        $user_id = auth()->user()->id;
        $user_name=auth()->user()->name;
        return view('content.chatgpt.index', compact('user_id','user_name'));
    }

    //Obtener todos los chats del usuario (usa 'chats')
    public function getChats(){
        $user_id = auth()->user()->id;
        $user_name=auth()->user()->name;
        $chats=Chat::where('user_id', $user_id)
            ->with(['details'=>function($query){
                // (Cambio) antes se usaba ->latest()->first() dentro del eager load
                // Se limitó a 1 para que with traiga sólo el último detalle correctamente
                $query->latest()->limit(1);
        }])
            ->orderBy('updated_at','DESC')
            ->get();
        

        return response()->json([
        'status' => true,
        'data' => $chats,
        'message' => 'Chats recuperados con éxito'
    ], 200)
    ->header('Access-Control-Allow-Origin', 'https://agente.picklechatbot.promolider.org')
    ->header('Access-Control-Allow-Credentials', 'true');
    }

    //Crear nuevo chat (en 'chats', enforza límite 20)
    public function createChat(Request $request){
        $userId=auth()->user()->id;
        $count=Chat::where('user_id',$userId)->whereNull('deleted_at')->count();
        if ($count >= 20) {
            // Soft delete el más antiguo
            $oldest = Chat::where('user_id', $userId)
                ->whereNull('deleted_at')
                ->orderBy('updated_at', 'ASC')
                ->first();
            if ($oldest) {
                $oldest->delete();
            }
        }

        $chat=Chat::create([
            'title'=>$request->title?? 'Nuevo Chat',
            'param'=>2,
            'level'=>$request->level?? 1,
            'status'=>1,
            'user_id'=>$userId,
        ]);

        return response()->json([
            'status'=>true,
            'data'=>$chat,
            'message'=>'Chat creado con éxito'
        ],201);
    }
    // Obtener mensajes de un chat (de 'chats_details')
    public function getChatDetails($chatId){
        $userId=auth()->user()->id;
        $chat=Chat::where('id',$chatId)
            ->where('user_id',$userId)
            ->firstOrFail();
        $details=$chat->details()
            ->orderBy('created_at','ASC')
            ->get();

        return response()->json([
            'status'=>true,
            'data'=>$details,
            'message'=>'Mensajes recuperados'
        ],200);
    }

    public function deleteChat($chatId){
        $userId=auth()->user()->id;
        $chat=Chat::where('id',$chatId)
            ->where('user_id',$userId)
            ->firstOrFail();
        $chat->delete();

        return response()->json([
            'status'=>true,
            'message'=>'Chat borrado con éxito'
        ],200);
    }
    /**
     * /
    
     */
    public function getChat($id)
    {
        $chats = Chat::where('user_id', $id)->get();
        return response()->json([
            'status' => true,
            'data' => $chats,
            'message' => 'Data recuperada con exito'
        ], 200);
        
    }

    public function chat(Request $request){

        $userId=auth()->user()->id;
        $chatId=$request->input('chat_id');
        $prompt=$request->input('queryResult');
        $param=$request->input('prom');
        $level=$request->input('level');
        $history=$request->input('history',[]);

        if(!$chatId){
            // (Codigo agregado) Crear chat directamente en vez de llamar a createChat que devuelve JSON
            // Esto evita envolver la creación en una respuesta HTTP y simplifica la obtención del id.
            $newChat = Chat::create([
                'title' => $request->input('title', 'Nuevo Chat'),
                'param' => $param ?? 2,
                'level' => $level ?? 1,
                'status' => 1,
                'user_id' => $userId,
            ]);
            $chatId = $newChat->id;
        } 

        $chat=Chat::findOrFail($chatId);
        if($chat->user_id!==$userId){
            abort(403);
        }

        $levelCourse='';
        if($level==1){
            $levelCourse = "Curso básico con 2 módulos, un precio acorde a la cantidad de módulos.";
        }else if($level== 2){
            $levelCourse = "Curso Intermedio con 4 módulos, un precio acorde a la cantidad de módulos.";
        }else{
            $levelCourse = "Curso Avanzado con 6 módulos, un precio acorde a la cantidad de módulos.";
        }

        // (Cambio) Obtener systemPrompt desde config/openai.php (una sola fuente, se evitan repeticiones)
        $systemPrompt = config('openai.system_prompt');

        $conversationContext='';

        foreach ($history as $msg) {
            $conversationContext .= "\n" . ucfirst($msg['role']) . ": " . $msg['content'];
        }

        // Concat final: solo historia + prompt usuario + levelCourse (sin repetir el systemPrompt)
        $userMessage = "Historia de conversación:" . $conversationContext . "\nUsuario: " . $prompt . "\n" . $levelCourse;
        if($levelCourse){
            $userMessage .= "\nUsa esta guía para nivel confirmado: " . $levelCourse;
        }
        
        // (Cambio) ahora envia() recibe system y user por separado. Se envia systemPrompt desde config
        $response=$this->envia([
            'system' => $systemPrompt,
            'user' => $userMessage
        ]);

        // (Codigo agregado) Se extrae el texto de forma segura incluso si la respuesta falla
        $responseText = $this->safeChoiceText($response);

        $chatDetail = new ChatDetail();
        $chatDetail->ask = $prompt;
        $chatDetail->answer = $responseText;  // Guardamos como string formateado (no JSON, ya que es HTML)
        $chatDetail->status = 1;  // Asumiendo status default
        $chatDetail->chat_id = $chatId;
        $chatDetail->save();

        $newTitle=$chat->title;
        if (empty($history)) {
            try {
                $titlePrompt = "Genera un título corto y atractivo para un chat sobre creación de cursos digitales basado en este mensaje inicial: '$prompt'. Máximo 50 caracteres. Responde solo con el título.";
                $titleResponse = $this->envia($titlePrompt);
                $generatedTitle = trim($this->safeChoiceText($titleResponse) ?? '');

                if (empty($generatedTitle)) {
                    throw new \Exception('Respuesta AI vacía para título');
                }

                $newTitle = $generatedTitle;
            } catch (\Exception $e) {
                Log::error('Error generando título AI para chat ' . $chatId . ': ' . $e->getMessage());
                // Fallback: Truncar prompt
                $newTitle = substr($prompt, 0, 50) . (strlen($prompt) > 50 ? '...' : '');
            }

            $chat->title = $newTitle;
            $chat->save();
        }
        
        // Actualizar updated_at del chat para ordenamiento
        $chat->touch();

        // Formateo responseText (tu lógica original)
        $linesToBold = ['Título general:', 'Descripción:', 'Precio:', 'Acerca del curso:', 'Lo que aprenderá:', 'Conocimientos previos:', 'Curso destinado para:', 'Esquemas del curso:'];
        
        foreach ($linesToBold as $line) {
            if (strpos($responseText, $line) !== false) {
                $responseText = str_replace($line, '<h3>' . $line . '</h3>', $responseText);
            }
        }
        if (strpos($responseText, 'Módulo') !== false) {
        $pattern = '/Módulo[^\n]*/';
        $responseText = preg_replace($pattern, '<h4>$0</h4>', $responseText);
    }
    
        return response()->json([
            'response'=>$responseText,
            'chat_id'=>$chatId,
        ],200);
    }

    /**
     * Envia a OpenAI.
     *
     * (Codigo agregado) Reemplazado curl por Http::post y uso de config('openai')
     * acepta string (user message) o array para roles
     */
    public function envia($param)
    {
        try {
            // (Cambio) obtener config desde config/openai.php (importante tener OPENAI_API_KEY en .env)
            $openaiKey = config('openai.key');
            $model = config('openai.model', 'gpt-3.5-turbo');
            $timeout = config('openai.timeout', 30);
            $maxTokens = config('openai.max_tokens', 700);

            if (empty($openaiKey)) {
                Log::error('OPENAI_API_KEY no configurada en .env');
                return ['error' => 'OPENAI_API_KEY no configurada'];
            }

            // Construir messages correctamente
            $messages = [];
            if (is_array($param)) {
                if (!empty($param['system'])) {
                    $messages[] = ['role' => 'system', 'content' => $param['system']];
                }
                if (!empty($param['user'])) {
                    $messages[] = ['role' => 'user', 'content' => $param['user']];
                }
                if (!empty($param['messages']) && is_array($param['messages'])) {
                    $messages = $param['messages'];
                }
            } else {
                $messages[] = ['role' => 'user', 'content' => (string)$param];
            }

            $data = [
                "model" => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => $maxTokens,
                'n' => 1,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openaiKey,
                'Content-Type' => 'application/json',
            ])->timeout($timeout)
              ->post('https://api.openai.com/v1/chat/completions', $data);

            if ($response->failed()) {
                Log::error('OpenAI request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['error' => 'OpenAI request failed', 'status' => $response->status(), 'body' => $response->body()];
            }

            $responseArr = $response->json();
            return $responseArr;

        } catch (\Throwable $th) {
            // Registrar el error
            Log::info("Ocurrió un error al consultar", [$th->getMessage()]);
            return ['error' => $th->getMessage()];
        }
    }

    // (Codigo agregado) Helper que extrae de forma segura el texto de la respuesta de OpenAI
    protected function safeChoiceText($responseArr)
    {
        if (!is_array($responseArr)) return '';
        if (isset($responseArr['choices']) && is_array($responseArr['choices']) && count($responseArr['choices']) > 0) {
            $choice = $responseArr['choices'][0];
            if (isset($choice['message']['content'])) {
                return $choice['message']['content'];
            }
            if (isset($choice['text'])) {
                return $choice['text'];
            }
        }
        if (isset($responseArr['error'])) {
            return 'Error: ' . (is_string($responseArr['error']) ? $responseArr['error'] : json_encode($responseArr['error']));
        }
        return '';
    }
    

    public function show($id)
    {
        $chat = Chat::with('details')->findOrFail($id);

        return response()->json([
            'data' => $chat,
            'messaga' => 'Data generada'
        ], 200);
    }

    public function subirPDF(Request $request){ 
        try {
            // Obtener el archivo PDF del request
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:10240', // Máximo 10MB
                'prom' => 'required|integer',
                'level' => 'required|integer|in:1,2,3',
                'chat_id' => 'nullable|integer|exists:chats,id',
            ]);
            $archivo = $request->file('pdf');
            $ext= $archivo->extension();
            $hoy= date('YmdHis');
            $nombreArchivo= "course_$hoy.$ext";
            $path= $archivo->storeAs('courses',$nombreArchivo,'public');
            
            // (Cambio) Arreglo ruta de archivo (antes faltaba la barra y concatenaba mal)
            $pdfFilePath = storage_path("app/public/" . $path);
            if(!file_exists($pdfFilePath)){
                return response()->json([
                    'mensaje'=>"El archivo PDF no se encontró en la ruta esperada."
                ], 404);
            }

            $extractedText = Pdf::getText($pdfFilePath);
            $extractedText = mb_convert_encoding($extractedText, 'UTF-8', 'UTF-8');

            $level=$request->input('level');
            $levelCourse = match ($level) {
                1 => "Curso básico con 2 módulos, un precio acorde a la cantidad de módulos.",
                2 => "Curso Intermedio con 4 módulos, un precio acorde a la cantidad de módulos.",
                default => "Curso Avanzado con 6 módulos, un precio acorde a la cantidad de módulos.",
            };

            // (Cambio) Obtener systemPrompt desde config/openai.php (una sola fuente, se evitan repeticiones)
            $systemPrompt = config('openai.system_prompt');

            $conversationContext='';
            $userPrompt="Analiza este contenido de PDF para crear un curso: \n" . $extractedText;
            $concat = $systemPrompt . "\n\nHistoria de conversación:" .$conversationContext. "\nUsuario:" .$userPrompt."\n". $levelCourse;

            $prom=$request->input('prom');

            // (Cambio) Se corrigió la condición errónea ($prom = 1) que asignaba. Ahora se compara correctamente
            // (Cambio) No repetimos systemPrompt dentro del user message (ahorro de tokens). Solo mandamos la historia/usuario
            $textConcat = ($prom == 1) ? "Usuario: " . $extractedText : "Historia de conversación:" . $conversationContext . "\nUsuario:" . $userPrompt . "\n" . $levelCourse;            // Verificar si se ha enviado un archivo
            
            // (Cambio) uso de envia() con roles para mejor coherencia (system desde config, user = textConcat)
            $response=$this->envia([
                'system' => $systemPrompt,
                'user' => $textConcat
            ]);

            // (Codigo agregado) Lectura segura del contenido retornado por OpenAI
            $responseText = $this->safeChoiceText($response);

            // Formateo (mismo que actual)
            $linesToBold = ['Título general:', 'Descripción:', 'Precio:', 'Acerca del curso:', 'Lo que aprenderá:', 'Conocimientos previos:', 'Curso destinado para:', 'Esquemas del curso:'];
            foreach ($linesToBold as $line) {
                if (strpos($responseText, $line) !== false) {
                    $responseText = str_replace($line, '<h3>' . $line . '</h3>', $responseText);
                }
            }
            if (strpos($responseText, 'Módulo') !== false) {
                $pattern = '/Módulo[^\n]*/';
                $responseText = preg_replace($pattern, '<h4>$0</h4>', $responseText);
            }

            // Integra con Chat (si chat_id existe o crea nuevo)
        $userId = auth()->user()->id;
        $chatId = $request->input('chat_id');
        if (!$chatId) {
            // (Codigo agregado) crear chat directo (evitar llamar createChat que devuelve JSON)
            $createChat = Chat::create([
                'title' => 'Curso desde PDF',
                'param' => $prom,
                'level' => $level,
                'status' => 1,
                'user_id' => $userId,
            ]);
            $chatId = $createChat->id;
        }
        $chat = Chat::findOrFail($chatId);
        if ($chat->user_id !== $userId) {
            abort(403);
        }

        // Guarda en ChatDetail
        $chatDetail = new ChatDetail();
        $chatDetail->ask = 'PDF: ' . $nombreArchivo; // O extractedText truncado
        $chatDetail->answer = $responseText;
        $chatDetail->status = 1;
        $chatDetail->chat_id = $chatId;
        $chatDetail->save();

        // Actualiza título si es nuevo (similar a chat)
        if (empty($chat->details()->count() - 1)) { // Solo si es el primero
            // Genera título basado en PDF, similar a chat
            $titlePrompt = "Genera un título corto para un chat basado en este PDF: '". Str::limit($extractedText, 800) . "'. Máx 50 chars.";
            $titleResponse = $this->envia(['user' => $titlePrompt]);
            $chat->title = trim($this->safeChoiceText($titleResponse) ?? 'Curso desde PDF');
            $chat->save();
        }
        $chat->touch(); // Actualiza updated_at

        return response()->json([
            'status' => true,
            'data' => $responseText,
            'mensaje' => 'PDF procesado y curso generado.',
            'chat_id' => $chatId,
        ], 200);
    } catch (\Exception $e) {
            // Manejar excepciones
            Log::error($e->getMessage());
            return response()->json([
                'mensaje'=>"Error al procesar el archivo PDF: " . $e->getMessage()
            ], 500);
        }
    }
}
