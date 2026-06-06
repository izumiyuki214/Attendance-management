@extends('layouts.app')
@section('title', 'ログイン')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection
@section('content')
<div class="auth">
    <div class="auth__card">
        <h1 class="auth__title">ログイン</h1>
        <form action="{{ route('login') }}" method="POST" class="form">
            @csrf
            <div class="form__group">
                <label class="form__label">メールアドレス</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form__input">
                @error('email')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form__group">
                <label class="form__label">パスワード</label>
                <input type="password" name="password" class="form__input">
                @error('password')
                    <p class="form__error">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="button button--primary button--block">ログインする</button>
        </form>
        <p class="auth__switch">
            <a href="{{ url('/register') }}">会員登録はこちら</a>
        </p>
    </div>
</div>
@endsection