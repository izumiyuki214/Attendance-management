<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 休憩入ボタンが表示され、処理後ステータスが「休憩中」になる
     */
    public function testBreakStartButtonIsShownAndStatusBecomesOnBreak(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_WORKING,
        ]);

        // 休憩入ボタンが表示されている
        $before = $this->actingAs($user)->get('/attendance');
        $before->assertSee('value="break_start"', false);

        // 休憩入処理後、ステータスが「休憩中」になる
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        $after = $this->actingAs($user)->get('/attendance');
        $after->assertSeeText('休憩中');
    }

    /**
     * 休憩は一日に何回でもできる（休憩入→休憩戻の後、再び休憩入ボタンが表示される）
     */
    public function testBreakCanBeTakenMultipleTimes(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        // 再び「休憩入」ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('value="break_start"', false);
    }

    /**
     * 休憩戻ボタンが表示され、処理後ステータスが「出勤中」になる
     */
    public function testBreakEndButtonIsShownAndStatusBecomesWorking(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        // 休憩戻ボタンが表示されている
        $during = $this->actingAs($user)->get('/attendance');
        $during->assertSee('value="break_end"', false);

        // 休憩戻処理後、ステータスが「出勤中」になる
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        $after = $this->actingAs($user)->get('/attendance');
        $after->assertSeeText('出勤中');
    }

    /**
     * 休憩戻は一日に何回でもできる（休憩入→休憩戻→休憩入の後、再び休憩戻ボタンが表示される）
     */
    public function testBreakEndCanBeDoneMultipleTimes(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_WORKING,
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        // 再び「休憩戻」ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('value="break_end"', false);
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できる
     */
    public function testBreakTimeIsRecordedInAttendanceList(): void
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::today()->setTime(9, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'clock_in']);

        Carbon::setTestNow(Carbon::today()->setTime(12, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_start']);

        Carbon::setTestNow(Carbon::today()->setTime(13, 0));
        $this->actingAs($user)->post('/attendance', ['action' => 'break_end']);

        Carbon::setTestNow();

        // 勤怠一覧で休憩時間（1時間）が確認できる
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSeeText('1:00');
    }
}