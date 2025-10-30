<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SQL injection attempts are prevented
     */
    public function test_sql_injection_prevention(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => "'; DROP TABLE users; --",
            'email' => 'sqlinjection@test.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        // Bảng users vẫn tồn tại
        $this->assertDatabaseHas('users', [
            'email' => 'sqlinjection@test.com',
        ]);

        // Input đã bị sanitize
        $user = User::where('email', 'sqlinjection@test.com')->first();
        $this->assertStringNotContainsString('DROP TABLE', $user->name);
    }

    /**
     * Test XSS attack patterns are neutralized
     */
    public function test_xss_attack_patterns_neutralized(): void
    {
        $xssPatterns = [
            '<img src=x onerror=alert("XSS")>',
            '<svg onload=alert("XSS")>',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(\'XSS\')">',
        ];

        foreach ($xssPatterns as $index => $pattern) {
            $email = "xss{$index}@test.com";

            $response = $this->postJson('/api/register', [
                'name' => $pattern,
                'email' => $email,
                'password' => 'SecurePass123!@#',
                'password_confirmation' => 'SecurePass123!@#',
            ]);

            $user = User::where('email', $email)->first();

            // Các thẻ HTML và script đã bị loại bỏ
            $this->assertStringNotContainsString('<img', $user->name);
            $this->assertStringNotContainsString('<svg', $user->name);
            $this->assertStringNotContainsString('<iframe', $user->name);
            $this->assertStringNotContainsString('onerror', $user->name);
            $this->assertStringNotContainsString('onload', $user->name);
        }
    }

    /**
     * Test multiple special characters are properly escaped
     */
    public function test_multiple_special_characters_escaped(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test & "Company" <script> \'alert\'',
            'email' => 'special@test.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $user = User::where('email', 'special@test.com')->first();

        // Tất cả special characters đã được escape
        $this->assertStringContainsString('&amp;', $user->name);
        $this->assertStringContainsString('&quot;', $user->name);
        $this->assertStringNotContainsString('<script>', $user->name);
    }

    /**
     * Test whitespace trimming
     */
    public function test_whitespace_trimming(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '   Whitespace Test   ',
            'email' => 'whitespace@test.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $user = User::where('email', 'whitespace@test.com')->first();

        // Whitespace đầu và cuối đã bị trim
        $this->assertEquals('Whitespace Test', $user->name);
        $this->assertStringNotContainsString('   ', $user->name);
    }

    /**
     * Test HSTS header on HTTPS connections
     */
    public function test_hsts_header_on_https(): void
    {
        // Giả lập HTTPS request
        $response = $this->get('/', ['HTTPS' => 'on']);

        // HSTS header chỉ có khi dùng HTTPS
        if ($response->isSecure()) {
            $this->assertTrue($response->headers->has('Strict-Transport-Security'));
            $hsts = $response->headers->get('Strict-Transport-Security');
            $this->assertStringContainsString('max-age=', $hsts);
        }
    }

    /**
     * Test password with all special characters works
     */
    public function test_complex_password_with_special_chars(): void
    {
        $complexPassword = 'P@ssw0rd!#$%^&*()_+-=[]{}|;:,.<>?';

        $response = $this->postJson('/api/register', [
            'name' => 'Complex Password User',
            'email' => 'complex@test.com',
            'password' => $complexPassword,
            'password_confirmation' => $complexPassword,
        ]);

        $user = User::where('email', 'complex@test.com')->first();

        // Password phức tạp vẫn hoạt động
        $this->assertNotNull($user);
        $this->assertTrue(\Hash::check($complexPassword, $user->password));
    }

    /**
     * Test sanitization does not break unicode characters
     */
    public function test_unicode_characters_preserved(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nguyễn Văn A 中文 日本語 العربية',
            'email' => 'unicode@test.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $user = User::where('email', 'unicode@test.com')->first();

        // Unicode characters vẫn được giữ nguyên
        $this->assertStringContainsString('Nguyễn', $user->name);
        $this->assertStringContainsString('中文', $user->name);
    }

    /**
     * Test frame-ancestors CSP directive prevents clickjacking
     */
    public function test_csp_prevents_clickjacking(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');

        // frame-ancestors 'none' ngăn chặn clickjacking
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);

        // X-Frame-Options cũng được set
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
    }

    /**
     * Test permissions policy restricts dangerous features
     */
    public function test_permissions_policy_restrictions(): void
    {
        $response = $this->get('/');

        $permissionsPolicy = $response->headers->get('Permissions-Policy');

        // Các features nguy hiểm đã bị restrict
        $dangerousFeatures = [
            'geolocation',
            'microphone',
            'camera',
            'payment',
            'usb',
        ];

        foreach ($dangerousFeatures as $feature) {
            $this->assertStringContainsString("{$feature}=()", $permissionsPolicy);
        }
    }

    /**
     * Test nested HTML tags are removed
     */
    public function test_nested_html_tags_removed(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '<div><span><a href="#">Nested</a></span></div>',
            'email' => 'nested@test.com',
            'password' => 'SecurePass123!@#',
            'password_confirmation' => 'SecurePass123!@#',
        ]);

        $user = User::where('email', 'nested@test.com')->first();

        // Tất cả HTML tags đã bị loại bỏ
        $this->assertStringNotContainsString('<div>', $user->name);
        $this->assertStringNotContainsString('<span>', $user->name);
        $this->assertStringNotContainsString('<a', $user->name);
        $this->assertStringContainsString('Nested', $user->name);
    }
}
