# PublishAI — API Integrations

## Complete .env template

```env
# App
APP_NAME=PublishAI
APP_ENV=local
APP_KEY=                          # php artisan key:generate
APP_URL=http://localhost:8000
APP_DEBUG=true

# Database (local dev — SQLite)
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Database (production — Supabase PostgreSQL)
# DB_CONNECTION=pgsql
# DATABASE_URL=postgresql://...

# Queue (local — no Redis needed)
QUEUE_CONNECTION=database

# Queue (production — Horizon requires Redis)
# QUEUE_CONNECTION=redis
# REDIS_HOST=
# REDIS_PASSWORD=
# REDIS_PORT=6379

# AI — Anthropic (primary)
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-sonnet-4-6

# AI — OpenAI (silent fallback)
OPENAI_API_KEY=sk-...

# Email — Resend
MAIL_MAILER=resend
RESEND_API_KEY=re_...
MAIL_FROM_ADDRESS=hello@publishai.app
MAIL_FROM_NAME=PublishAI

# Payments — Paystack
PAYSTACK_PUBLIC_KEY=pk_live_...
PAYSTACK_SECRET_KEY=sk_live_...
PAYSTACK_PAYMENT_URL=https://api.paystack.co

# Payments — Flutterwave
FLW_PUBLIC_KEY=FLWPUBK-...
FLW_SECRET_KEY=FLWSECK-...
FLW_SECRET_HASH=

# Storage
FILESYSTEM_DISK=local
```

---

## Anthropic Claude API

**Package:** `composer require anthropic-php/laravel`
**Model:** `claude-sonnet-4-6`
**Max tokens:** 4096
**Temperature:** 0.7

Add to `config/services.php`:
```php
'anthropic' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'model'   => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
],
```

Usage pattern in ClaudeService:
```php
use Anthropic\Laravel\Facades\Anthropic;

$response = Anthropic::messages()->create([
    'model'      => config('services.anthropic.model'),
    'max_tokens' => 4096,
    'system'     => $systemPrompt,
    'messages'   => [
        ['role' => 'user', 'content' => $userPrompt]
    ],
]);

return $response->content[0]->text;
```

Cost per operation (estimates):
| Operation | Est. cost |
|---|---|
| Research (10 angles) | ~$0.01 |
| Full outline | ~$0.02 |
| One chapter | ~$0.03 |
| Full 20-chapter book | ~$0.60 |
| KDP listing pack | ~$0.02 |
| Launch content pack | ~$0.03 |
| Full book end-to-end | ~$0.70 |
| Digital product end-to-end | ~$0.30 |

---

## OpenAI API (silent fallback)

**Package:** `composer require openai-php/laravel`
**Model:** `gpt-4o`

Add to `config/services.php`:
```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
],
```

Usage in ClaudeService fallback:
```php
use OpenAI\Laravel\Facades\OpenAI;

$response = OpenAI::chat()->create([
    'model'    => 'gpt-4o',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $userPrompt],
    ],
    'max_tokens' => 4096,
]);

return $response->choices[0]->message->content;
```

---

## ClaudeService — complete implementation pattern

