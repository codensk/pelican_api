<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_link', 500)->nullable()->default(null)->after('payload');
            $table->timestamp('expires_at')->nullable()->default(null)->after('payment_link');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('expires_at');
            $table->dropColumn('payment_link');
        });
    }
};
