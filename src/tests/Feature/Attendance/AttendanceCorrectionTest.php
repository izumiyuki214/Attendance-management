<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効なパラメータを生成する
     */
    private function validParams(array $overrides = []): array
    {
        return array_merge([
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'comment'   => 'テスト修正申請',
        ], $overrides);
    }

    /**
     * 今日付けの勤怠レコードを生成する
     */
    private function createTodayAttendance(User $user): AttendanceRecord
    {
        return AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => AttendanceRecord::STATUS_FINISHED,
        ]);
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testClockInAfterClockOutShowsError(): void
    {
        $user       = User::factory()->create();
        $attendance = $this->createTodayAttendance($user);

        $response = $this->actingAs($user)
            ->post('/attendance/detail/' . $attendance->id, $this->validParams([
                'clock_in'  => '18:00',
                'clock_out' => '09:00',
            ]));

        $response->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testBreakStartAfterClockOutShowsError(): void
    {
        $user       = User::factory()->create();
        $attendance = $this->createTodayAttendance($user);

        $response = $this->actingAs($user)
            ->post('/attendance/detail/' . $attendance->id, $this->validParams([
                'breaks' => [
                    ['break_start' => '19:00', 'break_end' => ''],
                ],
            ]));

        $response->assertSessionHasErrors(['breaks.0.break_start' => '休憩時間が不適切な値です']);
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testBreakEndAfterClockOutShowsError(): void
    {
        $user       = User::factory()->create();
        $attendance = $this->createTodayAttendance($user);

        $response = $this->actingAs($user)
            ->post('/attendance/detail/' . $attendance->id, $this->validParams([
                'breaks' => [
                    ['break_start' => '12:00', 'break_end' => '19:00'],
                ],
            ]));

        $response->assertSessionHasErrors(['breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    /**
     * 備考欄が未入力の場合、エラーメッセージが表示される
     */
    public function testEmptyCommentShowsError(): void
    {
        $user       = User::factory()->create();
        $attendance = $this->createTodayAttendance($user);

        $response = $this->actingAs($user)
            ->post('/attendance/detail/' . $attendance->id, $this->validParams(['comment' => '']));

        $response->assertSessionHasErrors(['comment' => '備考を記入してください']);
    }

    /**
     * 修正申請処理が実行され、attendance_corrections にレコードが作成される
     */
    public function testCorrectionRequestIsCreated(): void
    {
        $user       = User::factory()->create();
        $attendance = $this->createTodayAttendance($user);

        $this->actingAs($user)
            ->post('/attendance/detail/' . $attendance->id, $this->validParams());

        $this->assertDatabaseHas('attendance_corrections', [
            'user_id'              => $user->id,
            'attendance_record_id' => $attendance->id,
            'status'               => 'pending',
            'comment'              => 'テスト修正申請',
        ]);
    }

    /**
     * 「承認待ち」にログインユーザーが行った申請が全て表示されている
     */
    public function testPendingCorrectionsAreDisplayedInList(): void
    {
        $user       = User::factory()->create();
        $attendance = $this->createTodayAttendance($user);
        $attendance->attendanceCorrections()->create([
            'user_id'   => $user->id,
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => 'pending',
            'comment'   => 'テスト修正申請',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSeeText('承認待ち');
    }

    /**
     * 「承認済み」に管理者が承認した修正申請が全て表示されている
     */
    public function testApprovedCorrectionsAreDisplayedInList(): void
    {
        $user       = User::factory()->create();
        $attendance = $this->createTodayAttendance($user);
        $attendance->attendanceCorrections()->create([
            'user_id'   => $user->id,
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => 'approved',
            'comment'   => 'テスト修正申請',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSeeText('承認済み');
    }
}