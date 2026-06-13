# PublishAI — UI Rules & Language

## The one test before anything ships

> Could a non-technical solopreneur understand this immediately,
> without a tooltip or tutorial?
> If no — it is not ready.

---

## The core visual rule — never break this

| Label colour | Meaning | Text shown |
|---|---|---|
| Green badge | Claude did this automatically | "Claude handled this" |
| Orange badge | You need to do something | "Your action needed" |

Every single output panel in the app shows one of these two badges.
The user must never be confused about what is automated and what is manual.

---

## Navigation labels — fixed forever

| Internal name | UI label | Why |
|---|---|---|
| Voice Profile Manager | My Voice | Multiple domain profiles live here |
| Niche Research Engine | Research | The action they're taking |
| Outline Generator | Outline | Plain, clear |
| Manuscript Writer | Write | One word, active |
| Illustration Prompt Generator | Illustrations | What it produces |
| KDP Pack Generator | KDP Listing | What it produces |
| Digital Product Builder | Digital Product | Plain |
| Launch Content Generator | Launch | The outcome |
| Series Planner | My Series | Personal ownership |
| Project Library | My Projects | Personal ownership |

---

## Button language — always a verb or outcome

| Never use | Use instead |
|---|---|
| Generate | Write this for me |
| Submit | Save and continue |
| Confirm | Yes, this looks good |
| Reject | No, rewrite this |
| Initiate | Start |
| Configure | Set up |
| Authenticate | Connect |
| Terminate | Disconnect |
| Export document | Download as Word file |
| Export PDF | Download as PDF |
| Regenerate | Try again |
| Approve | Use this version |

---

## Pipeline step labels

| Internal | UI label |
|---|---|
| Step 1 — research | Find the right title |
| Step 2 — outline | Build the structure |
| Step 3 — write | Write the content |
| Step 4 — illustrations | Create illustration prompts |
| Step 5 — kdp listing | Build your Amazon listing |
| Step 5 (digital) — sales page | Write your sales page |
| Step 6 — launch | Prepare your launch |

---

## Status labels

| Internal | User sees |
|---|---|
| draft | Not finished yet |
| in_progress | Working on it |
| approved | Ready to use |
| exported | Downloaded |
| archived | Saved but not active |
| failed | Something went wrong — try again |

---

## Form field labels

| Internal / technical | Plain label shown to user |
|---|---|
| topic | What do you want to write about? |
| book_format | What type of book is this? |
| target_age | Who is this book for? (age) |
| target_reader | Who will buy or read this? |
| voice_profile | Which writing voice should I use? |
| domain_name | What is this domain called? |
| domain_description | What topics will this cover? |
| is_default | Use this as my default voice |
| product_type | What kind of digital product is this? |
| platform | Where will you sell this? |
| price_usd | How much will you charge? (USD) |
| author_pen_name | What name will appear on the book? |
| series_name | What is this series called? |

---

## Error and system messages — plain English only

| Raw / technical | Plain English shown to user |
|---|---|
| AnthropicAPIError | Claude is having a moment. Trying the backup AI now... |
| OpenAIError | Both AIs are unavailable. Check your API keys in Settings. |
| API key missing | No API key found. Go to Settings and add your Claude API key. |
| Pandoc not found | Word/PDF export needs Pandoc installed. See the setup guide. |
| SQLite write error | Couldn't save your work. Make sure the app has write permissions. |
| File not found | That file isn't where we expected it. Try uploading again. |
| Timeout | That took too long. Try again — it usually works on the second attempt. |

---

## Voice profile upload instructions

Shown on the My Voice page:

```
Upload anything you've written or said.
The more you give, the more it sounds like you.

Works best with:
- WhatsApp messages or voice note transcripts
- Old blog posts or articles
- Social media captions you've written
- Email newsletters
- Any notes, essays, or long messages

Minimum: 500 words
Recommended: 2,000+ words
More is always better.
```

---

## Illustration step instructions

Shown clearly with orange badge on the Illustrations page:

```
YOUR ACTION NEEDED — Claude cannot generate images.

Here's what to do for each page:
1. Copy the prompt below using the Copy button
2. Open Midjourney or Ideogram (free tier works)
3. Paste the prompt and generate your image
4. Save the image to your computer
5. Tick the checkbox below to mark this page as done

Once all pages are ticked, you can move to the next step.
```

