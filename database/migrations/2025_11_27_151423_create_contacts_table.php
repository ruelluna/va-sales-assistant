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
        if (! Schema::hasTable('contacts')) {
            Schema::create('contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('campaign_id')->nullable();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->string('company')->nullable();
                $table->json('tags')->nullable();
                $table->string('timezone')->nullable();
                $table->timestamps();
            });
        }

        // Add foreign key constraint only if campaigns table exists and constraint doesn't exist
        if (Schema::hasTable('campaigns') && Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'campaign_id')) {
            try {
                Schema::table('contacts', function (Blueprint $table) {
                    $table->foreign('campaign_id')
                        ->references('id')
                        ->on('campaigns')
                        ->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Foreign key constraint might already exist, ignore the error
                if (! str_contains($e->getMessage(), 'Duplicate key name') && ! str_contains($e->getMessage(), 'already exists')) {
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
        Schema::dropIfExists('contacts');
    }
};
