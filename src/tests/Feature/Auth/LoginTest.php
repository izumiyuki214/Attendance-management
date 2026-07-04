<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'password';

    /**
     * メールアドレスが未入力の場合、バリデーションエラーになる
     */
    public function testEmailRequiredValidationError(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => self::PASSWORD,
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * パスワードが未入力の場合、バリデーションエラーになる
     */
    public function testPasswordRequiredValidationError(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * 登録内容と一致しない場合、「ログイン情報が登録されていません」というエラーメッセージが表示される
     */
    public function testLoginFailsWithUnregisteredCredentials(): void
    {
        $response = $this->post('/login', [
            'email'    => 'notfound@example.com',
            'password' => self::PASSWORD,
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
        $this->assertGuest();
    }

}