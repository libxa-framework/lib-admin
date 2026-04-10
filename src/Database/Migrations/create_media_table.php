<?php

use Libxa\Atlas\Schema\Blueprint;
use Libxa\Atlas\DB;

return new class
{
    public function up(): void
    {
        DB::schema()->create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('disk');
            $table->string('path');
            $table->json('custom_properties')->nullable();
            $table->timestamps();

            $table->index('admin_user_id');
            $table->index('disk');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        DB::schema()->dropIfExists('media');
    }
};
