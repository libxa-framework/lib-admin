<?php

use Libxa\Atlas\Schema\Blueprint;
use Libxa\Atlas\DB;

return new class
{
    public function up(): void
    {
        DB::schema()->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('resource')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        DB::schema()->dropIfExists('permissions');
    }
};
