<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AdminSession;
use App\Models\SecurityPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // สร้าง Super Admin สำหรับทดสอบ
        $this->superAdmin = User::factory()->create([
            'name' => 'Test Super Admin',
            'email' => 'super@test.com',
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => now()
        ]);
    }

    /** @test */
    public function super_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->superAdmin)
                        ->get(route('super-admin.dashboard'));

        $response->assertStatus(200)
                 ->assertViewIs('super-admin.dashboard')
                 ->assertViewHas(['systemStats', 'todayStats', 'securityStats']);
    }

    /** @test */
    public function dashboard_displays_correct_statistics()
    {
        // สร้างข้อมูลทดสอบ
        $admin1 = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin2 = User::factory()->create(['role' => 'user', 'status' => 'suspended']);
        
        AdminSession::create([
            'user_id' => $admin1->id,
            'session_id' => 'test_session_1',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'login_at' => now(),
            'last_activity' => now(),
            'status' => 'active',
            'login_method' => 'password'
        ]);

        SecurityPolicy::create([
            'policy_name' => 'Test Policy',
            'policy_type' => 'test',
            'policy_rules' => ['test' => true],
            'applies_to' => 'admin',
            'is_active' => true,
            'created_by' => $this->superAdmin->id
        ]);

        $response = $this->actingAs($this->superAdmin)
                        ->get(route('super-admin.dashboard'));

        $response->assertStatus(200);
        
        // ตรวจสอบว่ามีข้อมูลสถิติครบถ้วน
        $response->assertViewHas('systemStats')
                 ->assertViewHas('todayStats')
                 ->assertViewHas('securityStats');
        
        // ตรวจสอบค่าสถิติ
        $systemStats = $response->viewData('systemStats');
        $todayStats = $response->viewData('todayStats');
        $securityStats = $response->viewData('securityStats');
        
        $this->assertEquals(3, $systemStats['total_users']); // super_admin + admin + user
        $this->assertEquals(1, $systemStats['total_admins']);
        $this->assertEquals(1, $systemStats['total_super_admins']);
        $this->assertEquals(1, $todayStats['active_sessions']);
        $this->assertEquals(1, $securityStats['active_security_policies']);
    }

    /** @test */
    public function super_admin_can_access_user_management()
    {
        $response = $this->actingAs($this->superAdmin)
                        ->get(route('super-admin.users.index'));

        $response->assertStatus(200)
                 ->assertViewIs('admin.super-admin.users.index');
    }

    /** @test */
    public function super_admin_can_create_new_user()
    {
        $userData = [
            'name' => 'Test New User',
            'email' => 'newuser@test.com',
            'role' => 'admin',
            'status' => 'active',
            'send_email' => false
        ];

        $response = $this->actingAs($this->superAdmin)
                        ->post(route('super-admin.users.store'), $userData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com',
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function super_admin_can_view_user_details()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->superAdmin)
                        ->get(route('super-admin.users.show', $user));

        $response->assertStatus(200)
                 ->assertViewIs('admin.super-admin.users.show')
                 ->assertViewHas('user');
    }

    /** @test */
    public function super_admin_can_toggle_user_status()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($this->superAdmin)
                        ->post(route('super-admin.users.toggle-status', $user), [
                            'status' => 'suspended',
                            'reason' => 'Testing'
                        ]);

        $response->assertJson(['success' => true]);
        
        $user->refresh();
        $this->assertEquals('suspended', $user->status);
    }

    /** @test */
    public function super_admin_can_view_sessions()
    {
        // สร้าง session ทดสอบ
        AdminSession::create([
            'user_id' => $this->superAdmin->id,
            'session_id' => 'test_session_super',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'login_at' => now(),
            'last_activity' => now(),
            'status' => 'active',
            'login_method' => 'password'
        ]);

        $response = $this->actingAs($this->superAdmin)
                        ->get(route('super-admin.users.sessions'));

        $response->assertStatus(200)
                 ->assertViewIs('admin.super-admin.users.sessions');
    }

    /** @test */
    public function super_admin_can_terminate_user_sessions()
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        AdminSession::create([
            'user_id' => $user->id,
            'session_id' => 'session_to_terminate',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Test Browser',
            'login_at' => now()->subHour(),
            'last_activity' => now()->subMinutes(10),
            'status' => 'active',
            'login_method' => 'password'
        ]);

        $response = $this->actingAs($this->superAdmin)
                        ->post(route('super-admin.users.terminate-sessions', $user));

        $response->assertJson(['success' => true]);
        
        // ตรวจสอบว่า session ถูก terminate
        $this->assertDatabaseHas('admin_sessions', [
            'user_id' => $user->id,
            'session_id' => 'session_to_terminate',
            'status' => 'terminated'
        ]);
    }

    /** @test */
    public function regular_admin_cannot_access_super_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
                        ->get(route('super-admin.dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_super_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
                        ->get(route('super-admin.dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_dashboard_handles_empty_data_gracefully()
    {
        // ทดสอบกรณีที่ไม่มีข้อมูล
        $response = $this->actingAs($this->superAdmin)
                        ->get(route('super-admin.dashboard'));

        $response->assertStatus(200);
        
        $systemStats = $response->viewData('systemStats');
        $todayStats = $response->viewData('todayStats'); 
        $securityStats = $response->viewData('securityStats');
        
        $this->assertEquals(1, $systemStats['total_users']); // เฉพาะ super admin
        $this->assertEquals(0, $todayStats['active_sessions']);
        $this->assertEquals(0, $securityStats['active_security_policies']);
    }

    /** @test */
    public function dashboard_chart_data_is_properly_formatted()
    {
        $response = $this->actingAs($this->superAdmin)
                        ->get(route('super-admin.dashboard'));

        $response->assertStatus(200);
        
        $usageData = $response->viewData('usageData');
        $registrationData = $response->viewData('registrationData');
        
        // ตรวจสอบ format ของข้อมูล chart
        $this->assertIsArray($usageData);
        $this->assertIsArray($registrationData);
        $this->assertEquals(30, count($usageData)); // 30 วันข้อมูล
    }
}
