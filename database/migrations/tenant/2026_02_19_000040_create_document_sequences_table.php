<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('prefix', 20)->nullable();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('current')->default(0);
            $table->string('pattern', 100)->default('{prefix}{year}-{number:05}');
            $table->timestamps();

            $table->unique(['company_id', 'type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
