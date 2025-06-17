<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Entities\Product as DomainProduct;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Models\Product as EloquentProductModel;
use Illuminate\Support\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(string $productId): ?DomainProduct
    {
        $eloquentProduct = EloquentProductModel::find($productId);
        if (!$eloquentProduct) {
            return null;
        }
        return new DomainProduct(
            $eloquentProduct->id,
            $eloquentProduct->name,
            $eloquentProduct->price,
            $eloquentProduct->stock
        );
    }

    public function findByIds(array $productIds): Collection
    {
        $eloquentProducts = EloquentProductModel::whereIn('id', $productIds)->get();
        return $eloquentProducts->map(fn($p) => new DomainProduct($p->id, $p->name, $p->price, $p->stock));
    }

    public function save(DomainProduct $product): void
    {
        $eloquentProduct = EloquentProductModel::firstOrNew(['id' => $product->getId()]);
        $eloquentProduct->name = $product->getName();
        $eloquentProduct->price = $product->getPrice();
        $eloquentProduct->stock = $product->getStock();
        $eloquentProduct->save();
    }
}