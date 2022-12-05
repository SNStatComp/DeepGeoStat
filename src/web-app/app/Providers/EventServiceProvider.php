<?php

namespace App\Providers;

use App\Models\Dataset;
use App\Models\Experiment;
use App\Models\LabelEvidence;
use App\Models\Prediction;
use App\Observers\DatasetObserver;
use App\Observers\ExperimentObserver;
use App\Observers\LabelEvidenceObserver;
use App\Observers\PredictionObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Dataset::class => [DatasetObserver::class],
        LabelEvidence::class => [LabelEvidenceObserver::class],
        Experiment::class => [ExperimentObserver::class],
        Prediction::class => [PredictionObserver::class],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
