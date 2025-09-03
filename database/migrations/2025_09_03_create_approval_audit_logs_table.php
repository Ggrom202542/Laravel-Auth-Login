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
        Schema::create('approval_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_approval_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ผู้ที่ทำการ
            $table->string('action'); // approve, reject, override, view, comment, etc.
            $table->string('old_status')->nullable(); // สถานะเดิม
            $table->string('new_status')->nullable(); // สถานะใหม่
            $table->text('reason')->nullable(); // เหตุผล
            $table->text('comments')->nullable(); // ความเห็นเพิ่มเติม
            $table->json('metadata')->nullable(); // ข้อมูลเพิ่มเติม (IP, User Agent, etc.)
            $table->boolean('is_override')->default(false); // เป็นการ Override หรือไม่
            $table->foreignId('overridden_by')->nullable()->constrained('users')->onDelete('set null'); // ใครเป็นคน Override
            $table->timestamp('performed_at'); // เวลาที่ทำ
            $table->timestamps();
            
            // Indexes
            $table->index(['registration_approval_id', 'performed_at']);
            $table->index(['user_id', 'action']);
            $table->index('is_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_audit_logs');
    }
};
