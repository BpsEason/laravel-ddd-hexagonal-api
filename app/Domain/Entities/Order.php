<?php

namespace App\Domain\Entities;

use App\Domain\Events\OrderPlaced; // 引入領域事件
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use DateTimeImmutable; // 確保 DateTimeImmutable 被正確引用

class Order
{
    private string $id;
    private string $customerId;
    private Collection $items; // Collection of OrderItem
    private float $totalAmount;
    private string $status;
    private DateTimeImmutable $createdAt; // 使用 DateTimeImmutable

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
        $this->createdAt = new DateTimeImmutable();
        $this->calculateTotalAmount();
    }

    public static function create(string $customerId, Collection $items): self
    {
        $order = new self($customerId, $items);
        // 記錄領域事件：訂單已下
        $order->recordEvent(new OrderPlaced($order->id, $order->customerId, $order->totalAmount));
        return $order;
    }

    /**
     * 從持久化數據重建 Order 領域實體。
     * 這個方法通常由 Repository 調用。
     */
    public static function fromPersistence(
        string $id,
        string $customerId,
        Collection $items,
        float $totalAmount,
        string $status,
        string $createdAt // 從資料庫讀取可能是字串，這裡接收字串
    ): self {
        $order = new self($customerId, $items); // 先調用私有構造函數設置基本屬性

        // 通過反射設置私有屬性，或者在構造函數中添加內部建構邏輯
        // 這裡演示最直接的反射方式，更推薦在 Order 內部設計專門的 private fromRawData 類似方法
        $reflection = new \ReflectionClass(self::class);

        $idProp = $reflection->getProperty('id');
        $idProp->setAccessible(true);
        $idProp->setValue($order, $id);

        // customerId 和 items 已經在 constructor 中設置
        // totalAmount 和 status 可能需要根據從 DB 讀取的值重新設定
        $totalAmountProp = $reflection->getProperty('totalAmount');
        $totalAmountProp->setAccessible(true);
        $totalAmountProp->setValue($order, $totalAmount);

        $statusProp = $reflection->getProperty('status');
        $statusProp->setAccessible(true);
        $statusProp->setValue($order, $status);

        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setAccessible(true);
        // 確保從資料庫讀取的日期字串被轉換為 DateTimeImmutable 物件
        $createdAtProp->setValue($order, new DateTimeImmutable($createdAt));

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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    // --- Domain Event Management ---
    public function recordEvent(object $event): void
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