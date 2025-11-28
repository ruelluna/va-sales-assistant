<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->text('pricing_info')->nullable();
            $table->longText('ai_prompt_context')->nullable();
            $table->json('common_objections')->nullable();
            $table->json('recommended_responses')->nullable();
            $table->longText('cold_call_script_template')->nullable();
            $table->text('success_definition')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
