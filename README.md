# Laravel 電子商務訂單系統範例

這是一個基於 **Laravel** 和 **領域驅動設計 (DDD)** 的電子商務訂單系統範例，展示了如何實現「下訂單」功能。項目採用分層架構，包含**領域層**、**應用程式層**、**基礎設施層**和**介面層**，並實現了領域事件、倉庫模式、命令處理和錯誤處理等 DDD 核心概念。

## 功能概述

- **下訂單**：用戶可以將購物車中的商品轉換為訂單，系統會檢查庫存、創建訂單並觸發領域事件（如發送確認郵件）。
- **分層架構**：
  - **領域層**：包含 `Order`、`OrderItem` 和 `Product` 實體，以及 `OrderPlacementService` 領域服務，封裝核心業務邏輯。
  - **應用程式層**：通過 `PlaceOrderCommand` 和 `PlaceOrderCommandHandler` 協調業務邏輯，使用 DTO 傳遞資料。
  - **基礎設施層**：實現 `EloquentOrderRepository` 和 `EloquentProductRepository`，處理資料持久化。
  - **介面層**：提供 RESTful API 端點，使用 `FormRequest` 驗證輸入並返回標準化 JSON 響應。
- **領域事件**：訂單創建後觸發 `OrderPlaced` 事件，可用於發送郵件、記錄日誌等。
- **併發控制**：使用資料庫鎖 (`lockForUpdate`) 防止庫存超賣。
- **錯誤處理**：自定義異常（如 `InsufficientStockException`）並提供標準化的 API 錯誤響應。

## 環境要求

- PHP >= 8.1
- Laravel >= 9.x
- MySQL 或其他 Laravel 支援的資料庫
- Composer
- Postman 或 cURL（用於測試 API）

## 安裝步驟

1. **克隆或創建項目**：
   ```bash
   laravel new my-ecommerce-app
   cd my-ecommerce-app
   ```

2. **複製程式碼**：
   - 將提供的程式碼檔案（位於 `app/` 和 `database/migrations/`）複製到對應目錄。
   - 確保 `routes/api.php` 和 `app/Providers/` 中的服務提供者配置正確。

