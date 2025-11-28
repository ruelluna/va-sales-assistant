<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we need to recreate the table
            Schema::table('call_sessions', function (Blueprint $table) {
                $table->dropUnique(['twilio_call_sid']);
            });

            DB::statement('CREATE TABLE call_sessions_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                contact_id INTEGER NOT NULL,
                campaign_id INTEGER,
                va_user_id INTEGER NOT NULL,
                twilio_call_sid VARCHAR(255) NULL,
                direction VARCHAR(255) NOT NULL DEFAULT "outbound",
                status VARCHAR(255) NOT NULL DEFAULT "initiated",
                started_at DATETIME NULL,
                ended_at DATETIME NULL,
                duration_seconds INTEGER NULL,
                recording_url VARCHAR(255) NULL,
                outcome VARCHAR(255) NULL,
                outcome_confidence NUMERIC NULL,
                summary TEXT NULL,
                next_action VARCHAR(255) NULL,
                next_action_due_at DATETIME NULL,
                ai_state TEXT NULL,
                real_time_tags TEXT NULL,
                ai_raw_data TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
                FOREIGN KEY (va_user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(twilio_call_sid)
            )');

            DB::statement('INSERT INTO call_sessions_new SELECT * FROM call_sessions');
            DB::statement('DROP TABLE call_sessions');
            DB::statement('ALTER TABLE call_sessions_new RENAME TO call_sessions');
        } else {
            Schema::table('call_sessions', function (Blueprint $table) {
                $table->dropUnique(['twilio_call_sid']);
                $table->string('twilio_call_sid')->nullable()->change();
                $table->unique('twilio_call_sid');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('call_sessions', function (Blueprint $table) {
                $table->dropUnique(['twilio_call_sid']);
            });

            DB::statement('CREATE TABLE call_sessions_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                contact_id INTEGER NOT NULL,
                campaign_id INTEGER,
                va_user_id INTEGER NOT NULL,
                twilio_call_sid VARCHAR(255) NOT NULL,
                direction VARCHAR(255) NOT NULL DEFAULT "outbound",
                status VARCHAR(255) NOT NULL DEFAULT "initiated",
                started_at DATETIME NULL,
                ended_at DATETIME NULL,
                duration_seconds INTEGER NULL,
                recording_url VARCHAR(255) NULL,
                outcome VARCHAR(255) NULL,
                outcome_confidence NUMERIC NULL,
                summary TEXT NULL,
                next_action VARCHAR(255) NULL,
                next_action_due_at DATETIME NULL,
                ai_state TEXT NULL,
                real_time_tags TEXT NULL,
                ai_raw_data TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
                FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE SET NULL,
                FOREIGN KEY (va_user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE(twilio_call_sid)
            )');

            DB::statement('INSERT INTO call_sessions_new SELECT * FROM call_sessions WHERE twilio_call_sid IS NOT NULL');
            DB::statement('DROP TABLE call_sessions');
            DB::statement('ALTER TABLE call_sessions_new RENAME TO call_sessions');
        } else {
            Schema::table('call_sessions', function (Blueprint $table) {
                $table->dropUnique(['twilio_call_sid']);
                $table->string('twilio_call_sid')->nullable(false)->change();
                $table->unique('twilio_call_sid');
            });
        }
    }
};
