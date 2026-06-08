<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ユーザーを取得
        $user1 = User::where('email', 'user1@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();
        $user3 = User::where('email', 'user3@example.com')->first();

        // user1: マイ勤怠レポート検証用データ
        // 過去5ヶ月: 各月平日15日の通常勤務（9:00-18:00）
        $this->createPastMonthsAttendance($user1);

        // user1: 当月データ（17日のパターン）
        // 通常10 / 残業3 / 遅刻2 / 早退1 / 長時間労働1
        $this->createCurrentMonthAttendance($user1);

        // user2: 基本的なダミーデータ
        $this->createBasicAttendance($user2);

        // user3: 基本的なダミーデータ
        $this->createBasicAttendance($user3);
    }

    /**
     * 過去5ヶ月の通常勤務データを作成
     * 各月平日15日の通常勤務（9:00-18:00）
     */
    private function createPastMonthsAttendance(User $user): void
    {
        // 過去5ヶ月分
        for ($monthsAgo = 5; $monthsAgo >= 1; $monthsAgo--) {
            $date = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $daysAdded = 0;

            // 各月の平日15日分
            while ($daysAdded < 15) {
                // 土日を除く平日のみ
                if ($date->isWeekday()) {
                    AttendanceRecord::create([
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                        'clock_in' => $date->copy()->setTime(9, 0, 0),
                        'clock_out' => $date->copy()->setTime(18, 0, 0),
                        'status' => AttendanceRecord::STATUS_FINISHED,
                        'comment' => null,
                    ]);
                    $daysAdded++;
                }
                $date->addDay();
            }
        }
    }

    /**
     * 当月データを作成（17日のパターン）
     * 通常10 / 残業3 / 遅刻2 / 早退1 / 長時間労働1
     */
    private function createCurrentMonthAttendance(User $user): void
    {
        $date = Carbon::now()->startOfMonth();
        $patterns = [];

        // パターン定義
        // 通常勤務: 9:00-18:00 (10日)
        for ($i = 0; $i < 10; $i++) {
            $patterns[] = [
                'clock_in' => 9,
                'clock_out' => 18,
            ];
        }

        // 残業: 9:00-20:00 (3日)
        for ($i = 0; $i < 3; $i++) {
            $patterns[] = [
                'clock_in' => 9,
                'clock_out' => 20,
            ];
        }

        // 遅刻: 9:30-18:00 (2日)
        for ($i = 0; $i < 2; $i++) {
            $patterns[] = [
                'clock_in' => 9,
                'clock_in_minute' => 30,
                'clock_out' => 18,
            ];
        }

        // 早退: 9:00-17:00 (1日)
        $patterns[] = [
            'clock_in' => 9,
            'clock_out' => 17,
        ];

        // 長時間労働: 8:00-21:00 (1日)
        $patterns[] = [
            'clock_in' => 8,
            'clock_out' => 21,
        ];

        // パターンを日付に割り当て
        $patternIndex = 0;
        $daysAdded = 0;

        while ($daysAdded < 17) {
            // 土日を除く平日のみ
            if ($date->isWeekday() && $patternIndex < count($patterns)) {
                $pattern = $patterns[$patternIndex];
                $clockInMinute = $pattern['clock_in_minute'] ?? 0;

                AttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'clock_in' => $date->copy()->setTime($pattern['clock_in'], $clockInMinute, 0),
                    'clock_out' => $date->copy()->setTime($pattern['clock_out'], 0, 0),
                    'status' => AttendanceRecord::STATUS_FINISHED,
                    'comment' => null,
                ]);

                $daysAdded++;
                $patternIndex++;
            }
            $date->addDay();
        }
    }

    /**
     * 基本的なダミーデータを作成（user2, user3用）
     */
    private function createBasicAttendance(User $user): void
    {
        // 過去1ヶ月の平日10日分
        $date = Carbon::now()->subMonth()->startOfMonth();
        $daysAdded = 0;

        while ($daysAdded < 10) {
            if ($date->isWeekday()) {
                AttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'clock_in' => $date->copy()->setTime(9, 0, 0),
                    'clock_out' => $date->copy()->setTime(18, 0, 0),
                    'status' => AttendanceRecord::STATUS_FINISHED,
                    'comment' => null,
                ]);
                $daysAdded++;
            }
            $date->addDay();
        }

        // 当月の平日5日分
        $date = Carbon::now()->startOfMonth();
        $daysAdded = 0;

        while ($daysAdded < 5) {
            if ($date->isWeekday()) {
                AttendanceRecord::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'clock_in' => $date->copy()->setTime(9, 0, 0),
                    'clock_out' => $date->copy()->setTime(18, 0, 0),
                    'status' => AttendanceRecord::STATUS_FINISHED,
                    'comment' => null,
                ]);
                $daysAdded++;
            }
            $date->addDay();
        }
    }
}