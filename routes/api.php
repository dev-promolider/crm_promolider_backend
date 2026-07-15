<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Arquitectura Hexagonal)
|--------------------------------------------------------------------------
| Aqui solo registramos las rutas que ya han sido migradas a la nueva
| estructura.
|
*/

    // ==========================================
    // Módulo: Autenticación
    // ==========================================
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [\Promolider\Infrastructure\Auth\In\Http\Controllers\AuthController::class, 'login'])->name('auth.login');
    });

    // ==========================================
    // Rutas Protegidas (Requieren Token)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {
        // ==========================================
        // Módulo: Dashboard
        // ==========================================
        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('topbar-stats', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'topbarStats'])->name('dashboard.stats');
            Route::get('widgets', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'dashboardWidgets'])->name('dashboard.widgets');
            Route::get('unilevel-tree', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'unilevelTree'])->name('dashboard.unilevel_tree');
            Route::get('binary-tree', [\Promolider\Infrastructure\Dashboard\In\Http\Controllers\DashboardController::class, 'binaryTree'])->name('dashboard.binary_tree');
        });

        // ==========================================
        // Módulo: Perfil
        // ==========================================
        Route::get('profile/info', function (\Illuminate\Http\Request $request) {
            return response()->json(['user' => $request->user()]);
        });

        Route::prefix('me')->group(function () {
            Route::get('infoproducts', \Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Me\GetMyProductsController::class)->name('profile.infoproducts');
        });

        // ==========================================
        // Módulo: Cursos
        // ==========================================
        Route::prefix('courses')->group(function () {
            Route::prefix('{courseId}')->group(function () {
                Route::prefix('modules')->group(function () {
                    Route::get('edit-data', \Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course\Module\GetEditModuleDataController::class)->name('course.module.edit_data');
                });
            });
        });
    });

    // ==========================================
    // Modulo: Preregistro (Gestion Patrocinador)
    // ==========================================
    Route::group(['prefix' => 'registration/preregistro', 'middleware' => ['auth:sanctum']], function () {
        Route::post('config', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'saveConfig'])->name('registration.preregistro.save_config');
        Route::get('referrals', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'referrals'])->name('registration.preregistro.referrals');
    });

    // ==========================================
    // Modulo: Registro (Dashboard Patrocinador)
    // ==========================================
    Route::group(['prefix' => 'dashboard/registro', 'middleware' => ['auth:sanctum']], function () {
        Route::get('link', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'getActiveLink'])->name('dashboard.registro.get_link');
        Route::post('link', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'generateLink'])->name('dashboard.registro.generate_link');
        Route::delete('link/{id}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'suspendLink'])->name('dashboard.registro.suspend_link');
        Route::get('directs', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistroDashboardController::class, 'getDirects'])->name('dashboard.registro.get_directs');
    });

