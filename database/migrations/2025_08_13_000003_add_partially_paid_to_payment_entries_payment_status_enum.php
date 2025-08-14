<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            // Add 'partially_paid' to the existing enum values on MySQL/MariaDB
            DB::statement(
                "ALTER TABLE `payment_entries` MODIFY COLUMN `payment_status` ENUM('pending','successful','failed','refunded','partially_paid') NOT NULL DEFAULT 'pending'"
            );
        } elseif ($driver === 'pgsql') {
            // Try to add the value to a native enum type if Laravel created one
            DB::statement(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM pg_type t
        WHERE t.typname = 'enum_payment_entries_payment_status'
    ) THEN
        ALTER TYPE enum_payment_entries_payment_status ADD VALUE IF NOT EXISTS 'partially_paid';
    ELSE
        -- If not using a native enum (e.g., check constraint or text), skip.
        RAISE NOTICE 'enum_payment_entries_payment_status type not found; skipping';
    END IF;
END $$;
SQL);
        } else {
            // sqlite or other drivers: no-op (SQLite stores enum as TEXT)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            // WARNING: This will fail if rows contain 'partially_paid'. Clean data first.
            DB::statement(
                "ALTER TABLE `payment_entries` MODIFY COLUMN `payment_status` ENUM('pending','successful','failed','refunded') NOT NULL DEFAULT 'pending'"
            );
        } elseif ($driver === 'pgsql') {
            // PostgreSQL cannot easily drop enum values; down is a no-op.
        } else {
            // sqlite or other drivers: no-op
        }
    }
};
