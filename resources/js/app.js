// ...existing code...
import axios from 'axios';
import moment from 'moment';
import Vue from 'vue';
import ElementUI from 'element-ui';
import PerfectScrollbar from 'vue2-perfect-scrollbar';
import Vue2OrgTree from 'vue2-org-tree';
import lang from 'element-ui/lib/locale/lang/es';
import locale from 'element-ui/lib/locale';
import Swal from 'sweetalert2'
import 'element-ui/lib/theme-chalk/index.css';
locale.use(lang);
Vue.use(ElementUI);
Vue.use(PerfectScrollbar);
Vue.use(Vue2OrgTree);

require('./bootstrap');

window.Vue = () => import('vue');

Vue.filter('formatDate', function (value) {
  if (value) {
    return moment(value).add(1, 'day').format('LLL');
  }
});
Vue.prototype.can = function (value) {
  return window.Laravel.jsPermissions.permissions.includes(value);
};
Vue.prototype.$swal = Swal
Vue.component('roles', () => import('./components/role/Roles'));
Vue.component('course-index', () => import('./components/course/Courses'));
Vue.component('courses-table', () => import('./components/course/coursesTable'));
Vue.component('marketplace-table', () => import('./components/marketplace/MarketplaceTable'));
Vue.component('dynamic-class', () => import('./components/dinamics/view/DinamicClass'));
Vue.component(
  'courses-table-verification',
  () => import('./components/course/verification/coursesVerification')
);
Vue.component('course-create', () => import('./components/course/CoursesCreate'));
Vue.component('course-create-exam', () => import('./components/course/exam/createExam'));
Vue.component('module-create-exam', () => import('./components/course/exam/module/createExam'));
Vue.component('lesson-create-exam', () => import('./components/course/exam/lesson/createExam'));
Vue.component('course-edit-exam', () => import('./components/course/exam/editExam'));
Vue.component('course-edit-game-1', () => import('./components/course/game/editGame1'));
Vue.component('course-edit-game-2', () => import('./components/course/game/editGame2'));
Vue.component('course-edit-game-3', () => import('./components/course/game/editGame3'));
Vue.component('course-edit-game-4', () => import('./components/course/game/editGame4'));
Vue.component('course-edit-game-5', () => import('./components/course/game/editGame5'));
Vue.component('course-edit-game-6', () => import('./components/course/game/editGame6'));
Vue.component('create-item', () => import('./components/course/game/item/create'));
Vue.component('course-game-item', () => import('./components/course/game/item/item'));
Vue.component('course-gamification', () => import('./components/course/gamification/index'));
Vue.component('course-students', () => import('./components/course/StudentsList'));

//masterclass
Vue.component('masterclass-table', () => import('./components/masterclass/masterclassTable'));
Vue.component('masterclass-create', () => import('./components/masterclass/MasterclassCreate'));
Vue.component('masterclass-card', () => import('./components/masterclass/marketplace/masterclassCard'));
Vue.component('masterclass-details', () => import('./components/masterclass/masterclassDetails'));
Vue.component('masterclass-request', () => import('./components/masterclass/MasterclassRequest'));
Vue.component('masterclass-report', () => import('./components/masterclass/MarketingReports.vue'));
Vue.component('general-report', () => import('./components/masterclass/GeneralReports.vue'));
Vue.component('masterclass-calendar', () => import('./components/masterclass/masterclassCalendar'));
Vue.component('masterclass-students', () => import('./components/masterclass/masterclassStudent'));
Vue.component('freecourse-create', () => import('./components/masterclass/freecourse/FreecourseCreate.vue'));