// ==========================================
// Modulo: Marketing
// ==========================================
Route::group(['prefix' => 'marketing'], function () {

    // Marketplace (specific routes first - avoid {type} collisions)
    Route::group(['prefix' => 'marketplace'], function () {
        Route::get('masterclasses', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'getMasterclasses'])->name('marketing.marketplace.masterclasses');
        Route::get('ebooks', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'getEbooks'])->name('marketing.marketplace.ebooks');
        Route::get('mini-courses', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'getMiniCourses'])->name('marketing.marketplace.mini_courses');
        Route::get('campaigns', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'getCampaigns'])->name('marketing.marketplace.campaigns');
        Route::post('{courseId}/toggle-visibility', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'toggleVisibility'])->name('marketing.marketplace.toggle')->middleware('auth:sanctum,can:masterclass.marketplace');
        Route::post('validate-distributor', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'validateDistributor'])->name('marketing.marketplace.validate_distributor')->middleware('auth:sanctum,can:masterclass.marketplace');

        // Detail endpoints
        Route::get('masterclass/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'getMasterclassDetail'])->name('marketing.marketplace.masterclass.detail');
        Route::get('ebook/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'getEbookDetail'])->name('marketing.marketplace.ebook.detail');
        Route::get('mini-course/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceController::class, 'getMiniCourseDetail'])->name('marketing.marketplace.minicourse.detail');
    });

    // Calendar - ALL under /marketing/calendar/ prefix (frontend expects this)
    Route::group(['prefix' => 'calendar'], function () {
        // Admin route sin userId (frontend llama /marketing/calendar/admin)
        Route::get('admin', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'getEventsAdmin'])->name('marketing.calendar.admin');
        Route::get('producer/{userId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'getEventsProducer'])->name('marketing.calendar.producer');
        Route::get('distributor/{userId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'getEventsDistributor'])->name('marketing.calendar.distributor');
        Route::get('activities/{userId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'getActivities'])->name('marketing.calendar.activities');
        Route::post('meetings', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'createMeeting'])->name('marketing.calendar.meetings.create')->middleware('auth:sanctum,can:marketing.calendar');

        // Calendar Notes
        Route::get('notes', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'getNotes'])->name('marketing.calendar.notes.get')->middleware('auth:sanctum,can:marketing.calendar');
        Route::post('notes/sync', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'syncNotes'])->name('marketing.calendar.notes.sync')->middleware('auth:sanctum,can:marketing.calendar');
        Route::post('notes', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'createNote'])->name('marketing.calendar.notes.create')->middleware('auth:sanctum,can:marketing.calendar');
        Route::put('notes/{noteId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'updateNote'])->name('marketing.calendar.notes.update')->middleware('auth:sanctum,can:marketing.calendar');
        Route::delete('notes/{noteId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CalendarController::class, 'deleteNote'])->name('marketing.calendar.notes.delete')->middleware('auth:sanctum,can:marketing.calendar');
    });

    // Dinamicas
    Route::get('dinamicas', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'index'])->name('marketing.dinamicas.index')->middleware('auth:sanctum');
    Route::get('dinamicas/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'show'])->name('marketing.dinamicas.show')->middleware('auth:sanctum');
    Route::post('dinamica/store', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'store'])->name('marketing.dinamica.store')->middleware('auth:sanctum');
    Route::put('dinamicas/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'update'])->name('marketing.dinamicas.update')->middleware('auth:sanctum');
    Route::delete('dinamicas/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'destroy'])->name('marketing.dinamicas.destroy')->middleware('auth:sanctum');
    Route::post('dinamicas/{id}/toggle-status', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'toggleStatus'])->name('marketing.dinamicas.toggle')->middleware('auth:sanctum');
    Route::post('dinamica/{dinamicaId}/specifications', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'storeSpecifications'])->name('marketing.dinamica.specifications')->middleware('auth:sanctum');
    Route::post('dinamica/{dinamicaId}/trivia', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicasController::class, 'saveTrivia'])->name('marketing.dinamica.trivia')->middleware('auth:sanctum');

    // Pages (Paginas / Plantillas)
    Route::group(['prefix' => 'pages'], function () {
        Route::get('templates', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'getTemplates'])->name('marketing.pages.templates');
        Route::get('user/{userId}/templates', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'getUserTemplates'])->name('marketing.pages.user_templates')->middleware('auth:sanctum,can:marketing.pages');
        Route::get('templates/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'getTemplate'])->name('marketing.pages.template');
        Route::post('templates', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'create'])->name('marketing.pages.create')->middleware('auth:sanctum,can:marketing.pages');
        Route::put('templates/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'update'])->name('marketing.pages.update')->middleware('auth:sanctum,can:marketing.pages');
        Route::delete('templates/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'delete'])->name('marketing.pages.delete')->middleware('auth:sanctum,can:marketing.pages');
        Route::post('templates/{id}/publish', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'publish'])->name('marketing.pages.publish')->middleware('auth:sanctum,can:marketing.pages');
        Route::post('templates/{id}/unpublish', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'unpublish'])->name('marketing.pages.unpublish')->middleware('auth:sanctum,can:marketing.pages');
    });

    // Reports
    Route::group(['prefix' => 'reports'], function () {
        Route::get('masterclass/{view}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getMasterclassReport'])->name('marketing.reports.masterclass');
        Route::get('mini-course/{view}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getMiniCourseReport'])->name('marketing.reports.mini_course');
        Route::get('ebook/{view}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getEbookReport'])->name('marketing.reports.ebook');
        Route::get('private-content', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getPrivateContent'])->name('marketing.reports.private_content');
        Route::get('private-content/{contentType}/{contentId}/students', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getPrivateContentStudents'])->name('marketing.reports.private_content.students');
        Route::get('content-by-status', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getContentByStatus'])->name('marketing.reports.content_by_status');
        Route::get('content-by-producer', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getContentByProducer'])->name('marketing.reports.content_by_producer');
        Route::get('general', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getGeneralReports'])->name('marketing.reports.general');
        Route::get('distributors/{type}/{contentId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getDistributors'])->name('marketing.reports.distributors');
        Route::get('students/{type}/{contentId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getStudents'])->name('marketing.reports.students');
        Route::get('participants/pending/{type}/{contentId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getPendingParticipants'])->name('marketing.reports.pending_participants');

        // Endpoints legacy: students list y last sells
        Route::get('students-list', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getStudentsList'])->name('marketing.reports.students_list')->middleware('auth:sanctum');
        Route::get('participants/all/{isParticipant?}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getAllPendingParticipantsByUser'])->name('marketing.reports.all_participants')->middleware('auth:sanctum');
        Route::get('last-sells', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ReportsController::class, 'getLastSells'])->name('marketing.reports.last_sells')->middleware('auth:sanctum');

        // Wallet & Movements routes (Hexagonal Migration)
        Route::get('mymovements/{userId}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'getAllMovementsWallet'])->name('marketing.reports.mymovements')->middleware('auth:sanctum');
        Route::get('wallet/balance', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'getWalletBalance'])->name('marketing.reports.wallet.balance')->middleware('auth:sanctum');
        Route::get('mymovements-history', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'getAllMovementsHistoryWallet'])->name('marketing.reports.mymovements_history')->middleware('auth:sanctum');
        Route::post('movements/transfer-founds', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'transferFounds'])->name('marketing.reports.movements.transfer_founds')->middleware('auth:sanctum');
        Route::post('movements/request-founds', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'requestFounds'])->name('marketing.reports.movements.request_founds')->middleware('auth:sanctum');
        Route::get('movements/request-founds/list', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'requestFoundsList'])->name('marketing.reports.movements.request_founds_list')->middleware('auth:sanctum');
        Route::post('movements/request-founds/reject', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'rejectRequest'])->name('marketing.reports.movements.request_founds_reject')->middleware('auth:sanctum');
        Route::post('movements/request-founds/approve', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'approveRequest'])->name('marketing.reports.movements.request_founds_approve')->middleware('auth:sanctum');
        Route::get('binary-history', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'getBinaryHistory'])->name('marketing.reports.binary_history')->middleware('auth:sanctum');
        Route::get('getsales/{id}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'getSales'])->name('marketing.reports.get_sales')->middleware('auth:sanctum');
        Route::get('movements/my-directs', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'getMyDirects'])->name('marketing.reports.movements.my_directs')->middleware('auth:sanctum');
        Route::get('mypurchases/{userId}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\WalletMovementsController::class, 'getMyPurchases'])->name('marketing.reports.mypurchases')->middleware('auth:sanctum');
    });

    // Question Categories & Items
    Route::group(['prefix' => 'question-categories'], function () {
        Route::get('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'index'])->name('marketing.question_categories.index');
        Route::post('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'store'])->name('marketing.question_categories.store')->middleware('auth:sanctum');
        Route::get('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'show'])->name('marketing.question_categories.show');
        Route::put('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'update'])->name('marketing.question_categories.update')->middleware('auth:sanctum');
        Route::patch('{id}/toggle', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'toggle'])->name('marketing.question_categories.toggle')->middleware('auth:sanctum');
        Route::delete('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'destroy'])->name('marketing.question_categories.destroy')->middleware('auth:sanctum');

        // Questions within categories
        Route::get('{categoryId}/questions', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'questionsIndex'])->name('marketing.question_categories.questions.index');
        Route::post('{categoryId}/questions', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'storeQuestion'])->name('marketing.question_categories.questions.store')->middleware('auth:sanctum');
        Route::put('{categoryId}/questions/{questionId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'updateQuestion'])->name('marketing.question_categories.questions.update')->middleware('auth:sanctum');
        Route::delete('{categoryId}/questions/{questionId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\QuestionCategoriesController::class, 'destroyQuestion'])->name('marketing.question_categories.questions.destroy')->middleware('auth:sanctum');
    });

    // Free Courses (Cursos Gratuitos)
    Route::group(['prefix' => 'freecourses'], function () {
        Route::get('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\FreeCoursesController::class, 'index'])->name('marketing.freecourses.index');
        Route::post('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\FreeCoursesController::class, 'store'])->name('marketing.freecourses.store')->middleware('auth:sanctum');
        Route::delete('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\FreeCoursesController::class, 'destroy'])->name('marketing.freecourses.destroy')->middleware('auth:sanctum');
    });

    // Mini Course Modules & Classes
    Route::group(['prefix' => 'mini-course'], function () {
        // Modules
        Route::get('{miniCourseId}/modules', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'modulesIndex'])->name('marketing.minicourse.modules');
        Route::post('modules', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'modulesStore'])->name('marketing.minicourse.modules.store')->middleware('auth:sanctum');
        Route::put('modules/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'modulesUpdate'])->name('marketing.minicourse.modules.update')->middleware('auth:sanctum');
        Route::delete('modules/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'modulesDestroy'])->name('marketing.minicourse.modules.destroy')->middleware('auth:sanctum');

        // Classes
        Route::get('modules/{moduleId}/classes', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'classesIndex'])->name('marketing.minicourse.classes');
        Route::post('classes', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'classesStore'])->name('marketing.minicourse.classes.store')->middleware('auth:sanctum');
        Route::put('classes/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'classesUpdate'])->name('marketing.minicourse.classes.update')->middleware('auth:sanctum');
        Route::delete('classes/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'classesDestroy'])->name('marketing.minicourse.classes.destroy')->middleware('auth:sanctum');

        // Documents
        Route::delete('documents/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MiniCourseModulesController::class, 'documentsDestroy'])->name('marketing.minicourse.documents.destroy')->middleware('auth:sanctum');
    });

    // Invitation Links (Product Distributors)
    Route::group(['prefix' => 'invitations'], function () {
        Route::post('{productType}/{productId}/create', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\InvitationLinksController::class, 'create'])->name('marketing.invitations.create')->middleware('auth:sanctum');
        Route::get('{productType}/{productId}/check', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\InvitationLinksController::class, 'check'])->name('marketing.invitations.check')->middleware('auth:sanctum');
    });

    // Participant Status & Observation (Mini-Course, Ebook, Masterclass)
    Route::group(['prefix' => 'participants'], function () {
        // Mini-Course
        Route::patch('mini-course/{userId}/participant', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'updateMiniCourseParticipantStatus'])->name('marketing.participants.minicourse.status')->middleware('auth:sanctum');
        Route::patch('mini-course/{userId}/observation', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'updateMiniCourseObservation'])->name('marketing.participants.minicourse.observation')->middleware('auth:sanctum');

        // Ebook
        Route::patch('ebook/{userId}/participant', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'updateEbookParticipantStatus'])->name('marketing.participants.ebook.status')->middleware('auth:sanctum');
        Route::patch('ebook/{userId}/observation', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'updateEbookObservation'])->name('marketing.participants.ebook.observation')->middleware('auth:sanctum');

        // Masterclass
        Route::patch('masterclass/{userId}/participant', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'updateMasterclassParticipantStatus'])->name('marketing.participants.masterclass.status')->middleware('auth:sanctum');
        Route::patch('masterclass/{userId}/observation', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'updateMasterclassObservation'])->name('marketing.participants.masterclass.observation')->middleware('auth:sanctum');
    });

    // Sponsor Link (ShareLink remaining endpoints)
    Route::group(['prefix' => 'sponsor-links'], function () {
        Route::get('remaining-time', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\SponsorLinkController::class, 'remainingTime'])->name('marketing.sponsor_links.remaining_time')->middleware('auth:sanctum');
        Route::get('user-info/{username}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\SponsorLinkController::class, 'userInfo'])->name('marketing.sponsor_links.user_info');
    });

    // Sistema de Cursos
    Route::group(['prefix' => 'courses'], function () {
        // Cursos — literals FIRST, parameterized LAST
        Route::get('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'index'])->name('marketing.courses.index');
        Route::get('search', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'search'])->name('marketing.courses.search');
        Route::post('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'store'])->name('marketing.courses.store')->middleware('auth:sanctum');
        Route::get('released', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'released'])->name('marketing.courses.released')->middleware('auth:sanctum');
        Route::get('last-played', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'lastPlayed'])->name('marketing.courses.last_played')->middleware('auth:sanctum');
        Route::get('list/random', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'listRandom'])->name('marketing.courses.list_random');
        Route::get('recommended', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'recommendedCourses'])->name('marketing.courses.recommended')->middleware('auth:sanctum');
        Route::get('available-books', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'listAvailableBooks'])->name('marketing.courses.available_books');
        Route::get('interesting', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'interestingCourses'])->name('marketing.courses.interesting')->middleware('auth:sanctum');
        Route::get('add-latest-lesson/{classId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'addLatestLesson'])->name('marketing.courses.add_latest_lesson')->middleware('auth:sanctum');
        Route::get('show-latest-lesson', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'showLatestLesson'])->name('marketing.courses.show_latest_lesson')->middleware('auth:sanctum');
        Route::get('producer/{producerId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'listProducer'])->name('marketing.courses.producer');
        Route::get('student-dashboard', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'studentDashboard'])->name('marketing.courses.student_dashboard')->middleware('auth:sanctum');
        Route::get('congratulations', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'getCongratulations'])->name('marketing.courses.congratulations')->middleware('auth:sanctum');
        Route::get('{courseId}/related', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'relatedCourses'])->name('marketing.courses.related');
        Route::get('{courseId}/expiration', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'expiration'])->name('marketing.courses.expiration')->middleware('auth:sanctum');
        Route::get('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'show'])->name('marketing.courses.show')->whereNumber('id');
        Route::put('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'update'])->name('marketing.courses.update')->middleware('auth:sanctum')->whereNumber('id');
        Route::delete('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'destroy'])->name('marketing.courses.destroy')->middleware('auth:sanctum')->whereNumber('id');

        // Modulos
        Route::get('{courseId}/modules', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'modulesIndex'])->name('marketing.courses.modules');
        Route::post('modules', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'modulesStore'])->name('marketing.courses.modules.store')->middleware('auth:sanctum');
        Route::put('modules/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'modulesUpdate'])->name('marketing.courses.modules.update')->middleware('auth:sanctum');
        Route::delete('modules/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'modulesDestroy'])->name('marketing.courses.modules.destroy')->middleware('auth:sanctum');
        Route::post('{courseId}/modules/reorder', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'modulesReorder'])->name('marketing.courses.modules.reorder')->middleware('auth:sanctum');

        // Clases
        Route::get('modules/{moduleId}/classes', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'classesIndex'])->name('marketing.courses.classes');
        Route::post('classes', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'classesStore'])->name('marketing.courses.classes.store')->middleware('auth:sanctum');
        Route::put('classes/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'classesUpdate'])->name('marketing.courses.classes.update')->middleware('auth:sanctum');
        Route::delete('classes/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'classesDestroy'])->name('marketing.courses.classes.destroy')->middleware('auth:sanctum');

        // Progreso
        Route::get('{courseId}/progress', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'getProgress'])->name('marketing.courses.progress')->middleware('auth:sanctum');
        Route::post('{courseId}/lessons/{lessonId}/complete', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'completeLesson'])->name('marketing.courses.lessons.complete')->middleware('auth:sanctum');
        Route::post('{courseId}/progress', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'updateProgress'])->name('marketing.courses.progress.update')->middleware('auth:sanctum');

        // Valoraciones
        Route::get('{courseId}/ratings', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'ratingsIndex'])->name('marketing.courses.ratings');
        Route::post('{courseId}/ratings', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'ratingsStore'])->name('marketing.courses.ratings.store')->middleware('auth:sanctum');

        // Observaciones
        Route::post('observations', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'observationsStore'])->name('marketing.courses.observations.store')->middleware('auth:sanctum');
        Route::get('classes/{classId}/observations', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'observationsList'])->name('marketing.courses.observations')->middleware('auth:sanctum');

        // Juegos
        Route::get('{courseId}/games', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gamesIndex'])->name('marketing.courses.games');
        Route::get('{courseId}/dinamicas', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'dinamicasList'])->name('marketing.courses.dinamicas');
        Route::get('dinamicas/{gameId}/data', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'dinamicaData'])->name('marketing.courses.dinamicas.data');
        Route::post('games', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gamesStore'])->name('marketing.courses.games.store')->middleware('auth:sanctum');
        Route::put('games/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gamesUpdate'])->name('marketing.courses.games.update')->middleware('auth:sanctum');
        Route::delete('games/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gamesDestroy'])->name('marketing.courses.games.destroy')->middleware('auth:sanctum');
        Route::get('games/{gameId}/details', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gameDetailsIndex'])->name('marketing.courses.games.details');
        Route::post('game-details', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gameDetailsStore'])->name('marketing.courses.games.details.store')->middleware('auth:sanctum');
        Route::delete('game-details/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gameDetailsDestroy'])->name('marketing.courses.games.details.destroy')->middleware('auth:sanctum');

        // Comentarios de Juegos
        Route::get('{courseId}/games/top', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gamesTop'])->name('marketing.courses.games.top')->middleware('auth:sanctum');
        // Active Game endpoints (student-facing)
        Route::group(['prefix' => 'game'], function () {
            Route::post('active', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'getActiveGame'])->name('marketing.courses.game.active')->middleware('auth:sanctum');
            Route::post('module-active', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'getActiveModuleGame'])->name('marketing.courses.game.module_active')->middleware('auth:sanctum');
            Route::post('add-points', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'addPointsToUser'])->name('marketing.courses.game.add_points')->middleware('auth:sanctum');
            Route::post('retrieve-top', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'retrieveDynamicTop'])->name('marketing.courses.game.retrieve_top')->middleware('auth:sanctum');
        });

        Route::get('games/{gameId}/comments', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gameCommentsIndex'])->name('marketing.courses.games.comments')->middleware('auth:sanctum');
        Route::post('games/{gameId}/comments', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'gameCommentsStore'])->name('marketing.courses.games.comments.store')->middleware('auth:sanctum,throttle:3,1');

        // Certificados
        Route::get('certificates', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'certificatesIndex'])->name('marketing.courses.certificates')->middleware('auth:sanctum');
        Route::post('certificates', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'certificatesStore'])->name('marketing.courses.certificates.store')->middleware('auth:sanctum');
        Route::get('templates', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'templatesIndex'])->name('marketing.courses.templates');
        Route::post('templates', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'templatesStore'])->name('marketing.courses.templates.store')->middleware('auth:sanctum');
        Route::put('templates/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'templatesUpdate'])->name('marketing.courses.templates.update')->middleware('auth:sanctum');

        // Examenes (Estudiante)
        Route::get('{courseId}/exam/active', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'active'])->name('marketing.courses.exam.active')->middleware('auth:sanctum');
        Route::post('{courseId}/exam/submit', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'submit'])->name('marketing.courses.exam.submit')->middleware('auth:sanctum');
        Route::get('{courseId}/exam/results', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'results'])->name('marketing.courses.exam.results')->middleware('auth:sanctum');
        Route::get('{courseId}/exam/list', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'examList'])->name('marketing.courses.exam.list')->middleware('auth:sanctum');
        Route::post('{courseId}/exam/calification', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'getCalification'])->name('marketing.courses.exam.calification')->middleware('auth:sanctum');
        Route::post('{courseId}/exam/indicators', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'getIndicators'])->name('marketing.courses.exam.indicators')->middleware('auth:sanctum');
        Route::post('{courseId}/exam/module/active', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'getActiveModuleExams'])->name('marketing.courses.exam.module_active')->middleware('auth:sanctum');
        Route::get('{courseId}/exam/isconfig', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'checkCourseConfig'])->name('marketing.courses.exam.isconfig')->middleware('auth:sanctum');
        Route::get('{courseId}/certificate/check', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'checkCertificate'])->name('marketing.courses.certificate.check')->middleware('auth:sanctum');
        Route::get('congratulations', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\ExamsController::class, 'getCongratulations'])->name('marketing.courses.congratulations')->middleware('auth:sanctum');

        // Student Dashboard
        Route::get('student-dashboard', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\CoursesController::class, 'studentDashboard'])->name('marketing.courses.student_dashboard')->middleware('auth:sanctum');

        // Daily Questions (globales, no por curso)
        Route::get('daily-question', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DailyQuestionsController::class, 'index'])->name('marketing.courses.daily_question')->middleware('auth:sanctum');
        Route::post('daily-question/validate', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DailyQuestionsController::class, 'validate'])->name('marketing.courses.daily_question.validate')->middleware('auth:sanctum');
        // Video Streaming
        Route::group(['prefix' => 'video'], function () {
            Route::get('stream', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\VideoController::class, 'streamVideo'])->name('marketing.courses.video.stream')->middleware('auth:sanctum');
            Route::post('save-time', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\VideoController::class, 'saveTime'])->name('marketing.courses.video.save_time')->middleware('auth:sanctum');
            Route::get('show-time', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\VideoController::class, 'showTime'])->name('marketing.courses.video.show_time')->middleware('auth:sanctum');
            Route::patch('update-status', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\VideoController::class, 'updateStatus'])->name('marketing.courses.video.update_status')->middleware('auth:sanctum');
            Route::get('generate-url', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\VideoController::class, 'generatePresignedUrl'])->name('marketing.courses.video.generate_url')->middleware('auth:sanctum');
            Route::get('update-video-url/{id}/{name}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\VideoController::class, 'updateVideo'])->name('marketing.courses.video.update_url')->middleware('auth:sanctum');
        });

        // Cursos Comprados (Purchased Courses)
        Route::group(['prefix' => 'purchased'], function () {
            Route::post('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PurchasedCoursesController::class, 'store'])->name('marketing.courses.purchased.store')->middleware('auth:sanctum');
            Route::put('update', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PurchasedCoursesController::class, 'update'])->name('marketing.courses.purchased.update')->middleware('auth:sanctum');
            Route::get('show', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PurchasedCoursesController::class, 'show'])->name('marketing.courses.purchased.show')->middleware('auth:sanctum');
            Route::patch('save-class-seen', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PurchasedCoursesController::class, 'saveClassSeen'])->name('marketing.courses.purchased.save_class_seen')->middleware('auth:sanctum');
            Route::get('show-class-seen', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PurchasedCoursesController::class, 'showClassSeen'])->name('marketing.courses.purchased.show_class_seen')->middleware('auth:sanctum');
            Route::get('certificate-data', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PurchasedCoursesController::class, 'certificateData'])->name('marketing.courses.purchased.certificate_data')->middleware('auth:sanctum');
            Route::get('get-time', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PurchasedCoursesController::class, 'getTime'])->name('marketing.courses.purchased.get_time')->middleware('auth:sanctum');
        });
    });

    // Gamificacion: Puntos, Niveles, Insignias y Recompensas
    Route::group(['prefix' => 'gamification'], function () {
        // Ranking
        Route::get('ranking', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'ranking'])->name('marketing.gamification.ranking')->middleware('auth:sanctum');
        Route::get('my-info', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'myInfo'])->name('marketing.gamification.my_info')->middleware('auth:sanctum');

        // Configuracion de Puntos
        Route::get('config', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'getConfig'])->name('marketing.gamification.config');
        Route::put('config/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'updateConfig'])->name('marketing.gamification.config.update')->middleware('auth:sanctum');

        // Niveles de Usuario
        Route::get('levels', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'levelsIndex'])->name('marketing.gamification.levels');
        Route::post('levels', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'levelsStore'])->name('marketing.gamification.levels.store')->middleware('auth:sanctum');
        Route::put('levels/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'levelsUpdate'])->name('marketing.gamification.levels.update')->middleware('auth:sanctum');

        // Insignias (Badges)
        Route::get('badges', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'badgesIndex'])->name('marketing.gamification.badges')->middleware('auth:sanctum');
        Route::get('my-badges', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'myBadges'])->name('marketing.gamification.my_badges')->middleware('auth:sanctum');
        Route::post('badges', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'badgesStore'])->name('marketing.gamification.badges.store')->middleware('auth:sanctum');
        Route::put('badges/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'badgesUpdate'])->name('marketing.gamification.badges.update')->middleware('auth:sanctum');
        Route::delete('badges/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'badgesDestroy'])->name('marketing.gamification.badges.destroy')->middleware('auth:sanctum');

        // Recompensas (Admin)
        Route::get('rewards', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'rewardsIndex'])->name('marketing.gamification.rewards')->middleware('auth:sanctum');
        Route::post('rewards', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'rewardsStore'])->name('marketing.gamification.rewards.store')->middleware('auth:sanctum');
        Route::put('rewards/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'rewardsUpdate'])->name('marketing.gamification.rewards.update')->middleware('auth:sanctum');
        Route::delete('rewards/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'rewardsDestroy'])->name('marketing.gamification.rewards.destroy')->middleware('auth:sanctum');
        Route::post('rewards/{id}/restore', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'rewardsRestore'])->name('marketing.gamification.rewards.restore')->middleware('auth:sanctum');
        Route::get('rewards/stats', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'rewardsStats'])->name('marketing.gamification.rewards.stats')->middleware('auth:sanctum');

        // Insercion Manual de Puntos
        Route::post('points/insert', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'insertPoints'])->name('marketing.gamification.points.insert')->middleware('auth:sanctum');

        // Canjes (Redemptions)
        Route::get('redemptions', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'redemptionsIndex'])->name('marketing.gamification.redemptions')->middleware('auth:sanctum');
        Route::post('redemptions/{id}/process', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'processRedemption'])->name('marketing.gamification.redemptions.process')->middleware('auth:sanctum');

        // Recompensas (Usuario)
        Route::get('available-rewards', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'availableRewards'])->name('marketing.gamification.available_rewards')->middleware('auth:sanctum');
        Route::get('my-credits', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'myCredits'])->name('marketing.gamification.my_credits')->middleware('auth:sanctum');
        Route::post('redeem', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'redeemReward'])->name('marketing.gamification.redeem')->middleware('auth:sanctum');
        Route::get('my-redemptions', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\GamificationController::class, 'myRedemptions'])->name('marketing.gamification.my_redemptions')->middleware('auth:sanctum');
    });

    // Payment Accounts (PayPal & Binance accounts configuration)
    Route::group(['prefix' => 'payment', 'middleware' => 'auth:sanctum'], function () {
        Route::get('paypal-accounts', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\PaypalAccountController::class, 'index']);
        Route::post('paypal-accounts', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\PaypalAccountController::class, 'store']);
        Route::get('paypal-accounts/{id}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\PaypalAccountController::class, 'show']);
        Route::put('paypal-accounts/{id}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\PaypalAccountController::class, 'update']);
        Route::delete('paypal-accounts/{id}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\PaypalAccountController::class, 'destroy']);

        Route::get('binance-accounts', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\BinanceAccountController::class, 'index']);
        Route::post('binance-accounts', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\BinanceAccountController::class, 'store']);
        Route::get('binance-accounts/{id}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\BinanceAccountController::class, 'show']);
        Route::put('binance-accounts/{id}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\BinanceAccountController::class, 'update']);
        Route::delete('binance-accounts/{id}', [\Promolider\Infrastructure\Wallet\In\Http\Controllers\BinanceAccountController::class, 'destroy']);
    });

    // Payment Links (Enlaces de Pago)
    Route::group(['prefix' => 'payment-links'], function () {
        Route::get('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PaymentLinksController::class, 'index'])->name('marketing.payment_links.index');
        Route::post('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PaymentLinksController::class, 'store'])->name('marketing.payment_links.store')->middleware('auth:sanctum');
        Route::get('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PaymentLinksController::class, 'show'])->name('marketing.payment_links.show');
        Route::put('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PaymentLinksController::class, 'update'])->name('marketing.payment_links.update')->middleware('auth:sanctum');
        Route::patch('{id}/toggle', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PaymentLinksController::class, 'toggle'])->name('marketing.payment_links.toggle')->middleware('auth:sanctum');
        Route::delete('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PaymentLinksController::class, 'destroy'])->name('marketing.payment_links.destroy')->middleware('auth:sanctum');
    });

    // Tools (Herramientas) with constrained {type} to avoid route conflicts
    Route::get('tools', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'tools'])->name('marketing.tools')->middleware('auth:sanctum,can:marketing.tools');

    // Editable Pages (EdiTemplates)
    Route::group(['prefix' => 'edi-templates'], function () {
        Route::get('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\EditablePagesController::class, 'index'])->name('marketing.edi_templates.index')->middleware('auth:sanctum');
        Route::get('user/{userId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\EditablePagesController::class, 'userPages'])->name('marketing.edi_templates.user')->middleware('auth:sanctum');
        Route::get('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\EditablePagesController::class, 'show'])->name('marketing.edi_templates.show')->middleware('auth:sanctum');
        Route::get('public/{slug}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\EditablePagesController::class, 'publicPage'])->name('marketing.edi_templates.public');
        Route::post('/', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\EditablePagesController::class, 'store'])->name('marketing.edi_templates.store')->middleware('auth:sanctum');
        Route::put('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\EditablePagesController::class, 'update'])->name('marketing.edi_templates.update')->middleware('auth:sanctum');
        Route::delete('{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\EditablePagesController::class, 'destroy'])->name('marketing.edi_templates.destroy')->middleware('auth:sanctum');
    });

    // Campaigns: specific routes FIRST to avoid {type} collision
    Route::get('campaigns/mine', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'getUserCampaigns'])->name('marketing.campaigns.mine')->middleware('auth:sanctum,can:marketing.tools');
    Route::get('campaigns', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'getCampaigns'])->name('marketing.campaigns');
    Route::get('campaigns/{type}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'getCampaignsByType'])->name('marketing.campaigns.type');

    Route::get('categories', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'getCategories'])->name('marketing.categories');
    Route::post('categories', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'createCategory'])->name('marketing.categories.create')->middleware('auth:sanctum');

    // Tools CRUD (generic routes, literals FIRST, parameterized LAST)
    // IMPORTANTE: order específico para evitar que rutas con {type} capturen otras rutas
    Route::post('{type}/store', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'store'])->name('marketing.tools.store')->middleware('auth:sanctum,can:marketing.tools')->where('type', 'masterclass|ebook|mini-course|minicourse');
    Route::patch('{type}/{toolId}/status', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'updateStatus'])->name('marketing.tools.status')->middleware('auth:sanctum,can:marketing.tools')->where('type', 'masterclass|ebook|mini-course|minicourse');
    Route::delete('{type}/{toolId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'delete'])->name('marketing.tools.delete')->middleware('auth:sanctum,can:marketing.tools')->where('type', 'masterclass|ebook|mini-course|minicourse');
    Route::get('{type}/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'getTool'])->name('marketing.tools.get')->middleware('auth:sanctum,can:marketing.tools')->where('type', 'masterclass|ebook|mini-course|minicourse');
    Route::put('{type}/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketingToolsController::class, 'updateTool'])->name('marketing.tools.update')->middleware('auth:sanctum,can:marketing.tools')->where('type', 'masterclass|ebook|mini-course|minicourse');
});

// ==========================================
// Modulo: Registro y Preregistro
// ==========================================
Route::group(['prefix' => 'registration'], function () {
    // Preregistro (Landing publica)
    Route::get('preregistro/retorno/{token}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'retorno'])->name('registration.preregistro.retorno');
    Route::post('preregistro/openpay', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'openpay'])->name('registration.preregistro.openpay');

    // Webhook Openpay
    Route::post('preregistro/webhook/openpay', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'openpayWebhook'])->name('registration.preregistro.webhook.openpay');

    Route::get('preregistro/check-duplicate', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'checkDuplicate'])->name('registration.preregistro.check_duplicate');
    Route::get('preregistro/config/{username}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'config'])->name('registration.preregistro.config');
    Route::post('preregistro/radar', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'radar'])->name('registration.preregistro.radar');

    // Rutas publicas de integracion n8n
    Route::post('preregistro/resend-link', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'resendLink'])->name('registration.preregistro.resend_link');
    Route::get('preregistro/check-payment/{email}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'checkPaymentStatus'])->name('registration.preregistro.check_payment');

    // Mover ruta con comodin al final para evitar colisiones
    Route::post('preregistro/{username}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\PreregistroController::class, 'store'])->name('registration.preregistro.store');

    // Registro Principal
    Route::get('sponsor-link/{id}/{code}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'validateSponsorLink'])->name('registration.sponsor_link');
    Route::get('form-data', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'getFormData'])->name('registration.form_data');
    Route::post('check-availability', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'checkAvailability'])->name('registration.check_availability');
    Route::post('create', [\Promolider\Infrastructure\Registration\In\Http\Controllers\RegistrationController::class, 'create'])->name('registration.create');

    // Registro a Productos (Cursos, Ebooks, Masterclass)
    Route::post('ebook', [\Promolider\Infrastructure\Registration\In\Http\Controllers\EbookRegistrationController::class, 'register'])->name('registration.ebook');
    Route::post('ebook/{ebookId}', [\Promolider\Infrastructure\Registration\In\Http\Controllers\EbookRegistrationController::class, 'register'])->name('registration.ebook.specific');
    Route::post('minicourse', [\Promolider\Infrastructure\Registration\In\Http\Controllers\MinicourseRegistrationController::class, 'register'])->name('registration.minicourse');
    Route::post('masterclass', [\Promolider\Infrastructure\Registration\In\Http\Controllers\MasterclassRegistrationController::class, 'register'])->name('registration.masterclass');
});


// ==========================================
// Modulo: Marketplace — Registro como Distribuidor (autenticado)
// ==========================================
//
// IMPORTANTE: Usamos metodos wrapper para evitar el uso de ->defaults('type', ...)
// porque en esta version de PHP/Laravel, los defaults se mapean incorrectamente
// a los parametros del controller (se invierte el orden).
//
Route::middleware('auth:sanctum')->group(function () {
    // --- Masterclass ---
    Route::post('registration/distributor/masterclass', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'registerAsMasterclassDistributor'])
        ->name('registration.distributor.masterclass');
    Route::get('masterclass/check-registration/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'checkMasterclassRegistration'])
        ->name('masterclass.check.registration');
    Route::get('masterclass/check-invitation/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'checkMasterclassInvitation'])
        ->name('masterclass.check.invitation');
    Route::post('masterclass/create-invitation/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'createMasterclassInvitation'])
        ->name('masterclass.create.invitation');

    // --- Ebook ---
    Route::post('marketing/ebook/purchase/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'registerAsEbookDistributor'])
        ->name('ebook.purchase');
    Route::get('marketing/ebook/check-purchase/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'checkEbookRegistration'])
        ->name('ebook.check.purchase');
    Route::get('marketing/ebook/check-invitation/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'checkEbookInvitation'])
        ->name('ebook.check.invitation');
    Route::post('marketing/ebook/invitation-link/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'createEbookInvitation'])
        ->name('ebook.create.invitation');

    // --- Mini Course ---
    Route::post('marketing/mini-course/invitation/purchase/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'registerAsMinicourseDistributor'])
        ->name('minicourse.purchase');
    Route::get('marketing/mini-course/invitation/check-purchase/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'checkMinicourseRegistration'])
        ->name('minicourse.check.purchase');
    Route::get('marketing/mini-course/invitation/check-invitation/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'checkMinicourseInvitation'])
        ->name('minicourse.check.invitation');
    Route::post('marketing/mini-course/invitation/invitation-link/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'createMinicourseInvitation'])
        ->name('minicourse.create.invitation');

    // Unified route (has {type} in URI, no defaults needed)
    Route::post('registration/marketplace/{type}/{productId}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'registerAsDistributor'])
        ->name('registration.marketplace.register')
        ->where('type', 'masterclass|ebook|mini-course|minicourse');
});

// ==========================================
// Modulo: Productos Publicos — Invitacion y Registro de Estudiantes
// ==========================================
Route::get('products/invitation/{code}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'getInvitationInfo'])
    ->name('products.invitation.info');
Route::post('products/register', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'registerStudent'])
    ->name('products.register.student');

// ==========================================
// Modulo: Mini Curso — Acceso Publico via Token
// ==========================================
Route::get('minicourse/access/{id}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\MarketplaceRegistrationController::class, 'accessMiniCourse'])
    ->name('minicourse.access');

// ==========================================
// Modulo: Paginas Publicas (sin autenticacion)
// ==========================================
Route::get('pages/public/{slug}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PagesController::class, 'getPublicPage'])->name('pages.public.show');

// ==========================================
// Modulo: Dinamicas Publicas (sin autenticacion, con sesion por cookie)
// ==========================================
Route::group(['prefix' => 'd', 'middleware' => [
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
]], function () {
    Route::get('{slug}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'show'])->name('dinamica.public.show');
    Route::post('{slug}/register', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'register'])->name('dinamica.public.register');
    Route::post('{slug}/spin', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'spin'])->name('dinamica.public.spin');
    Route::get('{slug}/status', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'status'])->name('dinamica.public.status');
    Route::get('{slug}/participants-feed', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'participantsFeed'])->name('dinamica.participants.feed');
    Route::post('{slug}/marcar-jugado', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'marcarJugado'])->name('dinamica.public.marcar_jugado');
    Route::post('{slug}/registrar-ganador', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'registrarGanador'])->name('dinamica.public.registrar_ganador');

    // Trivia routes under /d/{slug}/
    Route::get('{slug}/preview', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'triviaPreview'])->name('dinamica.public.trivia.preview');
    Route::get('{slug}/pregunta/{numero}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'triviaQuestion'])->name('dinamica.public.trivia.question');
    Route::post('{slug}/pregunta/{numero}/respuesta', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'submitAnswer'])->name('dinamica.public.trivia.answer');
    Route::get('{slug}/resultados', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'triviaResults'])->name('dinamica.public.trivia.results');
});

// ==========================================
// Modulo: Payment Links Publicos (sin autenticacion)
// ==========================================
Route::group(['prefix' => 'pay'], function () {
    Route::get('{slug}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\PaymentLinksController::class, 'publicShow'])->name('payment_links.public.show');
});

// Trivia alias routes for backward compatibility
Route::group(['prefix' => 'trivia'], function () {
    Route::get('{slug}/preview', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'triviaPreview'])->name('trivia.preview');
    Route::get('{slug}/pregunta/{numero}', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'triviaQuestion'])->name('trivia.question');
    Route::post('{slug}/pregunta/{numero}/respuesta', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'submitAnswer'])->name('trivia.answer');
    Route::get('{slug}/resultados', [\Promolider\Infrastructure\Marketing\In\Http\Controllers\DinamicaPublicController::class, 'triviaResults'])->name('trivia.results');
});
