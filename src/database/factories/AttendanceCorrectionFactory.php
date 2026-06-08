<?php

namespace Database\Factories;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        $date = $this->faker->dateTime();

        return [
            'user_id' => User::factory(),
            'attendance_record_id' => AttendanceRecord::factory(),
            'clock_in' => $date,
            'clock_out' => $date,
            'status' => AttendanceCorrection::STATUS_PENDING,
            'comment' => $this->faker->text(100),
        ];
    }
}