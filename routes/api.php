<?php

use App\Http\Controllers\Exam;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuxController;
use App\Http\Controllers\DailyQuestion;
use App\Http\Controllers\PayController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\CourseProgressController;
use App\Http\Controllers\CourseCertificateController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MasterclassController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\CourseGameController;
use App\Http\Controllers\CourseGameCommentController;
use App\Http\Controllers\MinicourseRegisterController;
use App\Http\Controllers\EbookRegisterController;
use App\Http\Controllers\MasterclassRegisterController;
use App\Http\Controllers\MiniCourseDistributorController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\ModuleClassController;
use App\Http\Controllers\RamaBinariaController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\CertificatesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ClassResourceController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\MC\ParticipantController;
use App\Http\Controllers\Api\AccountTypeController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\PreferencesController;
use App\Http\Controllers\FrequentQuestionsController;
use App\Http\Controllers\UnverifiedPaymentController;
use App\Http\Controllers\UserClassroomPointController;
use App\Http\Controllers\Api\PruebasApiJuegoController;
use App\Http\Controllers\CourseConfigurationController;
use App\Http\Controllers\Api\PurchasedCoursesController;
use App\Http\Controllers\ClassroomPointDetailController;
use App\Http\Controllers\Api\ApiWalletMovementsController;
use App\Http\Controllers\Api\PropiertiesforUserController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\MC\VideoController as MCVideoController;
use App\Http\Controllers\CourseController as ControllersCourseController;
use App\Http\Controllers\EdiTemplateController;
use App\Http\Controllers\MC\NotificationController as MCNotificationController;
use App\Http\Controllers\Templates\TemplateController;
use App\Http\Controllers\PreregistroController;

// Post Store User
Route::group(['prefix' => '/v1'], function () {
    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {});
});

Route::get('/config/settings', [OptionController::class, 'values']);

Route::post('/register', [UserController::class, 'store']);

