<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('price_history', function (Blueprint $table) {
            $table->json("payload")->nullable()->after("price");
        });
    }

    public function down(): void
    {
        Schema::table('price_history', function (Blueprint $table) {
            $table->dropColumn("payload");
        });
    }
};
