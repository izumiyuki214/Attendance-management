<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤ボタンが表示され、処理後ステータスが「出勤中」になる
     */
    public function testClockInButtonIsShownAndStatusBecomesWorking(): void
    {
        $user = User::factory()->create();

        // 出勤ボタンが表示されている
        $before = $this->actingAs($user)->get('/attendance');
        $before->assertSee('value="clock_in"', false);

        // 出勤処理後、ステータスが「出勤中」になる
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_in']);

        $after = $this->actingAs($user)->get('/attendance');
        $after->assertSeeText('出勤中');
    }

    /**
     * 退勤済みのユーザーには出勤ボタンが表示されない
     */
    public function testClockInButtonIsNotShownWhenAlreadyFinished(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('value="clock_in"', false);
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function testClockInTimeIsRecordedInAttendanceList(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::today()->setTime(9, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_in']);
        Carbon::setTestNow();

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSeeText('09:00');
    }
}