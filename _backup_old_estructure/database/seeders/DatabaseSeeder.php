<?php

namespace Database\Seeders;

use App\FunctionsSeeder;
use App\Http\Controllers\DailyQuestion;
use App\Models\Classified;
use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\BankSeeder;
use Database\Seeders\WalletSeeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\CountrySeeder;
use Database\Seeders\PaymentSeeder;
use Database\Seeders\ClassifiedSeeder;
use Database\Seeders\AccountTypeSeeder;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\AdvertisementSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\UserMembreshipSeeder;
use Database\Seeders\AccountTypePoinstMoneySeeder;
use Database\Seeders\CoursePaymentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\MessageSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RolesUser;
use Database\Seeders\CourseUserSeeder;
use Database\Seeders\ClassroomPointConfigSeeder;
use Database\Seeders\ClassroomPointDetailSeeder;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\OptionSeeder;
use Database\Seeders\OpenPaySeeder;
use Database\Seeders\CertificatesSeeder;
use Database\Seeders\UserConfigurationSeeder;
use Database\Seeders\UserClassroomPointSeeder;
use Database\Seeders\DefaultAvatarSeeder;
use Database\Seeders\frequentQuestionSeeder;
use Database\Seeders\ExamCourseSeeder;
use Database\Seeders\ExamCourseQuestionSeeder;
use Database\Seeders\UserDailyQuizzSeeder;
use Database\Seeders\PruebasApiJuegosSeeder;
use Database\Seeders\GenerationalBonusesSeeder;
use Database\Seeders\NewPermissionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CategorySeeder::class);
        $this->call(CourseLevelSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(AccountTypeSeeder::class);
        $this->call(PaymentMethodSeeder::class);

        $this->call(UserSeeder::class);
        $this->call(RolesUserSeeder::class);
        $this->call(ClassifiedSeeder::class);
        $this->call(AccountTypePoinstMoneySeeder::class);
        $this->call(WalletSeeder::class);
        $this->call(DefaultAvatarSeeder::class);
        $this->call(frequentQuestionSeeder::class);
        $this->call(UserDailyQuizzSeeder::class);
        $this->call(RankBonusSeeder::class);
        $this->call(BadgeSeeder::class);
        $this->call(QuestionTypeSeeder::class);
        $this->call(ClassroomPointConfigSeeder::class);
        $this->call(ExamTypeSeeder::class);
        $this->call(GameTypeSeeder::class);
        $this->call(ConfigurationSeeder::class);
        $this->call(OptionSeeder::class);
        $this->call(OpenPaySeeder::class);
        $this->call(UserConfigurationSeeder::class);
        $this->call(CertificatesSeeder::class);
        $this->call(UserLevelSeeder::class);
        $this->call(BonusTypeSeeder::class);
        $this->call(ExpansionBonusSeeder::class);
        $this->call(GenerationalBonusesSeeder::class);
        $this->call(NewPermissionsSeeder::class);

        // $this->call(CourseSeeder::class);
        // $this->call(ModuleSeeder::class);
        // $this->call(ClassSeeder::class);
        // $this->call(ClassResourceSeeder::class);
        // $this->call(CourseVideoSeeder::class);
        // $this->call(ClassroomPointDetailSeeder::class);
        $this->call(UserClassroomPointSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(PruebasApiJuegosSeeder::class);
        // $this->call(ExamCourseSeeder::class);
        // $this->call(ExamCourseQuestionSeeder::class);
        // $this->call(BadgeDetailSeeder::class);
    }
}
