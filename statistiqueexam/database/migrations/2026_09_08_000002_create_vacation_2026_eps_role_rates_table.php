<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_2026_eps_role_rates', function (Blueprint $table) {
            $table->id();
            $table->string('role_key')->unique();
            $table->string('label');
            $table->decimal('rate', 12, 2)->default(0);
            $table->timestamps();
        });

        DB::table('vacation_2026_eps_role_rates')->insert([
            ['role_key' => 'chef_centre', 'label' => 'Chefs de centre et adjoints', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['role_key' => 'surveillant', 'label' => 'Surveillants', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['role_key' => 'interrogateur', 'label' => 'Interrogateurs', 'rate' => 20000, 'created_at' => now(), 'updated_at' => now()],
            ['role_key' => 'secretariat', 'label' => 'Secrétariat', 'rate' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['role_key' => 'medecin', 'label' => 'Médecins', 'rate' => 30000, 'created_at' => now(), 'updated_at' => now()],
            ['role_key' => 'agent_stade', 'label' => 'Agents de stade', 'rate' => 15000, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_2026_eps_role_rates');
    }
};
