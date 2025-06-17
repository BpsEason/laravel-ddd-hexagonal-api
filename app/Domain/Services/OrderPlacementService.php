<?php

namespace App\Domain\Services;

use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Collection;

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
        $products = $this->productRepository->findByIds($productIds)->keyBy(fn($p) => $p->getId());

        $orderItems = new Collection();
        foreach ($cartItemsData as $itemData) {
            $productId = $itemData['product_id'];
            $quantity = $itemData['quantity'];

            /** @var \App\Domain\Entities\Product $product */
            $product = $products->get($productId);

            if (!$product) {
                throw new \RuntimeException("Product with ID {$productId} not found.");
            }

            // 檢查庫存並扣減
            $product->decreaseStock($quantity);
            $this->productRepository->save($product); // 持久化產品庫存變更

            $orderItems->push(new OrderItem(
                $product->getId(),
                $product->getName(),
                $product->getPrice(),
                $quantity
            ));
        }

        // 創建領域實體 Order
        $order = Order::create($customerId, $orderItems);

        return $order;
    }
}