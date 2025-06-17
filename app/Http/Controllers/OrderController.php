<?php

namespace App\Http\Controllers;

use App\Application\Commands\PlaceOrderCommand;
use App\Application\Handlers\PlaceOrderCommandHandler;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Resources\OrderResource; // 自定義的 API Resource
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // 引入 Request

class OrderController extends Controller
{
    private PlaceOrderCommandHandler $placeOrderCommandHandler;

    public function __construct(PlaceOrderCommandHandler $placeOrderCommandHandler)
    {
        $this->placeOrderCommandHandler = $placeOrderCommandHandler;
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        // 實際應用中，customer ID 會從認證用戶中獲取
        // 例如：$customerId = $request->user()->id;
        $customerId = 'user-123'; // 這裡為簡化範例寫死

        $command = new PlaceOrderCommand(
            $customerId,
            $request->input('cart_items')
        );

        try {
            $orderDto = $this->placeOrderCommandHandler->handle($command);

            return new JsonResponse([
                'message' => 'Order placed successfully.',
                'order'   => new OrderResource($orderDto) // 使用 DTO 轉換為 API Resource
            ], 201);
        } catch (\Exception $e) {
            // 這裡應該根據異常類型返回不同的 HTTP 狀態碼和錯誤信息
            // 例如：商品庫存不足可以返回 409 Conflict
            return new JsonResponse([
                'message' => 'Failed to place order.',
                'error'   => $e->getMessage(),
            ], 400);
        }
    }
}