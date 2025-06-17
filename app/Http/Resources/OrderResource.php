<?php

namespace App\Http\Resources;

use App\Application\DTOs\OrderCreatedDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @var OrderCreatedDTO $this
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id'     => $this->orderId,
            'total_amount' => $this->totalAmount,
            'status'       => 'pending', // 根據實際狀態返回
            'message'      => 'Your order has been placed successfully.',
        ];
    }
}