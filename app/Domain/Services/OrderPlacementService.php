<?php

namespace App\Domain\Services;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Exceptions\InsufficientStockException; // 引入自定義異常
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; // 引入 DB facade

class OrderPlacementService
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function createOrder(string $customerId, array $cartItemsData): Order
    {
        $productIds = array_column($cartItemsData, 'product_id');

        // 在查詢產品時加上排他鎖 (X-lock)，防止其他並發請求修改庫存
        // 確保這個鎖是在應用層的 DB::transaction 內部。
        $products = $this->productRepository->findByIds($productIds)
                                            ->keyBy(fn($p) => $p->getId());

        $orderItems = new Collection();
        foreach ($cartItemsData as $itemData) {
            $productId = $itemData['product_id'];
            $quantity = $itemData['quantity'];

            /** @var \App\Domain\Entities\Product $product */
            $product = $products->get($productId);

            if (!$product) {
                // 更具體的錯誤信息，如 product not found
                throw new \RuntimeException("Product with ID {$productId} not found.");
            }

            try {
                // 檢查庫存並扣減 (這是領域實體 Product 的職責)
                $product->decreaseStock($quantity);
                // 持久化產品庫存變更 (通過 Repository)
                $this->productRepository->save($product);
            } catch (\RuntimeException $e) {
                // 轉換為自定義的業務異常，以便在應用層統一處理
                throw new InsufficientStockException("Insufficient stock for product '{$product->getName()}'. Requested: {$quantity}, Available: {$product->getStock() + $quantity}.");
            }


            $orderItems->push(new OrderItem(
                $product->getId(),
                $product->getName(),
                $product->getPrice(),
                $quantity
            ));
        }

        // 創建領域實體 Order
        $order = Order::create(
            $customerId,
            $orderItems
        );

        return $order;
    }
}