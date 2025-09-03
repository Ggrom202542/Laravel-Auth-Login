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
        Schema::create('security_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name')->unique()->comment('Policy identifier');
            $table->string('policy_type')->comment('Type of policy: ip_restriction, 2fa, session, etc.');
            $table->json('policy_rules')->comment('Policy rules in JSON format');
            $table->boolean('is_active')->default(true)->comment('Whether policy is active');
            $table->enum('applies_to', ['all', 'admin', 'super_admin', 'user'])
                   ->default('all')
                   ->comment('Who this policy applies to');
            $table->text('description')->nullable()->comment('Policy description');
            $table->foreignId('created_by')->constrained('users')->comment('Who created this policy');
            $table->timestamp('effective_from')->nullable()->comment('When policy becomes effective');
            $table->timestamp('expires_at')->nullable()->comment('When policy expires');
            $table->timestamps();

            // Indexes
            $table->index(['policy_type', 'is_active']);
            $table->index(['applies_to', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_policies');
    }
};
