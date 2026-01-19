<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;

class KioskQueueGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_twice_in_a_row_increments_sequence()
    {
        // Create organization and setting
        $org = Organization::factory()->create(['organization_code' => 'TST']);

        OrganizationSetting::create([
            'organization_id' => $org->id,
            'code' => $org->organization_code,
            'queue_number_digits' => 4,
            'last_queue_sequence' => 0,
        ]);

        // Create an online counter user
        $counter = User::factory()->create([
            'organization_id' => $org->id,
            'role' => 'counter',
            'is_online' => true,
        ]);

        $url = route('kiosk.generate', ['organization_code' => $org->organization_code]);

        $resp1 = $this->get($url . '?counter_id=' . $counter->id);
        $resp1->assertStatus(200);
        $data1 = $resp1->json('queue');
        $this->assertNotNull($data1);
        $this->assertEquals('0001', $data1['queue_number']);

        $resp2 = $this->get($url . '?counter_id=' . $counter->id);
        $resp2->assertStatus(200);
        $data2 = $resp2->json('queue');
        $this->assertNotNull($data2);
        $this->assertEquals('0002', $data2['queue_number']);
    }
}
