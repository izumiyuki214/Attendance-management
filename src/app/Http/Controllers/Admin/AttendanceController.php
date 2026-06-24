<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * 管理者：勤怠一覧画面（日別・全ユーザー）
     */
    public function index(Request $request): View
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : Carbon::today();

        $attendances = AttendanceRecord::with(['user', 'breakRecords'])
            ->whereDate('date', $date)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.index', compact('attendances', 'date'));
    }
}