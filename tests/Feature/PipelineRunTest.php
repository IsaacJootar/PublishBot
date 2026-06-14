<?php

namespace Tests\Feature;

use App\Models\PipelineRun;
use App\Models\Series;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\VoiceProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PipelineRunTest extends TestCase
{
    use RefreshDatabase;

    private function userWithKey(): User
    {
        $user = User::factory()->create();
        UserSetting::factory()->create([
            'user_id' => $user->id,
            'api_key_encrypted' => 'test-key',
        ]);

        return $user;
    }

    private function userWithoutKey(): User
    {
        return User::factory()->create();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_dashboard(): void
    {
        $this->actingAs($this->userWithKey())->get(route('dashboard'))->assertOk()->assertSee('Run pipeline');
    }

    public function test_user_without_api_key_is_redirected_to_settings(): void
    {
        Queue::fake();
        $this->actingAs($this->userWithoutKey())
            ->post(route('runs.create'), ['topic' => 'Parenting toddlers'])
            ->assertRedirect(route('settings.index'));

        Queue::assertNothingPushed();
    }

    public function test_user_with_api_key_can_start_run(): void
    {
        Queue::fake();
        $user = $this->userWithKey();

        $this->actingAs($user)->post(route('runs.create'), ['topic' => 'Parenting toddlers'])->assertRedirect();

        $this->assertDatabaseHas('pipeline_runs', ['user_id' => $user->id, 'topic' => 'Parenting toddlers']);
    }

    public function test_run_topic_is_required(): void
    {
        $this->actingAs($this->userWithKey())
            ->post(route('runs.create'), ['topic' => ''])
            ->assertSessionHasErrors('topic');
    }

    public function test_duplicate_topics_get_unique_slugs(): void
    {
        Queue::fake();
        $user = $this->userWithKey();

        $this->actingAs($user)->post(route('runs.create'), ['topic' => 'Same Topic']);
        $this->actingAs($user)->post(route('runs.create'), ['topic' => 'Same Topic']);

        $slugs = PipelineRun::where('user_id', $user->id)->pluck('slug');
        $this->assertEquals(2, $slugs->count());
        $this->assertNotEquals($slugs[0], $slugs[1]);
    }

    public function test_voice_profile_is_attached_to_run(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $voice = VoiceProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('runs.create'), ['topic' => 'Finance', 'voice_profile_id' => $voice->id]);

        $this->assertDatabaseHas('pipeline_runs', ['user_id' => $user->id, 'voice_profile_id' => $voice->id]);
    }

    public function test_series_is_attached_to_run(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $series = Series::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('runs.create'), ['topic' => 'Book Two', 'series_id' => $series->id]);

        $this->assertDatabaseHas('pipeline_runs', ['user_id' => $user->id, 'series_id' => $series->id]);
    }

    public function test_other_users_voice_profile_is_rejected(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $otherVoice = VoiceProfile::factory()->create(['user_id' => $this->userWithoutKey()->id]);

        $this->actingAs($user)->post(route('runs.create'), ['topic' => 'Finance', 'voice_profile_id' => $otherVoice->id]);

        $this->assertDatabaseHas('pipeline_runs', ['user_id' => $user->id, 'voice_profile_id' => null]);
    }

    public function test_history_page_loads(): void
    {
        $this->actingAs($this->userWithKey())->get(route('runs.index'))->assertOk();
    }

    public function test_user_only_sees_own_runs(): void
    {
        $user = $this->userWithKey();
        $other = $this->userWithoutKey();

        PipelineRun::factory()->create(['user_id' => $user->id, 'topic' => 'My Topic']);
        PipelineRun::factory()->create(['user_id' => $other->id, 'topic' => 'Their Topic']);

        $this->actingAs($user)->get(route('runs.index'))
            ->assertSee('My Topic')
            ->assertDontSee('Their Topic');
    }

    public function test_run_detail_page_loads(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('runs.show', $run))->assertOk();
    }

    public function test_other_user_cannot_view_run(): void
    {
        $owner = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($this->userWithoutKey())->get(route('runs.show', $run))->assertForbidden();
    }

    public function test_user_can_delete_run(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('runs.destroy', $run))->assertRedirect(route('runs.index'));

        $this->assertDatabaseMissing('pipeline_runs', ['id' => $run->id]);
    }

    public function test_export_returns_404_for_invalid_format(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('runs.export', [$run, 'bad_format']))->assertNotFound();
    }
}
