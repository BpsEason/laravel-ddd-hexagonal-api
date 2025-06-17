<?php

namespace App\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced
{
    use Dispatchable, SerializesModels;

    public string $orderId;
    public string $customerId;
    public float $totalAmount;

    public function __construct(string $orderId, string $customerId, float $totalAmount)
    {
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->totalAmount = $totalAmount;
    }
}