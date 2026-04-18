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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // The actor (who performed the action)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // The action and the target
            $table->string('action'); // e.g., 'Changed Role', 'Deleted Ticket', 'Added Comment'
            $table->string('target_type'); // e.g., 'User', 'Ticket'
            $table->unsignedBigInteger('target_id'); // e.g., The ID of the User or Ticket

            // The data changes
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
