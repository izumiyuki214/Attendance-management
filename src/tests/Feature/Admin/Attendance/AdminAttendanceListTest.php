<?php

namespace Tests\Feature\Admin\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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
     * その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function testAllUsersAttendanceIsDisplayedForTheDay(): void
    {
        $admin  = $this->createAdmin();
        $staff1 = $this->createStaff(['name' => 'スタッフA']);
        $staff2 = $this->createStaff(['name' => 'スタッフB']);

        AttendanceRecord::factory()->create([
            'user_id'  => $staff1->id,
            'date'     => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'status'   => AttendanceRecord::STATUS_WORKING,
        ]);
        AttendanceRecord::factory()->create([
            'user_id'  => $staff2->id,
            'date'     => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(10, 0),
            'status'   => AttendanceRecord::STATUS_WORKING,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText('スタッフA');
        $response->assertSeeText('スタッフB');
        $response->assertSeeText('09:00');
        $response->assertSeeText('10:00');
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の日付が表示される
     */
    public function testCurrentDateIsDisplayedOnAttendanceList(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText(Carbon::today()->format('Y/m/d'));
    }

    /**
     * 「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function testPreviousDayAttendanceIsDisplayed(): void
    {
        $admin     = $this->createAdmin();
        $staff     = $this->createStaff();
        $yesterday = Carbon::today()->subDay();

        AttendanceRecord::factory()->create([
            'user_id'  => $staff->id,
            'date'     => $yesterday,
            'clock_in' => $yesterday->copy()->setTime(9, 0),
            'status'   => AttendanceRecord::STATUS_WORKING,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=' . $yesterday->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSeeText($yesterday->format('Y/m/d'));
    }

    /**
     * 「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function testNextDayAttendanceIsDisplayed(): void
    {
        $admin    = $this->createAdmin();
        $staff    = $this->createStaff();
        $tomorrow = Carbon::today()->addDay();

        AttendanceRecord::factory()->create([
            'user_id'  => $staff->id,
            'date'     => $tomorrow,
            'clock_in' => $tomorrow->copy()->setTime(9, 0),
            'status'   => AttendanceRecord::STATUS_WORKING,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=' . $tomorrow->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertSeeText($tomorrow->format('Y/m/d'));
    }
}