<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_PASSWORD = 'password123';

    /**
     * 有効な登録パラメータを生成する
     */
    private function validParams(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'テスト太郎',
            'email'                 => 'test@example.com',
            'password'              => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ], $overrides);
    }

    /**
     * 名前が未入力の場合、「お名前を入力してください」が表示される
     */
    public function testNameRequiredShowsErrorMessage(): void
    {
        $response = $this->post('/register', $this->validParams(['name' => '']));

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * メールアドレスが未入力の場合、「メールアドレスを入力してください」が表示される
     */
    public function testEmailRequiredShowsErrorMessage(): void
    {
        $response = $this->post('/register', $this->validParams(['email' => '']));

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが8文字未満の場合、「パスワードは8文字以上で入力してください」が表示される
     */
    public function testPasswordMinLengthShowsErrorMessage(): void
    {
        $response = $this->post('/register', $this->validParams([
            'password'              => 'pass1',
            'password_confirmation' => 'pass1',
        ]));

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * パスワードが一致しない場合、「パスワードと一致しません」が表示される
     */
    public function testPasswordConfirmationMismatchShowsErrorMessage(): void
    {
        $response = $this->post('/register', $this->validParams([
            'password_confirmation' => 'different123',
        ]));

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /**
     * パスワードが未入力の場合、「パスワードを入力してください」が表示される
     */
    public function testPasswordRequiredShowsErrorMessage(): void
    {
        $response = $this->post('/register', $this->validParams([
            'password'              => '',
            'password_confirmation' => '',
        ]));

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function testUserDataIsSavedWithValidInput(): void
    {
        $this->post('/register', $this->validParams());

        $this->assertDatabaseHas('users', [
            'name'         => 'テスト太郎',
            'email'        => 'test@example.com',
            'admin_status' => false,
        ]);
    }
}