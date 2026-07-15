<?php

namespace Promolider\Infrastructure\Marketing\Providers;

use Illuminate\Support\ServiceProvider;
use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\MarketplaceRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\CalendarRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\ReportRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\FreeCourseRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\ProductDistributorRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\GameCommentRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\EditablePageRepositoryInterface;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentToolRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentMarketplaceRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentCalendarRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentPageRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentReportRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentDinamicaRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentQuestionCategoryRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentPaymentLinkRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentFreeCourseRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentProductDistributorRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentGamificationRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentCourseRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentGameCommentRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentEditablePageRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentExamRepository;
use Promolider\Infrastructure\Marketing\Out\Persistence\EloquentPurchasedCourseRepository;
use Promolider\Domain\Marketing\Ports\Out\ExamRepositoryInterface;
use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ToolRepositoryInterface::class, EloquentToolRepository::class);
        $this->app->bind(MarketplaceRepositoryInterface::class, EloquentMarketplaceRepository::class);
        $this->app->bind(CalendarRepositoryInterface::class, EloquentCalendarRepository::class);
        $this->app->bind(PageRepositoryInterface::class, EloquentPageRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, EloquentReportRepository::class);
        $this->app->bind(DinamicaRepositoryInterface::class, EloquentDinamicaRepository::class);
        $this->app->bind(QuestionCategoryRepositoryInterface::class, EloquentQuestionCategoryRepository::class);
        $this->app->bind(PaymentLinkRepositoryInterface::class, EloquentPaymentLinkRepository::class);
        $this->app->bind(FreeCourseRepositoryInterface::class, EloquentFreeCourseRepository::class);
        $this->app->bind(ProductDistributorRepositoryInterface::class, EloquentProductDistributorRepository::class);
        $this->app->bind(GamificationRepositoryInterface::class, EloquentGamificationRepository::class);
        $this->app->bind(CourseRepositoryInterface::class, EloquentCourseRepository::class);
        $this->app->bind(GameCommentRepositoryInterface::class, EloquentGameCommentRepository::class);
        $this->app->bind(EditablePageRepositoryInterface::class, EloquentEditablePageRepository::class);
        $this->app->bind(ExamRepositoryInterface::class, EloquentExamRepository::class);
        $this->app->bind(PurchasedCourseRepositoryInterface::class, EloquentPurchasedCourseRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
