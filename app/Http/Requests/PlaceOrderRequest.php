<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 這裡可以根據業務邏輯判斷用戶是否有權限下單
    }

    public function rules(): array
    {
        return [
            'cart_items'            => ['required', 'array', 'min:1'],
            'cart_items.*.product_id' => ['required', 'string'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1'],
            // 'customer_id' 實際應用中會從認證用戶中獲取，而非請求中
        ];
    }
}