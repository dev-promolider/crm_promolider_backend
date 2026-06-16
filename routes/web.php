<?php
use App\Http\Controllers\Exam;
use App\Http\Controllers\GenerationalBonusController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamQuestion;
use App\Http\Controllers\EbookRegisterController;
use App\Http\Controllers\MinicourseRegisterController;
use App\Http\Controllers\MiniCourseController;
use App\Http\Controllers\MC\NotificationController as MCNotificationController;
use App\Http\Controllers\MiniCourseModuleController;
use App\Http\Controllers\MiniCourseDistributorController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\PaymentLinkController;
use App\Http\Controllers\DailyQuestion;
use App\Http\Controllers\PayController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CertificateTemplateController;
use App\Http\Controllers\CourseCertificateController;
use App\Http\Controllers\TestEmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\WalletPaymetMethodController;
use App\Http\Controllers\BinanceAccountController;
use App\Http\Controllers\PaypalAccountController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\NiubizController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\DinamicaController;
use App\Http\Controllers\DinamicaPublicController;
use App\Http\Controllers\QuestionCategoryController;
use App\Http\Controllers\QuestionItemController;
use App\Http\Controllers\ChatGptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BinaryCutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RankBonusController;
use App\Http\Controllers\ShareLinkController;
use App\Http\Controllers\UserLevelController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClassifiedController;
use App\Http\Controllers\CourseGameController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\GrowthBonusController;
use App\Http\Controllers\ModuleClassController;
use App\Http\Controllers\RamaBinariaController;
use App\Http\Controllers\RoleRequestController;
use App\Http\Controllers\UserRequestController;
use App\Http\Controllers\BinaryBranchController;
use App\Http\Controllers\CertificatesController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DefaultAvatarController;
use App\Http\Controllers\MiscellaneousController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\StartingBonusController;
use App\Http\Controllers\AdvertisementsController;
use App\Http\Controllers\UnverifiedUserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\UserExamHeaderController;
use App\Http\Controllers\PurchasedCourseController;
use App\Http\Controllers\WalletMovementsController;
use App\Http\Controllers\CourseGameDetailController;
use App\Http\Controllers\AccountTypeDetailController;
use App\Http\Controllers\Api\OPCController;
use App\Http\Controllers\FrequentQuestionsController;
use App\Http\Controllers\UnverifiedPaymentController;
use App\Http\Controllers\UserClassroomPointController;
use App\Http\Controllers\CourseConfigurationController;
use App\Http\Controllers\ClassroomPointConfigController;
use App\Http\Controllers\FreecourseController;
use App\Http\Controllers\Templates\TemplateController;
use App\Http\Controllers\Templates\UserTemplateController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MasterclassController;
use App\Http\Controllers\MasterclassRegisterController;
use App\Http\Controllers\EdiTemplateController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\AdminRewardController;
use App\Http\Controllers\Infoproduct\Book\BookController;
use App\Models\Chat;    
use App\Http\Controllers\PublicTriviaController;
use App\Http\Controllers\PreregistroController;



Route::get('/user/get-status', function () {
    if (Auth::check()) {
        return response()->json([
            'expiration_date' => Auth::user()->expiration_date
        ]);
    }
    return response()->json(['error' => 'Unauthenticated'], 401);
})->middleware('auth');

Broadcast::routes(['middleware' => 'auth:admin']);

Auth::routes(['verify' => true]);
Route::get('/preview', [UserController::class, 'preview']);
Route::post('login', [LoginController::class, 'login2'])->name('main-login');
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('redirect-login', [LoginController::class, 'redirectToLoginWithMessage'])->name('redirect-with-message');
Route::post('/openpay-order', [OptionController::class, 'openpayOrder']);
Route::post('/verify-email', [RegisterController::class, 'verifyEmail']);
Route::post('/verify-document', [RegisterController::class, 'verifyDocument']);
Route::post('/verify-username', [RegisterController::class, 'verifyUsername']);
Route::post('/unverified-user/create', [UnverifiedUserController::class, 'create']);
Route::post('/unverified-payment/create', [UnverifiedPaymentController::class, 'create']);
Route::post('/test-webhook', [PaymentController::class, 'openpayWebhookConfirm']);
// Ruta para validar link de registro (AJAX)
Route::post('/api/validate-registration-link', [ShareLinkController::class, 'validateRegistrationLink'])
    ->name('validate-registration-link');

Route::group(['prefix' => 'pay'], function () {
    
    // ==========================================
    // RUTAS DE PAGOS GENERALES (sin middlewares especiales)
    // ==========================================
    Route::post('/openpay', [PayController::class, 'payOpenpay'])->name('pay.openpay');
    Route::get('/membership', [PayController::class, 'viewMembershipPay'])->name('membership-view');
    Route::get('/membership/process/{ordenId}', [PayController::class, 'process'])->name('membership-process-paypal');
    Route::get('/membership-update/{membershipId}', [PayController::class, 'viewMembershipPayUpdate'])->name('membership-update-view');
    Route::get('/membership-update-process/{ordenId}/{membershipId}', [PayController::class, 'processUpdate'])->name('membership-update-process-paypal');
    Route::post('/membership-update-process-basic', [UserController::class, 'membershipUpdateBasic'])->name('membership-update-process-basic');
    Route::get('/recompra', [PayController::class, 'viewRecompra'])->name('recompra-view');
    Route::get('/opc-niubiz', [PayController::class, 'payOPC'])->name('opc-pay-niubiz');
    Route::get('/opc-openpay', [PayController::class, 'openpayOPC'])->name('opc-pay-openpay');
    Route::get('/membership-openpay/{id}', [PayController::class, 'openpayMembership']);
    Route::get('/recharge-openpay/{amount}/{type_payment}', [PayController::class, 'openpayRecharge']);
    Route::post('/course-openpay', [PayController::class, 'openpayCourse']);
    Route::post('/recharge', [PayController::class, 'rechargeOpenpayProcess']);
    Route::post('/register', [PayController::class, 'registerOpenpayProcess']);
    Route::get('/get-openpay-conditions', [PaymentController::class, 'getOpenpayConditions']);
    Route::get('/recompra-process/{ordenId}', [PayController::class, 'processRecompra'])->name('recompra-process-paypal');

   
    // ==========================================
    // RUTAS CRÍTICAS CON MIDDLEWARES ESPECÍFICOS
    // ==========================================
    Route::get('/payment-success', [PaymentLinkController::class, 'paymentSuccess'])
    ->name('payment.success');
    // Ruta OPC con protección contra spam/rate limiting
    Route::post('/opc-wallet', [PayController::class, 'payWallet'])
        ->middleware('opc.rate.limit')
        ->name('opc-wallet-payment');
    
    // Ruta de actualización de membresía con validaciones específicas
    Route::post('/membership-wallet', [PayController::class, 'payMembershipUpdate'])
        ->middleware('validate.membership.update')
        ->name('membership-wallet-payment');
});

Route::group(['prefix' => 'rank_bonus'], function () {
    Route::get('/list', [RankBonusController::class, 'index']);
    Route::put('/update', [RankBonusController::class, 'store']);
});

Route::group(['prefix' => 'generational_bonus'], function () {
    Route::get('/list', [GenerationalBonusController::class, 'index']);
    Route::put('/update', [GenerationalBonusController::class, 'store']);
});

Route::group(['prefix' => 'opc_config'], function () {
    Route::get('/list', [OPCController::class, 'index']);
    Route::put('/update', [OPCController::class, 'store']);
    Route::get('/get-current-price', [OPCController::class, 'getCurrentPrice']);
    Route::get('/get-expiration-date', [OPCController::class, 'getExpirationDate']);
});

Route::group(['prefix' => 'user'], function () {
    Route::get('/get-public-data/{name}', [UserController::class, 'getPublicUserData']);
    Route::get('/get-public-courses/{id}', [UserController::class, 'getPublicCourse']);
});

/* Rutas Programada - inicio */
/**
 * Todas las rutas establecidas deben de estar dentro de "Middleware Auth"
 */
