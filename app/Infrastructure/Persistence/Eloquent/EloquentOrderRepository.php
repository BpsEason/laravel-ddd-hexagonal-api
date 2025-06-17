<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Entities\Order as DomainOrder;
use App\Domain\Entities\OrderItem as DomainOrderItem;
use App\Domain\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Models\Order as EloquentOrderModel;
use App\Infrastructure\Persistence\Models\OrderItem as EloquentOrderItemModel;
use Illuminate\Support\Collection; // 確保引入 Collection

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
            // 從 Domain Order 獲取創建時間並轉換為資料庫可識別格式
            $eloquentOrder->created_at = $order->getCreatedAt()->format('Y-m-d H:i:s');
        }
        $eloquentOrder->save();

        // 處理訂單項
        // 對於更新訂單，需要更複雜的同步邏輯（新增、更新、刪除現有項目）
        // 這裡的邏輯是針對「新訂單」創建所有訂單項。
        // 如果是更新訂單，您需要先刪除舊的訂單項，再插入新的，或者執行更精細的 upsert/sync。
        // 為了範例的簡潔性，我們只處理新訂單的項目新增。
        // 在生產環境中，對於訂單項的修改/刪除，您需要明確處理。

        // 判斷是否為新訂單，或者需要處理更新邏輯
        if ($eloquentOrder->wasRecentlyCreated) {
            $itemsToInsert = $order->getItems()->map(function (DomainOrderItem $domainItem) use ($order) {
                return [
                    'order_id' => $order->getId(),
                    'product_id' => $domainItem->getProductId(),
                    'product_name' => $domainItem->getProductName(),
                    'unit_price' => $domainItem->getUnitPrice(),
                    'quantity' => $domainItem->getQuantity(),
                ];
            })->toArray();
            EloquentOrderItemModel::insert($itemsToInsert);
        } else {
            // 如果是更新訂單，這裡需要更複雜的邏輯來同步 order_items
            // 例如：
            // 1. 獲取現有的 Eloquent 訂單項
            // 2. 比較領域訂單項和 Eloquent 訂單項，找出新增、更新、刪除的項目
            // 3. 執行對應的資料庫操作
            // 或者，如果訂單項不允許修改/刪除，則直接拋出錯誤或忽略。
            // 由於範例重點在創建，這裡保持簡化。
        }
    }

    public function findById(string $orderId): ?DomainOrder
    {
        $eloquentOrder = EloquentOrderModel::with('items')->find($orderId);

        if (!$eloquentOrder) {
            return null;
        }

        $domainOrderItems = $eloquentOrder->items->map(function (EloquentOrderItemModel $item) {
            return new DomainOrderItem(
                $item->product_id,
                $item->product_name,
                $item->unit_price,
                $item->quantity
            );
        });

        // 使用 Domain Order 實體的靜態工廠方法從持久化數據重建
        return DomainOrder::fromPersistence(
            $eloquentOrder->id,
            $eloquentOrder->customer_id,
            $domainOrderItems,
            (float) $eloquentOrder->total_amount,
            $eloquentOrder->status,
            $eloquentOrder->created_at->toDateTimeString() // 確保傳遞字串
        );
    }
}