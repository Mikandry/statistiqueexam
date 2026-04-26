<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('bepc_copy_margin_percent', 5, 2)->default(5.00);
            $table->timestamps();
        });

        DB::table('global_settings')->insert([
            'bepc_copy_margin_percent' => 5.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
