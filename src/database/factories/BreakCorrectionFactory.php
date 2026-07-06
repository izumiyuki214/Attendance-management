<?php

namespace Database\Factories;

use App\Models\AttendanceCorrection;
use App\Models\BreakCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakCorrectionFactory extends Factory
{
    protected $model = BreakCorrection::class;

    public function definition()
    {
        $startTime = $this->faker->dateTime();
        $endTime = (clone $startTime)->modify('+1 hour');

        return [
            'attendance_correction_id' => AttendanceCorrection::factory(),
            'break_start' => $startTime,
            'break_end' => $endTime,
        ];
    }
}