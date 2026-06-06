<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'atte')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header-left">
                <a href="{{ url('/') }}" class="header__logo">
                    <img class="header__logo-img" src="{{ asset('img/header-logo.png') }}" alt="atte">
                </a>
            </div>
            <nav class="header__nav">
                @auth
                    @if (auth()->user()->admin_status)
                        {{-- 管理者ナビ --}}
                        <a href="{{ route('admin.attendance.list') }}" class="header__link">勤怠一覧</a>
                        <a href="{{ route('admin.staff.list') }}" class="header__link">スタッフ一覧</a>
                        <a href="{{ route('admin.correction.list') }}" class="header__link">申請一覧</a>
                        <form action="{{ route('admin.logout') }}" method="POST" class="header__logout-form">
                            @csrf
                            <button type="submit" class="header__link header__logout-button">ログアウト</button>
                        </form>
                    @elseif (auth()->user()->attendanceRecords()->whereDate('date', today())->value('status') === 'finished')
                        {{-- 一般：退勤済み --}}
                        <a href="{{ route('attendance.list') }}" class="header__link">今月の出勤一覧</a>
                        <a href="{{ route('correction.list') }}" class="header__link">申請一覧</a>
                        <form action="{{ route('logout') }}" method="POST" class="header__logout-form">
                            @csrf
                            <button type="submit" class="header__link header__logout-button">ログアウト</button>
                        </form>
                    @else
                        {{-- 一般：退勤以外 --}}
                        <a href="{{ route('attendance.index') }}" class="header__link">勤怠</a>
                        <a href="{{ route('attendance.list') }}" class="header__link">勤怠一覧</a>
                        <a href="{{ route('correction.list') }}" class="header__link">申請</a>
                        <form action="{{ route('logout') }}" method="POST" class="header__logout-form">
                            @csrf
                            <button type="submit" class="header__link header__logout-button">ログアウト</button>
                        </form>
                    @endif
                @endauth
                {{-- 未ログイン時は何も表示しない --}}
            </nav>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>
</html>