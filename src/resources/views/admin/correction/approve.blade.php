@extends('layouts.app')
@section('title', '勤怠詳細')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-correction.css') }}">
@endsection
@section('content')
<div class="attendance-detail">

    <h1 class="attendance-detail__title">勤怠詳細</h1>

    @php
        $isApproved = $correction->status === 'approved';

        $date = \Carbon\Carbon::parse($correction->attendanceRecord->date);

        $formatTime = fn ($value) => $value
            ? \Carbon\Carbon::parse($value)->format('H:i')
            : '';
    @endphp

    <table class="detail-table">

        {{-- 名前 --}}
        <tr class="detail-table__row">
            <th class="detail-table__th">名前</th>
            <td class="detail-table__td" colspan="3">
                {{ $correction->user->name }}
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
                <input type="text" class="detail-input" value="{{ $formatTime($correction->clock_in) }}" disabled>
            </td>
            <td class="detail-table__td detail-table__td--separator">〜</td>
            <td class="detail-table__td">
                <input type="text" class="detail-input" value="{{ $formatTime($correction->clock_out) }}" disabled>
            </td>
        </tr>

        {{-- 休憩 --}}
        @foreach ($correction->breakCorrections as $index => $break)
            <tr class="detail-table__row">
                <th class="detail-table__th">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </th>
                <td class="detail-table__td">
                    <input type="text" class="detail-input" value="{{ $formatTime($break->break_start) }}" disabled>
                </td>
                <td class="detail-table__td detail-table__td--separator">〜</td>
                <td class="detail-table__td">
                    <input type="text" class="detail-input" value="{{ $formatTime($break->break_end) }}" disabled>
                </td>
            </tr>
        @endforeach

        {{-- 備考 --}}
        <tr class="detail-table__row">
            <th class="detail-table__th">備考</th>
            <td class="detail-table__td" colspan="3">
                <textarea rows="3" class="detail-input detail-input--comment" disabled>{{ $correction->comment }}</textarea>
            </td>
        </tr>

    </table>

    {{-- 承認ボタン or 承認済みメッセージ --}}
    <div class="detail-footer">
        @if ($isApproved)
            <button type="button" class="button button--approved" disabled>承認済み</button>
        @else
            <form action="{{ route('admin.correction.approve', $correction->id) }}" method="POST">
                @csrf
                <button type="submit" class="button button--primary button--submit">承認</button>
            </form>
        @endif
    </div>

</div>
@endsection