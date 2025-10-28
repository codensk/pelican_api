<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->integer("user_id")->nullable();
            $table->string("price_id", 100)->nullable();
            $table->double("full_price")->nullable()->comment("полная стоимость заказа");
            $table->double("order_price")->nullable()->comment("чистая цена трипа с букинга");
            $table->double("services_price")->nullable()->comment("стоимость доп. услуг");
            $table->string("order_id", 40)->default(null)->nullable();
            $table->boolean("is_paid")->default(false);

            $table->timestamps();

            $table->index('order_id');
            $table->index('user_id');
            $table->index('price_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
