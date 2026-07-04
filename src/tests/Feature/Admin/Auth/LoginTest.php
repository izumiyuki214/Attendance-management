<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'password';

    /**
     * メールアドレスが未入力の場合、「メールアドレスを入力してください」が表示される
     */
    public function testEmailRequiredShowsErrorMessage(): void
    {
        $response = $this->post('/admin/login', [
            'email'    => '',
            'password' => self::PASSWORD,
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが未入力の場合、「パスワードを入力してください」が表示される
     */
    public function testPasswordRequiredShowsErrorMessage(): void
    {
        $admin = User::factory()->create(['admin_status' => true]);

        $response = $this->post('/admin/login', [
            'email'    => $admin->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * 登録内容と一致しない場合、「ログイン情報が登録されていません」が表示される
     */
    public function testUnregisteredCredentialsShowsErrorMessage(): void
    {
        $response = $this->post('/admin/login', [
            'email'    => 'notfound@example.com',
            'password' => self::PASSWORD,
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
        $this->assertGuest();
    }
}