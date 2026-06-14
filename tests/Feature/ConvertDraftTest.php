<?php

namespace Tests\Feature;

use App\Jobs\CleanDraftSectionJob;
use App\Jobs\InferChapterOrderJob;
use App\Models\PipelineRun;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ConvertDraftTest extends TestCase
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
        $user = User::factory()->create();
        UserSetting::factory()->create([
            'user_id' => $user->id,
            'api_key_encrypted' => null,
        ]);

        return $user;
    }

    private function makeTxt(string $name, string $content = 'Hello world content for testing.'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $content);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_guest_cannot_access_convert_index(): void
    {
        $this->get(route('convert.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_convert_index(): void
    {
        $user = $this->userWithKey();
        $this->actingAs($user)->get(route('convert.index'))->assertOk()->assertViewIs('convert.index');
    }

    public function test_convert_index_shows_recent_converts(): void
    {
        $user = $this->userWithKey();
        PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'topic' => 'My Converted Book',
            'status' => 'completed',
        ]);

        $this->actingAs($user)->get(route('convert.index'))
            ->assertOk()
            ->assertSee('My Converted Book');
    }

    // ── Upload — no API key ───────────────────────────────────────────────────

    public function test_upload_redirects_to_settings_without_api_key(): void
    {
        $user = $this->userWithoutKey();
        $file = $this->makeTxt('chapter.txt');

        $this->actingAs($user)
            ->post(route('convert.upload'), ['title' => 'Test Book', 'files' => [$file]])
            ->assertRedirect(route('settings.index'));
    }

    // ── Upload — validation ───────────────────────────────────────────────────

    public function test_upload_requires_title(): void
    {
        $user = $this->userWithKey();
        $file = $this->makeTxt('chapter.txt');

        $this->actingAs($user)
            ->post(route('convert.upload'), ['files' => [$file]])
            ->assertSessionHasErrors('title');
    }

    public function test_upload_requires_at_least_one_file(): void
    {
        $user = $this->userWithKey();

        $this->actingAs($user)
            ->post(route('convert.upload'), ['title' => 'Test Book', 'files' => []])
            ->assertSessionHasErrors('files');
    }

    // ── Upload — single file ──────────────────────────────────────────────────

    public function test_single_file_upload_creates_run_and_dispatches_clean_jobs(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $file = $this->makeTxt('chapter1.txt', "Chapter 1\n\nThis is the content of chapter one. It has enough text.");

        $this->actingAs($user)
            ->post(route('convert.upload'), ['title' => 'My First Book', 'files' => [$file]])
            ->assertRedirect();

        $run = PipelineRun::where('user_id', $user->id)->where('creation_mode', 'converted')->first();
        $this->assertNotNull($run);
        $this->assertEquals('My First Book', $run->topic);
        $this->assertEquals('running', $run->status);
        $this->assertNotNull($run->raw_uploaded_content);
        $this->assertNotNull($run->uploaded_files);

        Queue::assertPushed(CleanDraftSectionJob::class);
        Queue::assertNotPushed(InferChapterOrderJob::class);
    }

    public function test_single_file_upload_redirects_to_review(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $file = $this->makeTxt('book.txt', 'Content here.');

        $response = $this->actingAs($user)
            ->post(route('convert.upload'), ['title' => 'My Book', 'files' => [$file]]);

        $run = PipelineRun::where('user_id', $user->id)->first();
        $response->assertRedirect(route('convert.review', $run));
    }

    // ── Upload — multiple files ───────────────────────────────────────────────

    public function test_multi_file_upload_dispatches_infer_job(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $files = [
            $this->makeTxt('chapter1.txt', 'Chapter One content here.'),
            $this->makeTxt('chapter2.txt', 'Chapter Two content here.'),
        ];

        $this->actingAs($user)
            ->post(route('convert.upload'), ['title' => 'Multi Chapter Book', 'files' => $files])
            ->assertRedirect();

        Queue::assertPushed(InferChapterOrderJob::class);
        Queue::assertNotPushed(CleanDraftSectionJob::class);
    }

    public function test_multi_file_upload_redirects_to_order_page(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $files = [
            $this->makeTxt('ch1.txt', 'First chapter.'),
            $this->makeTxt('ch2.txt', 'Second chapter.'),
        ];

        $response = $this->actingAs($user)
            ->post(route('convert.upload'), ['title' => 'Two Chapter Book', 'files' => $files]);

        $run = PipelineRun::where('user_id', $user->id)->first();
        $response->assertRedirect(route('convert.order', $run));
    }

    public function test_multi_file_upload_stores_all_files(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $files = [
            $this->makeTxt('intro.txt', 'Introduction content.'),
            $this->makeTxt('chapter1.txt', 'Chapter one content.'),
        ];

        $this->actingAs($user)
            ->post(route('convert.upload'), ['title' => 'Book', 'files' => $files]);

        $run = PipelineRun::where('user_id', $user->id)->first();
        $this->assertCount(2, $run->uploaded_files);
        $this->assertEquals('intro.txt', $run->uploaded_files[0]['filename']);
        $this->assertEquals('chapter1.txt', $run->uploaded_files[1]['filename']);
    }

    // ── Order page ────────────────────────────────────────────────────────────

    public function test_order_page_requires_auth(): void
    {
        $run = PipelineRun::factory()->create(['creation_mode' => 'converted']);
        $this->get(route('convert.order', $run))->assertRedirect(route('login'));
    }

    public function test_order_page_shows_for_owner(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'status' => 'paused',
            'inferred_order' => [
                'order' => [
                    ['filename' => 'ch1.txt', 'title' => 'Chapter 1', 'position' => 1, 'reason' => 'First'],
                ],
                'summary' => 'Alphabetical',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('convert.order', $run))
            ->assertOk()
            ->assertViewIs('convert.order');
    }

    public function test_order_page_returns_403_for_other_user(): void
    {
        $owner = $this->userWithKey();
        $other = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $owner->id, 'creation_mode' => 'converted']);

        $this->actingAs($other)->get(route('convert.order', $run))->assertForbidden();
    }

    // ── Confirm order ─────────────────────────────────────────────────────────

    public function test_confirm_order_dispatches_clean_jobs_and_redirects_to_review(): void
    {
        Queue::fake();
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'status' => 'paused',
            'uploaded_files' => [
                ['filename' => 'ch1.txt', 'content' => 'Chapter one content here.', 'word_count' => 4],
                ['filename' => 'ch2.txt', 'content' => 'Chapter two content here.', 'word_count' => 4],
            ],
            'inferred_order' => ['order' => [], 'summary' => ''],
        ]);

        $response = $this->actingAs($user)
            ->post(route('convert.order.confirm', $run), ['order' => ['ch1.txt', 'ch2.txt']]);

        $response->assertRedirect(route('convert.review', $run));

        $run->refresh();
        $this->assertEquals(['ch1.txt', 'ch2.txt'], $run->confirmed_order);
        $this->assertEquals('running', $run->status);
        $this->assertStringContainsString('Chapter one', $run->raw_uploaded_content);

        Queue::assertPushed(CleanDraftSectionJob::class);
    }

    public function test_confirm_order_requires_order_field(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $user->id, 'creation_mode' => 'converted']);

        $this->actingAs($user)
            ->post(route('convert.order.confirm', $run), [])
            ->assertSessionHasErrors('order');
    }

    public function test_confirm_order_forbidden_for_non_owner(): void
    {
        $owner = $this->userWithKey();
        $other = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $owner->id, 'creation_mode' => 'converted']);

        $this->actingAs($other)
            ->post(route('convert.order.confirm', $run), ['order' => ['ch1.txt']])
            ->assertForbidden();
    }

    // ── Review / Status / Approve ─────────────────────────────────────────────

    public function test_review_page_shows_for_owner(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'status' => 'running',
        ]);

        $this->actingAs($user)->get(route('convert.review', $run))->assertOk()->assertViewIs('convert.review');
    }

    public function test_review_page_forbidden_for_non_owner(): void
    {
        $owner = $this->userWithKey();
        $other = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $owner->id, 'creation_mode' => 'converted']);

        $this->actingAs($other)->get(route('convert.review', $run))->assertForbidden();
    }

    public function test_status_endpoint_returns_json(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'status' => 'running',
            'cleaned_content' => null,
            'cleaning_approved' => false,
        ]);

        $this->actingAs($user)
            ->getJson(route('convert.status', $run))
            ->assertOk()
            ->assertJsonStructure(['status', 'progress_note', 'cleaning_approved', 'has_cleaned']);
    }

    public function test_approve_saves_cleaned_content_and_redirects_to_listing(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'status' => 'running',
            'cleaned_content' => null,
        ]);

        $this->actingAs($user)
            ->post(route('convert.approve', $run), ['cleaned_content' => 'Cleaned text here.'])
            ->assertRedirect(route('convert.listing', $run));

        $run->refresh();
        $this->assertEquals('Cleaned text here.', $run->cleaned_content);
        $this->assertTrue($run->cleaning_approved);
        $this->assertEquals('completed', $run->status);
    }

    public function test_approve_requires_cleaned_content(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $user->id, 'creation_mode' => 'converted']);

        $this->actingAs($user)
            ->post(route('convert.approve', $run), [])
            ->assertSessionHasErrors('cleaned_content');
    }

    public function test_approve_forbidden_for_non_owner(): void
    {
        $owner = $this->userWithKey();
        $other = $this->userWithKey();
        $run = PipelineRun::factory()->create(['user_id' => $owner->id, 'creation_mode' => 'converted']);

        $this->actingAs($other)
            ->post(route('convert.approve', $run), ['cleaned_content' => 'text'])
            ->assertForbidden();
    }

    // ── Export ────────────────────────────────────────────────────────────────

    public function test_export_returns_404_without_cleaned_content(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'cleaned_content' => null,
        ]);

        $this->actingAs($user)->get(route('convert.export', [$run, 'premium']))->assertNotFound();
    }

    public function test_export_returns_404_for_invalid_format(): void
    {
        $user = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $user->id,
            'creation_mode' => 'converted',
            'cleaned_content' => 'Some content.',
        ]);

        $this->actingAs($user)->get(route('convert.export', [$run, 'invalid']))->assertNotFound();
    }

    public function test_export_forbidden_for_non_owner(): void
    {
        $owner = $this->userWithKey();
        $other = $this->userWithKey();
        $run = PipelineRun::factory()->create([
            'user_id' => $owner->id,
            'creation_mode' => 'converted',
            'cleaned_content' => 'Content.',
        ]);

        $this->actingAs($other)->get(route('convert.export', [$run, 'premium']))->assertForbidden();
    }
}
