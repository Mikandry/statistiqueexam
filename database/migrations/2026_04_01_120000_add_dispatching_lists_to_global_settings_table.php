<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->text('dispatching_axes')->nullable()->after('cepe_print_order');
            $table->text('dispatching_drop_points')->nullable()->after('dispatching_axes');
        });

        $defaultAxes = implode("\n", [
            'SAVA',
            'FORT DAUPHIN',
            'RN7',
            'MELAKY',
            'SOFIA',
            'VANGAINDRANO',
            'MENABE',
            'ANALANJIROFO',
            'BOENY',
            'VATOMANDRY',
            'FENOARIVOBE',
            'ALAOTRA MANGORO',
        ]);

        DB::table('global_settings')
            ->where(function ($query) {
                $query->whereNull('dispatching_axes')
                    ->orWhere('dispatching_axes', '');
            })
            ->update([
                'dispatching_axes' => $defaultAxes,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['dispatching_axes', 'dispatching_drop_points']);
        });
    }
};
