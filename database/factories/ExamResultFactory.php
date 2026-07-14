<?php

namespace Database\Factories;

use App\Models\Cisco;
use App\Models\ExamResult;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamResultFactory extends Factory
{
    protected $model = ExamResult::class;

    public function definition(): array
    {
        $cisco = Cisco::query()->inRandomOrder()->first();
        $total = $this->faker->numberBetween(300, 6000);
        $absent = $this->faker->numberBetween(0, (int) ($total * 0.12));
        $present = $total - $absent;
        $admitted = $this->faker->numberBetween((int) ($present * 0.25), (int) ($present * 0.92));

        return [
            'dren_id' => $cisco?->dren_id,
            'cisco_id' => $cisco?->id,
            'year' => 2026,
            'exam_name' => 'BEPC',
            'total_candidates' => $total,
            'absent_candidates' => $absent,
            'present_candidates' => $present,
            'admitted_candidates' => $admitted,
            'admission_threshold' => $this->faker->randomFloat(2, 9, 12),
            'published_at' => $this->faker->dateTimeBetween('-20 days', 'now'),
            'status' => ExamResult::STATUS_PUBLISHED,
        ];
    }
}
