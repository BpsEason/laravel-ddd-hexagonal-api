<?php

namespace App\Domain\Entities;

class Product
{
    private string $id;
    private string $name;
    private float $price;
    private int $stock;

    public function __construct(string $id, string $name, float $price, int $stock)
    {
        if ($price <= 0) {
            throw new \InvalidArgumentException("Product price must be positive.");
        }
        if ($stock < 0) {
            throw new \InvalidArgumentException("Product stock cannot be negative.");
        }
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function decreaseStock(int $quantity): void
    {
        if ($this->stock < $quantity) {
            throw new \RuntimeException("Insufficient stock for product " . $this->name);
        }
        $this->stock -= $quantity;
    }
}