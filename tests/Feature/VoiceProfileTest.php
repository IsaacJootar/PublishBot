<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VoiceProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoiceProfileTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_guest_cannot_access_voice_index(): void
    {
        $this->get(route('voice.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_voice_index(): void
    {
        $this->actingAs($this->user())->get(route('voice.index'))->assertOk();
    }

    public function test_voice_index_shows_existing_profiles(): void
    {
        $user = $this->user();
        VoiceProfile::factory()->create(['user_id' => $user->id, 'name' => 'My Niche']);
        $this->actingAs($user)->get(route('voice.index'))->assertSee('My Niche');
    }

    public function test_voice_index_shows_suggested_domains_when_empty(): void
    {
        $this->actingAs($this->user())->get(route('voice.index'))->assertSee('General Writing');
    }

    public function test_user_can_create_profile(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('voice.store'), ['name' => 'Tech and AI'])->assertRedirect();
        $this->assertDatabaseHas('voice_profiles', ['user_id' => $user->id, 'name' => 'Tech and AI']);
    }

    public function test_first_profile_is_auto_default(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('voice.store'), ['name' => 'First']);
        $this->assertDatabaseHas('voice_profiles', ['user_id' => $user->id, 'is_default' => 1]);
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs($this->user())->post(route('voice.store'), ['name' => ''])->assertSessionHasErrors('name');
    }

    public function test_other_user_cannot_view_profile(): void
    {
        $profile = VoiceProfile::factory()->create(['user_id' => $this->user()->id]);
        $this->actingAs($this->user())->get(route('voice.show', $profile))->assertForbidden();
    }

    public function test_user_can_set_default(): void
    {
        $user = $this->user();
        $p1 = VoiceProfile::factory()->create(['user_id' => $user->id, 'is_default' => true]);
        $p2 = VoiceProfile::factory()->create(['user_id' => $user->id, 'is_default' => false]);
        $this->actingAs($user)->post(route('voice.default', $p2))->assertRedirect();
        $this->assertDatabaseHas('voice_profiles', ['id' => $p2->id, 'is_default' => 1]);
        $this->assertDatabaseHas('voice_profiles', ['id' => $p1->id, 'is_default' => 0]);
    }

    public function test_train_accepts_pasted_text(): void
    {
        $user = $this->user();
        $profile = VoiceProfile::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->post(route('voice.train', $profile), ['pasted_text' => 'Some sample writing about my niche topic.'])->assertRedirect();
        $profile->refresh();
        $this->assertNotNull($profile->raw_content);
        $this->assertGreaterThan(0, $profile->word_count);
    }

    public function test_train_warns_when_no_content(): void
    {
        $user = $this->user();
        $profile = VoiceProfile::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->post(route('voice.train', $profile), [])->assertRedirect()->assertSessionHas('toast');
    }

    public function test_extract_requires_raw_content(): void
    {
        $user = $this->user();
        $profile = VoiceProfile::factory()->create(['user_id' => $user->id, 'raw_content' => null]);
        $this->actingAs($user)->post(route('voice.extract', $profile))->assertRedirect()->assertSessionHas('toast');
        $this->assertDatabaseHas('voice_profiles', ['id' => $profile->id, 'status' => 'draft']);
    }

    public function test_status_endpoint_returns_json(): void
    {
        $user = $this->user();
        $profile = VoiceProfile::factory()->create(['user_id' => $user->id, 'status' => 'ready', 'word_count' => 1200]);
        $this->actingAs($user)->getJson(route('voice.status', $profile))
            ->assertOk()->assertJsonFragment(['status' => 'ready', 'word_count' => 1200]);
    }

    public function test_user_can_delete_own_profile(): void
    {
        $user = $this->user();
        $profile = VoiceProfile::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->delete(route('voice.destroy', $profile))->assertRedirect(route('voice.index'));
        $this->assertDatabaseMissing('voice_profiles', ['id' => $profile->id]);
    }

    public function test_other_user_cannot_delete_profile(): void
    {
        $profile = VoiceProfile::factory()->create(['user_id' => $this->user()->id]);
        $this->actingAs($this->user())->delete(route('voice.destroy', $profile))->assertForbidden();
        $this->assertDatabaseHas('voice_profiles', ['id' => $profile->id]);
    }
}
