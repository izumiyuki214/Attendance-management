<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 打刻画面に現在の日付・時刻が正しい形式で表示される
     */
    public function testCurrentDateTimeIsDisplayedOnAttendancePage(): void
    {
        $user = User::factory()->create();
        $now  = Carbon::now();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText($now->format('Y年n月j日'));
        $response->assertSeeText($now->format('H:i'));
    }
}