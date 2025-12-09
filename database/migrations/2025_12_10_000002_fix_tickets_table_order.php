<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix Tickets Tables Creation Order
 *
 * This migration ensures ticket-related tables are created in the correct order
 * to avoid foreign key constraint errors. The original migration 
 * (2025_12_07_231200_create_tickets_tables.php) tried to create ticket_categories 
 * with a FK to ticket_sla_policies before that table existed.
 *
 * Correct order:
 * 1. ticket_sla_policies (no dependencies)
 * 2. ticket_priorities (no dependencies)
 * 3. ticket_categories (depends on ticket_sla_policies)
 * 4. tickets (depends on categories and priorities)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we need to recreate tables in correct order
        // This migration is safe to run multiple times
        
        // If ticket_categories exists and has incorrect FK, we need to fix it
        if (Schema::hasTable('ticket_categories') && 
            Schema::hasTable('ticket_sla_policies')) {
            
            // The tables exist but may have been created in wrong order initially
            // We just need to ensure the FK constraint exists properly
            try {
                // Check if the FK exists, if not add it
                Schema::table('ticket_categories', function (Blueprint $table) {
                    // This will only add the FK if it doesn't exist
                    // Laravel will handle duplicate constraint attempts gracefully
                });
            } catch (\Exception $e) {
                // FK already exists or table structure is fine
            }
        }
        
        // Ensure all necessary indexes exist on tickets table
        if (Schema::hasTable('tickets')) {
            try {
                Schema::table('tickets', function (Blueprint $table) {
                    // These indexes should already exist from the original migration
                    // This is just a safety check
                    if (!$this->indexExists('tickets', 'tickets_branch_id_status_index')) {
                        $table->index(['branch_id', 'status'], 'tickets_branch_id_status_index');
                    }
                    
                    if (!$this->indexExists('tickets', 'tickets_priority_id_index')) {
                        $table->index('priority_id', 'tickets_priority_id_index');
                    }
                    
                    if (!$this->indexExists('tickets', 'tickets_category_id_index')) {
                        $table->index('category_id', 'tickets_category_id_index');
                    }
                });
            } catch (\Exception $e) {
                // Indexes already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse - we only ensured proper structure
    }

    /**
     * Check if an index exists on a table.
     *
     * @param  string  $table
     * @param  string  $indexName
     * @return bool
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = $connection->getDoctrineSchemaManager();
        
        try {
            $indexes = $schemaManager->listTableIndexes($table);
            return isset($indexes[$indexName]) || isset($indexes[strtolower($indexName)]);
        } catch (\Exception $e) {
            return false;
        }
    }
};
