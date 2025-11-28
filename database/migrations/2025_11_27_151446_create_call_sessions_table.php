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
        Schema::create('call_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('va_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('twilio_call_sid')->unique();
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->enum('status', ['initiated', 'ringing', 'in_progress', 'completed', 'failed', 'no_answer'])->default('initiated');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('recording_url')->nullable();
            $table->enum('outcome', [
                'sale_won',
                'appointment_booked',
                'qualified_lead',
                'not_interested',
                'busy_callback',
                'voicemail',
                'no_answer',
                'other'
            ])->nullable();
            $table->decimal('outcome_confidence', 3, 2)->nullable();
            $table->text('summary')->nullable();
            $table->string('next_action')->nullable();
            $table->timestamp('next_action_due_at')->nullable();
            $table->json('ai_state')->nullable();
            $table->json('real_time_tags')->nullable();
            $table->json('ai_raw_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_sessions');
    }
};