---

## KDP upload instructions

Shown with orange badge on the KDP Listing page:

```
YOUR ACTION NEEDED — Claude built your listing, but you upload it.

Here's what to do:
1. Download your manuscript using the button below
2. Go to kdp.amazon.com and sign in
3. Click "Add new title"
4. Copy and paste each section from this page into KDP
5. Upload your manuscript file and cover image
6. Click Publish

Your listing copy is saved here so you can come back any time.
```

---

## Structural rules

**One primary action per screen.**
Every page has one clear next step. The main button is always violet.
Secondary actions (download, regenerate) are smaller and below.

**Progress bar is always visible.**
A step indicator at the top of every pipeline page shows:
✓ Done steps (green) → Current step (violet) → Locked steps (grey)

**Nothing is ever lost.**
Every AI output is saved to the database before showing it to the user.
If the app crashes, the work is still there when they come back.

**Regenerate is always available.**
Every AI output has a small "Try again" button below it.
Regenerating does not delete the previous version — it saves both.

**Approve before advancing.**
The "Continue to next step" button is disabled until the user clicks
"Yes, this looks good" on the current step's output.

---

## Toast Notification System — all system messages go here

**Rule: no inline success/error messages anywhere in the app.**
Every system message — success, error, warning, info — is a toast
notification appearing top-right. Always. No exceptions.

---

### Toast types and colours

| Type | When to use | DaisyUI class | Icon |
|---|---|---|---|
| Success | Action completed | `alert-success` | heroicon: check-circle |
| Error | Something failed | `alert-error` | heroicon: x-circle |
| Warning | Needs attention | `alert-warning` | heroicon: exclamation-triangle |
| Info | Neutral message | `alert-info` | heroicon: information-circle |
| Loading | Processing started | `alert-info` + spinner | animated spinner |

---

### Toast messages — plain English only

**Success examples:**
```
"Voice profile saved successfully."
"Chapter 3 approved."
"Research complete — 10 title angles found."
"Your manuscript has been exported."
"Project archived."
"API key connected successfully."
"Outline approved — ready to write."
"Launch pack saved."
```

**Error examples:**
```
"Generation failed. Check your API keys in Settings."
"Could not save your work. Please try again."
"Export failed. Make sure Pandoc is installed."
"File upload failed. Accepted formats: .txt .md .docx"
"Could not connect to Claude. Trying backup AI..."
"Session expired. Please log in again."
```

**Warning examples:**
```
"Your voice profile has less than 500 words. Add more for better results."
"No domain profile selected. Output will use default tone."
"This will overwrite your previous outline. Are you sure?"
```

**Info examples:**
```
"Writing Chapter 3... this takes about 15 seconds."
"Extracting your writing style..."
"Switching to backup AI..."
"Saving your work..."
```

---

### Toast implementation — Blade component

Create: `resources/views/components/toast.blade.php`

```html
<!-- resources/views/components/toast.blade.php -->
<div
    x-data="toastManager()"
    x-on:toast.window="addToast($event.detail)"
    class="toast toast-top toast-end z-50 fixed top-4 right-4 flex flex-col gap-2"
    style="max-width: 360px;"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            :class="`alert ${toast.class} shadow-lg`"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-full"
        >
            <span x-html="toast.icon"></span>
            <span x-text="toast.message" class="text-sm font-medium"></span>
            <button
                @click="removeToast(toast.id)"
                class="btn btn-ghost btn-xs ml-auto"
            >✕</button>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        addToast({ message, type = 'info', duration = 4000 }) {
            const id = Date.now();
            const config = {
                success: {
                    class: 'alert-success',
                    icon: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
                error: {
                    class: 'alert-error',
                    icon: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
                warning: {
                    class: 'alert-warning',
                    icon: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
                },
                info: {
                    class: 'alert-info',
                    icon: '<svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
            };
            this.toasts.push({ id, message, ...config[type] });
            if (duration > 0) {
                setTimeout(() => this.removeToast(id), duration);
            }
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }
}
</script>
```

Include in `layouts/app.blade.php` once — before closing `</body>`:
```html
<x-toast />
```

---

### Triggering toasts — from anywhere

