<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakRecordFactory extends Factory
{
    protected $model = BreakRecord::class;

    public function definition()
    {
        $startTime = $this->faker->dateTime();
        $endTime = (clone $startTime)->modify('+1 hour');

        return [
            'attendance_record_id' => AttendanceRecord::factory(),
            'break_start' => $startTime,
            'break_end' => $endTime,
        ];
    }
}