<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function testDetailPageShowsLoginUsersName(): void
    {
        $user       = User::factory()->create(['name' => 'テスト太郎']);
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSeeText('テスト太郎');
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function testDetailPageShowsSelectedDate(): void
    {
        $user       = User::factory()->create();
        $date       = Carbon::today();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => $date,
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertSeeText($date->year . '年');
        $response->assertSeeText($date->month . '月' . $date->day . '日');
    }

    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function testDetailPageShowsCorrectClockInOutTimes(): void
    {
        $user       = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function testDetailPageShowsCorrectBreakTimes(): void
    {
        $user       = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => AttendanceRecord::STATUS_FINISHED,
        ]);
        BreakRecord::factory()->create([
            'attendance_record_id' => $attendance->id,
            'break_start'          => Carbon::today()->setTime(12, 0),
            'break_end'            => Carbon::today()->setTime(13, 0),
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}