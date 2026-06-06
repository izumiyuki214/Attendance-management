<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    // ステータス定数
    private const STATUS_OFF_WORK  = 'off_work';
    private const STATUS_WORKING   = 'working';
    private const STATUS_ON_BREAK  = 'on_break';
    private const STATUS_FINISHED  = 'finished';

    // ========================================
    // 打刻画面
    // ========================================

    /**
     * 打刻画面を表示する
     */
    public function index(): View
    {
        $user   = Auth::user();
        $today  = Carbon::today();
        $now    = Carbon::now();

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $status = $attendance?->status ?? self::STATUS_OFF_WORK;

        return view('attendance.index', compact('attendance', 'status', 'now'));
    }

    /**
     * 打刻処理（出勤・休憩開始・休憩終了・退勤）
     */
    public function store(Request $request): RedirectResponse
    {
        $user   = Auth::user();
        $today  = Carbon::today();
        $now    = Carbon::now();

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $action = $request->input('action');

        match ($action) {
            'clock_in'      => $this->clockIn($user->id, $today, $now),
            'break_start'   => $this->breakStart($attendance, $now),
            'break_end'     => $this->breakEnd($attendance, $now),
            'clock_out'     => $this->clockOut($attendance, $now),
            default         => null,
        };

        return redirect()->route('attendance.index');
    }

    /**
     * 出勤処理
     */
    private function clockIn(int $userId, Carbon $today, Carbon $now): void
    {
        AttendanceRecord::create([
            'user_id'  => $userId,
            'date'     => $today->toDateString(),
            'clock_in' => $now,
            'status'   => self::STATUS_WORKING,
        ]);
    }

    /**
     * 休憩開始処理
     */
    private function breakStart(AttendanceRecord $attendance, Carbon $now): void
    {
        BreakRecord::create([
            'attendance_record_id' => $attendance->id,
            'break_start'          => $now,
        ]);

        $attendance->update(['status' => self::STATUS_ON_BREAK]);
    }

    /**
     * 休憩終了処理
     */
    private function breakEnd(AttendanceRecord $attendance, Carbon $now): void
    {
        $attendance->breakRecords()
            ->whereNull('break_end')
            ->latest('break_start')
            ->first()
            ?->update(['break_end' => $now]);

        $attendance->update(['status' => self::STATUS_WORKING]);
    }

    /**
     * 退勤処理
     */
    private function clockOut(AttendanceRecord $attendance, Carbon $now): void
    {
        $attendance->update([
            'clock_out' => $now,
            'status'    => self::STATUS_FINISHED,
        ]);
    }

    // ========================================
    // 勤怠一覧（一般）
    // ========================================

    /**
     * ログインユーザーの勤怠一覧を表示する
     */
    public function list(Request $request): View
    {
        $user  = Auth::user();
        $month = $request->input('month', Carbon::today()->format('Y-m'));

        $attendances = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', Carbon::parse($month)->year)
            ->whereMonth('date', Carbon::parse($month)->month)
            ->with('breakRecords')
            ->orderBy('date')
            ->get();

        return view('attendance.list', compact('attendances', 'month'));
    }

    // ========================================
    // 勤怠詳細（一般）
    // ========================================

    /**
     * 勤怠詳細画面を表示する
     */
    public function show(int $id): View
    {
        $attendance = AttendanceRecord::with('breakRecords')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('attendance.detail', compact('attendance'));
    }

    /**
     * 勤怠修正申請を登録する
     */
    public function update(AttendanceRequest $request, int $id): RedirectResponse
    {
        $attendance = AttendanceRecord::where('user_id', Auth::id())
            ->findOrFail($id);

        $date = $attendance->date;

        $attendance->corrections()->create([
            'user_id'              => Auth::id(),
            'attendance_record_id' => $attendance->id,
            'clock_in'             => Carbon::parse($date . ' ' . $request->clock_in),
            'clock_out'            => Carbon::parse($date . ' ' . $request->clock_out),
            'status'               => 'pending',
            'comment'              => $request->comment,
        ]);

        $breaks = collect($request->input('breaks', []))
            ->filter(fn($b) => filled($b['break_start'] ?? null));

        $correction = $attendance->corrections()->latest()->first();

        $breaks->each(function ($break) use ($correction, $date) {
            $correction->breakCorrections()->create([
                'break_start' => Carbon::parse($date . ' ' . $break['break_start']),
                'break_end'   => filled($break['break_end'] ?? null)
                    ? Carbon::parse($date . ' ' . $break['break_end'])
                    : null,
            ]);
        });

        return redirect()->route('correction.list');
    }
}