3. **配置環境**：
   - 複製 `.env.example` 為 `.env`：
     ```bash
     cp .env.example .env
     ```
   - 在 `.env` 中配置資料庫連線，例如：
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=ecommerce
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```

4. **安裝依賴**：
   ```bash
   composer install
   ```

5. **生成應用程式密鑰**：
   ```bash
   php artisan key:generate
   ```

6. **運行資料庫遷移**：
   ```bash
   php artisan migrate
   ```

7. **播種測試資料（可選）**：
   - 創建並運行 `ProductSeeder` 以填充產品資料：
     ```php
     // database/seeders/ProductSeeder.php
     <?php
     namespace Database\Seeders;
     use Illuminate\Database\Seeder;
     use Illuminate\Support\Facades\DB;
     use Illuminate\Support\Str;

     class ProductSeeder extends Seeder
     {
         public function run(): void
         {
             DB::table('products')->insert([
                 [
                     'id' => (string) Str::uuid(),
                     'name' => 'Sample Product A',
                     'price' => 19.99,
                     'stock' => 100,
                     'created_at' => now(),
                     'updated_at' => now(),
                 ],
                 [
                     'id' => (string) Str::uuid(),
                     'name' => 'Sample Product B',
                     'price' => 29.50,
                     'stock' => 50,
                     'created_at' => now(),
                     'updated_at' => now(),
                 ],
             ]);
         }
     }
     ```
   - 在 `database/seeders/DatabaseSeeder.php` 中調用：
     ```php
     public function run(): void
     {
         $this->call(ProductSeeder::class);
     }
     ```
   - 執行播種：
     ```bash
     php artisan db:seed
     ```

8. **啟動開發伺服器**：
   ```bash
   php artisan serve
   ```

## API 使用

### **端點：創建訂單**

- **URL**: `POST /api/orders`
- **Header**:
  - `Accept: application/json`
  - `Content-Type: application/json`
- **Body (JSON)**:
  ```json
  {
      "cart_items": [
          {
              "product_id": "YOUR_PRODUCT_A_UUID",
              "quantity": 2
          },
          {
              "product_id": "YOUR_PRODUCT_B_UUID",
              "quantity": 1
          }
      ]
  }
  ```
- **成功響應** (HTTP 201)：
  ```json
  {
      "message": "Order placed successfully.",
      "order": {
          "order_id": "uuid-of-order",
          "total_amount": 69.48,
          "status": "pending",
          "message": "Your order has been placed successfully."
      }
  }
  ```
- **錯誤響應** (例如庫存不足，HTTP 409)：
  ```json
  {
      "message": "Order cannot be placed due to insufficient stock.",
      "error": "Insufficient stock for product 'Sample Product A'. Requested: 200, Available: 100.",
      "code": "INSUFFICIENT_STOCK"
  }
  ```

### **測試 API**

使用 Postman 或 cURL 測試：
```bash
curl -X POST http://127.0.0.1:8000/api/orders \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-d '{
    "cart_items": [
        {"product_id": "YOUR_PRODUCT_A_UUID", "quantity": 2},
        {"product_id": "YOUR_PRODUCT_B_UUID", "quantity": 1}
    ]
}'
```

## 程式碼結構

```
app/
├── Console/
├── Exceptions/
│   ├── Handler.php
│   └── InsufficientStockException.php
├── Http/
│   ├── Controllers/
│   │   └── OrderController.php
│   ├── Requests/
│   │   └── PlaceOrderRequest.php
│   └── Resources/
│       └── OrderResource.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── EventServiceProvider.php
├── Domain/
│   ├── Entities/
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── Product.php
│   ├── Repositories/
│   │   ├── OrderRepositoryInterface.php
│   │   └── ProductRepositoryInterface.php
│   ├── Services/
│   │   └── OrderPlacementService.php
│   └── Events/
│       └── OrderPlaced.php
├── Application/
│   ├── Commands/
│   │   └── PlaceOrderCommand.php
│   ├── Handlers/
│   │   └── PlaceOrderCommandHandler.php
│   └── DTOs/
│       └── OrderCreatedDTO.php
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── EloquentOrderRepository.php
│   │   │   └── EloquentProductRepository.php
│   │   └── Models/
│   │       ├── Order.php
│   │       ├── OrderItem.php
│   │       └── Product.php
│   └── Events/
│       └── Listeners/
│           └── SendOrderConfirmationEmail.php
└── ...其他 Laravel 預設目錄
```

## 改進建議

1. **領域實體重建**：
   - 已實現 `Order::fromPersistence` 方法，避免使用反射，提高可讀性和可維護性。
2. **併發控制**：
   - 在 `EloquentProductRepository` 的 `findByIds` 方法中建議使用 `lockForUpdate` 防止庫存超賣。
3. **購物車管理**：
   - 目前直接接收 `cart_items`，建議引入 `Cart` 實體和 `CartService` 管理購物車邏輯。
4. **錯誤處理**：
   - 已實現 `InsufficientStockException` 和統一的異常處理，建議根據業務需求添加更多自定義異常。
5. **單元測試**：
   - 為 `Order`、`OrderPlacementService` 和 `PlaceOrderCommandHandler` 添加單元測試，使用 PHPUnit 或 Pest。
6. **基礎設施即程式碼 (IaC)**：
   - 若部署到雲端（如 AWS），可使用 Terraform 或 CloudFormation 配置 RDS、EC2 或 ECS。
7. **日誌與監控**：
   - 使用 Laravel 日誌或第三方工具（如 Sentry）記錄錯誤和事件。

## 測試與驗證

1. **單元測試**：
   - 為關鍵組件添加測試，例如：
     ```php
     public function test_order_creation_with_valid_items()
     {
         $product = new Product('prod-1', 'Test Product', 10.0, 100);
         $productRepository = $this->createMock(ProductRepositoryInterface::class);
         $productRepository->method('findByIds')->willReturn(collect([$product]));
         $productRepository->expects($this->once())->method('save');

         $service = new OrderPlacementService($productRepository);
         $order = $service->createOrder('user-123', [
             ['product_id' => 'prod-1', 'quantity' => 2]
         ]);

         $this->assertEquals('user-123', $order->getCustomerId());
         $this->assertEquals(20.0, $order->getTotalAmount());
     }
     ```

2. **API 測試**：
   - 使用 Postman 或 Laravel 的內建測試功能（`php artisan test`）驗證 API 行為。

## 注意事項

- **UUID**：項目使用 UUID 作為主鍵，適合分散式系統。
- **事務管理**：`PlaceOrderCommandHandler` 使用 `DB::transaction` 確保資料一致性。
- **領域事件**：`OrderPlaced` 事件已註冊 `SendOrderConfirmationEmail` 監聽器，可擴展以實現其他業務邏輯（如記錄日誌、更新分析數據）。
- **客戶認證**：範例中硬編碼了 `customerId`，實際應用應從 `Auth::user()` 獲取。

## 聯繫與貢獻

如有問題或改進建議，請提交 Issue 或 Pull Request。歡迎為項目添加新功能或測試！