<?php

namespace App\Domain\Entities;

use App\Domain\Events\OrderPlaced; // 引入領域事件
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Order
{
    private string $id;
    private string $customerId;
    private Collection $items; // Collection of OrderItem
    private float $totalAmount;
    private string $status;
    private \DateTimeImmutable $createdAt;

    private array $recordedEvents = []; // 用於記錄領域事件

    private function __construct(string $customerId, Collection $items)
    {
        if ($items->isEmpty()) {
            throw new \InvalidArgumentException("Order must have at least one item.");
        }

        $this->id = (string) Str::uuid();
        $this->customerId = $customerId;
        $this->items = $items;
        $this->status = 'pending'; // 初始狀態
        $this->createdAt = new \DateTimeImmutable();
        $this->calculateTotalAmount();
    }

    public static function create(string $customerId, Collection $items): self
    {
        $order = new self($customerId, $items);
        // 記錄領域事件：訂單已下
        $order->recordEvent(new OrderPlaced($order->id, $order->customerId, $order->totalAmount));
        return $order;
    }

    private function calculateTotalAmount(): void
    {
        $this->totalAmount = $this->items->sum(fn(OrderItem $item) => $item->getTotalPrice());
    }

    public function addItem(OrderItem $item): void
    {
        $this->items->push($item);
        $this->calculateTotalAmount();
    }

    public function markAsPaid(): void
    {
        if ($this->status !== 'pending') {
            throw new \RuntimeException("Order cannot be marked as paid from status: " . $this->status);
        }
        $this->status = 'paid';
        // 觸發支付成功事件 (如果需要，這裡可以記錄另一個領域事件)
    }

    // --- Getters ---
    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getItems(): Collection
    {
        return $this->items->map(fn($item) => clone $item); // 返回副本防止外部修改
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    // --- Domain Event Management ---
    protected function recordEvent($event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = []; // 清空已發布的事件
        return $events;
    }
}