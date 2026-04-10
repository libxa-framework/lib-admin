<?php

use Libxa\Atlas\Schema\Blueprint;
use Libxa\Atlas\DB;

return new class
{
    public function up(): void
    {
        DB::schema()->create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('event');
            $table->string('resource_type')->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['resource_type', 'resource_id']);
            $table->index('admin_user_id');
            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        DB::schema()->dropIfExists('audit_logs');
    }
};
