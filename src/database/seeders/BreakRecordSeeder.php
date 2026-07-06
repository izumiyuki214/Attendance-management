<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Illuminate\Database\Seeder;

class BreakRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * すべての勤怠記録に固定休憩（12:00-13:00 = 1時間）を追加
     */
    public function run(): void
    {
        $attendanceRecords = AttendanceRecord::all();

        foreach ($attendanceRecords as $record) {
            BreakRecord::create([
                'attendance_record_id' => $record->id,
                'break_start' => $record->date->copy()->setTime(12, 0, 0),
                'break_end' => $record->date->copy()->setTime(13, 0, 0),
            ]);
        }
    }
}