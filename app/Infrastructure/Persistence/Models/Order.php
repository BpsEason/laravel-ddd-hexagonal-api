<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 這裡我們將其命名為 OrderModel 以避免與 Domain Entity 衝突
class Order extends Model
{
    protected $table = 'orders'; // 確保表名正確
    protected $keyType = 'string'; // 如果 ID 是 UUID
    public $incrementing = false; // 如果 ID 是 UUID

    protected $fillable = [
        'id',
        'customer_id',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
}