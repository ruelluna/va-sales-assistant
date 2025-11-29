<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // Add foreign key constraint if campaigns table exists
        // If campaigns table doesn't exist yet, this will be skipped (it will be created in a later migration)
        // We'll add the foreign key constraint in a separate migration that runs after campaigns is created
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'campaign_id')) {
            // Use raw query to check if campaigns table exists (more reliable than Schema::hasTable)
            $campaignsExists = DB::selectOne(
                "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'campaigns'"
            );

            if ($campaignsExists && $campaignsExists->count > 0) {
                try {
                    Schema::table('contacts', function (Blueprint $table) {
                        $table->foreign('campaign_id')
                            ->references('id')
                            ->on('campaigns')
                            ->nullOnDelete();
                    });
                } catch (\Illuminate\Database\QueryException $e) {
                    // Ignore errors if foreign key already exists
                    $errorInfo = $e->errorInfo ?? [];
                    $errorCode = $errorInfo[1] ?? null;
                    $errorMessage = $e->getMessage();

                    // MySQL error code 1826 = Duplicate foreign key
                    if ($errorCode != 1826 &&
                        ! str_contains($errorMessage, 'Duplicate key name') &&
                        ! str_contains($errorMessage, 'already exists')) {
                        throw $e;
                    }
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
