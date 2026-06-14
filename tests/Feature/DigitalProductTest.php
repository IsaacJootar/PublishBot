<?php

namespace Tests\Feature;

use App\Jobs\DigitalProducts\ContentJob;
use App\Jobs\DigitalProducts\PublishPackJob;
use App\Jobs\DigitalProducts\ResearchJob;
use App\Jobs\DigitalProducts\StructureJob;
use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DigitalProductTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_guest_cannot_access_digital_products(): void
    {
        $this->get(route('digital-products.index'))->assertRedirect(route('login'));
    }

    public function test_user_sees_digital_products_index(): void
    {
        $this->actingAs($this->user())->get(route('digital-products.index'))
            ->assertOk()->assertSee('Digital Products Factory');
    }

    public function test_index_shows_all_three_product_type_cards(): void
    {
        $this->actingAs($this->user())->get(route('digital-products.index'))
            ->assertSee('Prompt Library')
            ->assertSee('SOP Pack')
            ->assertSee('Email Sequence Vault');
    }

    public function test_create_page_loads_for_each_type(): void
    {
        $user = $this->user();
        foreach (['prompt_library', 'sop_pack', 'email_sequence_vault'] as $type) {
            $this->actingAs($user)->get(route('digital-products.create', $type))->assertOk();
        }
    }

    public function test_create_returns_404_for_invalid_type(): void
    {
        $this->actingAs($this->user())->get(route('digital-products.create', 'invalid_type'))->assertNotFound();
    }

    public function test_store_prompt_library_dispatches_research_job(): void
    {
        Queue::fake();
        $user = $this->user();

        $this->actingAs($user)->post(route('digital-products.store'), [
            'product_type' => 'prompt_library',
            'niche' => 'Freelance designers',
            'buyer_description' => 'Self-employed designers',
            'buyer_problem' => 'Too much time on proposals',
            'prompt_count' => 30,
        ])->assertRedirect();

        Queue::assertPushed(ResearchJob::class);
        $this->assertDatabaseHas('digital_products', ['user_id' => $user->id, 'product_type' => 'prompt_library']);
    }

    public function test_store_sop_pack_dispatches_research_job(): void
    {
        Queue::fake();
        $this->actingAs($this->user())->post(route('digital-products.store'), [
            'product_type' => 'sop_pack',
            'niche' => 'Freelance writers',
            'buyer_description' => 'Freelancers who want systems',
            'buyer_problem' => 'Repetitive tasks',
            'sop_count' => 10,
        ])->assertRedirect();

        Queue::assertPushed(ResearchJob::class);
    }

    public function test_store_email_vault_dispatches_research_job(): void
    {
        Queue::fake();
        $this->actingAs($this->user())->post(route('digital-products.store'), [
            'product_type' => 'email_sequence_vault',
            'niche' => 'Online coaches',
            'buyer_description' => 'Coaches needing templates',
            'buyer_problem' => 'Hours writing emails',
            'sequence_count' => 5,
            'emails_per_sequence' => 5,
        ])->assertRedirect();

        Queue::assertPushed(ResearchJob::class);
    }

    public function test_store_requires_niche(): void
    {
        $this->actingAs($this->user())->post(route('digital-products.store'), [
            'product_type' => 'prompt_library',
            'niche' => '',
            'buyer_description' => 'Someone',
            'buyer_problem' => 'Something',
        ])->assertSessionHasErrors('niche');
    }

    public function test_user_can_view_own_product(): void
    {
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('digital-products.show', $product))->assertOk();
    }

    public function test_other_user_cannot_view_product(): void
    {
        $owner = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($this->user())->get(route('digital-products.show', $product))->assertForbidden();
    }

    public function test_status_returns_json(): void
    {
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id, 'status' => 'researching', 'current_stage' => 1]);

        $this->actingAs($user)->getJson(route('digital-products.status', $product))
            ->assertOk()
            ->assertJsonFragment(['status' => 'researching', 'current_stage' => 1]);
    }

    public function test_advance_from_stage_2_dispatches_structure_job(): void
    {
        Queue::fake();
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id, 'current_stage' => 2, 'status' => 'draft']);

        $this->actingAs($user)->post(route('digital-products.advance', $product))->assertRedirect();

        Queue::assertPushed(StructureJob::class);
    }

    public function test_advance_from_stage_3_dispatches_content_job(): void
    {
        Queue::fake();
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id, 'current_stage' => 3, 'status' => 'draft']);

        $this->actingAs($user)->post(route('digital-products.advance', $product))->assertRedirect();

        Queue::assertPushed(ContentJob::class);
    }

    public function test_advance_from_stage_4_dispatches_publish_pack_job(): void
    {
        Queue::fake();
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id, 'current_stage' => 4, 'status' => 'draft']);

        $this->actingAs($user)->post(route('digital-products.advance', $product))->assertRedirect();

        Queue::assertPushed(PublishPackJob::class);
    }

    public function test_regenerate_dispatches_correct_job(): void
    {
        Queue::fake();
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id, 'current_stage' => 2]);

        $this->actingAs($user)->post(route('digital-products.regenerate', [$product, 2]))->assertRedirect();

        Queue::assertPushed(ResearchJob::class);
    }

    public function test_user_can_delete_product(): void
    {
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete(route('digital-products.destroy', $product))->assertRedirect(route('digital-products.index'));

        $this->assertDatabaseMissing('digital_products', ['id' => $product->id]);
    }

    public function test_other_user_cannot_delete_product(): void
    {
        $owner = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($this->user())->delete(route('digital-products.destroy', $product))->assertForbidden();

        $this->assertDatabaseHas('digital_products', ['id' => $product->id]);
    }

    public function test_export_returns_404_when_no_content(): void
    {
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id, 'content_output' => null]);

        $this->actingAs($user)->get(route('digital-products.export', [$product, 'premium']))->assertNotFound();
    }

    public function test_export_returns_404_for_invalid_format(): void
    {
        $user = $this->user();
        $product = DigitalProduct::factory()->create(['user_id' => $user->id, 'content_output' => 'content']);

        $this->actingAs($user)->get(route('digital-products.export', [$product, 'bad']))->assertNotFound();
    }
}
