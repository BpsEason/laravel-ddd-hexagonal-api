<?php

namespace App\Application\Handlers;

use App\Application\Commands\PlaceOrderCommand;
use App\Application\DTOs\OrderCreatedDTO;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Domain\Services\OrderPlacementService;
use Illuminate\Events\Dispatcher; // 用於發布領域事件
use Illuminate\Support\Facades\DB; // 用於事務管理

class PlaceOrderCommandHandler
{
    private OrderPlacementService $orderPlacementService;
    private OrderRepositoryInterface $orderRepository;
    private Dispatcher $eventDispatcher; // Laravel 的事件發布器

    public function __construct(
        OrderPlacementService $orderPlacementService,
        OrderRepositoryInterface $orderRepository,
        Dispatcher $eventDispatcher
    ) {
        $this->orderPlacementService = $orderPlacementService;
        $this->orderRepository = $orderRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(PlaceOrderCommand $command): OrderCreatedDTO
    {
        return DB::transaction(function () use ($command) {
            // 1. 執行核心業務邏輯 (由領域服務負責)
            $order = $this->orderPlacementService->createOrder(
                $command->customerId,
                $command->cartItems
            );

            // 2. 持久化訂單 (由基礎設施層的 Repository 實作)
            $this->orderRepository->save($order);

            // 3. 發布所有在領域實體中記錄的領域事件
            foreach ($order->releaseEvents() as $event) {
                $this->eventDispatcher->dispatch($event);
            }

            // 4. 返回 DTO
            return new OrderCreatedDTO($order->getId(), $order->getTotalAmount());
        });
    }
}