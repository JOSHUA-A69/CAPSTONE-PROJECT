<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CreateRoleTestUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CreateRoleTestUsersSeeder::class);
    }

    public function test_admin_redirects_to_admin_dashboard(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_redirects_to_staff_dashboard(): void
    {
        $staff = User::where('role', 'staff')->first();
        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertRedirect(route('staff.dashboard'));
    }

    public function test_adviser_redirects_to_adviser_dashboard(): void
    {
        $adviser = User::where('role', 'adviser')->first();
        $this->actingAs($adviser)
            ->get('/dashboard')
            ->assertRedirect(route('adviser.dashboard'));
    }

    public function test_priest_redirects_to_priest_dashboard(): void
    {
        $priest = User::where('role', 'priest')->first();
        $this->actingAs($priest)
            ->get('/dashboard')
            ->assertRedirect(route('priest.dashboard'));
    }

    public function test_requestor_redirects_to_requestor_dashboard(): void
    {
        $requestor = User::where('role', 'requestor')->first();
        $this->actingAs($requestor)
            ->get('/dashboard')
            ->assertRedirect(route('requestor.dashboard'));
    }
}
