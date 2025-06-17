<?php

namespace App\Exceptions;

use Exception; // 確保使用正確的基類

class InsufficientStockException extends Exception
{
    // 可以添加自定義的構造函數或其他方法
    public function __construct(string $message = "Insufficient stock.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}