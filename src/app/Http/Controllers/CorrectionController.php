<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CorrectionController extends Controller
{
    // 修正申請ステータス定数
    private const STATUS_PENDING  = 'pending';
    private const STATUS_APPROVED = 'approved';

    /**
     * ログインユーザーの修正申請一覧を表示する
     * status指定がない、または不正な場合は「承認待ち」を表示する
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', self::STATUS_PENDING);

        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED], true)) {
            $status = self::STATUS_PENDING;
        }

        $corrections = AttendanceCorrection::where('user_id', Auth::id())
            ->where('status', $status)
            ->with('attendanceRecord')
            ->latest()
            ->get();

        return view('correction.list', compact('corrections', 'status'));
    }
}