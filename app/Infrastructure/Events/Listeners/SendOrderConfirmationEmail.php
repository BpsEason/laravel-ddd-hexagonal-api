<?php

namespace App\Infrastructure\Events\Listeners;

use App\Domain\Events\OrderPlaced; // 引入領域事件
use Illuminate\Contracts\Queue\ShouldQueue; // 如果希望異步處理，可以實現此介面
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log; // 用於日誌記錄

class SendOrderConfirmationEmail implements ShouldQueue // 實現 ShouldQueue 介面以異步處理
{
    use InteractsWithQueue; // 提供異步處理所需的方法

    /**
     * 處理訂單下單事件。
     *
     * @param  \App\Domain\Events\OrderPlaced  $event
     * @return void
     */
    public function handle(OrderPlaced $event): void
    {
        // 實際應用中，這裡會調用一個郵件服務來發送郵件
        // 例如：Mail::to($event->customerEmail)->send(new OrderConfirmation($event->orderId));

        Log::info("Sending order confirmation email for Order ID: {$event->orderId}");
        Log::info("Customer ID: {$event->customerId}, Total Amount: {$event->totalAmount}");

        // 模擬發送郵件的耗時操作
        sleep(2);

        Log::info("Order confirmation email sent successfully for Order ID: {$event->orderId}");
    }

    /**
     * 處理作業失敗。
     *
     * @param  \App\Domain\Events\OrderPlaced  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(OrderPlaced $event, Throwable $exception): void
    {
        Log::error("Failed to send order confirmation email for Order ID: {$event->orderId}. Error: " . $exception->getMessage());
        // 可以記錄到 Sentry 或其他監控系統
    }
}