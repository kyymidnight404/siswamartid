<?php

namespace Tests\Feature\Auth;

use App\Models\Penjual;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Lupa password?');
        $response->assertSee('Kirim Link Reset Password');
    }

    public function test_reset_password_link_can_be_requested_by_active_admin(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'email' => 'admin_test_reset_'.uniqid().'@siswamart.test',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $admin->email,
        ]);

        $response->assertSessionHas('status', __('passwords.sent'));

        Notification::assertSentTo(
            $admin,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($admin) {
                return ! empty($notification->token);
            }
        );
    }

    public function test_reset_password_link_can_be_requested_by_active_seller(): void
    {
        Notification::fake();

        $seller = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'email' => 'seller_test_reset_'.uniqid().'@siswamart.test',
        ]);

        Penjual::create([
            'user_id' => $seller->id,
            'nama_toko' => 'Toko Uji Seller',
            'nomor_whatsapp' => '081234567890',
            'lokasi_kelas' => '11 RPL 1',
            'nisn' => (string) random_int(1000000000, 9999999999),
            'alamat' => 'Bandung',
            'status' => 'disetujui',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $seller->email,
        ]);

        $response->assertSessionHas('status', __('passwords.sent'));

        Notification::assertSentTo(
            $seller,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($seller) {
                return ! empty($notification->token);
            }
        );
    }

    public function test_reset_password_notification_mail_content_is_secure_and_branded(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin SiswaMart',
            'role' => 'admin',
            'is_active' => true,
            'email' => 'admin_mail_'.uniqid().'@siswamart.test',
        ]);

        $token = 'sample-secret-token-12345';
        $notification = new ResetPasswordNotification($token);
        $mail = $notification->toMail($admin);

        $this->assertEquals('Reset Password SiswaMart', $mail->subject);
        $html = (string) $mail->render();

        $this->assertStringContainsString('Reset Password SiswaMart', $html);
        $this->assertStringContainsString('Halo Admin SiswaMart,', $html);
        $this->assertStringContainsString('sample-secret-token-12345', $html);
        $this->assertStringContainsString(urlencode($admin->email), $html);
        $this->assertStringContainsString('Atur Ulang Password', $html);
        $this->assertStringContainsString('60 menit', $html);
        $this->assertStringContainsString('Tim SiswaMart', $html);
        $this->assertStringContainsString('favicon.png', $html);
        // Pastikan password rahasia tidak pernah dikirimkan melalui URL atau email
        $this->assertStringNotContainsString('password=', strtolower($mail->actionUrl));
        $this->assertStringNotContainsString('plain_password', strtolower($html));
    }

    public function test_reset_password_request_for_unregistered_email_does_not_leak_user_presence(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent_'.uniqid().'@siswamart.test',
        ]);

        // Respons harus tetap aman dan bertanda sukses generik (mencegah enumerasi akun)
        $response->assertSessionHas('status', __('passwords.sent'));
        $response->assertSessionDoesntHaveErrors();

        Notification::assertNothingSent();
    }

    public function test_reset_password_request_for_inactive_account_does_not_send_notification(): void
    {
        Notification::fake();

        $inactiveUser = User::factory()->create([
            'role' => 'penjual',
            'is_active' => false,
            'email' => 'inactive_'.uniqid().'@siswamart.test',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $inactiveUser->email,
        ]);

        // Respons sukses generik ditampilkan tanpa membocorkan status akun dan tanpa mengirim email
        $response->assertSessionHas('status', __('passwords.sent'));
        Notification::assertNothingSent();
    }

    public function test_reset_password_request_is_throttled_on_rapid_repeated_requests(): void
    {
        $user = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'email' => 'throttle_'.uniqid().'@siswamart.test',
        ]);

        // Permintaan pertama berhasil
        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', __('passwords.sent'));

        // Permintaan kedua secara instan harus terkena throttling dari broker bawaan Laravel
        $response = $this->post('/forgot-password', ['email' => $user->email]);
        $response->assertSessionHasErrors('email');
    }

    public function test_reset_password_screen_can_be_rendered_with_token(): void
    {
        $response = $this->get('/reset-password/sample-token-123?email=test@siswamart.test');

        $response->assertStatus(200);
        $response->assertSee('Reset Password');
        $response->assertSee('sample-token-123');
        $response->assertSee('test@siswamart.test');
    }

    public function test_password_can_be_reset_with_valid_token_and_user_can_login_with_new_password(): void
    {
        $user = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'password' => Hash::make('OldPassword123!'),
            'email' => 'valid_reset_'.uniqid().'@siswamart.test',
            'must_change_password' => true,
        ]);

        Penjual::create([
            'user_id' => $user->id,
            'nama_toko' => 'Toko Uji Reset',
            'nomor_whatsapp' => '081234567891',
            'lokasi_kelas' => '12 TKJ 2',
            'nisn' => (string) random_int(1000000000, 9999999999),
            'alamat' => 'Bandung',
            'status' => 'disetujui',
        ]);

        // Generate token resmi melalui Password Broker
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status', __('passwords.reset'));

        // Verifikasi password ter-hash di database dan must_change_password ter-reset
        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->password));
        $this->assertFalse(Hash::check('OldPassword123!', $user->password));
        $this->assertFalse((bool) $user->must_change_password);

        // Verifikasi login dengan password baru berhasil
        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'NewSecurePassword123!',
        ]);

        $loginResponse->assertRedirect(route('penjual.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_password_can_be_reset_and_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('AdminOldPass123!'),
            'email' => 'admin_reset_'.uniqid().'@siswamart.test',
        ]);

        $token = Password::createToken($admin);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'AdminNewPass123!',
            'password_confirmation' => 'AdminNewPass123!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status', __('passwords.reset'));

        // Admin login dengan password baru
        $loginResponse = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'AdminNewPass123!',
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'password' => Hash::make('InitialPassword123!'),
            'email' => 'invalid_token_'.uniqid().'@siswamart.test',
        ]);

        Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => 'completely-wrong-token-abc',
            'email' => $user->email,
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        $response->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertTrue(Hash::check('InitialPassword123!', $user->password));
        $this->assertFalse(Hash::check('BrandNewPassword123!', $user->password));
    }

    public function test_password_cannot_be_reset_with_expired_token(): void
    {
        $user = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'password' => Hash::make('InitialPassword123!'),
            'email' => 'expired_token_'.uniqid().'@siswamart.test',
        ]);

        $token = Password::createToken($user);

        // Ubah created_at di tabel password_reset_tokens menjadi 61 menit yang lalu
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => Carbon::now()->subMinutes(61)]);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);

        $response->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertTrue(Hash::check('InitialPassword123!', $user->password));
    }

    public function test_token_cannot_be_reused_after_successful_password_reset(): void
    {
        $user = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'password' => Hash::make('InitialPassword123!'),
            'email' => 'single_use_'.uniqid().'@siswamart.test',
        ]);

        $token = Password::createToken($user);

        // Reset pertama: Sukses
        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'FirstResetPassword123!',
            'password_confirmation' => 'FirstResetPassword123!',
        ])->assertRedirect('/login');

        // Reset kedua dengan token yang sama: Gagal karena token sudah dihapus
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'SecondResetPassword123!',
            'password_confirmation' => 'SecondResetPassword123!',
        ]);

        $response->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertTrue(Hash::check('FirstResetPassword123!', $user->password));
        $this->assertFalse(Hash::check('SecondResetPassword123!', $user->password));
    }

    public function test_token_for_one_user_cannot_be_used_to_reset_another_user_account(): void
    {
        $seller = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'password' => Hash::make('SellerPass123!'),
            'email' => 'seller_legit_'.uniqid().'@siswamart.test',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('AdminPass123!'),
            'email' => 'admin_target_'.uniqid().'@siswamart.test',
        ]);

        // Token digenerate untuk seller
        $sellerToken = Password::createToken($seller);

        // Mencoba menggunakan token seller untuk mereset password admin
        $response = $this->post('/reset-password', [
            'token' => $sellerToken,
            'email' => $admin->email,
            'password' => 'HackedPassword123!',
            'password_confirmation' => 'HackedPassword123!',
        ]);

        $response->assertSessionHasErrors('email');

        // Pastikan password admin tidak berubah
        $admin->refresh();
        $this->assertTrue(Hash::check('AdminPass123!', $admin->password));
        $this->assertFalse(Hash::check('HackedPassword123!', $admin->password));
    }

    public function test_password_reset_requires_valid_confirmation_and_minimum_length(): void
    {
        $user = User::factory()->create([
            'role' => 'penjual',
            'is_active' => true,
            'email' => 'validation_test_'.uniqid().'@siswamart.test',
        ]);

        $token = Password::createToken($user);

        // Password kurang dari 8 karakter
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $response->assertSessionHasErrors('password');

        // Password konfirmasi tidak cocok
        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'MismatchPassword123!',
        ]);
        $response->assertSessionHasErrors('password');
    }
}
