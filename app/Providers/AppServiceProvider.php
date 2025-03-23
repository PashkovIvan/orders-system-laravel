<?php

namespace App\Providers;

use App\Contracts\Messages\Consumer\MessageConsumerInterface;
use App\Contracts\Messages\Producer\MessageProducerInterface;
use App\Messages\Consumers\OrderMessageConsumer;
use App\Messages\Producers\OrderMessageProducer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MessageProducerInterface::class, OrderMessageProducer::class);
        $this->app->bind(MessageConsumerInterface::class, OrderMessageConsumer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
