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
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary(); // 使用 string 類型作為 UUID 主鍵
            $table->string('customer_id')->comment('下單顧客的ID'); // 實際應用中通常會是 users 表的外鍵
            $table->decimal('total_amount', 10, 2)->comment('訂單總金額');
            $table->string('status')->default('pending')->comment('訂單狀態：pending, paid, shipped, cancelled, completed');
            $table->timestamps(); // created_at 和 updated_at

            // 增加索引以優化查詢
            $table->index('customer_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};