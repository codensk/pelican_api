<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_refundable')->nullable()->default(null)->after('services_price');
            $table->float('refundable_ticket_percent')->nullable()->default(null)->after('is_refundable');
            $table->double('full_price_refundable')->nullable()->default(null)->after('full_price');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_refundable');
            $table->dropColumn('refundable_ticket_percent');
            $table->dropColumn('full_price_refundable');
        });
    }
};
