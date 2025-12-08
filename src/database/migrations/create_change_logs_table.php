<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Imamsudarajat04\ChangeLogs\Enums\RecordAction;
use Imamsudarajat04\ChangeLogs\Enums\Table;

return new class extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create(Table::CHANGE_LOGS->value, function (Blueprint $table) {
            # Primary Key
            $table->uuid('id')->primary();

            # Polymorphic Relationship
            $table->string('loggable_type');
            $table->string('loggable_id');

            # Action Information
            $table->enum('action', array_column(RecordAction::cases(), 'value'));

            # Change Details
            $table->string('field_column')->nullable()->comment('Specific field that changed');
            $table->json('old_value')->nullable()->comment('Previous value(s)');
            $table->json('new_value')->nullable()->comment('New value(s)');

            # User Information
            $table->uuid('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            # Request Information
            $table->string('method')->nullable()->comment('HTTP method or CLI command');
            $table->string('endpoint')->nullable()->comment('Request URL or command');

            # Metadata
            $table->text('description')->nullable();
            $table->json('tags')->nullable()->comment('For categorization/filtering');

            # Date for easy filtering and cleanup
            $table->date('date')->nullable()->comment('Date of the change (for easy querying)');

            # Timestamps
            $table->timestamps();

            # Composite Indexes (Most Important - Query Performance)
            $table->index(['loggable_type', 'loggable_id'], 'cl_loggable_idx');
            $table->index(['loggable_type', 'action'], 'cl_type_action_idx');
            $table->index(['user_id', 'created_at'], 'cl_user_created_idx');
            $table->index(['date', 'action'], 'cl_date_action_idx');

            # Single Column Indexes (Frequently Queried Alone)
            $table->index('action', 'cl_action_idx');
            $table->index('method', 'cl_method_idx');
            $table->index('endpoint', 'cl_endpoint_idx');
            $table->index('created_at', 'cl_created_idx');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::CHANGE_LOGS->value);
    }
};