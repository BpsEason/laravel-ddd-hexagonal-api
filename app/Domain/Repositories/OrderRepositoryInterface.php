<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;
    public function findById(string $orderId): ?Order;
    // ...其他訂單相關操作
}