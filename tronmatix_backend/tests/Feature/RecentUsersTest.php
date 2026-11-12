<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RecentUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_with_recent_filter_only_shows_recent_logins(): void
    {
        // A user who logged in
        $recentUser = User::create([
            'name'          => 'Recent Login',
            'username'      => 'recent_login',
            'email'         => 'recent@example.com',
            'password'      => bcrypt('password'),
            'role'          => 'customer',
            'last_login_at' => now(),
        ]);

        // A user who has NOT logged in
        $otherUser = User::create([
            'name'      => 'No Login',
            'username'  => 'no_login',
            'email'     => 'no_login@example.com',
            'password'  => bcrypt('password'),
            'role'      => 'customer',
        ]);

        $admin = Admin::create([
            'name'      => 'Admin',
            'username'  => 'admin',
            'email'     => 'admin@example.com',
            'password'  => bcrypt('password'),
            'role'      => 'superadmin',
            'is_active' => true,
        ]);

        Auth::guard('admin')->login($admin);

        $response = $this->get(route('dashboard.users', ['recent' => 1]));

        $response->assertOk();
        $response->assertSee('Recent Login');
        $response->assertDontSee('No Login');
    }
}