Route::group(['prefix' => '/v1'], function () {
    Route::group(['prefix' => '/public'], function () {
        Route::post('/sendRecoveryEmail', [UserController::class, 'sendRecoveryEmail'])->name('sendRecoveryEmail');
        Route::post('/recoveryPassword', [UserController::class, 'recoveryPassword'])->name('recoveryPassword');
        
        // Endpoint para n8n: Verificar estado de pago de preregistro
        Route::get('/preregistro/check-payment/{email}', [PreregistroController::class, 'checkPaymentStatus'])->name('preregistro.check-payment');
    });

    if (config('app.is_api')) {
        // Rutas API para tipos de documentos
        Route::get('/listDocumentType', [ApiUserController::class, 'listDocumentType'])->name('document.list');

        Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function () {
            // Rutas de autenticaci贸n
            Route::post('login', [AuthController::class, 'login'])->name('auth.login');
            Route::post('redirect', [AuthController::class, 'loginApiForRedirect'])->name('auth.loginRedirect');
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:api');
        });

        // =================== Rutas para proyecto Juegos ===================
        Route::group(['prefix' => '/games-test'], function () {
            Route::get('/', [PruebasApiJuegoController::class, 'list']);
        });
        
        Route::get('/certificate/download/{course_id}', [CertificatesController::class, 'downloadCertificate'])->middleware('auth:api');

        Route::middleware(['auth:api', 'checkAuth'])->group(function () {

            Route::group(['prefix' => 'dashboard'], function () {
                Route::get('/topbar-stats', [\App\Http\Controllers\Api\DashboardController::class, 'topbarStats']);
                Route::get('/widgets', [\App\Http\Controllers\Api\DashboardController::class, 'dashboardWidgets']);
                Route::get('/binary-tree', [\App\Http\Controllers\RamaBinariaController::class, 'listbinary']);
                Route::get('/unilevel-tree', [\App\Http\Controllers\Api\DashboardController::class, 'unilevelTree']);
            });

            Route::group(['prefix' => 'notifications'], function () {
                Route::get('/list', [NotificationController::class, 'myNotifications'])->name('notifications-list');
            });

            Route::get('/my-courses/{course}/certificate/download', [CourseCertificateController::class, 'downloadStudentCertificate']);
            Route::get('/my-courses/{module}/module/certificate/download', [CourseCertificateController::class, 'downloadStudentCertificateModule']);
            Route::get('/my-courses/{course}/certificate', [CourseCertificateController::class, 'getStudentCertificate']);

            Route::group(['prefix' => '/marketing'], function () {

                // Rutas para Marketplace - usando exactamente los mismos m茅todos que ya tienes
                Route::get('/marketplace/masterclass/list', [MarketingController::class, 'masterclassList'])
                    ->name('api.marketing.marketplace.masterclass.list');

                Route::get('marketplace/masterclasses-paginated', [MarketingController::class, 'masterclassesPaginated']);

                Route::get('/marketplace/ebooks/list', [MarketingController::class, 'ebooksList'])
                    ->name('api.marketing.marketplace.ebooks.list');
                
                Route::get('marketplace/ebook-paginated', [MarketingController::class, 'ebooksPaginated']);

                Route::get('/marketplace/minicourses/list', [MarketingController::class, 'miniCoursesList'])
                    ->name('api.marketing.marketplace.minicourses.list');

                Route::get('marketplace/minicourses-paginated', [MarketingController::class, 'miniCoursesPaginated']);

                Route::get('/{id}/list-students', [MarketingController::class, 'listMasterclassStudents']);
                Route::get('/{id}/list-students/minicourse', [MarketingController::class, 'listMinicourseStudents']);
                Route::get('/{id}/list-students/ebook', [MarketingController::class, 'listEbookStudents']);

                Route::get('/report-admin-m', [MarketingController::class, 'reportMasterclassAdmin_M']);
                Route::get('/report-admin-d', [MarketingController::class, 'reportMasterclassAdmin_D']);
                Route::get('/report-producer-m/{id}', [MarketingController::class, 'reportMasterclassProducer_M']);
                Route::get('/report-producer-d/{id}', [MarketingController::class, 'reportMasterclassProducer_D']);
                Route::get('report-distributor/{id}',[MarketingController::class, 'reportMasterclassDistributor']);
                // Rutas para reportes de MiniCursos
                Route::get('/report-minicourse-admin-m', [MarketingController::class, 'reportMiniCourseAdmin_M']);
                Route::get('/report-minicourse-admin-d', [MarketingController::class, 'reportMiniCourseAdmin_D']);
                Route::get('/report-minicourse-producer-m/{id}', [MarketingController::class, 'reportMiniCourseProducer_M']);
                Route::get('/report-minicourse-producer-d/{id}', [MarketingController::class, 'reportMiniCourseProducer_D']);
                Route::get('/report-minicourse-distributor/{id}', [MarketingController::class, 'reportMiniCourseDistributor']);
                // Rutas para reportes de Ebooks
                Route::get('/report-ebook-admin-m', [MarketingController::class, 'reportEbookAdmin_M']);
                Route::get('/report-ebook-admin-d', [MarketingController::class, 'reportEbookAdmin_D']);
                Route::get('/report-ebook-producer-m/{id}', [MarketingController::class, 'reportEbookProducer_M']);
                Route::get('/report-ebook-producer-d/{id}', [MarketingController::class, 'reportEbookProducer_D']);
                Route::get('/report-ebook-distributor/{id}', [MarketingController::class, 'reportEbookDistributor']);

                Route::get('/masterclass/distributors-data/{id}', [MarketingController::class, 'listMasterclassDistributors'])
                    ->name('marketing.masterclass.distributors-data');
                    
                Route::get('/minicourse/distributors-data/{id}', [MarketingController::class, 'listMiniCourseDistributors'])
                    ->name('marketing.minicourse.distributors-data');
                    
                Route::get('/ebook/distributors-data/{id}', [MarketingController::class, 'listEbookDistributors'])
                    ->name('marketing.ebook.distributors-data');

                Route::get('/{id}/pending-students/{type?}', [MarketingController::class, 'getPendingParticipants']);
                Route::get('/students-list', [MarketingController::class, 'getStudentsList']);
                Route::get('/participants/all/{isParticipant?}', [MarketingController::class, 'getAllPendingParticipantsByUser']);

                Route::group(['prefix' => 'mini-course'], function () {
                    Route::patch('/user/{user_id}/participant', [MinicourseRegisterController::class, 'updateParticipantStatus']);
                    Route::patch('/user/{user_id}/observation', [MinicourseRegisterController::class, 'updateObservation']);
                    Route::post('/purchase/{id}', function ($id) {
                        Log::info("馃殌 Ingres贸 a la ruta: /invitation/purchase/{$id}");
                        return app(\App\Http\Controllers\MiniCourseDistributorController::class)->purchase($id);
                    })->name('marketing.mini-course.purchase');                
                    Route::get('/check-purchase/{id}', function ($id) {
                        Log::info("馃殌 Ingres贸 a la ruta: /invitation/check-purchase/{$id}");
                        return app(\App\Http\Controllers\MiniCourseDistributorController::class)->checkPurchase($id);
                    })->name('marketing.mini-course.check-purchase');                
                    Route::post('/invitation-link/{id}', function ($id) {
                        Log::info("馃殌 Ingres贸 a la ruta: /invitation/invitation-link/{$id}");
                        return app(\App\Http\Controllers\MiniCourseDistributorController::class)->createInvitationLink($id);
                    })->name('marketing.mini-course.create-invitation-link');                
                    Route::get('/check-invitation/{id}', function ($id) {
                        Log::info("馃殌 Ingres贸 a la ruta: /invitation/check-invitation/{$id}");
                        return app(\App\Http\Controllers\MiniCourseDistributorController::class)->checkInvitation($id);
                    })->name('marketing.mini-course.check-invitation');
                });

                Route::group(['prefix' => 'ebook'], function () {
                    Route::patch('/user/{user_id}/participant', [EbookRegisterController::class, 'updateParticipantStatus']);
                    Route::patch('/user/{user_id}/observation', [EbookRegisterController::class, 'updateObservation']);
                    // Invitaciones
                    Route::post('/purchase/{id}', [EbookController::class, 'purchase'])->name('marketing.ebook.purchase');
                    Route::get('/check-purchase/{id}', [EbookController::class, 'checkPurchase'])->name('marketing.ebook.check-purchase');
                    Route::post('/invitation-link/{id}', [EbookController::class, 'createInvitationLink'])->name('marketing.ebook.create-invitation-link');
                    Route::get('/check-invitation/{id}', [EbookController::class, 'checkInvitation'])->name('marketing.ebook.check-invitation');                
                });

                Route::group(['prefix' => 'calendar'], function () {
                    Route::get('/notes', [MarketingController::class, 'getNotes']);
                    Route::post('/notes', [MarketingController::class, 'createNote']);
                    Route::post('/sync-notes', [MarketingController::class, 'syncNotes']);
                    Route::put('/notes/{id}', [MarketingController::class, 'updateNote']);
                    Route::delete('/notes/{id}', [MarketingController::class, 'deleteNote']);
                });

            });

            Route::group(['prefix' => 'masterclass'], function () {
                Route::get('/check-registration/{id_masterclass}', [MasterclassController::class, 'checkRegistration']);
                Route::get('/check-invitation/{id_masterclass}', [MasterclassController::class, 'checkInvitation']);
                Route::patch('/user/{user_id}/participant', [MasterclassRegisterController::class, 'updateParticipantStatus']);
                Route::patch('/user/{user_id}/observation', [MasterclassRegisterController::class, 'updateObservation']);
                Route::post('/register-masterclass/{id_masterclass}', [MasterclassController::class, 'registerMasterclass']);
                Route::post('/create-invitation/{id_masterclass}', [MasterclassController::class, 'createInvitationLink']);
            });

            // =================== Rutas para proyecto Master Class ===================
            Route::group(['prefix' => 'mc'], function () {
                // Notificaciones y videos de Master Class
                Route::get('notifications', [MCNotificationController::class, 'list']);
                Route::put('notifications/{id}', [MCNotificationController::class, 'markAsSeen']);
                Route::get('indicators', [MCVideoController::class, 'indicators']);
                Route::post('store', [MCVideoController::class, 'store']);
                Route::get('list', [MCVideoController::class, 'list']);
                Route::get('upcoming', [MCVideoController::class, 'upcoming'])->name('mc.upcoming');
                Route::post('filter', [MCVideoController::class, 'filter'])->name('mc.filter');
                Route::delete('delete/{id}', [MCVideoController::class, 'delete']);

                Route::post('show', [MCVideoController::class, 'show']);

                // Rutas relacionadas con cursos
                Route::group(['prefix' => 'course'], function () {
                    Route::get('list', [CourseController::class, 'listCourses']);
                });

                // Participantes de cursos
                Route::group(['prefix' => 'participant'], function () {
                    Route::post('subscribe', [ParticipantController::class, 'subscribe']);
                    Route::get('list', [ParticipantController::class, 'list']);
                });
            });

            // API para gestionar usuario
            Route::group(['prefix' => '/user'], function () {
                Route::post('/update', [UserController::class, 'update']);
                Route::post('/update-user', [UserController::class, 'updateUser']);
                Route::get('/show', [UserController::class, 'show']);
                Route::get('/{id}/detail', [UserController::class, 'getDataUserId']);
                Route::post('/verify-duplicate', [UserController::class, 'verifyDuplicate']);
                Route::get('/get-rolename', [UserController::class, 'getRolename']);
                Route::post('/verify-unique-email', [UserController::class, 'verifyUniqueEmail']);
                Route::get('get-data-currentuser', [UserController::class, 'getDataCurrentUser']);
            });

            // API para el tipo de cuenta
            Route::group(['prefix' => '/accout-type'], function () {
                Route::get('/{id}', [AccountTypeController::class, 'getDataBytId']);
            });

            // API para mensajes
            Route::group(['prefix' => '/messages'], function () {
                Route::get('/with/{email}', [MessageController::class, 'show']);
                Route::get('list', [MessageController::class, 'list']);
                Route::post('/add', [MessageController::class, 'addMessage']);
                Route::get('listAll', [MessageController::class, 'listAll']);
                Route::get('/listContacts', [MessageController::class, 'listContacts']);
                Route::get('/listNewContacts/{id}', [MessageController::class, 'listNewContacts']);
                Route::post('/sendNewMessage', [MessageController::class, 'sendNewMessage']);
                Route::post('/content', [MessageController::class, 'getContent']);
            });

            // API para categor铆as
            Route::group(['prefix' => 'category'], function () {
                Route::get('/list', [CategoryController::class, 'list']);
            });

            // API para preferencias
            Route::group(['prefix' => 'preferences'], function () {
                Route::post('/add', [PreferencesController::class, 'store']);
                Route::post('/update/{id}', [PreferencesController::class, 'updatePreference']);
                Route::post('/delete-preferences', [PreferencesController::class, 'deleteUserPreferences']);
                Route::post('/delete/{id}', [PreferencesController::class, 'deletePreference']);
                Route::get('/show-preferences', [PreferencesController::class, 'myPreferences']);
            });

            // API para pagos
            Route::group(['prefix' => '/pay'], function () {
                Route::post('/course-openpay', [PayController::class, 'openpayCourse']);
                Route::post('/openpay-order', [OptionController::class, 'openpayOrder']);
                Route::post('/unverified-payment/create', [UnverifiedPaymentController::class, 'create']);
                Route::get('/get-openpay-conditions', [PaymentController::class, 'getOpenpayConditions']);
            });

            // M茅todos de pago
            Route::group(['prefix' => '/config/payment-method'], function () {
                Route::get('/list-array', [PaymentMethodController::class, 'listPaymentMethods'])->name('payment-method-list-array');
            });

            // Reportes de movimientos en la billetera
            Route::group(['prefix' => '/reports'], function () {
                Route::get('/mymovements/{user_id}', [ApiWalletMovementsController::class, 'getAllMovementsWallet']);
            });

            // API de cursos
            Route::group(['prefix' => '/course'], function () {
                Route::post('/buy-purchased-course', [ControllersCourseController::class, 'buyPurchasedCourse']);
                Route::get('/purchased-courses', [CourseController::class, 'purchasedCourses']);
                Route::get('/{courseId}/completed-lessons', [CourseProgressController::class, 'getCompletedLessons']);
                Route::get('/{course_id}/modules-completion', [CourseConfigurationController::class, 'getModuleCompletionStatus']);
                Route::get('/{course_id}/module/{module_id}/completion', [CourseConfigurationController::class, 'checkSpecificModuleCompletion']);
                Route::post('/{course_id}/save-completed-lessons', [CourseConfigurationController::class, 'saveCompletedLessons']);
                Route::get('/{courseId}/progress', [CourseProgressController::class, 'getProgress']);
                Route::post('/{courseId}/complete-lesson', [CourseProgressController::class, 'completeLesson']);
                Route::get('/{courseId}/sync-progress', [CourseProgressController::class, 'syncProgress']);
                Route::post('/{courseId}/update-progress', [CourseProgressController::class, 'updateCourseProgress']);
                Route::get('/test/{id}', [ControllersCourseController::class, 'approved']);
                Route::get('/list', [CourseController::class, 'list']);
                Route::get('/list/random', [CourseController::class, 'listRandom']);
                Route::get('/temary/get-all-class/{id}', [CourseController::class, 'show']);
                Route::get('/producter/{id}', [CourseController::class, 'listProducer']);
                Route::get('/details/{id}', [CourseController::class, 'detailsCourse']);
                Route::get('/recomendations/{category}', [CourseController::class, 'recomendations']);
                Route::get('/add/lesson/{id}', [CourseController::class, 'addLatestLesson']);
                Route::get('/show/lesson', [CourseController::class, 'showLatestLesson']);
                Route::get('/related-courses', [CourseController::class, 'recommendedCourses']);
                Route::get('/list-available-books', [CourseController::class, 'listAvailableBooks']);
                Route::get('/interesting-courses', [CourseController::class, 'interestingCourses']);
                Route::get('/released-courses', [CourseController::class, 'releasedCourses']);
                Route::get('/last-courses-rep', [CourseController::class, 'lastCoursesReprod']);
                Route::get('/purchased-courses', [CourseController::class, 'purchasedCourses']);
                Route::post('/buy-purchased-course', [CourseController::class, 'setPurshaseCouse']);
                Route::get('/search-courses/{str}', [CourseController::class, 'searchCourses']);
                Route::get('/certificate-list', [CertificatesController::class, 'getCertificateUserList']);
                Route::get('/certificate-discount', [AccountTypeController::class, 'certificateDiscount']);
                Route::get('/course-discount', [AccountTypeController::class, 'courseDiscount']);
                Route::get('/rate/show/{id}', [CourseController::class, 'rateCourseShow']);
                Route::post('/rate/store', [CourseController::class, 'rateCourseStore']);
                Route::get('/list-actives/producer', [CourseController::class, 'listActiveCourses']);
                Route::get('/certificate/data', [CourseConfigurationController::class, 'getCertificateConfiguration']);
                
                Route::get('/expiration-date', [CourseController::class, 'expirationDate']);
                Route::get('/all-dynamics-top/{id}', [CourseGameController::class, 'allDynamicsTop']);

                Route::middleware(['cors'])->group(function () {
                    Route::get('/certificate/{id}', [CertificatesController::class, 'getCertificateUser']);
                });

                Route::group(['prefix' => '/exam'], function () {
                    Route::post('/', [CourseController::class, 'exam']);
                    Route::post('/active', [Exam::class, 'getActiveExam']);
                    Route::post('/module/active', [Exam::class, 'getActiveExamModules']);
                    Route::get('/daily', [DailyQuestion::class, 'get']);
                    Route::post('/daily/points', [DailyQuestion::class, 'validateResponseDaily']);
                    Route::post('/answers', [Exam::class, 'getAnswers']);
                    Route::post('/results', [Exam::class, 'getResults']);
                    Route::get('/isconfig/{id}', [CourseConfigurationController::class, 'checkIfCourseIsConfigurated']);
                    Route::post('/calification', [Exam::class, 'getCalification']);
                    Route::post('/indicators', [Exam::class, 'getIndicators']);
                    Route::get('/list', [Exam::class, 'examList']);
                });

                Route::group(['prefix' => '/game'], function () {
                    Route::post('/', [CourseGameController::class, 'game']);
                    Route::post('/active', [CourseGameController::class, 'getActiveGame']);
                    Route::post('/module/active', [CourseGameController::class, 'getActiveModuleGame']);
                    Route::post('/add-points', [CourseGameController::class, 'addPointsToUser']);
                    Route::post('/retrieve-dynamic-top', [CourseGameController::class, 'retrieveDynamicTop']);
                    Route::group(['prefix' => '/comments'], function () {
                        Route::get('/list/{id_course_game}', [CourseGameCommentController::class, 'listDynamicComments']);
                        Route::post('/create', [CourseGameCommentController::class, 'createDynamicComment'])->middleware('throttle:3,1');
                    });
                });

                Route::group(['prefix' => '/dinamicas'], function () {
                    Route::get('/list/{id}', [GameController::class, 'list']);
                    Route::get('/datos/{id}', [GameController::class, 'datos']);
                });

                Route::group(['prefix' => '/certificate'], function () {
                    Route::get('/check/{id}', [CourseConfigurationController::class, 'isReadyToClaimCertificate']);
                });

                Route::group(['prefix' => '/congratulations'], function () {
                    Route::get('/', [CertificateController::class, 'getCongratulation']);
                });
            });

            //Api Dashboard
            Route::group(['prefix' => 'dashboard'], function () {
                Route::get('/getattributes', [PropiertiesforUserController::class, 'getPropierties']);
                Route::get('/saleshistory', [SalesController::class, 'index'])->name('api.saleshistory.index');
                Route::get('/saleshistory/{payment}', [SalesController::class, 'show'])->name('api.saleshistory.show');
                Route::get('/lastlessonseen', LessonController::class);
            });

            //Api Reports
            Route::group(['prefix' => 'reports'], function () {
                Route::get('/last-sells', [ReportsController::class, 'lastSells']);
            });

            //Api Puntos Classroom
            Route::group(['prefix' => 'classroom-points'], function () {
                Route::post('/insert-user-points', [ClassroomPointDetailController::class, 'insert']);
                Route::get('/ranking', [UserClassroomPointController::class, 'show']);
            });

            //Api Profile
            Route::group(['prefix' => 'profile'], function () {
                Route::post('/upload-photo', [UserController::class, 'uploadPhoto']);
                Route::get('/info', [UserController::class, 'myInfo']);
                Route::get('/points/{id}', [UserController::class, 'myPoints']);
                Route::post('/change-pass', [UserController::class, 'changePassword']);
            });

            Route::get('/countries', [UserController::class, 'getCountries']);

            //Api Configuracion Opciones
            Route::group(['prefix' => 'options'], function () {
                Route::get('/values', [OptionController::class, 'values']);
            });

            //Api Cart
            Route::group(['prefix' => 'cart'], function () {
                Route::post('/buy-course', [CartController::class, 'buyCourse']);
                Route::post('/buy-certificate', [CartController::class, 'buyCertificate']);
                Route::get('/show', [CartController::class, 'showCart']);
                Route::get('/add/{course}', [CartController::class, 'validateCart']);
                Route::get('/remove/{cartDetail}', [CartController::class, 'removeCart']);
                Route::get('/clear', [CartController::class, 'clearCart']);
                Route::get('/update/{action}', [CartController::class, 'payCart']);
            });

            //Api comentarios entre alumnos y productos
            Route::group(['prefix' => 'comments'], function () {
                Route::post('/send-comments', [CommentsController::class, 'sendComments']);
                Route::get('/show-comments', [CommentsController::class, 'showComments']);
            });

            //Api logros
            Route::group(['prefix' => 'badges'], function () {
                Route::get('/list', [BadgeController::class, 'getAll'])->name("all");
                Route::get('/my-badges', [BadgeController::class, 'getMyBadges'])->name('fetch-my-badges');
                Route::get('/my-progress', [BadgeController::class, 'getBadges'])->name('get-my-progress');
            });

            //Api para clases
            Route::group(['prefix' => 'class'], function () {
                Route::get('/show-class/{courseId}', [ModuleClassController::class, 'getDetailsClass']);
                Route::post('/{id}/progress', [CourseConfigurationController::class, 'updateProgress']);
                Route::get('/{id}/progress', [CourseConfigurationController::class, 'getProgress']);
            });

            //Api para classResources
            Route::group(['prefix' => 'class-resource'], function () {
                Route::get('/show-resources', [ClassResourceController::class, 'showResources']);
                Route::get('/download-resource', [ClassResourceController::class, 'downloadResources']);
            });

            //Api para video
            Route::group(['prefix' => 'video'], function () {
                Route::get('/update-status', [VideoController::class, 'updateStatus']);
                Route::get('/stream-video', [VideoController::class, 'streamVideo']);
                Route::post('/save-time', [VideoController::class, 'saveTime']);
                Route::get('/show-time', [VideoController::class, 'showTime']);
                Route::post('/save-video', [VideoController::class, 'saveVideo']);
                Route::post('/get-video', [VideoController::class, 'getVideo']);
                Route::post('/delete-video', [VideoController::class, 'deleteVideo']);
                Route::post('/create-folder', [VideoController::class, 'createFolder']);
                Route::post('/delete-folder', [VideoController::class, 'deleteFolder']);
            });

            //Api de cursos comprados
            Route::group(['prefix' => 'purchased'], function () {
                Route::put('/update', [PurchasedCoursesController::class, 'update']);
                Route::get('/show', [PurchasedCoursesController::class, 'show']);
                Route::patch('/save-class-seen', [PurchasedCoursesController::class, 'saveClassSeen']);
                Route::get('/show-class-seen', [PurchasedCoursesController::class, 'showClassSeen']);
                Route::get('/completed-course', [PurchasedCoursesController::class, 'completedCourse']);
                Route::get('/certificate-data', [PurchasedCoursesController::class, 'certificateData']);
                Route::get('/get-time', [PurchasedCoursesController::class, 'getTime']);
            });

            Route::group(['prefix' => 'notifications'], function () {
                Route::get('/list', [NotificationController::class, 'myNotifications'])->name('list');
            });

            Route::group(['prefix' => 'aux'], function () {
                Route::get('/all-countries', [AuxController::class, 'allCountries']);
                Route::get('/get-states-by-country/{name_country}', [AuxController::class, 'getStatesByCountry']);
            });

            Route::group(['prefix' => '/frequent-questions'], function () {
                Route::get('/', [FrequentQuestionsController::class, 'getFrequentQuestion']);
            });

            //Api Rama Binaria
            Route::controller(RamaBinariaController::class)->prefix('ramabinaria')->group(function () {
                Route::get('/listbinary', 'listbinary');
            });

            // Api plantillas
            Route::apiResource('/templates', TemplateController::class);
            Route::apiResource('/edi-templates', EdiTemplateController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
            // LIST BONNUS
            Route::controller(BonusController::class)->prefix('bonus')->group(function () {
                Route::get('/get-bonuses', 'index');
            });
        });
    }
});

// RUTAS P脷BLICAS DE TEMPLATES (sin autenticaci贸n)
Route::prefix('v1')->group(function () {
    Route::apiResource('/templates', TemplateController::class)->only(['index', 'show']);
});