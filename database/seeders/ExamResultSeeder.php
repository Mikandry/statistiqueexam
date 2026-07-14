<?php

namespace Database\Seeders;

use App\Models\Cisco;
use App\Models\ExamResult;
use Illuminate\Database\Seeder;

class ExamResultSeeder extends Seeder
{
    public function run(): void
    {
        Cisco::query()->with('dren')->take(30)->get()->each(function (Cisco $cisco): void {
            ExamResult::factory()->create([
                'dren_id' => $cisco->dren_id,
                'cisco_id' => $cisco->id,
            ]);
        });
    }
}
