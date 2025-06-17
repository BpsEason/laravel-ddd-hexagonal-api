<?php

namespace App\Application\Commands;

class PlaceOrderCommand
{
    public string $customerId;
    public array $cartItems; // 例如: [['product_id' => 'abc', 'quantity' => 2]]

    public function __construct(string $customerId, array $cartItems)
    {
        $this->customerId = $customerId;
        $this->cartItems = $cartItems;
    }
}