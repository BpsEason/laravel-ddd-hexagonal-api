<?php

namespace App\Domain\Entities;

class OrderItem
{
    private string $productId;
    private string $productName;
    private float $unitPrice;
    private int $quantity;

    public function __construct(string $productId, string $productName, float $unitPrice, int $quantity)
    {
        if ($unitPrice <= 0 || $quantity <= 0) {
            throw new \InvalidArgumentException("Unit price and quantity must be positive.");
        }
        $this->productId = $productId;
        $this->productName = $productName;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalPrice(): float
    {
        return $this->unitPrice * $this->quantity;
    }
}