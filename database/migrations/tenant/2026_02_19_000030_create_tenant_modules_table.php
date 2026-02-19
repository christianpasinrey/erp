<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50);
            $table->boolean('is_active')->default(true);
            $table->string('plan', 20)->default('free');
            $table->json('limits')->nullable();
            $table->json('features')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
