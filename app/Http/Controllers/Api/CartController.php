<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Badge;
use App\Models\Point;
use App\Models\Course;
use App\Models\Option;
use App\Models\Wallet;
use App\Models\Classified;
use App\Models\AccountType;
use App\Models\BadgeDetail;
use Illuminate\Http\Request;
use App\Models\ClassroomCart;
use App\Models\Notifications;
use App\Traits\ResponseFormat;
use App\Models\PurchasedCourse;
use App\Models\UserCertificate;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\DB;
use App\Models\ClassroomCartDetail;
use App\Models\CourseConfiguration;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyBuyCourseMailable;
use App\Services\PHPMailerService; // ✅ AGREGADO: PHPMailerService para emails
use App\Http\Controllers\PayController;
use App\Http\Controllers\Api\ApiWalletMovementsController;

class CartController extends Controller
{
    use ResponseFormat;

    public function buyCourse(Request $request)
    {
        try {
            if (!$request->has('id_course')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El ID del curso es requerido'
                ], 400);
            }

            DB::beginTransaction();

            $user = auth()->user();
            $user_id = $user ? $user->id : $request->user_id;

            $courseInfo = Course::find($request->id_course);
            if (!$courseInfo) {
                throw new \Exception('Curso no encontrado');
            }

            $existingPurchase = PurchasedCourse::where('course_id', $request->id_course)
                ->where('user_id', $user_id)
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ya estás inscrito en este curso'
                ], 400);
            }

            $id_producer = $courseInfo->user_id;
            $producer_data = User::find($id_producer);
            $purchase_percentage_producer = AccountType::where('id', $producer_data->id_account_type)->first();

            if ($courseInfo->price == 0 || $request->type_purchase == 3) {
                Log::info('Intentando inscribir curso gratuito', [
                    'course_id' => $request->id_course,
                    'user_id' => $user_id,
                    'course_price' => $courseInfo->price,
                    'type_purchase' => $request->type_purchase
                ]);

                if ($request->type_purchase == 3 && $courseInfo->price > 0) {
                    throw new \Exception('Este curso no es gratuito');
                }

                $purchased_course = new PurchasedCourse();
                $purchased_course->course_id = $request->id_course;
                $purchased_course->user_id = $user_id;
                $purchased_course->save();
            } else {
                if ($request->type_purchase == 2) {
                    $nameCourse = $courseInfo->title;

                    $price_with_discount = round($courseInfo->price - ($courseInfo->price * $purchase_percentage_producer->disc_purchases_course / 100), 2);
                    $user_wallet_balance = $this->retrieveWalletBalanceUser($user_id);

                    if ($user_wallet_balance < $price_with_discount) {
                        throw new \Exception('Saldo insuficiente. Por favor, recarga tu billetera.');
                    }

                    app(PayController::class)->saveOpcWallet($user_id, $price_with_discount, 3, $nameCourse);
                    
                    // 💳 ENVIAR COMPROBANTE DE PAGO - Pago con Wallet
                    try {
                        $this->sendPaymentReceipt(
                            $request->id_course, 
                            $user_id, 
                            $price_with_discount, 
                            'Billetera Promolíder'
                        );
                    } catch (\Exception $e) {
                        Log::error('Error enviando comprobante de pago (wallet): ' . $e->getMessage());
                    }
                }

                $last_batch = Option::lastBatch();
                $last_batch = (int) $last_batch->value;

                $purchased_course = new PurchasedCourse();
                $purchased_course->course_id = $request->id_course;
                $purchased_course->user_id = $user_id;
                $purchased_course->save();

                if ($courseInfo->price > 0) {
                    $fecha = date('d-m-Y H:i');
                    $myUser = User::find($user_id);

                    $notification = new Notifications();
                    $notification->id_generator = $user_id;
                    $notification->id_receiver = $courseInfo->user_id;
                    $notification->title = "Nueva venta";
                    $notification->body = $myUser->name . ' ' . $myUser->last_name . ' acaba de comprar tu curso: ' . $courseInfo->title;
                    $notification->type = 2;

                    $notification->save();

                    $fullName = $myUser->name;

                    $referrer_data = User::find($myUser->id_referrer_sponsor);

                    if ($referrer_data->id > 1) {

                        $purchase_percentage_referrer = AccountType::where('id', $referrer_data->id_account_type)
                            ->first();

                        $bonus = $courseInfo->price * $purchase_percentage_referrer->course_selling_bonus / 100;

                        $wallet_referrer_data = Wallet::where('user_id', $referrer_data->id)
                            ->first();
                        if ($myUser->active && $myUser->membershipActive) {
                            $wallet_referrer = new WalletMovements();
                            $wallet_referrer->wallet_id = $wallet_referrer_data->id;
                            $wallet_referrer->amount = $bonus;
                            $wallet_referrer->type = 1;
                            $wallet_referrer->batch = $last_batch;
                            $wallet_referrer->status = 1;
                            $wallet_referrer->reason = "Bono por compra de curso de " . $fullName;
                            $wallet_referrer->bonus_type_id = 2;
                            $wallet_referrer->user_purchase_id = $myUser->id;
                            $wallet_referrer->save();
                        }
                    }

                    $bonus = $courseInfo->price * $purchase_percentage_producer->productor_bonus / 100;
                    $wallet_producer_data = Wallet::where('user_id', $producer_data->id)
                        ->first();

                    if ($myUser->membershipActive) {
                        $wallet_producer = new WalletMovements();
                        $wallet_producer->wallet_id = $wallet_producer_data->id;
                        $wallet_producer->amount = $bonus;
                        $wallet_producer->type = 1;
                        $wallet_producer->batch = $last_batch;
                        $wallet_producer->status = 1;
                        $wallet_producer->reason = "Bono por compra de curso de " . $fullName;
                        $wallet_producer->bonus_type_id = 3;
                        $wallet_producer->user_purchase_id = $myUser->id;
                        $wallet_producer->save();
                    }

                    $membersip = $myUser->id_account_type;
                    $fullName = $myUser->name;
                    $change_currency = Option::where('description', 'currency_value')->first();
                    $change_to_points = $courseInfo->price * $change_currency->value;
                    $change_to_points = round($change_to_points, 0, PHP_ROUND_HALF_UP);
                    $classified_user = Classified::where('user_id', $myUser->id)->first();
                    $save_position_branch = $classified_user->position;

                    $aux = false;

                    if ($membersip != 5 && $membersip != 6) {
                        $tmp_id = $classified_user->user_id;
                        while ($aux == false) {
                            $user_data = Classified::where('user_id', $tmp_id)->first();

                            if (!$user_data) {
                                Log::warning("No se encontró clasificación para el user_id: $tmp_id");
                                break;
                            }

                            $aux = $user_data->user_above == null ? true : false;

                            $user_status = User::find($tmp_id);
                            if ($user_status && $user_status->active && $user_status->qualified && $user_status->membershipActive) {
                                Point::create([
                                    'user_id' => $myUser->id,
                                    'sponsor_id' => $user_data->user_id,
                                    'points' => $change_to_points,
                                    'side' => $save_position_branch,
                                    'reason' => "Course buy, " . $fullName
                                ]);
                            } elseif ($classified_user->id_user_sponsor == $user_data->user_id) {
                                Point::create([
                                    'user_id' => $myUser->id,
                                    'sponsor_id' => $classified_user->id_user_sponsor,
                                    'points' => $change_to_points,
                                    'side' => $save_position_branch,
                                    'reason' => "Course buy, " . $fullName
                                ]);
                            }

                            $save_position_branch = $user_data->position;
                            $tmp_id = $user_data->user_above;
                        }
                    }

                    $this->validateBadgeBuyCourse($user_id);
                    $this->badgeSubscriberCollector($courseInfo->id, $courseInfo->user_id);
                    /*try {
                        $this->sendNotifyBuyCourse(
                            $myUser->email,
                            $courseInfo->title,
                            $courseInfo->price,
                            $myUser->name,
                            $fecha,
                            $courseInfo->url_portada
                        );
                    } catch (\Exception $e) {
                        Log::error('Error al realizar esta operación: ' . $e->getMessage());
                    }
                    */
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'ok',
                'message' => $courseInfo->price == 0 ? 'Inscripción exitosa' : 'Compra exitosa',
                'data' => $purchased_course
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error en buyCourse: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function retrieveWalletBalanceUser($user_id)
    {
        $isAdmin = AccountType::find(User::find($user_id)->id_account_type)->account === 'Admin';
        if (!$isAdmin) {
            $allMovements = app(ApiWalletMovementsController::class)->getAllMovementsWallet($user_id);

            $result = array_reduce($allMovements->resolve(), function ($carry, $item) use ($user_id) {

                if ($item['type'] == 1) {
                    return $carry + $item['amount'];
                } else if ($item['type'] == 0) {
                    if ($item['id_receiver'] === $user_id) {
                        return $carry + $item['amount'];
                    } else {
                        return $carry - $item['amount'];
                    }
                }

                return $carry;
            }, 0);
            return $result;
        }
    }

    public function buyCertificate(Request $request)
    {
        $user = auth()->user();
        $user_certificate = UserCertificate::where('id_course', $request->id_course)->where('id_user', $user->id)->get()->first();
        $user_certificate->is_paid = 1;
        $user_certificate->save();

        $notification = new Notifications();
        $notification->id_generator = $user->id;
        $notification->id_receiver = $user->id;
        $notification->title = "Certificado comprado";
        $notification->body = 'Acaba de comprar un certificado';
        $notification->type = 2;
        $notification->save();

        $id = $user->id;
        $fullName = $user->name;
        $membersip = $user->id_account_type;

        $change_currency = Option::where('description', 'currency_value')->first();
        $course_data = CourseConfiguration::where('course_id', 1)->first();
        $price = $course_data->data['certificate_price'];
        $change_to_points = $price / $change_currency->value;
        $change_to_points = round($change_to_points, 0, PHP_ROUND_HALF_UP);
        $classified_user = Classified::where('user_id', $id)->first();
        $save_position_branch = $classified_user->position;

        $aux = false;

        if ($membersip != 5 && $membersip != 6) {
            $tmp_id = $classified_user->user_id;
            while ($aux == false) {
                $user_data = Classified::where('user_id', $tmp_id)->first();
                $aux = $user_data->user_above == null ? true : false;
                $user_status = User::find($tmp_id);
                if ($user_status->active && $user_status->qualified && $user_status->membershipActive) {
                    Point::create([
                        'user_id' => $user->id,
                        'sponsor_id' => $user_data->user_id,
                        'points' => $change_to_points,
                        'side' => $save_position_branch,
                        'reason' => "Certificate buy, " . $fullName
                    ]);
                } elseif ($classified_user->id_user_sponsor == $user_data->user_id) {
                    Point::create([
                        'user_id' => $user->id,
                        'sponsor_id' => $classified_user->id_user_sponsor,
                        'points' => $change_to_points,
                        'side' => $save_position_branch,
                        'reason' => "Certificate buy, " . $fullName
                    ]);
                }
                $save_position_branch = $user_data->position;
                $tmp_id = $user_data->user_above;
            }
        }

        return $this->responseOk('saved data', $user_certificate);
    }

    public function validateBadgeBuyCourse($user_id)
    {
        $purchased_courses = PurchasedCourse::where(['user_id' => $user_id])->get();
        if (count($purchased_courses) > 0) {
            $badges = Badge::select('id', 'name', 'description', 'level', 'condition', 'icon')
                ->where('id', '>=', 7)
                ->where('id', '<=', 9)
                ->orderBy('condition')
                ->get();
            $this->validateBadge($badges, $purchased_courses, $user_id);
        }
    }

    public function badgeSubscriberCollector($course_id, $user_id)
    {
        $purchased_courses = PurchasedCourse::where(['course_id' => $course_id])->get();

        if (count($purchased_courses) > 0) {
            $badges = Badge::select('id', 'name', 'description', 'level', 'condition', 'icon')
                ->where('id', '>=', 16)
                ->where('id', '<=', 18)
                ->orderBy('condition')
                ->get();
            $this->validateBadge($badges, $purchased_courses, $user_id);
        }
    }

    public function validateBadge($badges, $purchased_courses, $user_id)
    {

        for ($i = 0; $i < count($badges); $i++) {

            $badge = $badges[$i];

            if (count($purchased_courses) >= $badge->condition) {
                $badges_details = BadgeDetail::select('id', 'user_id', 'badge_id')
                    ->where(['user_id' => $user_id, 'badge_id' => $badge->id])
                    ->get();

                if (count($badges_details) == 0) {
                    $badge_detail = new BadgeDetail();
                    $badge_detail->user_id = $user_id;
                    $badge_detail->badge_id = $badge->id;

                    if ($badge_detail->save()) {

                        $notification = new Notifications();
                        $notification->id_generator = 1;
                        $notification->id_receiver = $user_id;
                        $notification->id_badge = $badge->id;
                        $notification->title = "Logro desbloqueado";
                        $notification->body = "Obtuvo el logro de " . $badge->name;
                        $notification->type = 1;
                        $notification->seen = 0;
                        $notification->save();
                    }
                }
            } else {
                $i = count($badges);
            }
        }
    }

    public function validateCart(Course $course)
    {
        $courseBuy = ClassroomCart::join('classroom_cart_detail', 'classroom_cart_detail.classroom_cart_id', '=', 'classroom_cart.id')
            ->where('user_id', auth()->user()->id)->where('classroom_cart_detail.courses_id', $course->id)->count() == 0;

        $cart = ClassroomCart::CartUser()->first();
        if (!isset($cart) or $cart->status == "BOUGHT") {
            if ($courseBuy) {
                $this->createCart(auth()->user()->id);
                $cart = (ClassroomCart::CartWaiting()->first())->id;
                $this->addCart($course->id, $cart);
            } else {
                return $this->responseOk('course', 'this course has already been purchased');
            }
        } else if ($cart->status == "WAITING") {
            if ($courseBuy) {
                $this->addCart($course->id, $cart->id);
            } else {
                return $this->responseOk('course', 'this course has already been purchased');
            }
        }
        return $this->showCart();
    }

    public function createCart($user)
    {
        $cart = new ClassroomCart();
        $cart->user_id = $user;
        $cart->save();
    }

    public function addCart($id, $cart)
    {
        $cartDetail = new ClassroomCartDetail();
        $cartDetail->classroom_cart_id = $cart;
        $cartDetail->courses_id = $id;
        $cartDetail->save();
    }

    public function removeCart($cartDetail)
    {
        ClassroomCartDetail::where('id', $cartDetail)->delete();
        return $this->showCart();
    }

    public function clearCart()
    {
        $cart = ClassroomCart::CartWaiting()->first();
        ClassroomCartDetail::where('classroom_cart_id', $cart->id)->delete();
        return ["cart" => "Empty shopping cart"];
    }

    public function payCart($action)
    {
        $cart = ClassroomCart::CartWaiting()->first();
        if ($cart != null) {
            $details = ClassroomCartDetail::SltData()->where('classroom_cart_id', $cart->id)->with('courses')->get();
            if (count($details) > 0) {
                if ($action == 1) {
                    $cart->status = "BOUGHT";
                    $cart->save();
                    return $this->responseOk('cart_status', 'paid');
                } else {
                    return $this->responseOk('cart_status', 'waiting');
                }
            } else {
                return ["error" => "Empty shopping cart"];
            }
        } else {
            return ["error" => "Not exists shopping cart"];
        }
        ;
    }

    public function showCart()
    {
        $cart = ClassroomCart::SltData()->CartWaiting()->first();
        if ($cart != null) {
            $details = ClassroomCartDetail::SltData()->where('classroom_cart_id', $cart->id)->with('courses')->get();
            if (count($details) > 0) {
                $cart->cart_details = $details;
                return $this->responseOk('', $cart);
            } else {
                return ["error" => "Empty shopping cart"];
            }
        } else {
            return ["error" => "Not exists shopping cart"];
        }
    }

    /**
     * Envía el comprobante de pago por email cuando se confirma una compra de curso
     */
    public function sendPaymentReceipt($courseId, $userId, $amountPaid, $paymentMethod = 'Billetera Promolíder', $transactionId = null)
    {
        try {
            $course = Course::find($courseId);
            $student = User::find($userId);
            
            if (!$course || !$student) {
                Log::warning('No se pudo enviar comprobante: Curso o usuario no encontrado', [
                    'course_id' => $courseId,
                    'user_id' => $userId
                ]);
                return;
            }

            // Obtener datos del curso
            $category = \App\Models\Category::find($course->id_categories);
            $instructor = User::find($course->user_id);
            $totalModules = \App\Models\Module::where('id_courses', $course->id)->count();
            $totalLessons = \App\Models\Clas::whereHas('module', function($query) use ($course) {
                $query->where('id_courses', $course->id);
            })->count();

            // Calcular descuento si aplica
            $accountType = AccountType::find($student->id_account_type);
            $discountPercentage = $accountType ? $accountType->disc_purchases_course : 0;
            $discountAmount = ($course->price * $discountPercentage) / 100;

            // 🆕 GENERAR NÚMERO DE COMPROBANTE CORRELATIVO
            $receiptNumber = $this->generateReceiptNumber();
            
            // Guardar comprobante en la base de datos
            $purchasedCourse = \App\Models\PurchasedCourse::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->orderBy('id', 'desc')
                ->first();
            
            DB::table('payment_receipts')->insert([
                'receipt_number' => $receiptNumber,
                'user_id' => $userId,
                'course_id' => $courseId,
                'purchased_course_id' => $purchasedCourse ? $purchasedCourse->id : null,
                'amount_paid' => $amountPaid,
                'course_price' => $course->price,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'payment_method' => $paymentMethod,
                'payment_reference' => $transactionId,
                'email_sent_to' => $student->email,
                'email_sent_at' => now(),
                'email_status' => 'sent',
                'course_title' => $course->title,
                'instructor_name' => $instructor ? $instructor->name . ' ' . $instructor->last_name : 'Promolíder',
                'user_name' => $student->name . ' ' . $student->last_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Usar el número de comprobante como transaction_id
            $transactionId = $receiptNumber;

            // Preparar datos para la plantilla
            $templateData = [
                // Estudiante
                'student_name' => $student->name . ' ' . $student->last_name,
                'student_email' => $student->email,
                
                // Curso
                'course_title' => $course->title,
                'course_image' => $course->url_portada ? "https://vcr.promolider.info/storage/{$course->url_portada}" : '',
                'course_category' => $category ? $category->category : 'General',
                'instructor_name' => $instructor ? $instructor->name . ' ' . $instructor->last_name : 'Promolíder',
                'total_modules' => $totalModules,
                'total_lessons' => $totalLessons,
                'course_duration' => $course->duration ?? 'Acceso ilimitado',
                'course_url' => "https://vcr.promolider.info/course/{$course->id}",
                
                // Pago
                'transaction_id' => $transactionId,
                'payment_date' => now()->format('d/m/Y H:i:s'),
                'payment_method' => $paymentMethod,
                'course_price' => $course->price,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'amount_paid' => $amountPaid,
                
                // Facturación (opcional)
                'include_billing_info' => false,
                'billing_name' => '',
                'billing_address' => '',
            ];

            $phpMailerService = new PHPMailerService();
            $phpMailerService->sendEmailWithTemplate(
                $student->email,
                'Comprobante de Pago - ' . $course->title,
                'emails.comprobante-pago-curso',
                $templateData
            );

            Log::info('Comprobante de pago enviado exitosamente', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'email' => $student->email,
                'amount' => $amountPaid,
                'transaction_id' => $transactionId
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando comprobante de pago: ' . $e->getMessage(), [
                'course_id' => $courseId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzar excepción para no interrumpir el flujo de compra
        }
    }

    public function sendNotifyBuyCourse($email, $name_course, $price_course, $name_user, $date_buy, $img_course)
    {
        try {
            $phpMailerService = new PHPMailerService();
            
            // Datos para la plantilla de notificación de compra
            $templateData = [
                'name_course' => $name_course,
                'price' => $price_course,
                'name_user' => $name_user,
                'date_buy' => $date_buy,
                'img_course' => $img_course,
                'support_email' => 'soporte@promolider.info',
                'platform_url' => url('/')
            ];

            $subject = 'Confirmación de Compra - ' . $name_course;
            
            // Usar plantilla de email para notificación de compra
            $phpMailerService->sendEmailWithTemplate(
                $email,
                $subject,
                'emails.course-purchase-notification', // Plantilla para compra de curso
                $templateData,
                'Promolíder - Confirmación de Compra'
            );
            
            Log::info('Email de compra de curso enviado exitosamente', [
                'email' => $email,
                'course' => $name_course,
                'user' => $name_user,
                'price' => $price_course
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error enviando email de compra de curso: ' . $e->getMessage(), [
                'email' => $email,
                'course' => $name_course,
                'user' => $name_user
            ]);
            // No lanzar excepción para evitar interrumpir la compra
        }
    }

    /**
     * Genera el número de comprobante correlativo de 5 dígitos
     * Empieza desde 00100 y va incrementando: 00101, 00102, etc.
     */
    private function generateReceiptNumber()
    {
        // Obtener el último número de comprobante
        $lastReceipt = DB::table('payment_receipts')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastReceipt) {
            // Incrementar el último número
            $lastNumber = intval($lastReceipt->receipt_number);
            $newNumber = $lastNumber + 1;
        } else {
            // Primer comprobante
            $newNumber = 100;
        }
        
        // Formatear a 5 dígitos con ceros a la izquierda
        return str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
