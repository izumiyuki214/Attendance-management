@extends('layouts.app')
@section('title', '勤怠詳細')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection
@section('content')
<div class="attendance-detail">

    <h1 class="attendance-detail__title">勤怠詳細</h1>

    @php
        $latestCorrection = $attendance->attendanceCorrections()
            ->with('breakCorrections')
            ->latest()
            ->first();

        $isPending = $latestCorrection?->status === 'pending';

        $date = \Carbon\Carbon::parse($attendance->date);

        $clockInSource  = $latestCorrection?->clock_in  ?? $attendance->clock_in;
        $clockOutSource = $latestCorrection?->clock_out ?? $attendance->clock_out;
        $commentSource  = $latestCorrection?->comment   ?? $attendance->comment;
        $breaksSource   = $latestCorrection
            ? $latestCorrection->breakCorrections
            : $attendance->breakRecords;

        $breakRows = $breaksSource->count();

        $formatTime = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('H:i') : '';
    @endphp

    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf

        <table class="detail-table">

            {{-- 名前 --}}
            <tr class="detail-table__row">
                <th class="detail-table__th">名前</th>
                <td class="detail-table__td" colspan="3">
                    {{ $attendance->user->name }}
                </td>
            </tr>

            {{-- 日付（変更不可） --}}
            <tr class="detail-table__row">
                <th class="detail-table__th">日付</th>
                <td class="detail-table__td">{{ $date->year }}年</td>
                <td class="detail-table__td"></td>
                <td class="detail-table__td" colspan="2">{{ $date->month }}月{{ $date->day }}日</td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr class="detail-table__row">
                <th class="detail-table__th">出勤・退勤</th>
                <td class="detail-table__td">
                    <input
                        type="text"
                        name="clock_in"
                        value="{{ old('clock_in', $formatTime($clockInSource)) }}"
                        class="detail-input"
                        pattern="^([01]\d|2[0-3]):[0-5]\d$"
                        {{ $isPending ? 'disabled' : '' }}
                    >
                    @error('clock_in')
                        <p class="detail-input__error">{{ $message }}</p>
                    @enderror
                </td>
                <td class="detail-table__td detail-table__td--separator">〜</td>
                <td class="detail-table__td">
                    <input
                        type="text"
                        name="clock_out"
                        value="{{ old('clock_out', $formatTime($clockOutSource)) }}"
                        class="detail-input"
                        pattern="^([01]\d|2[0-3]):[0-5]\d$"
                        {{ $isPending ? 'disabled' : '' }}
                    >
                    @error('clock_out')
                        <p class="detail-input__error">{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            {{-- 休憩（既存） --}}
            @foreach ($breaksSource as $index => $break)
                <tr class="detail-table__row">
                    <th class="detail-table__th">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </th>
                    <td class="detail-table__td">
                        <input
                            type="text"
                            name="breaks[{{ $index }}][break_start]"
                            value="{{ old('breaks.' . $index . '.break_start', $formatTime($break->break_start)) }}"
                            class="detail-input"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            {{ $isPending ? 'disabled' : '' }}
                        >
                        @error('breaks.' . $index . '.break_start')
                            <p class="detail-input__error">{{ $message }}</p>
                        @enderror
                    </td>
                    <td class="detail-table__td detail-table__td--separator">〜</td>
                    <td class="detail-table__td">
                        <input
                            type="text"
                            name="breaks[{{ $index }}][break_end]"
                            value="{{ old('breaks.' . $index . '.break_end', $formatTime($break->break_end)) }}"
                            class="detail-input"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                            {{ $isPending ? 'disabled' : '' }}
                        >
                        @error('breaks.' . $index . '.break_end')
                            <p class="detail-input__error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            @endforeach

            {{-- 空欄の休憩行（申請待ちでなければ表示） --}}
            @if (!$isPending)
                <tr class="detail-table__row">
                    <th class="detail-table__th">
                        {{ $breakRows === 0 ? '休憩' : '休憩' . ($breakRows + 1) }}
                    </th>
                    <td class="detail-table__td">
                        <input
                            type="text"
                            name="breaks[{{ $breakRows }}][break_start]"
                            value="{{ old('breaks.' . $breakRows . '.break_start') }}"
                            class="detail-input"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                        >
                        @error('breaks.' . $breakRows . '.break_start')
                            <p class="detail-input__error">{{ $message }}</p>
                        @enderror
                    </td>
                    <td class="detail-table__td detail-table__td--separator">〜</td>
                    <td class="detail-table__td">
                        <input
                            type="text"
                            name="breaks[{{ $breakRows }}][break_end]"
                            value="{{ old('breaks.' . $breakRows . '.break_end') }}"
                            class="detail-input"
                            pattern="^([01]\d|2[0-3]):[0-5]\d$"
                        >
                        @error('breaks.' . $breakRows . '.break_end')
                            <p class="detail-input__error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            @endif

            {{-- 備考 --}}
            <tr class="detail-table__row">
                <th class="detail-table__th">備考</th>
                <td class="detail-table__td" colspan="3">
                    <textarea
                        rows="3"
                        name="comment"
                        class="detail-input detail-input--comment"
                        {{ $isPending ? 'disabled' : '' }}
                    >{{ old('comment', $commentSource) }}</textarea>
                    @error('comment')
                        <p class="detail-input__error">{{ $message }}</p>
                    @enderror
                </td>
            </tr>

        </table>

        {{-- 修正ボタン or 承認待ちメッセージ --}}
        <div class="detail-footer">
            @if ($isPending)
                <p class="detail-footer__pending">＊承認待ちのため修正はできません。</p>
            @else
                <button type="submit" class="button button--primary button--submit">修正</button>
            @endif
        </div>

    </form>

</div>
@endsection