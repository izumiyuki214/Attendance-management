<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceCorrection extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'attendance_corrections';

    /**
     * 一括割り当て可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'attendance_record_id',
        'clock_in',
        'clock_out',
        'status',
        'comment',
    ];

    /**
     * キャスト対象の属性
     *
     * @var array<string, string>
     */
    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    /**
     * ステータスの定数
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';

    /**
     * 修正申請の所有ユーザー
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 修正申請の対象となる勤怠記録
     *
     * @return BelongsTo
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * 修正申請の休憩修正情報
     *
     * @return HasMany
     */
    public function breakCorrections(): HasMany
    {
        return $this->hasMany(BreakCorrection::class);
    }

    /**
     * 修正申請が承認済みか判定
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * 修正申請が待機中か判定
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * 労働時間計算（分単位）
     *
     * @return int
     */
    public function getWorkingMinutes(): int
    {
        $workingTime = $this->clock_out->diffInMinutes($this->clock_in);
        $breakTime = $this->breakCorrections->sum(function (BreakCorrection $break) {
            return $break->break_end->diffInMinutes($break->break_start);
        });

        return $workingTime - $breakTime;
    }

    /**
     * 労働時間計算（時間単位）
     *
     * @return float
     */
    public function getWorkingHours(): float
    {
        return $this->getWorkingMinutes() / 60;
    }
}