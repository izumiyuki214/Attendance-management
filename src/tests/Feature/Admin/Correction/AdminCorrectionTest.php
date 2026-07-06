<?php

namespace Tests\Feature\Admin\Correction;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['admin_status' => true]);
    }

    private function createStaff(): User
    {
        return User::factory()->create(['admin_status' => false]);
    }

    /**
     * 勤怠レコードと修正申請をまとめて生成する
     */
    private function createCorrectionWithAttendance(
        User $staff,
        string $status = AttendanceCorrection::STATUS_PENDING
    ): AttendanceCorrection {
        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $staff->id,
            'date'      => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => AttendanceRecord::STATUS_FINISHED,
        ]);

        return $attendance->attendanceCorrections()->create([
            'user_id'   => $staff->id,
            'clock_in'  => Carbon::today()->setTime(10, 0),
            'clock_out' => Carbon::today()->setTime(19, 0),
            'status'    => $status,
            'comment'   => '遅刻のため修正',
        ]);
    }

    /**
     * 承認待ちの修正申請が全て表示されている
     */
    public function testPendingCorrectionsAreDisplayed(): void
    {
        $admin      = $this->createAdmin();
        $staff      = $this->createStaff();
        $correction = $this->createCorrectionWithAttendance($staff, AttendanceCorrection::STATUS_PENDING);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSeeText('承認待ち');
        $response->assertSeeText($staff->name);
    }

    /**
     * 承認済みの修正申請が全て表示されている
     */
    public function testApprovedCorrectionsAreDisplayed(): void
    {
        $admin      = $this->createAdmin();
        $staff      = $this->createStaff();
        $correction = $this->createCorrectionWithAttendance($staff, AttendanceCorrection::STATUS_APPROVED);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSeeText('承認済み');
        $response->assertSeeText($staff->name);
    }

    /**
     * 修正申請の詳細内容が正しく表示されている
     */
    public function testCorrectionDetailIsDisplayedCorrectly(): void
    {
        $admin      = $this->createAdmin();
        $staff      = User::factory()->create(['admin_status' => false, 'name' => 'テスト太郎']);
        $correction = $this->createCorrectionWithAttendance($staff);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/approve/' . $correction->id);

        $response->assertStatus(200);
        $response->assertSeeText('テスト太郎');
        // 修正申請の出退勤時刻（10:00〜19:00）が表示されている
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSeeText('遅刻のため修正');
    }

    /**
     * 修正申請の承認処理が正しく行われる（勤怠情報が更新され、ステータスが承認済みになる）
     */
    public function testApprovalUpdatesCorrectionStatusAndAttendanceRecord(): void
    {
        $admin      = $this->createAdmin();
        $staff      = $this->createStaff();
        $correction = $this->createCorrectionWithAttendance($staff);
        $attendance = $correction->attendanceRecord;

        $this->actingAs($admin)
            ->post('/admin/stamp_correction_request/approve/' . $correction->id);

        // 修正申請のステータスが承認済みになっている
        $this->assertDatabaseHas('attendance_corrections', [
            'id'     => $correction->id,
            'status' => AttendanceCorrection::STATUS_APPROVED,
        ]);

        // 勤怠レコードが修正申請の内容で更新されている
        $this->assertEquals(
            $correction->clock_in->format('H:i'),
            $attendance->fresh()->clock_in->format('H:i')
        );
        $this->assertEquals(
            $correction->clock_out->format('H:i'),
            $attendance->fresh()->clock_out->format('H:i')
        );
    }
}