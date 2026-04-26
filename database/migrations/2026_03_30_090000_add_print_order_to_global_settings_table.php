<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->text('bepc_print_order')->nullable()->after('bepc_pages_all');
            $table->text('cepe_print_order')->nullable()->after('bepc_print_order');
        });
    }

    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['bepc_print_order', 'cepe_print_order']);
        });
    }
};
