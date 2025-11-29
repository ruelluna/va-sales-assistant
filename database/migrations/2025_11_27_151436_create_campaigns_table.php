<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('campaigns')) {
            Schema::create('campaigns', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('product_name');
                $table->longText('script')->nullable();
                $table->longText('ai_prompt_context')->nullable();
                $table->text('success_definition')->nullable();
                $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
                $table->timestamps();
            });
        }

        // Add foreign key constraint to contacts table now that campaigns exists
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'campaign_id')) {
            try {
                Schema::table('contacts', function (Blueprint $table) {
                    $table->foreign('campaign_id')
                        ->references('id')
                        ->on('campaigns')
                        ->nullOnDelete();
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // Ignore if foreign key already exists
                $errorInfo = $e->errorInfo ?? [];
                $errorCode = $errorInfo[1] ?? null;
                $errorMessage = $e->getMessage();

                if ($errorCode != 1826 &&
                    ! str_contains($errorMessage, 'Duplicate key name') &&
                    ! str_contains($errorMessage, 'already exists')) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
