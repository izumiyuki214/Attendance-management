<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 退勤ボタンが表示され、処理後ステータスが「退勤済」になる
     */
    public function testClockOutButtonIsShownAndStatusBecomesFinished(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_WORKING,
        ]);

        // 退勤ボタンが表示されている
        $before = $this->actingAs($user)->get('/attendance');
        $before->assertSee('value="clock_out"', false);

        // 退勤処理後、ステータスが「退勤済」になる
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_out']);

        $after = $this->actingAs($user)->get('/attendance');
        $after->assertSeeText('退勤済');
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function testClockOutTimeIsRecordedInAttendanceList(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::today()->setTime(9, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow(Carbon::today()->setTime(18, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_out']);

        Carbon::setTestNow();

        // 勤怠一覧で退勤時刻が確認できる
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSeeText('18:00');
    }
}