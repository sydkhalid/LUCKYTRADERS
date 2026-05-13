<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_first_admin_can_register_and_is_logged_in(): void
    {
        $this->post('/register', [
            'name' => 'Admin',
            'email' => 'admin@luckytraders.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'admin@luckytraders.test',
        ]);
    }

    public function test_registration_is_closed_after_first_admin_exists(): void
    {
        User::factory()->create();

        $this->get('/register')
            ->assertRedirect('/login')
            ->assertSessionHas('status');
    }

    public function test_existing_admin_can_login_and_logout(): void
    {
        User::factory()->create([
            'email' => 'admin@luckytraders.test',
            'password' => 'password123',
        ]);

        $this->post('/login', [
            'email' => 'admin@luckytraders.test',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();

        $this->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
