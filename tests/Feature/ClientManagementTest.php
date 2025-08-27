
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Client;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_the_client_index_page()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.clients.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.clients.index');
    }

    /** @test */
    public function admin_can_create_a_new_client()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $clientData = [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'plan_type' => 'free',
            'status' => 'active',
        ];

        $response = $this->post(route('admin.clients.store'), $clientData);

        $response->assertRedirect(route('admin.clients.index'));
        $this->assertDatabaseHas('clients', ['email' => 'test@example.com']);
    }

    /** @test */
    public function admin_can_view_a_client()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $client = Client::factory()->create();

        $response = $this->get(route('admin.clients.show', $client));

        $response->assertStatus(200);
        $response->assertViewIs('admin.clients.show');
        $response->assertSee($client->name);
    }

    /** @test */
    public function admin_can_update_a_client()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $client = Client::factory()->create();

        $updatedData = [
            'name' => 'Updated Client Name',
            'email' => 'updated@example.com',
            'plan_type' => 'premium',
            'status' => 'inactive',
        ];

        $response = $this->put(route('admin.clients.update', $client), $updatedData);

        $response->assertRedirect(route('admin.clients.show', $client));
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Updated Client Name']);
    }

    /** @test */
    public function admin_can_delete_a_client()
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $client = Client::factory()->create();

        $response = $this->delete(route('admin.clients.destroy', $client));

        $response->assertRedirect(route('admin.clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
