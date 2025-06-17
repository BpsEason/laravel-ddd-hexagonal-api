<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // 自增主鍵
            $table->string('order_id'); // 關聯訂單表的ID
            $table->string('product_id')->comment('商品ID'); // 實際應用中通常會是 products 表的外鍵
            $table->string('product_name')->comment('下單時的商品名稱'); // 防止商品名稱變更導致訂單資訊不符
            $table->decimal('unit_price', 8, 2)->comment('下單時的商品單價'); // 防止商品價格變更導致訂單資訊不符
            $table->integer('quantity')->comment('商品數量');

            // 設置外鍵約束，當訂單被刪除時，其所有訂單項也會被級聯刪除
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            // 為 product_id 增加索引以優化查詢
            $table->index('product_id');

            // 訂單項通常不需要獨立的時間戳，由訂單的時間戳統一管理
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};