Route::group(['middleware' => ['web', 'auth']], function () {

    // Premios disponibles
    Route::get('/rewards', [RewardController::class, 'index']);
    
    // Créditos del usuario
    Route::get('/rewards/credits', [RewardController::class, 'getCredits']);
    
    // Canjear premio
    Route::post('/rewards/redeem', [RewardController::class, 'redeem']);
    
    // Historial de canjes del usuario
    Route::get('/rewards/my-redemptions', [RewardController::class, 'myRedemptions']);

    Route::get('/ad/rewards', [AdminRewardController::class, 'index']);
    Route::post('/ad/rewards', [AdminRewardController::class, 'store']);
    Route::put('/ad/rewards/{id}', [AdminRewardController::class, 'update']);
    Route::delete('/ad/rewards/{id}', [AdminRewardController::class, 'destroy']);
    Route::post('/ad/rewards/{id}/restore', [AdminRewardController::class, 'restore']);
    
    // Estadísticas
    Route::get('/ad/rewards/stats', [AdminRewardController::class, 'stats']);
    
    // Gestión de canjes
    Route::get('/ad/rewards/redemptions', [AdminRewardController::class, 'redemptions']);
    Route::post('/ad/rewards/redemptions/{id}/process', [AdminRewardController::class, 'processRedemption']);

    // Ruta para ver documentos
    Route::get('/documents/{document}/view', [DocumentController::class, 'view'])
        ->name('documents.view');
    Route::get('/chats',[ChatGptController::class,'getChats']);
    Route::post('/chats',[ChatGptController::class,'createChat']);
    Route::get('/chats/{chatId}/details',[ChatGptController::class,'getChatDetails']);
    Route::delete('chats/{chatId}',[ChatGptController::class,'deleteChat']);

    Route::get('viewTree', [RamaBinariaController::class, 'getBinaryThreeData']);
    Route::get('binaryTreeLocal', [RamaBinariaController::class, 'getBinaryTreeFromLocalData']);
    Route::get('binaryTreeLocal/{id}', [RamaBinariaController::class, 'getBinaryTreeForUser']);
    Route::get('debug/binaryTree', [RamaBinariaController::class, 'debugBinaryTreeStructure']);
    Route::get('viewBinaryTree', [RamaBinariaController::class, 'getBinaryThreeDataInternal']);

    Route::view('uninivel', 'uninivel')->name('uninivel');
    Route::view('binario', 'binario')->name('binario');
    Route::get('listbinary', [RamaBinariaController::class, 'listbinary']);
    Route::get('mypointslog', [PointController::class, 'getPointsForUser'])->name('mypointslog');
    // Main Page Route
    Route::get('/', [DashboardController::class, 'dashboardEcommerce'])->name('menu-dashboard')->middleware('verified');

    /* Route Dashboards */
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('analytics', [DashboardController::class, 'dashboardAnalytics'])->name('dashboard-analytics');
        Route::get('ecommerce', [DashboardController::class, 'dashboardEcommerce'])->name('dashboard-ecommerce');
    });
    /* Route Dashboards */

    Route::get('/my-courses/{course}/certificate/download', [CourseCertificateController::class, 'downloadStudentCertificate']);
    Route::get('/my-courses/{course}/certificate', [CourseCertificateController::class, 'getStudentCertificate']);

    Route::get('certificate-templates', [CertificateTemplateController::class, 'index'])
        ->name('certificate-templates.index');

    // Crear plantilla
    Route::post('certificate-templates', [CertificateTemplateController::class, 'store'])
        ->name('certificate-templates.store');

    // Actualizar plantilla
    Route::put('certificate-templates/{template}', [CertificateTemplateController::class, 'update'])
        ->name('certificate-templates.update');

    Route::apiResource('course-certificates', CourseCertificateController::class);

    Route::post('/instructor-signature/{course}', [CourseCertificateController::class, 'uploadInstructorSignature']);
    
    // Rutas personalizadas
    Route::post('course-certificates/{certificate}/issue', [CourseCertificateController::class, 'issue'])
        ->name('course-certificates.issue');
    
    Route::get('course-certificates/download/{certificateCode}', [CourseCertificateController::class, 'download'])
        ->name('course-certificates.download');
    
    Route::post('course-certificates/bulk-generate', [CourseCertificateController::class, 'bulkGenerate'])
        ->name('course-certificates.bulk-generate');

    Route::get('course-certificates/{certificate}/preview', [CourseCertificateController::class, 'preview'])
        ->name('course-certificates.preview');

    /* Route Setting */
    Route::group(['prefix' => 'setting'], function () {
        Route::get('profile', [UserController::class, 'viewProfileSetting'])->name('setting-profile');
        Route::get('countrys', [UserController::class, 'getCountries'])->name('setting-country');
        Route::post('update-notifications', [NotificationController::class, 'update'])->name('update-notifications');
    });

    /*Router roles*/
    Route::group(['prefix' => 'roles'], function () {
        Route::get('currentRole', [RoleController::class, 'roleCurrentUser'])->name('current-role');
        Route::get('users/distributor', [RoleController::class, 'usersDistributor'])
            ->name('roles.users.distributor');
    });

    Route::group(['prefix' => 'mc'], function () {
        // Notificaciones y videos de Master Class
        Route::get('notifications', [MCNotificationController::class, 'list']);
        Route::put('notifications/{id}', [MCNotificationController::class, 'markAsSeen']);
    });

    Route::group(['prefix' => 'system'], function () {
        Route::get('preregistro', function () {
            return view('content.preregistro.share-link');
        })->middleware('can:preregistro')->name('preregistro');
        Route::get('binary-branch', [BinaryBranchController::class, 'binary_branch'])->name('binary-branch');
    });


    Route::group(['prefix' => 'payment'], function () {
        Route::get('/', [PaymentController::class, 'index'])->name('payment');
        Route::get('/list', [PaymentController::class, 'List'])->name('payment-list');
        Route::get('/get-all', [PaymentController::class, 'getAll']);
        Route::get('/get-all-user/{id}', [PaymentController::class, 'getAllUser']);


        // ==========================================
        // RUTAS DE METODOS DE PAGOS
        // ==========================================
        Route::get('/payment-methods', [PaymentMethodController::class, 'paymentAccounts']);
        Route::get('/payment-methods/types', [PaymentMethodController::class, 'getAvailableTypes']);

        // ========================================
        // RUTAS DE BINANCE (CRUD Completo)
        // ========================================
        Route::prefix('binance-accounts')->name('binance-accounts.')->group(function () {
            Route::get('/', [BinanceAccountController::class, 'index'])->name('index');          // GET /binance-accounts
            Route::post('/', [BinanceAccountController::class, 'store'])->name('store');        // POST /binance-accounts
            Route::get('/{id}', [BinanceAccountController::class, 'show'])->name('show');       // GET /binance-accounts/{id}
            Route::put('/{id}', [BinanceAccountController::class, 'update'])->name('update');   // PUT /binance-accounts/{id}
            Route::patch('/{id}', [BinanceAccountController::class, 'update'])->name('patch');  // PATCH /binance-accounts/{id}
            Route::delete('/{id}', [BinanceAccountController::class, 'destroy'])->name('destroy'); // DELETE /binance-accounts/{id}
        });

        // ========================================
        // RUTAS DE PAYPAL (CRUD Completo)
        // ========================================
        Route::prefix('paypal-accounts')->name('paypal-accounts.')->group(function () {
            Route::get('/', [PaypalAccountController::class, 'index'])->name('index');          // GET /paypal-accounts
            Route::post('/', [PaypalAccountController::class, 'store'])->name('store');        // POST /paypal-accounts
            Route::get('/{id}', [PaypalAccountController::class, 'show'])->name('show');       // GET /paypal-accounts/{id}
            Route::put('/{id}', [PaypalAccountController::class, 'update'])->name('update');   // PUT /paypal-accounts/{id}
            Route::patch('/{id}', [PaypalAccountController::class, 'update'])->name('patch');  // PATCH /paypal-accounts/{id}
            Route::delete('/{id}', [PaypalAccountController::class, 'destroy'])->name('destroy'); // DELETE /paypal-accounts/{id}
        });

    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/credits', [UserController::class, 'getMyCredits']);
        Route::get('/list', [UserController::class, 'list'])->name('users-list');
        Route::get('/check-opc', [UserController::class, 'getOpcStatus'])->name('users-opc');
        Route::post('/update-user', [UserController::class, 'updateUser'])->name('update-user');
        Route::post('/update-password', [UserController::class, 'updatePassword'])->name('update-password');
        Route::get('/sha/{purchase_operation_number}/{purchase_amount}', [UserController::class, 'credentials']);
        Route::get('/get-data-user/{name}', [UserController::class, 'getDataUser']);
        Route::get('/get-data-user-id/{id}', [UserController::class, 'getDataUserId']);
        Route::get('/get-data-currentuser', [UserController::class, 'getDataCurrentUser']);
        Route::post('/change-position-currentuser', [UserController::class, 'changePositionCurrentUser']);
        /*Start api users*/
        Route::get('/api', [BinaryBranchController::class, 'getListUsersMembreship'])
            ->name('getListUsersMembreship');
        Route::get('/api/my-directs', [BinaryBranchController::class, 'getMyDirects']);
        Route::get('/api/list', [UserController::class, 'GetList'])
            ->name('GetList');
        Route::get('get-bonuses', [BonusController::class, 'index']);
        Route::post('/change-role', [RoleRequestController::class, 'changeRole']);
        Route::post('/change-role-tools', [RoleRequestController::class, 'changeRoleTools']);
        /*End api users*/
    });

    //account types Routes
    Route::group(['prefix' => '/account-type'], function () {
        //view
        Route::get('get-data-id/{id}', [AccountTypeController::class, 'getDataBytId']);
        Route::get('/', [AccountTypeController::class, 'retornarVista'])->name('account-type');
        //api
        Route::apiResource('accountType', AccountTypeController::class);
        //history membership of user
        Route::get('/detail/membership-history', [AccountTypeDetailController::class, 'getHistoryOfUserMembership']);
    });

    //account types Routes
    Route::group(['prefix' => '/starting-bonus'], function () {
        //view
        Route::get('/', [StartingBonusController::class, 'retornarVista'])->name('starting-bonus');
        //api
        Route::apiResource('startingBonus', StartingBonusController::class)->except(['update']);
    });

    Route::group(['prefix' => 'user-levels'], function () {
        Route::get('/', [UserLevelController::class, 'index'])->name('user-levels-index');
        Route::get('/list', [UserLevelController::class, 'showAll'])->name("showAll");
        Route::post('/createUpdate', [UserLevelController::class, 'createUpdate'])->name('createUpdate');
        Route::get('/list-my-info', [UserLevelController::class, 'getInfoUser'])->name('list-badges');
        Route::get('/num-ranking', [UserClassroomPointController::class, 'getPosicionRanking'])->name('n_ranking');
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'create'])->name('index');
        Route::get('/list', [NotificationController::class, 'myNotifications'])->name('notifications-list');
    });

    Route::group(['prefix' => 'badges'], function () {
        Route::get('/', [BadgeController::class, 'create'])->name('create-badge');
        Route::get('/list-all', [BadgeController::class, 'getAll'])->name("badges-list");
        Route::post('/store', [BadgeController::class, 'store'])->name('store-badge');
        Route::get('/my-badges', [BadgeController::class, 'myBadges'])->name('my-badges');
        Route::get('/get-my-badges', [BadgeController::class, 'getMyBadges'])->name('get-my-badges');
        Route::post('/update', [BadgeController::class, 'update'])->name('update');
    });

    //account types Routes
    Route::group(['prefix' => '/growth-bonus'], function () {
        //view
        Route::get('/', [GrowthBonusController::class, 'retornarVista'])->name('growth-bonus');
        //api
        Route::apiResource('growthBonus', GrowthBonusController::class)->except(['update']);
    });

    Route::group(['prefix' => 'config'], function () {
        Route::resource('binarycut', BinaryCutController::class)->only(['index', 'store']);
    });

    Route::prefix('binary-cut')->group(function () {
        Route::get('/', [BinaryCutController::class, 'index'])->name('binary-cut.index');
        Route::post('/', [BinaryCutController::class, 'store'])->name('binary-cut.store');
        Route::get('/history', [BinaryCutController::class, 'history'])->name('binarycut.history');
        Route::get('/history/{user}', [BinaryCutController::class, 'userHistory'])->name('binarycut.user-history');
    });

    //Admin
    Route::group(['prefix' => 'config/certificates'], function () {
        Route::get('/', [CertificatesController::class, 'index'])->name('configuration-certificates');
        Route::get('/list', [CertificatesController::class, 'showAll']);
        Route::post('/add', [CertificatesController::class, 'addCertificate']);
        Route::delete('/{id}', [CertificatesController::class, 'destroyCertificate']);
    });

    Route::group(['prefix' => 'config/settings'], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('config-settings');
        Route::get('/points', [SettingsController::class, 'getPoints']);
        Route::post('/points', [SettingsController::class, 'savePoints']);
    });

    //Productor
    Route::group(['prefix' => 'certificates'], function () {
        Route::post('/save', [CertificatesController::class, 'saveConfigCertificate']);
    });

    Route::group(['prefix' => 'config/frequent-questions'], function () {
        Route::get('/', [FrequentQuestionsController::class, 'index'])->name('frequent-questions');
        Route::get('/list', [FrequentQuestionsController::class, 'showAll']);
        Route::post('/add', [FrequentQuestionsController::class, 'store']);
        Route::post('/update', [FrequentQuestionsController::class, 'update']);
        Route::post('/status', [FrequentQuestionsController::class, 'changeStatus']);
        Route::delete('/{id}', [FrequentQuestionsController::class, 'destroy']);
    });

    Route::group(['prefix' => 'config/bank'], function () {
        Route::get('/', [BankController::class, 'index'])->name('bank');
        /*Start api config bank*/
        Route::get('/detail/{id}', [BankController::class, 'Detail'])->name('bank-detail');
        Route::get('/list', [BankController::class, 'List'])->name('bank-lit');
        Route::post('/add', [BankController::class, 'Add'])->name('bank-add');
        Route::post('/edit', [BankController::class, 'Edit'])->name('Edit');
        Route::delete('/delete/{id}', [BankController::class, 'Delete'])->name('Delete');
        /*End api config bank*/
    });

    Route::group(['prefix' => 'config/advertisements'], function () {
        Route::get('/', [AdvertisementsController::class, 'index'])->name('advertisements');
        /*Start api config messages*/
        Route::get('/detail/{id}', [AdvertisementsController::class, 'Detail'])->name('advertisements-detail');
        Route::get('/list', [AdvertisementsController::class, 'List'])->name('advertisements-list');
        Route::post('/add', [AdvertisementsController::class, 'Add'])->name('advertisements-add');
        Route::put('/edit/{id}', [AdvertisementsController::class, 'Edit'])->name('advertisements-edit');
        Route::delete('/delete/{id}', [AdvertisementsController::class, 'Delete'])->name('advertisements-delete');
        /*End api config messages*/
    });

    Route::group(['prefix' => 'config/payment-method'], function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('payment-method');
        /*Start api config payment-method*/
        Route::get('/detail/{id}', [PaymentMethodController::class, 'Detail'])->name('payment-method-detail');
        Route::get('/list', [PaymentMethodController::class, 'List']);
        Route::get('/list-array', [PaymentMethodController::class, 'listPaymentMethods'])->name('payment-method-list-array-web');
        Route::post('/add', [PaymentMethodController::class, 'Add'])->name('payment-method-add');
        Route::put('/edit/{id}', [PaymentMethodController::class, 'Edit'])->name('payment-method-edit');
        Route::delete('/delete/{id}', [PaymentMethodController::class, 'Delete'])->name('payment-method-delete');
        /*End api config payment-method*/
    });

    // User Request    
    Route::group(['prefix' => 'config/user-request'], function () {
        Route::view('/', 'content.config.user_request')->middleware('can:new-users')->name('user-request');
        Route::get('/list', [UserRequestController::class, 'index']);
        Route::get('/get-user-by-id/{id}', [UserRequestController::class, 'getUserById']);
        Route::post('/update-unverified-request', [UserRequestController::class, 'updateUnverifiedRequest']);
    });

    Route::group(['prefix' => 'config/role-request'], function () {
        Route::view('/', 'content.config.role-request');
        Route::get('/list', [UserController::class, 'listUserRoleRequest']);
        Route::get('/get', [RoleRequestController::class, 'getRoleRequest']);
        Route::patch('/confirm-change', [RoleRequestController::class, 'confirmChange']);
        Route::patch('/reject-change', [RoleRequestController::class, 'rejectChange']);
    });

    Route::group(['prefix' => 'config/role-request-tools'], function () {
        Route::view('/', 'content.config.role-request-tools');
        Route::get('/list', [UserController::class, 'listUserRoleToolRequest']);
        Route::get('/get', [RoleRequestController::class, 'getRoleToolsRequest']);
        Route::patch('/confirm-change', [RoleRequestController::class, 'confirmToolChange']);
        Route::patch('/reject-change', [RoleRequestController::class, 'rejectToolChange']);
    });

    Route::group(['prefix' => 'config/category'], function () {
        Route::view('/', 'content.category.index')->name('category-index');
        Route::view('/create', 'content.category.create')->name('category.create');
        Route::post('/store', [CategoryController::class, 'store'])->name('category.store');
        Route::post('/update', [CategoryController::class, 'update'])->name('category.update');
    });

    //CONFIG CLASSROOM POINTS
    Route::group(['prefix' => 'config/classroom-point-config'], function () {
        Route::view('/', 'content.config.classroom-point-config');
        Route::get('/list', [ClassroomPointConfigController::class, 'show'])->name('classroom-point-config');
        Route::get('/{classroomPointConfig}/edit', [ClassroomPointConfigController::class, 'edit']);
        Route::post('/{id}/update', [ClassroomPointConfigController::class, 'update']);
    });

    //CONFIG OPTIONS
    Route::group(['prefix' => 'config/option'], function () {
        Route::get('/list', [OptionController::class, 'show'])->name('option');
        Route::post('/update', [OptionController::class, 'update']);
        Route::get('/avatars/list', [DefaultAvatarController::class, 'show']);
        Route::post('/avatars/add', [DefaultAvatarController::class, 'store']);
        Route::delete('/avatars/delete/{id}', [DefaultAvatarController::class, 'delete']);
    });

    Route::group(['prefix' => 'config/share-link'], function () {
        //view
        Route::view('/', 'content.config.share-link')->name('config.share-link');

        // Asegúrate de que estas rutas estáticas estén antes de las rutas dinámicas
        Route::post('/add', [ShareLinkController::class, 'Add'])->name('share-link-add');
        Route::put('/edit/{id}', [ShareLinkController::class, 'Edit'])->name('share-link-edit');
        Route::delete('/delete/{id}', [ShareLinkController::class, 'Delete'])->name('share-link-delete');
        Route::get('/referrals/{username}', [ShareLinkController::class, 'referralsByUsername'])->name('share-link-referrals');

        // La ruta dinámica debe ir después de las rutas estáticas
        Route::get('/{username}', [ShareLinkController::class, 'retornarVista'])->name('share-link-detail');

        //api
        Route::apiResource('shareLink', ShareLinkController::class)->except(['update']);
    });

    Route::group(['prefix' => 'marketing'], function () {
        Route::get('/campaigns/list', [MarketingController::class, 'getCampaigns'])
            ->name('marketing.campaigns.list');        
        Route::get('/campaigns/{type}', [MarketingController::class, 'getCampaignsByType'])
            ->name('marketing.campaigns.type')
            ->where('type', 'minicurso|ebook|masterclass');
        // Generales
        Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');
        Route::get('/activities/{id}', [MarketingController::class, 'getActivities']);
        Route::get('/calendar', [MarketingController::class, 'calendar'])->name('marketing.calendar')->middleware('can:marketing.calendar');
        Route::get('/calendar-admin', [MarketingController::class, 'calendarAdmin']);
        Route::get('/calendar-distributor/{id}', [MarketingController::class, 'calendarDistributor']);
        Route::get('/calendar-producer/{id}', [MarketingController::class, 'calendarProducer']);
        Route::get('/categories', [MarketingController::class, 'getCategories'])->name('marketing.categories');
        Route::post('/create-meeting', [MarketingController::class, 'createMeeting']);
        Route::get('/pages', [MarketingController::class, 'pages'])->name('marketing.pages');
        Route::get('/payments_link', [PaymentLinkController::class, 'index'])->name('marketing.payments_link');
        Route::get('/report', [MarketingController::class, 'report'])->name('marketing.report');
        Route::get('/my_report', [MarketingController::class, 'myReports'])->name('marketing.my_reports');
        Route::get('/general_report', [MarketingController::class, 'generalReports'])->name('marketing.general_reports');
        Route::get('/tools', [MarketingController::class, 'tools'])->name('marketing.tools');
        Route::get('/tools/list', [MarketingController::class, 'getAllTools'])->name('marketing.tools.list');
        Route::get('/report-content-by-status', [MarketingController::class, 'reportContentByStatus']);
        Route::get('/report-content-producer', [MarketingController::class, 'reportContentByProducer']);
        Route::get('/report-all-private-content', [MarketingController::class, 'reportAllPrivateContent']);
        Route::get('/report-private-content-by-producer/{id}', [MarketingController::class, 'reportPrivateContentByProducer']);
        Route::get('/report-private-masterclass-students/{id}', [MarketingController::class, 'reportPrivateMasterclassStudents']);
        Route::get('/report-private-minicourse-students/{id}', [MarketingController::class, 'reportPrivateMiniCourseStudents']);
        Route::get('/report-private-ebook-students/{id}', [MarketingController::class, 'reportPrivateEbookStudents']);

        // Admin Payment Links
        Route::get('/admin/payment-links', [PaymentLinkController::class, 'index'])->name('payment-links.index');
        Route::get('/admin/payment-links/create', [PaymentLinkController::class, 'create'])->name('marketing.payments-link.create');
        Route::post('/admin/payment-links', [PaymentLinkController::class, 'store'])->name('payment-links.store');
        Route::patch('/admin/payment-links/{paymentLink}/toggle', [PaymentLinkController::class, 'toggle'])->name('payment-links.toggle');
        Route::delete('/admin/payment-links/{paymentLink}', [PaymentLinkController::class, 'destroy'])->name('payment-links.destroy');

        // Marketplace
        Route::get('marketplace', [MarketingController::class, 'marketplace'])->name('marketing.marketplace');
        Route::get('/marketplaceindex', [MarketingController::class, 'marketplaceIndex'])->name('marketing.masterclass.index');

        // Marketplace - Masterclass
        Route::get('marketplace/masterclass/list', [MarketingController::class, 'masterclassList'])->name('marketing.marketplace.list');
        Route::get('marketplace/masterclasses-paginated', [MarketingController::class, 'masterclassesPaginated']);
        Route::get('/{id}/masterclass', [MarketingController::class, 'masterclassDetails'])->name('marketing.masterclass.details');

        // Marketplace - EBooks
        Route::get('marketplace/ebooks/list', [MarketingController::class, 'ebooksList'])->name('marketing.marketplace.ebooks.list');
        Route::get('marketplace/ebook-paginated', [MarketingController::class, 'ebooksPaginated']);
        Route::get('/{id}/ebook', [MarketingController::class, 'ebookDetails'])->name('ebook.details');

        // Marketplace - Minicursos
        Route::get('marketplace/minicourses/list', [MarketingController::class, 'miniCoursesList'])->name('marketing.marketplace.minicourses.list');
        Route::get('marketplace/minicursos-paginated', [MarketingController::class, 'miniCoursesPaginated']);
        Route::get('/{id}/minicourse', [MarketingController::class, 'miniCourseDetails'])->name('minicourse.details');

        Route::get('/masterclass/distributors-data/{id}', [MarketingController::class, 'listMasterclassDistributors'])
            ->name('marketing.masterclass.distributors-data');

        Route::get('/minicourse/distributors-data/{id}', [MarketingController::class, 'listMiniCourseDistributors'])
            ->name('marketing.minicourse.distributors-data');

        Route::get('/ebook/distributors-data/{id}', [MarketingController::class, 'listEbookDistributors'])
            ->name('marketing.ebook.distributors-data');
        
        // Reportes - Masterclass
        Route::get('/report-admin-m', [MarketingController::class, 'reportMasterclassAdmin_M']);
        Route::get('/report-admin-d', [MarketingController::class, 'reportMasterclassAdmin_D']);
        Route::get('/report-producer-m/{id}', [MarketingController::class, 'reportMasterclassProducer_M']);
        Route::get('/report-producer-d/{id}', [MarketingController::class, 'reportMasterclassProducer_D']);
        Route::get('report-distributor/{id}', [MarketingController::class, 'reportMasterclassDistributor']);

        // Reportes - MiniCursos
        Route::get('/report-minicourse-admin-m', [MarketingController::class, 'reportMiniCourseAdmin_M']);
        Route::get('/report-minicourse-admin-d', [MarketingController::class, 'reportMiniCourseAdmin_D']);
        Route::get('/report-minicourse-producer-m/{id}', [MarketingController::class, 'reportMiniCourseProducer_M']);
        Route::get('/report-minicourse-producer-d/{id}', [MarketingController::class, 'reportMiniCourseProducer_D']);
        Route::get('/report-minicourse-distributor/{id}', [MarketingController::class, 'reportMiniCourseDistributor']);

        // Reportes - Ebooks
        Route::get('/report-ebook-admin-m', [MarketingController::class, 'reportEbookAdmin_M']);
        Route::get('/report-ebook-admin-d', [MarketingController::class, 'reportEbookAdmin_D']);
        Route::get('/report-ebook-producer-m/{id}', [MarketingController::class, 'reportEbookProducer_M']);
        Route::get('/report-ebook-producer-d/{id}', [MarketingController::class, 'reportEbookProducer_D']);
        Route::get('/report-ebook-distributor/{id}', [MarketingController::class, 'reportEbookDistributor']);

        // Listado de estudiantes
        Route::get('/masterclass/lista-estudiantes/{id}', function ($id) {
            $user = auth()->user();
            return view('content.masterclass.students', compact('user', 'id'))->with('contentType', 'masterclass');
        });
        Route::get('/minicourse/lista-estudiantes/{id}', function ($id) {
            $user = auth()->user();
            return view('content.masterclass.students', compact('user', 'id'))->with('contentType', 'minicourse');
        });
        Route::get('/ebook/lista-estudiantes/{id}', function ($id) {
            $user = auth()->user();
            return view('content.masterclass.students', compact('user', 'id'))->with('contentType', 'ebook');
        });

        // Listado de distribuidores
        Route::get('/masterclass/list-distributors/{id}', function ($id) {
            $user = auth()->user();
            return view('content.marketing.distributors', compact('user', 'id'))->with('contentType', 'masterclass');
        });
        Route::get('/minicourse/list-distributors/{id}', function ($id) {
            $user = auth()->user();
            return view('content.marketing.distributors', compact('user', 'id'))->with('contentType', 'minicourse');
        });
        Route::get('/ebook/list-distributors/{id}', function ($id) {
            $user = auth()->user();
            return view('content.marketing.distributors', compact('user', 'id'))->with('contentType', 'ebook');
        });

        Route::get('/verify-ownership/{type}/{id}', [MarketingController::class, 'verifyToolOwnership'])
            ->name('marketing.verifyOwnership')
            ->where(['type' => 'masterclass|minicourse|ebook', 'id' => '[0-9]+']);

        Route::post('/validate-distributor', [MarketingController::class, 'validateDistributor']);
        Route::get('/validate-access/{content}/{type}', [MarketingController::class, 'canViewStudentsByEmail']);

        Route::get('/{id}/list-students', [MarketingController::class, 'listMasterclassStudents']);
        Route::get('/{id}/list-students/ebook', [MarketingController::class, 'listEbookStudents']);
        Route::get('/{id}/list-students/minicourse', [MarketingController::class, 'listMinicourseStudents']);
        Route::get('/{id}/pending-students/{type?}', [MarketingController::class, 'getPendingParticipants']);

        Route::get('/students-list', [MarketingController::class, 'getStudentsList']);
        Route::get('/participants/all/{isParticipant?}', [MarketingController::class, 'getAllPendingParticipantsByUser']);

        // Mini Cursos
        Route::prefix('mini-course')->group(function () {
            Route::get('/create', [MiniCourseController::class, 'create'])->name('marketing.mini-course.create');
            Route::post('/store', [MiniCourseController::class, 'store'])->name('marketing.mini-course.store');
            Route::get('/{id}', [MiniCourseController::class, 'show'])->name('marketing.mini-course.show');
            Route::put('/{id}', [MiniCourseController::class, 'update'])->name('marketing.mini-course.update');
            Route::delete('/{id}', [MiniCourseController::class, 'destroy'])->name('marketing.mini-course.destroy');
            Route::patch('/participants/{user_id}', [MinicourseRegisterController::class, 'updateParticipantStatus']);
            Route::patch('/{id}/status', [MiniCourseController::class, 'updateStatus'])->name('mini-course.updateStatus');

            Route::get('/{id}/add-module', [MiniCourseModuleController::class, 'create'])->name('marketing.mini-course.add-module');
            Route::get('/{id}/module', [MiniCourseController::class, 'viewMiniCourse'])->name('marketing.mini-course.module');
            Route::get('/{id}/modules', [MiniCourseController::class, 'viewModules'])->name('marketing.mini-course.modules');
            Route::get('/{id}/modules/summary', [MiniCourseModuleController::class, 'getModulesByMiniCourse']);
            Route::get('/user/{id}', [MiniCourseController::class, 'getByUser'])->name('marketing.mini-course.by-user');

            Route::post('/{id}/module/basic', [MiniCourseModuleController::class, 'storeBasic'])->name('marketing.mini-course.store-basic-module');
            Route::post('/{id}/modules/store', [MiniCourseModuleController::class, 'storeModules'])->name('marketing.mini-course.store-modules');
            Route::post('/{miniCourseId}/{moduleId}/classes', [MiniCourseModuleController::class, 'addClasses'])->name('marketing.mini-course.add-classes');
            Route::put('/{miniCourseId}/{moduleId}/module', [MiniCourseModuleController::class, 'update'])->name('marketing.mini-course.update-module');
            Route::delete('/{miniCourseId}/{moduleId}/module/delete', [MiniCourseModuleController::class, 'destroy'])->name('marketing.mini-course.delete-module');

            Route::prefix('invitation')->group(function () {
                Route::post('/purchase/{id}', function ($id) {
                    \Log::info("🚀 Ingresó a la ruta: /invitation/purchase/{$id}");
                    return app(\App\Http\Controllers\MiniCourseDistributorController::class)->purchase($id);
                })->name('marketing.mini-course.purchase');
            
                Route::get('/check-purchase/{id}', function ($id) {
                    \Log::info("🚀 Ingresó a la ruta: /invitation/check-purchase/{$id}");
                    return app(\App\Http\Controllers\MiniCourseDistributorController::class)->checkPurchase($id);
                })->name('marketing.mini-course.check-purchase');
            
                Route::post('/invitation-link/{id}', function ($id) {
                    \Log::info("🚀 Ingresó a la ruta: /invitation/invitation-link/{$id}");
                    return app(\App\Http\Controllers\MiniCourseDistributorController::class)->createInvitationLink($id);
                })->name('marketing.mini-course.create-invitation-link');
            
                Route::get('/check-invitation/{id}', function ($id) {
                    \Log::info("🚀 Ingresó a la ruta: /invitation/check-invitation/{$id}");
                    return app(\App\Http\Controllers\MiniCourseDistributorController::class)->checkInvitation($id);
                })->name('marketing.mini-course.check-invitation');
            });

        });

        // Pages
        Route::prefix('pages')->group(function () {
            Route::post('/create/edit-template', [EdiTemplateController::class, 'store'])->name('marketing.pages.store');
            Route::get('/edit-templates', [EdiTemplateController::class, 'index'])->name('marketing.pages.index');
            Route::get('/edit-templates/{id}', [EdiTemplateController::class, 'show'])->name('marketing.pages.show');
            Route::put('/edit-templates/{editemplate}', [EdiTemplateController::class, 'update'])->name('marketing.pages.update');
            Route::delete('/edit-templates/{editemplate}', [EdiTemplateController::class, 'destroy'])->name('marketing.pages.destroy');
            Route::get('/user/{userId}/edit-templates', [EdiTemplateController::class, 'getUserTemplates'])->name('marketing.pages.userTemplates');
        });

        // Ebooks
        Route::prefix('ebook')->group(function () {
            Route::get('/create', [EbookController::class, 'create'])->name('marketing.ebook.create');
            Route::post('/store', [EbookController::class, 'store'])->name('marketing.ebook.store');
            Route::get('/{id}', [EbookController::class, 'show'])->name('marketing.ebook.show');
            Route::put('/{id}', [EbookController::class, 'update'])->name('marketing.ebook.update');
            Route::delete('/{id}', [EbookController::class, 'destroy'])->name('marketing.ebook.destroy');
            Route::get('/user/{id}', [EbookController::class, 'getByUser'])->name('marketing.ebook.by-user');
            Route::patch('/{id}/status', [EbookController::class, 'updateStatus'])->name('ebooks.updateStatus');


            // Invitaciones
            Route::post('/purchase/{id}', [EbookController::class, 'purchase'])->name('marketing.ebook.purchase');
            Route::get('/check-purchase/{id}', [EbookController::class, 'checkPurchase'])->name('marketing.ebook.check-purchase');
            Route::post('/invitation-link/{id}', [EbookController::class, 'createInvitationLink'])->name('marketing.ebook.create-invitation-link');
            Route::get('/check-invitation/{id}', [EbookController::class, 'checkInvitation'])->name('marketing.ebook.check-invitation');

            Route::patch('/participants/{user_id}', [EbookRegisterController::class, 'updateParticipantStatus']);
        });

        // Dinámicas
        Route::prefix('dinamica')->group(function () {
            Route::get('/create', [DinamicaController::class, 'create'])->name('marketing.dinamica.create');
            Route::get('/trivia', [DinamicaController::class, 'showTriviaDesigner'])->name('marketing.dinamica.trivia');
            Route::post('/trivia', [DinamicaController::class, 'storeTrivia'])->name('marketing.dinamica.trivia.store');
            Route::get('/trivia/create', [DinamicaController::class, 'showTriviaDesigner'])->name('marketing.dinamica.trivia.create');
            Route::get('/trivia/{trivia}/edit', [DinamicaController::class, 'editTrivia'])->name('marketing.dinamica.trivia.edit');
            Route::prefix('trivia/question-categories')->name('marketing.dinamica.trivia.categories.')->group(function () {
                Route::get('/', [QuestionCategoryController::class, 'index'])->name('index');
                Route::get('/create', [QuestionCategoryController::class, 'create'])->name('create');
                Route::post('/', [QuestionCategoryController::class, 'store'])->name('store');
                Route::get('/{category}', [QuestionCategoryController::class, 'show'])->name('show');
                Route::get('/{category}/edit', [QuestionCategoryController::class, 'edit'])->name('edit');
                Route::match(['put', 'patch'], '/{category}', [QuestionCategoryController::class, 'update'])->name('update');
                Route::patch('/{category}/toggle', [QuestionCategoryController::class, 'toggle'])->name('toggle');
                Route::prefix('{category}/questions')->name('questions.')->group(function () {
                    Route::get('/create', [QuestionItemController::class, 'create'])->name('create');
                    Route::post('/', [QuestionItemController::class, 'store'])->name('store');
                    Route::get('/{question}/edit', [QuestionItemController::class, 'edit'])->name('edit');
                    Route::match(['put', 'patch'], '/{question}', [QuestionItemController::class, 'update'])->name('update');
                    Route::delete('/{question}', [QuestionItemController::class, 'destroy'])->name('destroy');
                });
            });
            Route::get('/specifications', [DinamicaController::class, 'createSpecifications'])->name('marketing.dinamica.specifications');
            Route::post('/specifications', [DinamicaController::class, 'storeSpecifications'])->name('marketing.dinamica.store');
            Route::patch('/{id}/toggle', [DinamicaController::class, 'toggleStatus'])->name('marketing.dinamica.toggle');
            Route::delete('/{id}', [DinamicaController::class, 'destroy'])->name('marketing.dinamica.destroy');
        });

        // Notas del calendario
        Route::group(['prefix' => 'calendar'], function () {
            Route::get('/notes', [MarketingController::class, 'getNotes'])->middleware('auth');
            Route::post('/notes', [MarketingController::class, 'createNote'])->middleware('auth');
            Route::post('/sync-notes', [MarketingController::class, 'syncNotes'])->middleware('auth');
            Route::put('/notes/{id}', [MarketingController::class, 'updateNote'])->middleware('auth');
            Route::delete('/notes/{id}', [MarketingController::class, 'deleteNote'])->middleware('auth');
        });
    });

    //----------------------- MASTERCLASS
    Route::group(['prefix' => 'masterclass'], function (){
        Route::get('/', [MasterclassController::class, 'index'])->name('masterclass.index');
        Route::get('/{id}/masterclassList', [MasterclassController::class, 'masterclassList'])->name('masterclass.list');
        Route::get('/create', [MasterclassController::class, 'create'])->name('masterclass.create');
        Route::post('/store-masterclass', [MasterclassController::class, 'storeMasterclass']);
        Route::get('/marketplace', [MasterclassController::class, 'marketplace'])->name('masterclass.marketplace');
        Route::get('/masterclassCard', [MasterclassController::class, 'masterclassCard'])->name('masterclass.masterclassCard');
        Route::get('/{id}/masterclass', [MasterclassController::class, 'details'])->name('masterclass.details');
        Route::get('{id}', [MasterclassController::class, 'show']);
        Route::post('/create-invitation/{id_masterclass}', [MasterclassController::class, 'createInvitationLink']);
        Route::post('/register-masterclass/{id_masterclass}', [MasterclassController::class, 'registerMasterclass']);
        Route::get('/check-registration/{id_masterclass}', [MasterclassController::class, 'checkRegistration']);
        Route::get('/check-invitation/{id_masterclass}', [MasterclassController::class, 'checkInvitation']);
        Route::patch('/{id}/status', [MasterclassController::class, 'updateStatus'])->name('masterclass.updateStatus');
        Route::delete('/delete/{id}', [MasterclassController::class, 'delete']);
        Route::post('/{id}/update', [MasterclassController::class, 'update'])->name('masterclass.update');
        Route::get('/report', [MasterclassController::class, 'report'])->name('masterclass.report');
        Route::get('report-admin-m', [MasterclassController::class, 'reportAdmin_M']);
        Route::get('/report-admin-d', [MasterclassController::class, 'reportAdmin_D']);
        Route::get('/report-producer-m/{id}', [MasterclassController::class, 'reportProducer_M']);
        Route::get('/report-producer-d/{id}', [MasterclassController::class, 'reportProducer_D']);
        Route::get('report-distributor/{id}',[MasterclassController::class, 'reportDistributor']);
        Route::get('/calendar', [MasterclassController::class, 'calendar'])->name('masterclass.calendar');
        Route::get('/calendar-admin', [MasterclassController::class, 'calendarAdmin'])->name('masterclass.calendar-admin');
        Route::get('/calendar-producer/{id]', [MasterclassController::class, 'calendarProducer'])->name('masterclass.calendar-producer');
        Route::get('/calendar-distributor/{id}', [MasterclassController::class, 'calendarDistributor'])->name('masterclass.calendar-distributor');
        Route::get('{id}/participants', [MasterclassController::class, 'getParticipants'])->name('masterclass.participants');
        Route::post('/create-meeting', [MasterclassController::class, 'createMeeting']);
        Route::get('/{id}/list-students', [MasterclassController::class, 'listStudents'])->name('masterclass.list-students');
        Route::get('lista-estudiantes/{id}', [MasterclassController::class, 'viewStudents']);
        Route::get('/calendar-activities/{id}', [MasterclassController::class, 'getActivities'])->name('masterclass.calendar-activities');
        Route::get('/freecourse', [FreecourseController::class, 'viewFreecourses'])->name('masterclass.freecourse');
        Route::get('/freecourse/create', [FreecourseController::class, 'create'])->name('freecourse.create');
        Route::post('/freecourse/store', [FreecourseController::class, 'store'])->name('freecourse.store');
        Route::patch('/participants/{user_id}', [MasterclassRegisterController::class, 'updateParticipantStatus']);
    });

    Route::group(['prefix' => 'course'], function () {
        Route::get('/', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/categories', [CourseController::class, 'categoriesList'])->name('course.categories');
        Route::get('/levels', [CourseController::class, 'levelsList'])->name('course.levels');
        Route::get('/create/{product_type_id}', [CourseController::class, 'create'])->name('courses.create');
        Route::get('/{id}/courseList', [CourseController::class, 'courseList'])->name('course.list');
        Route::get('/courseListVerification', [CourseController::class, 'courseListVerification'])->name('course.listVerification');
        Route::post('/store-course', [CourseController::class, 'storeCourse']);
        Route::post('/{id}/store-book-file', [CourseController::class, 'storeBookFile'])->name('course.storeBookFile');
        Route::get('/{course}/book-files', [CourseController::class, 'getBookFiles'])->name('course.getBookFiles');
        Route::get('/{course}/book-content', [CourseController::class, 'bookContentView'])->name('course.bookContentView');
        Route::delete('/book-file/{bookFile}', [CourseController::class, 'deleteBookFile'])->name('course.deleteBookFile');
        Route::delete('/delete/{id}', [CourseController::class, 'delete']);
        Route::post('/{id}/sendRequest', [CourseController::class, 'sendRequest'])->name('courses.sendRequest');
        Route::post('/{id}/update', [CourseController::class, 'update'])->name('courses.update');
        Route::get('/{id}/subscribersList', [CourseController::class, 'subscribersList'])->name('course.subscribersList');
        Route::get('/{id}/modulesList', [CourseController::class, 'modulesList'])->name('course.modulesList');
        Route::get('/{id}/orders', [CourseController::class, 'getOrders']);
        Route::patch('/change-order', [CourseController::class, 'changeOrder']);
        Route::patch('/change-order-module', [CourseController::class, 'changeOrderModule']);

        // Listado de alumnos
        Route::get('/students-list/{id}', function ($id) {
            $user = auth()->user();
            return view('content.courses.list-students', compact('user', 'id'));
        });

        Route::group(['prefix' => '/module'], function () {
            Route::post('/store', [CourseModuleController::class, 'store'])->name('module.store');
            Route::get('/{id}/editModule', [CourseModuleController::class, 'editModule'])->name('module.editModule');
            Route::put('/{id}/update', [CourseModuleController::class, 'update'])->name('module.update');
            Route::delete('/{id}/delete', [CourseModuleController::class, 'delete'])->name('module.delete');

            Route::group(['prefix' => '/class'], function () {
                Route::post('/{id}/save', [ModuleClassController::class, 'save'])->name('class.save');
                Route::delete('/{id}/delete', [ModuleClassController::class, 'delete'])->name('class.delete');
                Route::get('/{id}/classList', [ModuleClassController::class, 'getClassList'])->name('class.list');
                Route::get('/{id}/listObservations', [ModuleClassController::class, 'listObservations'])->name('class.listObservations');
                Route::get('/{id}/details', [ModuleClassController::class, 'getClassDetails'])->name('class.details');
                Route::post('/{id}/update', [ModuleClassController::class, 'update'])->name('class.update');
                Route::get('/generate-url', [VideoController::class, 'generatePresignedUrl']);
                Route::get('/update-video-url/{id}/{name}', [VideoController::class, 'updateVideo']);
            });
        });

        Route::group(['prefix' => '/subscriber'], function () {
            Route::get('/', [CourseController::class, 'subscribers'])->name('courses.subs'); //view
        });

        Route::group(['prefix' => '/verification', 'middleware' => 'admin'], function () {
            Route::get('/', [CourseController::class, 'verification'])->name('courses.verification'); //view
            Route::get('/{id}/review', [CourseController::class, 'review'])->name('course.review'); //view
            Route::post('/{id}/approved', [CourseController::class, 'approved'])->name('course.verification.approved');
            Route::post('/{id}/sendObservations', [CourseController::class, 'sendObservations'])->name('course.verification.sendObservations');
            Route::group(['prefix' => '/class'], function () {
                Route::post('/changeStatus', [ModuleClassController::class, 'changeStatus'])->name('course.review.changeStatus');
            });
            Route::group(['prefix' => '/book'], function () {
                Route::post('/{course}/review', [BookController::class, 'reviewBook'])->name('course.verification.book.review');
            });
        });

        Route::group(['prefix' => '/exam'], function () {

            Route::post('/store', [Exam::class, 'store']);
            Route::get('{id}/create', [Exam::class, 'create'])->name('exam-create');
            Route::get('/{id}/createModuleExam', [Exam::class, 'createModuleExam'])->name('exam-module-create');
            Route::get('/{id}/createLessonExam', [Exam::class, 'createLessonExam'])->name('exam-lesson-create');
            Route::get('/{id}/list', [Exam::class, 'list']);
            Route::get('/module/{id}/list', [Exam::class, 'moduleList']);
            Route::get('/lesson/{id}/list', [Exam::class, 'lessonList']);
            Route::get('/edit/{id}', [Exam::class, 'edit'])->name('exam-edit');
            Route::get('/module/edit/{id}', [Exam::class, 'moduleEdit'])->name('exam-module-edit');
            Route::get('/lesson/edit/{id}', [Exam::class, 'lessonEdit'])->name('exam-lesson-edit');
            Route::get('/lesson/all', [Exam::class, 'allLessonsExam']);
            Route::post('/activate', [Exam::class, 'activate']);
            Route::get('/preview/{id}', [Exam::class, 'preview']);
            Route::get('/rate', [UserExamHeaderController::class, 'index'])->name('exams.rate');
            Route::get('/rate/{id}/list', [UserExamHeaderController::class, 'list'])->name('exams.list');
            Route::get('/rate/{id}/detailList', [UserExamHeaderController::class, 'detailList'])->name('exams.detailList');
            Route::post('/rate/update', [UserExamHeaderController::class, 'update'])->name('exams.rate.update');

            Route::group(['prefix' => '/question'], function () {
                Route::get('/{exam_id}/list', [ExamQuestion::class, 'list']);
                Route::get('/{id}/get', [ExamQuestion::class, 'get']);
                Route::get('/{id}/edit', [ExamQuestion::class, 'edit'])->name('exam.question.edit');
                Route::post('/store', [ExamQuestion::class, 'store']);
                Route::post('/options/store', [ExamQuestion::class, 'optionsStore']);
                Route::delete('/{id}/delete', [ExamQuestion::class, 'delete']);
            });
        });

        Route::group(['prefix' => '/game'], function () {   
            Route::get('/{id}', [CourseGameController::class, 'index'])->name('game-create');
            Route::post('/store', [CourseGameController::class, 'store'])->name('game.store');
            Route::get('/{id}/list', [CourseGameController::class, 'list']);
            Route::get('/edit/{id}', [CourseGameController::class, 'edit']);
            Route::post('/storeDetail', [CourseGameDetailController::class, 'storeDetail']); // update detail

            # Module Games
            Route::get('/module/{id}', [CourseGameController::class, 'indexModule'])->name('course.game.module');
            Route::get('/module/{id}/list', [CourseGameController::class, 'moduleList']);

            # Lesson Games
            Route::get('/lesson/{id}', [CourseGameController::class, 'indexLesson'])->name('course.game.lesson');
            Route::get('/lesson/{id}/list', [CourseGameController::class, 'lessonList']);

            Route::group(['prefix' => '/item'], function () {
                Route::post('/store', [CourseGameDetailController::class, 'storeItem']); // store item
                Route::get('/{id}/list', [CourseGameDetailController::class, 'listItem']); // list item
                Route::delete('/{id}/{index}/delete', [CourseGameDetailController::class, 'deleteItem']); // delete item by game_id and index json
                Route::post('/owl/store', [CourseGameDetailController::class, 'storeOwlQuestion']);
                Route::post('/owl/update', [CourseGameDetailController::class, 'updateOwlQuestion']);
                Route::post('/owl/update-lifes', [CourseGameDetailController::class, 'updateLifes']);
                Route::post('/wordWheel/store', [CourseGameDetailController::class, 'storeWheelQuestion']);
                Route::post('/wordWheel/update', [CourseGameDetailController::class, 'updateWheelQuestion']);
                Route::post('/complete-text/store', [CourseGameDetailController::class, 'storeCompleteText']);
                Route::post('/order-words/store', [CourseGameDetailController::class, 'storeOrderWords']);
                Route::post('/order-words/update', [CourseGameDetailController::class, 'updateOrderWords']);
            });

            Route::post('/activate', [CourseGameController::class, 'activate']);
        });

        Route::group(['prefix' => '/certificate'], function () {
            Route::get('/{id}', [CourseController::class, 'configureCertificate']);
            Route::get('/configuration/{id}', [CourseController::class, 'getConfigureCertificate']);
            Route::post('/store/configuration', [CourseConfigurationController::class, 'store']);
            Route::post('/deliver-certificate', [PurchasedCourseController::class, 'deliverCertificate']);
        });
    });

    Route::group(['prefix' => 'marketplace'], function () {
        Route::get('/', [MarketplaceController::class, 'viewMarketPlaceManagement'])->name('marketplace-management');
        Route::post('/toggle-sellable/{courseId}', [MarketplaceController::class, 'toggleMarketplaceViewability']);
    });

    Route::group(['prefix' => '/quizz'], function () {
        Route::get('/daily', [DailyQuestion::class, 'get'])->name('quiz-daily');
    });

    Route::get('/wallet/redemption', function () {
        return view('content.reports.redemption');
    })->name('wallet.redemption');

    Route::group(['prefix' => '/reports'], function () {
        Route::get('/growthBonus', [ClassifiedController::class, 'growthBonus'])->name('report-growthBonus');
        Route::get('/startingBonus', [ClassifiedController::class, 'startingBonus'])->name('report-startingBonus');
        Route::get('/mywalletinfo/{username}', [WalletController::class, 'getWalletForUser'])->name('report-mywalletinfo');
        Route::view('/wallets', 'content.reports.wallet')->middleware('can:report-wallets')->name('report-wallets');
        Route::get('/walletslist', [WalletController::class, 'getTotalWalletUsers']);
        Route::post('/movements/transfer-founds', [WalletMovementsController::class, 'transferFounds']);
        Route::post('/movements/request-founds/approve', [WalletMovementsController::class, 'approveRequest']);
        Route::get('/wallet/balance', [WalletMovementsController::class, 'getWalletBalance']);
        Route::patch('/movements/request-founds/reject', [WalletMovementsController::class, 'rejectRequest']);
        Route::get('/movements/request-founds/list', [WalletMovementsController::class, 'requestFoundsList']);
        Route::post('/movements/request-founds', [WalletMovementsController::class, 'requestFounds']);
        Route::get('/mymovements/{user_id}', [WalletMovementsController::class, 'getAllMovementsWallet']);
        Route::get('/movements/all', [WalletMovementsController::class, 'getAllMovementsHistoryWallet']);
        Route::get('/mywallet', [ReportsController::class, 'viewOption'])->middleware('can:report-nmywallet')->name('report-nmywallet');
        Route::get('/config', [ReportsController::class, 'config']);
        Route::get('/mypurchase', [ReportsController::class, 'myPurchase'])->middleware('can:report-purchase')->name('report-purchase');
        Route::get('/mysales', [ReportsController::class, 'mySales'])->middleware('can:report-sales')->name('report-sales-my');
        Route::get('/getsales/{id}', [ReportsController::class, 'getSales'])
            ->middleware('can:report-sales')
            ->name('report-sales');
        Route::get('/wallet-methods', [WalletPaymetMethodController::class, 'getAll']);
        Route::post('/wallet-payment-method/config', [WalletPaymetMethodController::class, 'config']);

// Nueva ruta para obtener las cuentas asociadas al usuario autenticado
        Route::get('/wallet-accounts', [WalletPaymetMethodController::class, 'getUserWalletAccounts']);
        Route::get('/historial', [ReportsController::class, 'historial'])->middleware('can:report-historial')->name('report-historial');
        Route::get('/get-binary-history', [ReportsController::class, 'getBinaryHistory'])->name('get-binary-history');
        //Route::get('/proyeccion', [ReportsController::class, 'proyeccion'])->middleware('can:report-proyeccion')->name('report-proyeccion');
    });

    Route::get('/chatgpt', [ChatGptController::class, 'index'])->name('chatgpt.index');
    Route::get('/chats/all/{id}', [ChatGptController::class, 'getChat']);
    Route::post('/chat/chatgpt', [ChatGptController::class, 'chat']);
    Route::post('/chat/chatpdf', [ChatGptController::class, 'subirPDF']);

    Route::group(['prefix' => '/requests'], function () {
        Route::get('/listUserPayments ', [PaymentController::class, 'listUserPayments']);
        Route::get('/listMyPayments ', [PaymentController::class, 'listMyPayments'])->name('request-listMyPayments');

        //Ruta Billetera - Fondos de Usuario
        Route::group(['prefix' => '/wallet'], function () {
            Route::get('/', [WalletController::class, 'retornarVista'])->name('wallet');
            Route::apiResource('wallets', WalletController::class)->only('index');
        });
    });
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::group(['prefix' => 'courses'], function () {
        Route::get('/list/producer', [CourseController::class, 'listCoursesProd'])->name('listCoursesProd');
        Route::get('/{id}', [CourseController::class, 'showCourseForCertificate'])->name('courses.showForCertificate');
    });

    Route::group(['prefix' => 'admin'], function () {
        Route::get('/role/get-actions', [RoleController::class, 'getActions']);
        Route::get('/role/get-modules', [RoleController::class, 'getModules']);
        Route::resource('role', RoleController::class);
        Route::get('/role/show', [RoleController::class, 'show']);
        Route::get('/role/get-sections/{id}', [RoleController::class, 'getSections']);
        Route::get('/role/submodule/{role}/{modul}', [RoleController::class, 'submodule']);
        Route::post('/role/actions', [RoleController::class, 'actions']);
        Route::post('/role/add/permission', [RoleController::class, 'addPermission']);
        Route::post('/role/remove/permission', [RoleController::class, 'removePermission']);
        Route::get('/role/remove/{role}', [RoleController::class, 'removeRole']);
    });
});

