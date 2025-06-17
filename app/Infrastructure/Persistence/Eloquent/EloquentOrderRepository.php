<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Entities\Order as DomainOrder;
use App\Domain\Entities\OrderItem as DomainOrderItem;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Models\Order as EloquentOrderModel;
use App\Infrastructure\Persistence\Models\OrderItem as EloquentOrderItemModel;
use Illuminate\Support\Collection;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function save(DomainOrder $order): void
    {
        // 將 Domain Entity 轉換為 Eloquent Model 進行持久化
        $eloquentOrder = EloquentOrderModel::firstOrNew(['id' => $order->getId()]);
        $eloquentOrder->customer_id = $order->getCustomerId();
        $eloquentOrder->total_amount = $order->getTotalAmount();
        $eloquentOrder->status = $order->getStatus();
        // 如果是新訂單，設置創建時間，否則更新時間由 Eloquent 自動處理
        if (!$eloquentOrder->exists) {
            $eloquentOrder->created_at = $order->getCreatedAt();
        }
        $eloquentOrder->save();

        // 處理訂單項
        // 通常對於新訂單，直接創建新項。對於更新訂單，需要複雜的同步邏輯。
        // 此處簡化為：對於新訂單，直接新增所有項目
        if (!$eloquentOrder->wasRecentlyCreated) {
            // 如果是更新訂單，這裡需要更複雜的邏輯來處理 order items 的增刪改
            // 簡化範例中，假設 order items 不會被修改或刪除，只在創建時一次性保存
            // 實際應用中，可能需要先刪除舊的 items 再重新創建，或者逐一比較更新
        } else {
            foreach ($order->getItems() as $domainOrderItem) {
                $eloquentOrderItem = new EloquentOrderItemModel([
                    'order_id' => $eloquentOrder->id,
                    'product_id' => $domainOrderItem->getProductId(),
                    'product_name' => $domainOrderItem->getProductName(),
                    'unit_price' => $domainOrderItem->getUnitPrice(),
                    'quantity' => $domainOrderItem->getQuantity(),
                ]);
                $eloquentOrder->items()->save($eloquentOrderItem);
            }
        }
    }

    public function findById(string $orderId): ?DomainOrder
    {
        $eloquentOrder = EloquentOrderModel::with('items')->find($orderId);

        if (!$eloquentOrder) {
            return null;
        }

        // 將 Eloquent Model 轉換回 Domain Entity
        $domainOrderItems = new Collection();
        foreach ($eloquentOrder->items as $item) {
            $domainOrderItems->push(new DomainOrderItem(
                $item->product_id,
                $item->product_name,
                $item->unit_price,
                $item->quantity
            ));
        }

        // 由於 Domain Order 的構造函數是 private，這裡需要一個 FromState 方法或使用反射
        // 為了簡化，這裡假設我們可以通過反射或其他方式來構造
        // 實際中，你可能會在 Domain Order 中提供一個 `fromPersistence` 靜態方法
        // 或者直接在領域實體中注入一個私有的靜態工廠方法
        $reflectionClass = new \ReflectionClass(DomainOrder::class);
        $order = $reflectionClass->newInstanceWithoutConstructor();

        // 使用反射設置屬性
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($order, $eloquentOrder->id);

        $customerIdProperty = $reflectionClass->getProperty('customerId');
        $customerIdProperty->setAccessible(true);
        $customerIdProperty->setValue($order, $eloquentOrder->customer_id);

        $itemsProperty = $reflectionClass->getProperty('items');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue($order, $domainOrderItems);

        $totalAmountProperty = $reflectionClass->getProperty('totalAmount');
        $totalAmountProperty->setAccessible(true);
        $totalAmountProperty->setValue($order, (float)$eloquentOrder->total_amount);

        $statusProperty = $reflectionClass->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($order, $eloquentOrder->status);

        $createdAtProperty = $reflectionClass->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($order, new \DateTimeImmutable($eloquentOrder->created_at));


        return $order;
    }
}