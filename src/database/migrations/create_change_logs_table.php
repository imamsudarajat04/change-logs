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
            $table->string('loggable_type')->index();
            $table->string('loggable_id')->index();

            # Action Information
            $table->enum('action', array_column(RecordAction::cases(), 'value'))->index();

            # Change Details
            $table->string('field_column')->nullable()->comment('Specific field that changed');
            $table->json('old_value')->nullable()->comment('Previous value(s)');
            $table->json('new_value')->nullable()->comment('New value(s)');

            # User Information
            $table->uuid('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            # Metadata
            $table->text('description')->nullable();
            $table->json('tags')->nullable()->comment('For categorization/filtering');

            # Date for easy filtering and cleanup
            $table->date('date')->nullable()->index()->comment('Date of the change (for easy querying)');

            # Timestamps
            $table->timestamps();

            # Indexes
            $table->index(['loggable_type', 'loggable_id'], 'idx_loggable');
            $table->index('user_id', 'idx_user');
            $table->index('created_at', 'idx_created');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Table::CHANGE_LOGS->name);
    }
};