//marketing:
Vue.component('tools', () => import('./components/marketing/tools.vue'));
Vue.component('marketing-request', () => import('./components/marketing/MarketingRequest.vue'));
Vue.component('mini-course-modules-form', () => import('./components/marketing/MiniCourseModulesForm.vue'));
Vue.component('mini-courses-summary', () => import('./components/marketing/MiniCoursesSummary.vue'));
Vue.component('mini-course-form', () => import('./components/marketing/MiniCourseForm.vue'));
Vue.component('dinamica-form', () => import('./components/marketing/DinamicaForm.vue'));
Vue.component('dinamica-specifications-form', () => import('./components/marketing/DinamicaSpecificationsForm.vue'));
Vue.component('trivia-dynamic-designer', () => import('./components/marketing/TriviaDynamicDesigner.vue'));
Vue.component('trivia-form-builder', () => import('./components/marketing/TriviaFormBuilder.vue'));
Vue.component('question-category-list', () => import('./components/marketing/QuestionCategoryList.vue'));
Vue.component('question-category-form', () => import('./components/marketing/QuestionCategoryForm.vue'));
Vue.component('question-category-detail', () => import('./components/marketing/QuestionCategoryDetail.vue'));
Vue.component('question-item-form', () => import('./components/marketing/QuestionItemForm.vue'));
Vue.component('dinamica-ruleta', () => import('./components/marketing/DinamicaRuleta.vue'));
Vue.component('ebook-form', () => import('./components/marketing/EbookForm.vue'));
Vue.component('calendar-component', () => import('./components/marketing/CalendarComponent.vue'));
Vue.component('campaigns-card', () => import('./components/marketing/CampaignsCard.vue'));
Vue.component('ebook-card', () => import('./components/marketing/EbookCard.vue'));
Vue.component('mini-course-card', () => import('./components/marketing/MiniCourseCard.vue'));
Vue.component('ebook-details', () => import('./components/marketing/EBookDetails'));
Vue.component('minicourse-details', () => import('./components/marketing/MiniCourseDetails'));
Vue.component('ebook-edit', () => import('./components/marketing/EbookEditModal'));
Vue.component('list-distributors', () => import('./components/marketing/ListDistributors'));
Vue.component('mini-course-edit', () => import('./components/marketing/MiniCourseEditModal'));
Vue.component('masterclass-edit', () => import('./components/marketing/MasterclassEditModal'));
Vue.component('mini-course-viewer', () => import('./components/marketing/MiniCourseViewer'));
Vue.component('template-gallery', () => import('./components/marketing/pages/TemplateGallery'));
Vue.component('my-templates-tab', () => import('./components/marketing/pages/MyTemplatesTab'));
Vue.component('gallery-view', () => import('./components/marketing/pages/GalleryView'));
Vue.component('editor-view', () => import('./components/marketing/pages/EditorView'));
Vue.component('editor-header', () => import('./components/marketing/pages/EditorHeader'));
Vue.component('live-preview', () => import('./components/marketing/pages/LivePreview'));
Vue.component('preview-view', () => import('./components/marketing/pages/PreviewView'));
Vue.component('temporizador-turno', () => import('./components/marketing/TemporizadorTurno.vue'));

Vue.component(
  'module-gamification',
  () => import('./components/course/gamification/indexModule')
);
Vue.component(
  'lesson-gamification',
  () => import('./components/course/gamification/indexLesson')
);
Vue.component(
  'course-certificate-config',
  () => import('./components/course/certificate/configure')
);
Vue.component(
  'course-exam-question-item',
  () => import('./components/course/exam/examCuestion')
);
Vue.component('rate-exam', () => import('./components/course/exam/rate'));
Vue.component(
  'course-exam-question-details',
  () => import('./components/course/exam/detailQuestion')
);
Vue.component('course-edit', () => import('./components/course/CoursesEdit'));
Vue.component(
  'user-level-table',
  () => import('./components/gamification/user-levels/userLevelTable')
);
Vue.component('badges-table', () => import('./components/gamification/badges/badgesTable'));
Vue.component(
  'badges-user-view',
  () => import('./components/gamification/badges/user-view-badges')
);
Vue.component('notification-view', () => import('./components/notification/notification'));
Vue.component(
  'course-module-list',
  () => import('./components/course/modules/CourseModuleList')
);
Vue.component('course-add-module', () => import('./components/course/modules/addModule'));
Vue.component('class-edit', () => import('./components/course/modules/classes/editClass'));
Vue.component('class-view', () => import('./components/course/modules/classes/viewClass'));
Vue.component('course-list', () => import('./components/course/subscriber/CourseList'));
Vue.component('review-course', () => import('./components/course/verification/review.vue'));
Vue.component('edit-book-content', () => import('./components/book/EditBookContent.vue'));
Vue.component('review-book', () => import('./components/course/verification/reviewBook.vue'));
Vue.component('categories-table', () => import('./components/category/CategoriesTable.vue'));
Vue.component('category-create', () => import('./components/category/CategoryCreate.vue'));
Vue.component('setting-profile-user', () => import('./components/setting/ProfileUser'));
Vue.component('setting-security-user', () => import('./components/setting/Security'));
Vue.component('billing-plans-profile', () => import('./components/setting/Billing-Plans'));
Vue.component('account-profile', () => import('./components/setting/Account-Profile.vue'));
Vue.component('share-link-profile', () => import('./components/setting/ShareLink.vue'));
Vue.component('share-link-registro', () => import('./components/setting/ShareLinkRegistro.vue'));
Vue.component('course-request', () => import('./components/course/CourseRequest.vue'));
Vue.component('chatgpt-chat', () => import('./components/chatgpt/Gpt.vue'));
Vue.component('rank-bonus-progress', () => import('./components/gamification/RankBonusProgress.vue'));
Vue.component('payment', () => import('./components/payment/Payment'));
/** Start components Config */
Vue.component('bank', () => import('./components/config/bank/Bank'));
Vue.component('advertisement', () => import('./components/config/advertisement/Advertisement'));
Vue.component(
  'payment-method',
  () => import('./components/config/payment-method/PaymentMethod')
);
Vue.component(
  'classroom-point-config',
  () => import('./components/config/classroom-point-config/ClassroomPointConfig.vue')
);
Vue.component(
  'config-option',
  () => import('./components/config/config-option/ConfigOption.vue')
);
Vue.component('users-list', () => import('./components/users/List'));
Vue.component('bonus-component', () => import('./components/config/BonusComponent.vue'));
Vue.component('account-type', () => import('./components/config/account-type/AccountType'));
Vue.component('user-request', () => import('./components/users/UserRequest.vue'));
Vue.component('role-request', () => import('./components/role/RoleRequest.vue'));
Vue.component('role-tool-request', () => import('./components/role/RoleToolsRequest.vue'));

