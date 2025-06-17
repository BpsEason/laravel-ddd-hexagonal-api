<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse; // 引入 JsonResponse
use Illuminate\Validation\ValidationException; // 引入 Laravel 的驗證異常

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // 處理自定義的庫存不足異常
        if ($exception instanceof InsufficientStockException) {
            return new JsonResponse([
                'message' => 'Order cannot be placed due to insufficient stock.',
                'error'   => $exception->getMessage(),
                'code'    => 'INSUFFICIENT_STOCK', // 自定義錯誤碼
            ], 409); // 409 Conflict 狀態碼表示請求與資源當前狀態衝突
        }

        // 處理 Laravel 的驗證異常
        if ($exception instanceof ValidationException) {
            return new JsonResponse([
                'message' => 'Validation failed.',
                'errors'  => $exception->errors(),
                'code'    => 'VALIDATION_ERROR',
            ], 422); // 422 Unprocessable Entity 狀態碼表示請求格式正確但語義有誤
        }

        // 處理 Product Not Found 異常
        if ($exception instanceof \RuntimeException && str_contains($exception->getMessage(), 'Product with ID')) {
            return new JsonResponse([
                'message' => 'One or more products in your cart were not found.',
                'error'   => $exception->getMessage(),
                'code'    => 'PRODUCT_NOT_FOUND',
            ], 404); // 404 Not Found 狀態碼
        }

        // 可以添加其他自定義異常處理...

        // 對於其他未處理的異常，使用 Laravel 預設的處理方式
        return parent::render($request, $exception);
    }
}