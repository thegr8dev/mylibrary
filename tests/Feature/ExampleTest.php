<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    use RefreshDatabase;

    /** @test */
    // public function test_login_redirects_to_dashboard()
    // {
    //     // Create a test user
    //     $user = User::factory()->create([
    //         'email' => 'testuser@example.com',
    //         'password' => bcrypt($password = 'password123'),
    //     ]);

    //     // Hit the root URL
    //     $response = $this->get('/');

    //     // Assert it redirects to admin login
    //     $response->assertRedirect('/admin/login');

    //     // Follow the redirect and login
    //     $response = $this->followingRedirects()->post('/admin/login', [
    //         'email' => $user->email,
    //         'password' => $password,
    //     ]);

    //     // Assert the user is redirected to the admin dashboard
    //     $response->assertRedirect('/admin/');
    // }

    /** @test */
    public function it_redirects_to_admin_login_when_not_authenticated()
    {
        // Make a request to the root URL without logging in
        $response = $this->get('/');

        // Assert that the response redirects to /admin/login
        $response->assertRedirect('/admin/login');
    }
}
