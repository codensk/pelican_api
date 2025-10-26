<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('price_history', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->after('price');

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('price_history', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
