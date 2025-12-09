<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Update store_orders table to use composite unique index on (external_order_id, branch_id)
     * instead of a single unique index on external_order_id alone.
     * This prevents cross-branch overwrites and enforces branch isolation.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // Drop the existing unique constraint on external_order_id
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE store_orders DROP INDEX store_orders_external_order_id_unique');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE store_orders DROP CONSTRAINT store_orders_external_order_id_unique');
        } elseif ($driver === 'sqlite') {
            // SQLite requires recreating the table for this change
            // For now, we'll skip the constraint drop on SQLite
        }

        Schema::table('store_orders', function (Blueprint $table): void {
            // Make branch_id not nullable since it's now required
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();

            // Add composite unique constraint on (external_order_id, branch_id)
            $table->unique(['external_order_id', 'branch_id'], 'store_orders_external_id_branch_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table): void {
            // Drop the composite unique constraint
            $table->dropUnique('store_orders_external_id_branch_unique');

            // Make branch_id nullable again
            $table->unsignedBigInteger('branch_id')->nullable()->change();

            // Restore the original unique constraint on external_order_id
            $table->unique('external_order_id');
        });
    }
};
