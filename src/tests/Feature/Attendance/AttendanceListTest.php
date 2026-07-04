<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分の勤怠情報が全て表示されている
     */
    public function testAllOwnAttendanceRecordsAreDisplayed(): void
    {
        $user  = User::factory()->create();
        $date1 = Carbon::today()->startOfMonth();
        $date2 = Carbon::today()->startOfMonth()->addDay();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => $date1,
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => $date2,
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText($date1->format('m/d'));
        $response->assertSeeText($date2->format('m/d'));
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function testCurrentMonthIsDisplayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText(Carbon::today()->format('Y/m'));
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function testPreviousMonthDataIsDisplayed(): void
    {
        $user      = User::factory()->create();
        $lastMonth = Carbon::today()->subMonth();
        $date      = $lastMonth->copy()->startOfMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => $date,
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $lastMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText($date->format('m/d'));
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function testNextMonthDataIsDisplayed(): void
    {
        $user      = User::factory()->create();
        $nextMonth = Carbon::today()->addMonth();
        $date      = $nextMonth->copy()->startOfMonth();

        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => $date,
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText($date->format('m/d'));
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function testDetailLinkNavigatesToAttendanceDetail(): void
    {
        $user       = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today(),
            'status'  => AttendanceRecord::STATUS_FINISHED,
        ]);

        $listResponse = $this->actingAs($user)->get('/attendance/list');
        $listResponse->assertSee(route('attendance.show', $attendance->id));

        $detailResponse = $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id));
        $detailResponse->assertStatus(200);
    }
}