<?php

namespace App\Application\DTOs;

class OrderCreatedDTO
{
    public string $orderId;
    public float $totalAmount;

    public function __construct(string $orderId, float $totalAmount)
    {
        $this->orderId = $orderId;
        $this->totalAmount = $totalAmount;
    }
}