**From Blade / Alpine.js (JavaScript):**
```javascript
// Success
window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: 'Chapter approved.', type: 'success' }
}));

// Error
window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: 'Generation failed. Check your API keys.', type: 'error' }
}));

// Warning (stays until dismissed — set duration: 0)
window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: 'No voice profile selected.', type: 'warning', duration: 0 }
}));

// Info (while processing)
window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: 'Writing Chapter 3...', type: 'info', duration: 0 }
}));
```

**From Livewire (PHP):**
```php
// In any Livewire component
$this->dispatch('toast', message: 'Voice profile saved.', type: 'success');
$this->dispatch('toast', message: 'Generation failed.', type: 'error');
$this->dispatch('toast', message: 'Saving...', type: 'info', duration: 0);
```

**From Laravel controllers (after redirect):**
```php
// In controller — flash to session, read in Blade on next page
return redirect()->route('research')->with('toast', [
    'message' => 'Project created successfully.',
    'type'    => 'success',
]);
```

```html
<!-- In layouts/app.blade.php — trigger from session flash -->
@if(session('toast'))
<script>
    window.addEventListener('load', () => {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                message: "{{ session('toast.message') }}",
                type: "{{ session('toast.type') }}"
            }
        }));
    });
</script>
@endif
```

---

### Toast duration rules

| Type | Duration | Why |
|---|---|---|
| Success | 4 seconds | Quick confirmation, then gone |
| Error | 6 seconds | User needs time to read |
| Warning | Until dismissed | Requires user acknowledgement |
| Info / Loading | Until dismissed | Replace with success/error when done |

**Loading toast pattern — show while processing, replace on completion:**
```javascript
// Show loading (stays open)
window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: 'Writing Chapter 3...', type: 'info', duration: 0 }
}));

// When done — the info auto-dismisses when you show success
window.dispatchEvent(new CustomEvent('toast', {
    detail: { message: 'Chapter 3 complete.', type: 'success' }
}));
```

---

## Button Loading States — all async actions

**Rule: every button that triggers an async action must show a loading
state immediately on click. The button is disabled while loading.
User must never wonder if their click registered.**

---

### Loading button — Alpine.js pattern

```html
<!-- Standard loading button -->
<button
    x-data="{ loading: false }"
    @click="loading = true; $wire.generateChapter()"
    :disabled="loading"
    :class="loading ? 'btn btn-primary loading' : 'btn btn-primary'"
>
    <span x-show="!loading">Write this chapter →</span>
    <span x-show="loading">Writing...</span>
</button>
```

---

### Loading states — what the button text changes to

| Action button | Loading text |
|---|---|
| Write this chapter → | Writing... |
| Find title angles → | Researching... |
| Build my outline → | Building... |
| Extract my writing style → | Learning your voice... |
| Generate illustration prompts → | Creating prompts... |
| Build my Amazon listing → | Writing listing... |
| Generate launch pack → | Writing your launch... |
| Download as Word file | Exporting... |
| Download as PDF | Exporting... |
| Test connection | Connecting... |
| Save my voice | Saving... |
| Approve and continue → | Saving... |
| Try again | Retrying... |

---

### DaisyUI loading button — simplest pattern

```html
<!-- DaisyUI handles the spinner with the 'loading' class -->
<button
    wire:click="generateResearch"
    wire:loading.class="loading"
    wire:loading.attr="disabled"
    class="btn btn-primary"
>
    <span wire:loading.remove>Find title angles →</span>
    <span wire:loading>Researching...</span>
</button>
```

For Livewire components, `wire:loading` handles the state automatically.
Always pair `wire:loading.attr="disabled"` to prevent double-clicks.

---

### Full page loading overlay — for long operations only

For operations over 10 seconds (full manuscript generation, voice extraction):

```html
<!-- Overlay shown during long operations -->
<div
    wire:loading
    wire:target="generateFullManuscript"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center"
>
    <div class="bg-base-100 rounded-2xl p-8 text-center shadow-2xl max-w-sm mx-4">
        <span class="loading loading-spinner loading-lg text-primary mb-4"></span>
        <p class="font-medium text-base-content">Writing your manuscript...</p>
        <p class="text-sm text-base-content/60 mt-2">This takes 1-2 minutes. Don't close this tab.</p>
    </div>
</div>
```

Use full overlay only for: full manuscript generation, voice profile extraction,
full digital product generation, bulk illustration prompt generation.
Use inline button loading for everything else.
