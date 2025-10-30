<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sanitize middleware removes HTML tags
     */
    public function test_sanitize_middleware_removes_html_tags(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '<script>alert("XSS")</script>Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        // Kiểm tra user được tạo
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // HTML tags đã bị loại bỏ
        $this->assertStringNotContainsString('<script>', $user->name);
        $this->assertStringNotContainsString('</script>', $user->name);
        $this->assertStringContainsString('Test User', $user->name);
    }

    /**
     * Test sanitize middleware escapes special characters
     */
    public function test_sanitize_middleware_escapes_special_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'User & Company "Test"',
            'email' => 'test2@example.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $user = User::where('email', 'test2@example.com')->first();

        // Special characters đã được escape
        $this->assertStringContainsString('&amp;', $user->name);
        $this->assertStringContainsString('&quot;', $user->name);
    }

    /**
     * Test sanitize middleware does not affect passwords
     */
    public function test_sanitize_middleware_preserves_passwords(): void
    {
        // Password có ký tự đặc biệt phức tạp
        $password = 'SecureTest@123!#$';

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test3@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        // Kiểm tra response thành công
        $response->assertStatus(201);

        // Password không bị sanitize và vẫn check được
        $user = User::where('email', 'test3@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(\Hash::check($password, $user->password));
    }

    /**
     * Test security headers are present in response
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        // Kiểm tra các security headers
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Kiểm tra có CSP và Permissions-Policy
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    /**
     * Test Content Security Policy header format
     */
    public function test_content_security_policy_header(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        // Kiểm tra CSP chứa các directives quan trọng
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('script-src', $csp);
        $this->assertStringContainsString('style-src', $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString('img-src', $csp);
    }

    /**
     * Test permissions policy header
     */
    public function test_permissions_policy_header(): void
    {
        $response = $this->get('/');

        $permissionsPolicy = $response->headers->get('Permissions-Policy');

        // Kiểm tra Permissions-Policy chứa các features bị restrict
        $this->assertStringContainsString('geolocation=()', $permissionsPolicy);
        $this->assertStringContainsString('microphone=()', $permissionsPolicy);
        $this->assertStringContainsString('camera=()', $permissionsPolicy);
    }
}