// Rutas públicas de dinámicas (sin autenticación)
Route::get('/d/{slug}', [DinamicaController::class, 'showPublic'])->name('dinamica.public');
Route::post('/d/{slug}/register', [DinamicaController::class, 'registerPublic'])->name('dinamica.public.register');
Route::post('/d/{slug}/spin', [DinamicaPublicController::class, 'spin'])->name('dinamica.public.spin');
Route::get('/d/{slug}/status', [DinamicaController::class, 'publicStatus'])->name('dinamica.public.status');
Route::get('/d/{slug}/participants-feed', [DinamicaController::class, 'participantsFeed'])->name('dinamica.public.participants');
Route::post('/d/{slug}/marcar-jugado', [DinamicaPublicController::class, 'marcarJugado'])->name('dinamica.public.marcarJugado');
Route::post('/d/{slug}/registrar-ganador', [DinamicaPublicController::class, 'registrarGanador'])->name('dinamica.public.registrarGanador');

// Vistas públicas del trivia (preview, preguntas y resultados)
Route::get('/d/{slug}/preview', [PublicTriviaController::class, 'preview'])->name('dinamica.public.preview');
Route::get('/d/{slug}/pregunta/{numero}', [PublicTriviaController::class, 'pregunta'])->name('dinamica.public.pregunta');
Route::post('/d/{slug}/pregunta/{numero}/respuesta', [PublicTriviaController::class, 'responderPregunta'])->name('dinamica.public.responder');
Route::get('/d/{slug}/resultados', [PublicTriviaController::class, 'resultados'])->name('dinamica.public.resultados');

