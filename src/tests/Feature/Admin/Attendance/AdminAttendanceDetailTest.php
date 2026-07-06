<?php

namespace Tests\Feature\Admin\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['admin_status' => true]);
    }

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

    private function validParams(array $overrides = []): array
    {
        return array_merge([
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'comment'   => 'テスト修正',
        ], $overrides);
    }

    /**
     * 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function testDetailPageShowsSelectedAttendanceData(): void
    {
        $admin      = $this->createAdmin();
        $staff      = User::factory()->create(['admin_status' => false, 'name' => 'テスト太郎']);
        $attendance = $this->createTodayAttendance($staff);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSeeText('テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function testClockInAfterClockOutShowsError(): void
    {
        $admin      = $this->createAdmin();
        $staff      = User::factory()->create(['admin_status' => false]);
        $attendance = $this->createTodayAttendance($staff);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/' . $attendance->id, $this->validParams([
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
        $admin      = $this->createAdmin();
        $staff      = User::factory()->create(['admin_status' => false]);
        $attendance = $this->createTodayAttendance($staff);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/' . $attendance->id, $this->validParams([
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
        $admin      = $this->createAdmin();
        $staff      = User::factory()->create(['admin_status' => false]);
        $attendance = $this->createTodayAttendance($staff);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/' . $attendance->id, $this->validParams([
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
        $admin      = $this->createAdmin();
        $staff      = User::factory()->create(['admin_status' => false]);
        $attendance = $this->createTodayAttendance($staff);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/' . $attendance->id, $this->validParams(['comment' => '']));

        $response->assertSessionHasErrors(['comment' => '備考を記入してください']);
    }
}