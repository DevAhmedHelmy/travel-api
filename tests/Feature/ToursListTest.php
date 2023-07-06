<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToursListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tours_list_by_travel_slug_return_correct_tours()
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);

        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $tour->id]);
    }

    public function test_tour_price_is_shown_correctly()
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 123.45]);
        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['price' => '123.45']);
    }

    public function test_tours_list_return_paginated_data_correctly(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory(20)->create(['travel_id' => $travel->id]);

        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_tours_list_sorts_by_starting_date(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $later_tour = Tour::factory()->create(['travel_id' => $travel->id, 'starting_date' => now()->addDays(2), 'ending_date' => now()->addDays(3)]);
        $earlier_tour = Tour::factory()->create(['travel_id' => $travel->id, 'starting_date' => now(), 'ending_date' => now()->addDays(1)]);

        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $earlier_tour->id)
            ->assertJsonPath('data.1.id', $later_tour->id);
    }

    public function test_tours_list_sorts_by_price(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 123.45]);
        $later_tour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 456.78]);

        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $tour->id)
            ->assertJsonPath('data.1.id', $later_tour->id);
    }

    public function test_tours_list_filters_by_price(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $tour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 123.45]);
        $later_tour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 456.78]);

        $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';
        $response = $this->getJson($endpoint.'?price_from=100');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $tour->id)
            ->assertJsonPath('data.1.id', $later_tour->id);

        $response = $this->getJson($endpoint.'?price_from=150');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $later_tour->id);

        $response = $this->getJson($endpoint.'?price_from=500');
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $response = $this->getJson($endpoint.'?price_to=50');
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');

        $response = $this->getJson($endpoint.'?price_from=100&price_to=500');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $tour->id)
            ->assertJsonPath('data.1.id', $later_tour->id);
    }

    public function test_tours_list_filters_by_starting_date(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);
        $later_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);
        $earlier_tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);

        $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';
        $response = $this->getJson($endpoint.'?date_from='.now());
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $earlier_tour->id)
            ->assertJsonPath('data.1.id', $later_tour->id);

        $response = $this->getJson($endpoint.'?date_from='.now()->addDay());
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonMissing(['id' => $earlier_tour->id])
            ->assertJsonFragment(['id' => $later_tour->id]);

        $response = $this->getJson($endpoint.'?date_from='.now()->addDays(5));
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonMissing(['id' => $earlier_tour->id])
            ->assertJsonMissing(['id' => $later_tour->id]);

        $response = $this->getJson($endpoint.'?date_to='.now());
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $earlier_tour->id);

        $response = $this->getJson($endpoint.'?date_to='.now()->subDay());
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonMissing(['id' => $earlier_tour->id])
            ->assertJsonMissing(['id' => $later_tour->id]);

        $response = $this->getJson($endpoint.'?date_from='.now()->addDay().'&date_to='.now()->addDays(5));
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $later_tour->id)
            ->assertJsonMissing(['id' => $earlier_tour->id]);

    }

    public function test_tour_list_returns_validations_errors(): void
    {
        $travel = Travel::factory()->create(['is_public' => true]);

        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours?date_form=abc');

        $response->assertStatus(422);

        $response = $this->getJson('/api/v1/travels/'.$travel->slug.'/tours?price_from=abc');
        $response->assertStatus(422);
    }
}