/** End components Config */

// User membreship
Vue.component('binary-branch', () => import('./components/binary-branch/BinaryBranch'));
Vue.component('payment-request', () => import('./components/payment/PaymentRequest.vue'));
Vue.component('modal-user', () => import('./components/users/ModalUser.vue'));
Vue.component('tree-component', () => import('./components/binary-branch/TreeComponent.vue'));
Vue.component('tree-binary', () => import('./components/binary-branch/BinaryComponent.vue'));
Vue.component('adjust-leg', () => import('./components/binary-branch/AdjustLeg.vue'));
Vue.component('user-status', () => import('./components/users/UserStatus.vue'));
Vue.component('user-dash', () => import('./components/users/UserDash.vue'));
Vue.component('binary-tree', () => import('./components/binary-branch/BinaryTree.vue'));
Vue.component('points-buttons', () => import('./components/gamification/PointsButtons.vue'));
Vue.component('wallet-history-user', () => import('./components/wallet/WalletHistoryUser.vue'));
Vue.component('rewards-redemption', () => import('./components/wallet/RewardsRedemption.vue'));
Vue.component(
  'wallet-history-complete',
  () => import('./components/wallet/WalletHistoryComplete.vue')
);
Vue.component('wallet-my-purchase', () => import('./components/wallet/WalletMyPurchase.vue'));
Vue.component('wallet-config', () => import('./components/wallet/ConfigWallet.vue'));
Vue.component('wallet-my-sales', () => import('./components/wallet/WalletMySales.vue'));
//Vue.component('wallet-proyeccion', () => import('./components/wallet/WalletProyeccion.vue'));
Vue.component('wallet-historial', () => import('./components/wallet/WalletHistorial.vue'));
Vue.component('users-funds', () => import('./components/wallet/UsersFunds.vue'));
Vue.component('user-bonuses', () => import('./components/wallet/UserBonuses.vue'));
Vue.component(
  'certificates-configurations',
  () => import('./components/config/certificates/certificates.vue')
);
Vue.component('settings', () => import('./components/config/settings/Settings.vue'));
Vue.component(
  'frequent-questions',
  () => import('./components/config/frequent-questions/FrequentQuestions.vue')
);
Vue.component('course-form', () => import('./components/course/CourseForm.vue'));
Vue.component('aviso', () => import('./components/common/aviso.vue'));
Vue.component('trivia-main', () => import('./components/marketing/TriviaMain.vue'));
Vue.component('trivia-public-main', () => import('./components/marketing/TriviaPublicMain.vue'));
Vue.component('ruleta-public-main', () => import('./components/marketing/RuletaPublicMain.vue'));
Vue.component('trivia-pregunta', () => import('./components/marketing/TriviaPregunta.vue'));
Vue.component('trivia-resultados', () => import('./components/marketing/TriviaResultados.vue'));
Vue.component('ruleta-socket-test', () => import('./components/marketing/RuletaSocketTest.vue'));

Vue.component('simple-wheel', () => import('./components/marketing/SimpleWheel.vue'));
Vue.component('websocket-test', () => import('./components/testing/WebSocketTest.vue'));
Vue.component('show-modules', () => import('./components/course/ShowModules'));

// Vue.component('preregistro-app', () => import('./components/preregistro/App.vue'));
// 🟢 APP PRINCIPAL
if (document.getElementById('app')) {
    new Vue({
        el: '#app',
    });
}

// 🟢 PREREGISTRO
if (document.getElementById('preregistro')) {
    new Vue({
        el: '#preregistro',
    });
}

