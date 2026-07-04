<?php

namespace Tests\Feature\Admin\Staff;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['admin_status' => true]);
    }

    private function createStaff(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['admin_status' => false], $overrides));
    }

    /**
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function testAllStaffNamesAndEmailsAreDisplayed(): void
    {
        $admin  = $this->createAdmin();
        $staff1 = $this->createStaff(['name' => 'スタッフA', 'email' => 'staff_a@example.com']);
        $staff2 = $this->createStaff(['name' => 'スタッフB', 'email' => 'staff_b@example.com']);

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSeeText('スタッフA');
        $response->assertSeeText('staff_a@example.com');
        $response->assertSeeText('スタッフB');
        $response->assertSeeText('staff_b@example.com');
    }

    /**
     * ユーザーの勤怠情報が正しく表示される
     */
    public function testStaffAttendanceIsDisplayedCorrectly(): void
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();

        AttendanceRecord::factory()->create([
            'user_id'   => $staff->id,
            'date'      => Carbon::today(),
            'clock_in'  => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'status'    => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id);

        $response->assertStatus(200);
        $response->assertSeeText(Carbon::today()->format('m/d'));
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function testPreviousMonthDataIsDisplayed(): void
    {
        $admin     = $this->createAdmin();
        $staff     = $this->createStaff();
        $lastMonth = Carbon::today()->subMonth();
        $date      = $lastMonth->copy()->startOfMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $staff->id,
            'date'    => $date,
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id . '?month=' . $lastMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText($date->format('m/d'));
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function testNextMonthDataIsDisplayed(): void
    {
        $admin     = $this->createAdmin();
        $staff     = $this->createStaff();
        $nextMonth = Carbon::today()->addMonth();
        $date      = $nextMonth->copy()->startOfMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $staff->id,
            'date'    => $date,
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id . '?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText($date->format('m/d'));
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function testDetailLinkNavigatesToAttendanceDetail(): void
    {
        $admin      = $this->createAdmin();
        $staff      = $this->createStaff();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $staff->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $listResponse = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id);
        $listResponse->assertSee(route('admin.attendance.show', $attendance->id));

        $detailResponse = $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance->id));
        $detailResponse->assertStatus(200);
    }
}