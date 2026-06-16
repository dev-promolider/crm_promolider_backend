<?php


namespace App\Traits;


use Illuminate\Http\Response;

trait ResponseFormat
{
    public function response(int $code = 200, string $msg = null, $data = null)
    {
        $response = ['status' => $code ];

        if($msg){
            $response['message'] = $msg;
        }

        if(!is_null($data)){
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function responseOk(string $msg = null, $data = null, $code = 200)
    {
        $response = ['status' => $code ];

        if($msg){
            $response['message'] = $msg;
        }

        $response['data'] = $data;


        return response()->json($response, 200);
    }

    public function responseUnauthorized(array $data = null, string $msg = null)
    {
        $response = ['status' => 401, 'message' => $msg ?? __('auth.failed') ];

        if(!is_null($data)){
            $response['data'] = $data;
        }

        return response()->json($response, 401);
    }

    public function responseForbidden(array $data = null, string $message = null){
        $response = ['status' => 403, 'message' => $message ?? __('http.forbidden') ];

        if(!is_null($data)){
            $response['data'] = $data;
        }

        return response()->json($response, 403);
    }

    public function responseConflict(string $message = null, $data = null){
        $response = ['status' => 409, 'message' => $message ?? __('http.conflict') ];

        if(!is_null($data)){
            $response['data'] = $data;
        }

        return response()->json($response, 409);
    }

    public function responseBadRequest(string $message = null, array $data = null){
        $response = ['status' => 400, 'message' => $message ?? trans('http.bad_request') ];

        if(!is_null($data)){
            $response['data'] = $data;
        }

        return response()->json($response, 400);
    }

    public function responseUnprocessableEntity(string $message = null, array $data = null){
        $response = ['status' => 422, 'message' => $message ?? __('http.unprocessable_entity') ];

        if(!is_null($data)){
            $response['data'] = $data;
        }

        return response()->json($response, 422);
    }

    public function errorResponse($error, $code = 400, $exception = null)
    {
        $error['status'] = $code;
        $error['dev'] = null;

        if(isset($exception)){
            logger($exception);

            if(env('APP_DEBUG', false) == true){
                $error['dev'] = $exception->getMessage() . ' - ' . $exception->getTraceAsString();
            }
        }

        return response()->json($error, $code);
    }
}
