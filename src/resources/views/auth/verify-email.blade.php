@extends('layouts.app')
@section('title', 'メール認証')
@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection
@section('content')
    <p class="guidance-text">
        登録したメールアドレスに認証リンクを送信しました。
    </p>
    <p class="guidance-text">
        メール認証を完了してください。
    </p>
    <div class="guidance-btn">
        <a class="guidance-btn--a" href="http://localhost:8025">
            認証はこちらから
        </a>
    </div>
    @if (session('message'))
        <p class="resend-message">{{ session('message') }}</p>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="resend-submit">
            認証メールを再送する
        </button>
    </form>
@endsection