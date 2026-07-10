<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerFeedbackFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_feedback_and_view_it(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'feedback-restaurant',
            'email' => 'feedback@example.com',
            'phone' => '1234567890',
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '22000000001',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->withSession(['restaurant_id' => $restaurant->id])
            ->post(route('customer.feedback.store'), [
                'type' => 'suggestion',
                'title' => 'Need more vegan options',
                'message' => 'Please add more vegan dishes',
                'rating' => 5,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feedback', [
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'title' => 'Need more vegan options',
        ]);
    }
}
