<?php

use Libxa\Atlas\Schema\Blueprint;
use Libxa\Atlas\DB;

return new class
{
    public function up(): void
    {
        DB::schema()->create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        DB::schema()->dropIfExists('roles');
    }
};