// Alias /trivia/* para compatibilidad con enlaces anteriores
Route::get('/trivia/{slug}/preview', [PublicTriviaController::class, 'preview'])->name('trivia.preview');
Route::get('/trivia/{slug}/pregunta/{numero}', [PublicTriviaController::class, 'pregunta'])->name('trivia.pregunta');
Route::post('/trivia/{slug}/pregunta/{numero}/respuesta', [PublicTriviaController::class, 'responderPregunta'])->name('trivia.responder');
Route::get('/trivia/{slug}/resultados', [PublicTriviaController::class, 'resultados'])->name('trivia.resultados');

Route::group(['prefix' => 'templates'], function () {
    Route::post('/store', [TemplateController::class, 'store']);
    Route::get('/show/{id}', [TemplateController::class, 'show']);
    Route::put('/update/{id}', [TemplateController::class, 'update']);
    Route::delete('/delete/{id}', [TemplateController::class, 'delete']);
});

Route::group(['prefix' => 'user-templates'], function () {
    Route::get('/content/{id}', [UserTemplateController::class, 'getContent']);
    Route::post('/store', [UserTemplateController::class, 'store']);
    Route::get('/list/{userId}', [UserTemplateController::class, 'list']);
    Route::put('/update/{id}', [UserTemplateController::class, 'update']);
    Route::delete('/delete/{id}', [UserTemplateController::class, 'delete']);
});

