<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 当日の勤怠レコードがない場合、「勤務外」が表示される
     */
    public function testShowsOffWorkStatusWhenNoAttendanceRecord(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('勤務外');
    }

    /**
     * ステータスが working の場合、「出勤中」が表示される
     */
    public function testShowsWorkingStatus(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('出勤中');
    }

    /**
     * ステータスが on_break の場合、「休憩中」が表示される
     */
    public function testShowsOnBreakStatus(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_ON_BREAK,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('休憩中');
    }

    /**
     * ステータスが finished の場合、「退勤済」が表示される
     */
    public function testShowsFinishedStatus(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('退勤済');
    }
}