<?php

use Libxa\Atlas\Schema\Blueprint;
use Libxa\Atlas\DB;

return new class
{
    public function up(): void
    {
        DB::schema()->create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_user_id')->constrained('admin_users')->onDelete('cascade');
            $table->timestamps();

            $table->primary(['role_id', 'admin_user_id']);
        });
    }

    public function down(): void
    {
        DB::schema()->dropIfExists('role_user');
    }
};