Route::get('/webpage/{code}', [UserTemplateController::class, 'show']);
//registro de usuarios
Route::post('users/create', [UserController::class, 'Create'])->name('users-create');
Route::post('users/create-free', [UserController::class, 'createFree'])->name('users-create-free');
Route::post('users/validate', [UserController::class, 'validateUser'])->name('users-create-session');
Route::post('/users/create-free1', [UserController::class, 'createFree1']);
Route::post('users/create-unverified-user', [UserController::class, 'createUnverifiedUser']);

// for general users
Route::get('/register/{id}/{code}/{hash?}', [UserController::class, 'RegisterSponsor'])->name('users-register');
/* Rutas Programada - fin */

Route::group(['prefix' => 'page'], function () {
    // Miscellaneous Pages With Page Prefix
    Route::get('coming-soon', [MiscellaneousController::class, 'coming_soon'])->name('misc-coming-soon');
    Route::get('not-authorized', [MiscellaneousController::class, 'not_authorized'])->name('misc-not-authorized');
    Route::get('maintenance', [MiscellaneousController::class, 'maintenance'])->name('misc-maintenance');
});
// Ruta para historial de traslados (para correos de transferencia)
Route::get('/traslados/historial', function() {
    // Puedes redirigir a una vista real o a un controlador si lo deseas
    return view('traslados.historial');
})->name('traslados.historial');

