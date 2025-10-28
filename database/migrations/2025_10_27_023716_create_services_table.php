<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->integer('service_id')->default(null)->nullable();
            $table->string('title', 100)->default(null)->nullable();
            $table->string('description', 255)->default(null)->nullable();
            $table->double('price')->default(null)->nullable();
            $table->string('currency', 10)->default(null)->nullable();
            $table->boolean('is_countable')->default(false)->nullable();

            $table->timestamps();

            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
