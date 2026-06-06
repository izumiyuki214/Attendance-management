<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CorrectionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================================
// ゲスト（一般）
// ========================================
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ========================================
// メール認証（認証済み一般ユーザー向け）
// ========================================
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/attendance');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '確認メールを再送信しました');
    })->middleware('throttle:6,1')->name('verification.send');
});

// ========================================
// 認証済み（一般）
// ========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/attendance/report', [ReportController::class, 'index'])->name('attendance.report');
    Route::get('/stamp_correction_request/list', [CorrectionController::class, 'index'])->name('correction.list');
});

// ========================================
// ゲスト（管理者）
// ========================================
Route::prefix('admin')->middleware('guest:admin')->group(function () {
    Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [Admin\AuthController::class, 'login']);
});

// ========================================
// 認証済み（管理者）
// ========================================
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/attendance/list', [Admin\AttendanceController::class, 'index'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [Admin\AttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::post('/attendance/{id}', [Admin\AttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/staff/list', [Admin\StaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/attendance/staff/{id}', [Admin\StaffController::class, 'show'])->name('admin.staff.show');
    Route::get('/stamp_correction_request/list', [Admin\CorrectionController::class, 'index'])->name('admin.correction.list');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [Admin\CorrectionController::class, 'show'])->name('admin.correction.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [Admin\CorrectionController::class, 'approve'])->name('admin.correction.approve');
});