/* Route Pages */

// Nibuiz routes
Route::get('/niubiz/payment', [NiubizController::class, 'index'])->name('niubiz-index');
Route::get('/get-different-access-token', [NiubizController::class, 'createNiubizToken'])->name('createNiubizToken');
Route::post('/authorizeTransaction', [NiubizController::class, 'authorizeTransaction'])->name('authorizeTransaction');
Route::post('/authorizeopc', [PayController::class, 'authorizeopc'])->name('authorizeopc');
Route::post('/niubiz/process', [NiubizController::class, 'process'])->name('niubiz-process');
Route::post('/opc/process', [PayController::class, 'opcprocess'])->name('opc-process');
Route::get('/terms-and-conditions', [UserController::class, 'conditions'])->name('conditions');
Route::get('/error', [MiscellaneousController::class, 'error'])->name('error');

// map leaflet

// locale Route
Route::get('lang/{locale}', [LanguageController::class, 'swap']);
Route::view('/virtualclassroom', 'newPage')->name('virtualclass');
Route::get('/screenshot', [CertificatesController::class, 'screenshot']);
Route::get('/get-certificado', [CertificatesController::class, 'getCertificado']);
Route::get('/course-user/dynamic/{id}', [CourseGameController::class, "previewGame"]);
Route::get('/api/obtener-tiempo-restante', [ShareLinkController::class, 'obtenerTiempoRestante']);
Route::get('/api/verificar-enlace/{id}', [ShareLinkController::class, 'verificarEnlace']);
Route::get('/check-link-status/{username}', [ShareLinkController::class, 'checkLinkStatus']);

