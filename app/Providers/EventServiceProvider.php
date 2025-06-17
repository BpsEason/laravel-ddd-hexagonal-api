<?php

namespace App\Providers;

use App\Domain\Events\OrderPlaced; // 引入領域事件
use App\Infrastructure\Events\Listeners\SendOrderConfirmationEmail; // 引入監聽器
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // ... 其他事件監聽

        // 註冊領域事件 OrderPlaced 及其監聽器
        OrderPlaced::class => [
            SendOrderConfirmationEmail::class,
            // 如果有其他需要響應 OrderPlaced 事件的監聽器，可以在這裡添加
            //例如：\App\Listeners\LogOrderCreation::class,
            // \App\Listeners\UpdateAnalytics::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // 這裡可以根據您的項目需求設置為 true 或 false
    }
}