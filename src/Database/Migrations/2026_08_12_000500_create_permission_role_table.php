<?php

use Libxa\Atlas\Schema\Blueprint;
use Libxa\Atlas\DB;

return new class
{
    public function up(): void
    {
        DB::schema()->create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down(): void
    {
        DB::schema()->dropIfExists('permission_role');
    }
};