//registros para los amsterclass promcionados por el distribuidor
Route::get('/register-masterclass', [MasterclassRegisterController::class, 'index']);
Route::post('/register-masterclass', [MasterclassRegisterController::class, 'submitRegistration'])->name('masterclass-register');

Route::get('/ebook/register', [EbookRegisterController::class, 'ebookRegisterForm'])->name('ebook-register-form');
Route::post('/ebook/register', [EbookRegisterController::class, 'ebookSubmitRegistration'])->name('ebook-register');
Route::get('/ebook/access/{id}', [EbookRegisterController::class, 'accessEbook'])
    ->name('ebook.access');
Route::get('mini-course/register', [MinicourseRegisterController::class, 'miniCourseRegisterForm'])->name('mini-course-register-form');
Route::post('mini-course/register', [MinicourseRegisterController::class, 'miniCourseSubmitRegistration'])->name('mini-course-register');

Route::get('mini-course/{id}/modules', [MiniCourseController::class, 'viewModules'])->name('marketing.mini-course.modules');

Route::get('/mini-course/access/{id}', [MinicourseRegisterController::class, 'accessMiniCourse'])
    ->name('minicourse.access');

// Esta ruta debe ir en web.php o en el nivel superior de rutas
Route::get('/pages/{slug}', [EdiTemplateController::class, 'showPublicPage'])->name('public.page.show');

