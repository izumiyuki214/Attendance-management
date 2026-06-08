<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition()
    {
        $date = $this->faker->dateTime();

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => $date,
            'clock_out' => $date,
            'status' => AttendanceRecord::STATUS_FINISHED,
            'comment' => null,
        ];
    }
}