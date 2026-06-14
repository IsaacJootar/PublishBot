<?php

namespace Tests\Feature;

use App\Models\PipelineRun;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeriesTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_guest_cannot_access_series(): void
    {
        $this->get(route('series.index'))->assertRedirect(route('login'));
    }

    public function test_user_sees_series_index(): void
    {
        $this->actingAs($this->user())->get(route('series.index'))->assertOk()->assertSee('My Series');
    }

    public function test_series_index_shows_only_own_series(): void
    {
        $user = $this->user();
        Series::factory()->create(['user_id' => $user->id, 'name' => 'My Series Alpha']);
        Series::factory()->create(['user_id' => $this->user()->id, 'name' => 'Other Series']);
        $this->actingAs($user)->get(route('series.index'))->assertSee('My Series Alpha')->assertDontSee('Other Series');
    }

    public function test_user_can_create_series(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('series.store'), ['name' => 'Finance Series', 'writing_tone' => 'Direct'])->assertRedirect();
        $this->assertDatabaseHas('series', ['user_id' => $user->id, 'name' => 'Finance Series']);
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs($this->user())->post(route('series.store'), ['name' => ''])->assertSessionHasErrors('name');
    }

    public function test_characters_are_saved(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('series.store'), [
            'name' => 'Kids Series',
            'characters' => [['name' => 'Kofi', 'age_appearance' => '7', 'personality' => 'Curious', 'key_detail' => 'Red backpack']],
        ]);
        $series = Series::where('user_id', $user->id)->first();
        $this->assertCount(1, $series->characters);
        $this->assertEquals('Kofi', $series->characters[0]['name']);
    }

    public function test_empty_character_rows_are_dropped(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('series.store'), [
            'name' => 'Test',
            'characters' => [
                ['name' => 'Ada', 'age_appearance' => '', 'personality' => '', 'key_detail' => ''],
                ['name' => '', 'age_appearance' => '', 'personality' => '', 'key_detail' => ''],
            ],
        ]);
        $series = Series::where('user_id', $user->id)->first();
        $this->assertCount(1, $series->characters);
    }

    public function test_user_can_view_own_series(): void
    {
        $user = $this->user();
        $series = Series::factory()->create(['user_id' => $user->id, 'name' => 'Health Series']);
        $this->actingAs($user)->get(route('series.show', $series))->assertOk()->assertSee('Health Series');
    }

    public function test_other_user_cannot_view_series(): void
    {
        $series = Series::factory()->create(['user_id' => $this->user()->id]);
        $this->actingAs($this->user())->get(route('series.show', $series))->assertForbidden();
    }

    public function test_user_can_update_series(): void
    {
        $user = $this->user();
        $series = Series::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->patch(route('series.update', $series), ['name' => 'New Name', 'writing_tone' => 'Warm'])
            ->assertRedirect(route('series.show', $series));
        $this->assertDatabaseHas('series', ['id' => $series->id, 'name' => 'New Name']);
    }

    public function test_plan_next_page_loads(): void
    {
        $user = $this->user();
        $series = Series::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->get(route('series.plan', $series))->assertOk()->assertSee('Plan the next book');
    }

    public function test_user_can_attach_run_to_series(): void
    {
        $user = $this->user();
        $series = Series::factory()->create(['user_id' => $user->id]);
        $run = PipelineRun::factory()->create(['user_id' => $user->id, 'series_id' => null]);
        $this->actingAs($user)->post(route('series.attach-run', [$series, $run]))->assertRedirect();
        $this->assertDatabaseHas('pipeline_runs', ['id' => $run->id, 'series_id' => $series->id]);
    }

    public function test_user_can_detach_run_from_series(): void
    {
        $user = $this->user();
        $series = Series::factory()->create(['user_id' => $user->id]);
        $run = PipelineRun::factory()->create(['user_id' => $user->id, 'series_id' => $series->id]);
        $this->actingAs($user)->delete(route('series.detach-run', [$series, $run]))->assertRedirect();
        $this->assertDatabaseHas('pipeline_runs', ['id' => $run->id, 'series_id' => null]);
    }

    public function test_user_can_delete_series(): void
    {
        $user = $this->user();
        $series = Series::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->delete(route('series.destroy', $series))->assertRedirect(route('series.index'));
        $this->assertDatabaseMissing('series', ['id' => $series->id]);
    }

    public function test_deleting_series_nullifies_run_series_id(): void
    {
        $user = $this->user();
        $series = Series::factory()->create(['user_id' => $user->id]);
        $run = PipelineRun::factory()->create(['user_id' => $user->id, 'series_id' => $series->id]);
        $this->actingAs($user)->delete(route('series.destroy', $series));
        $this->assertDatabaseHas('pipeline_runs', ['id' => $run->id, 'series_id' => null]);
    }

    public function test_prompt_context_includes_character_and_series_name(): void
    {
        $series = new Series([
            'name' => 'Test Series',
            'characters' => [['name' => 'Kofi', 'age_appearance' => '7', 'personality' => 'Curious', 'key_detail' => 'Red backpack']],
            'writing_tone' => 'Warm',
        ]);
        $context = $series->promptContext();
        $this->assertStringContainsString('Kofi', $context);
        $this->assertStringContainsString('Test Series', $context);
        $this->assertStringContainsString('Warm', $context);
    }
}