```php
// app/Services/AI/ClaudeService.php

namespace App\Services\AI;

use Anthropic\Laravel\Facades\Anthropic;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    public function generate(string $systemPrompt, string $userPrompt): string
    {
        try {
            return $this->callClaude($systemPrompt, $userPrompt);
        } catch (\Exception $claudeError) {
            Log::warning('Claude API failed, falling back to OpenAI', [
                'error' => $claudeError->getMessage()
            ]);
            try {
                return $this->callOpenAI($systemPrompt, $userPrompt);
            } catch (\Exception $openaiError) {
                Log::error('Both AI APIs failed', [
                    'claude_error' => $claudeError->getMessage(),
                    'openai_error' => $openaiError->getMessage(),
                ]);
                throw new \Exception(
                    'Generation failed. Check your API keys in Settings.'
                );
            }
        }
    }

    public function buildSystemPrompt(string $base, ?string $voiceProfile = null): string
    {
        if (!$voiceProfile) {
            return $base . "\n\nWrite in a clear, direct, practical tone. "
                . "Short sentences. No fluff. No filler phrases.";
        }

        return $base
            . "\n\n--- AUTHOR VOICE PROFILE ---\n"
            . $voiceProfile
            . "\n--- END VOICE PROFILE ---\n\n"
            . "CRITICAL: Write in this author's exact voice. "
            . "Match their sentence length, vocabulary, tone, energy, and style. "
            . "Sound like them personally wrote every word. "
            . "Do not sound like a generic AI.";
    }

    private function callClaude(string $system, string $user): string
    {
        $response = Anthropic::messages()->create([
            'model'      => config('services.anthropic.model'),
            'max_tokens' => 4096,
            'system'     => $system,
            'messages'   => [['role' => 'user', 'content' => $user]],
        ]);
        return $response->content[0]->text;
    }

    private function callOpenAI(string $system, string $user): string
    {
        $response = OpenAI::chat()->create([
            'model'      => 'gpt-4o',
            'max_tokens' => 4096,
            'messages'   => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
        ]);
        return $response->choices[0]->message->content;
    }
}
```

---

## ExportService — Pandoc via exec()

Pandoc must be installed on the machine separately.
Download: https://pandoc.org/installing.html

```php
// app/Services/ExportService.php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ExportService
{
    public function exportToDocx(string $content, string $filename, int $userId): string
    {
        $inputPath  = storage_path("app/users/{$userId}/manuscripts/temp_{$filename}.md");
        $outputPath = storage_path("app/users/{$userId}/exports/{$filename}.docx");

        $this->ensureDirectoryExists(dirname($outputPath));
        file_put_contents($inputPath, $content);

        exec("pandoc '{$inputPath}' -o '{$outputPath}' --standalone", $out, $code);

        unlink($inputPath);

        if ($code !== 0) {
            throw new \Exception(
                'Word export failed. Make sure Pandoc is installed. '
                . 'Download free at pandoc.org'
            );
        }

        return $outputPath;
    }

    public function exportToPdf(string $content, string $filename, int $userId): string
    {
        $inputPath  = storage_path("app/users/{$userId}/manuscripts/temp_{$filename}.md");
        $outputPath = storage_path("app/users/{$userId}/exports/{$filename}.pdf");

        $this->ensureDirectoryExists(dirname($outputPath));
        file_put_contents($inputPath, $content);

        exec("pandoc '{$inputPath}' -o '{$outputPath}' --pdf-engine=xelatex", $out, $code);

        unlink($inputPath);

        if ($code !== 0) {
            // Try wkhtmltopdf as fallback if xelatex not available
            exec("pandoc '{$inputPath}' -o '{$outputPath}'", $out, $code);
            if ($code !== 0) {
                throw new \Exception(
                    'PDF export failed. Make sure Pandoc is installed.'
                );
            }
        }

        return $outputPath;
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
```

---

## Resend email

**Package:** `composer require resend/resend-laravel`

```php
// Standard Laravel Mail — works with Resend driver automatically
Mail::to($user->email)->send(new LaunchPackReadyMail($project));
```

---

## Paystack

**Package:** `composer require unicodeveloper/laravel-paystack`

```php
// Initiate payment
return Paystack::getAuthorizationUrl()->redirectNow();

// Webhook handler in api.php route
Route::post('/webhooks/paystack', [BillingController::class, 'paystackWebhook']);
```

---

## Flutterwave

**Package:** `composer require kingflamez/laravelrave`

```php
// Initiate payment
return Flutterwave::initializePayment($data);

// Webhook handler
Route::post('/webhooks/flutterwave', [BillingController::class, 'flutterwaveWebhook']);
```

Payment routing rule:
```php
// BillingController.php
$gateway = $user->country === 'NG' ? 'paystack' : 'flutterwave';
```