Route::get('/pay/{paymentLink:slug}', [PaymentLinkController::class, 'show'])->name('payment-links.show');
Route::post('/pay/{paymentLink:slug}/process', [PaymentLinkController::class, 'processPayment'])->name('payment-links.process');
Route::post('/payment-links/pay/recharge', [PaymentLinkController::class, 'rechargeOpenpayProcess'])->name('payment-links.recharge');

Route::get('/php-limits', function () {
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
    ]);
});

Route::get('/test-phpmailer-email', [TestEmailController::class, 'testPHPMailer']);
Route::get('/test-email-form', [TestEmailController::class, 'showTestForm']);
Route::post('/send-test-email', [TestEmailController::class, 'sendTestEmail']);

use App\Http\Controllers\RuletaController;
Route::get('/ruleta-spin', [RuletaController::class, 'spin']);
Route::view('/ruleta/socket-test', 'tests.ruleta-socket')->name('ruleta.socket-test');

// ─────────────────────────────────────────────
// PREREGISTRO
// ─────────────────────────────────────────────

// Openpay
Route::post('/preregistro/openpay', [
    PreregistroController::class,
    'openpay'
])->middleware('throttle:10,1')->name('preregistro.openpay');

// Generar link personalizado
Route::post('/preregistro/generate-link', [
    PreregistroController::class,
    'generateLink'
])->name('preregistro.generate-link');

// Guardar configuracion
Route::post('/preregistro/save-config', [
    PreregistroController::class,
    'saveConfig'
]);

// Recuperar configuracion
Route::get('/preregistro/config/{username}', [
    PreregistroController::class,
    'getConfig'
]);

// Verificar duplicados en tiempo real (usuario, correo, documento)
// IMPORTANTE: debe ir ANTES de /preregistro/{username} para que no sea
// capturada por el comodín {username}
Route::get('/preregistro/check-duplicate', [
    PreregistroController::class,
    'checkDuplicate'
])->middleware('throttle:30,1')->name('preregistro.check-duplicate');

// Landing + formulario
Route::get('/preregistro/{username}', [
    PreregistroController::class,
    'index'
])->name('preregistro.index');

// Guardar preregistro
Route::post('/preregistro/{username}', [
    PreregistroController::class,
    'store'
])->middleware('throttle:10,1')->name('preregistro.store');

// ─────────────────────────────────────────────
// DASHBOARD
// ─────────────────────────────────────────────

Route::get('/mi-dashboard/{any?}', function () {
    return response()->file(
        public_path('dashboard/index.html')
    );
})->where('any', '.*');