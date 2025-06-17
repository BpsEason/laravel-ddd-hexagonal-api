<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Product;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function findById(string $productId): ?Product;
    public function findByIds(array $productIds): Collection;
    public function save(Product $product): void;
    // ...其他產品相關操作
}