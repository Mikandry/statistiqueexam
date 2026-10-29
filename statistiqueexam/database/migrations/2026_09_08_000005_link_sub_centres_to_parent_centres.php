<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centre_corrections', function (Blueprint $table) {
            $table->foreignId('parent_centre_correction_id')->nullable()->after('cisco_id')->constrained('centre_corrections')->nullOnDelete();
        });

        $centres = DB::table('centre_corrections')->select('id', 'cisco_id', 'nom', 'type_examen', 'centre_type')->get();
        foreach ($centres as $sousCentre) {
            if (strtoupper(trim((string) $sousCentre->centre_type)) !== 'SOUS-CENTRE' || !preg_match('/^(.+)-\\d+$/u', trim($sousCentre->nom), $matches)) continue;
            $parent = $centres->first(fn ($centre) => $centre->cisco_id === $sousCentre->cisco_id && trim($centre->nom) === trim($matches[1]) && (string) $centre->type_examen === (string) $sousCentre->type_examen);
            if ($parent) DB::table('centre_corrections')->where('id', $sousCentre->id)->update(['parent_centre_correction_id' => $parent->id]);
        }
    }

    public function down(): void
    {
        Schema::table('centre_corrections', fn (Blueprint $table) => $table->dropConstrainedForeignId('parent_centre_correction_id'));
    }
};
