@extends('layouts.app')
@section('title', 'マイ勤怠レポート')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-report.css') }}">
@endsection
@section('content')
<div class="attendance-report">

    <h1 class="attendance-report__title">マイ勤怠レポート</h1>
    <p class="attendance-report__lead">過去６か月の勤怠データから集計しています</p>

    <section class="attendance-report__section">
        <h2 class="attendance-report__heading">基本サマリー</h2>
        <div class="summary-grid">
            <div class="summary-grid__item">
                <p class="summary-grid__label">総労働時間</p>
                <p class="summary-grid__value">{{ $totalHours }}</p>
            </div>
            <div class="summary-grid__item">
                <p class="summary-grid__label">総残業時間</p>
                <p class="summary-grid__value">{{ $overtimeHours }}</p>
            </div>
            <div class="summary-grid__item">
                <p class="summary-grid__label">平均労働時間/日</p>
                <p class="summary-grid__value">{{ $averageHours }}</p>
            </div>
        </div>
    </section>

    <section class="attendance-report__section">
        <h2 class="attendance-report__heading">月次推移（過去6カ月）</h2>
        <table class="trend-table">
            <thead>
                <tr class="trend-table__row">
                    <th class="trend-table__th">月</th>
                    <th class="trend-table__th">労働時間</th>
                    <th class="trend-table__th">残業時間</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyTrend as $month)
                    <tr class="trend-table__row">
                        <td class="trend-table__td">{{ $month['month'] }}</td>
                        <td class="trend-table__td">{{ $month['workHours'] }}</td>
                        <td class="trend-table__td">{{ $month['overtimeHours'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="attendance-report__section">
        <h2 class="attendance-report__heading">今月の異常検知</h2>
        <p class="attendance-report__note">
            基準: 始業 {{ $standardStartTime }} / 終業 {{ $standardEndTime }} / 長時間労働は 1 日 {{ $longWorkThresholdHours }} 時間超
        </p>
        <div class="summary-grid">
            <div class="summary-grid__item">
                <p class="summary-grid__label">遅刻回数</p>
                <p class="summary-grid__value">{{ $lateCount }}回</p>
            </div>
            <div class="summary-grid__item">
                <p class="summary-grid__label">早退回数</p>
                <p class="summary-grid__value">{{ $earlyLeaveCount }}回</p>
            </div>
            <div class="summary-grid__item">
                <p class="summary-grid__label">長時間労働日数</p>
                <p class="summary-grid__value">{{ $longWorkCount }}日</p>
            </div>
        </div>
    </section>

</div>
@endsection