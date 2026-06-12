<x-app-layout>
    <x-slot name="title">Settings</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Page header --}}
        <div>
            <h1 class="text-2xl font-bold text-base-content">Settings</h1>
            <p class="text-base-content/60 mt-1">Configure your API keys and author profile.</p>
        </div>

        {{-- Author profile --}}
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <div class="card bg-base-100 border border-base-300 shadow-sm">
                <div class="card-body space-y-4">
                    <h2 class="font-semibold text-base-content">Author Profile</h2>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Your name</span>
                        </label>
                        <input
                            type="text"
                            name="author_name"
                            value="{{ old('author_name', $user->author_name) }}"
                            placeholder="e.g. Isaac Jootar"
                            class="input input-bordered w-full"
                        />
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Used in author bios and export documents.</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Pen name <span class="text-base-content/40">(optional)</span></span>
                        </label>
                        <input
                            type="text"
                            name="pen_name"
                            value="{{ old('pen_name', $user->pen_name) }}"
                            placeholder="e.g. I.J. Books"
                            class="input input-bordered w-full"
                        />
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">The name that will appear on your book covers.</span>
                        </label>
                    </div>

                    {{-- API Keys section --}}
                    <div class="divider text-sm text-base-content/40">AI API Keys</div>

                    <div class="rounded-xl p-4 space-y-1" style="background: #F0EBFF;">
                        <p class="text-sm font-medium" style="color: #5A2EC9;">Where to get your API keys</p>
                        <p class="text-xs" style="color: #5C5470;">
                            Claude key: <span class="font-mono">console.anthropic.com</span> →
                            API Keys → Create Key<br>
                            OpenAI key: <span class="font-mono">platform.openai.com</span> →
                            API Keys → Create key (optional fallback)
                        </p>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Claude API key <span class="text-error">*</span></span>
                            @if($user->anthropic_api_key)
                                <span class="label-text-alt">
                                    <span class="badge badge-success badge-sm gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Saved
                                    </span>
                                </span>
                            @endif
                        </label>
                        <input
                            type="password"
                            name="anthropic_api_key"
                            placeholder="{{ $user->anthropic_api_key ? '••••••••••••••••••••' : 'sk-ant-...' }}"
                            class="input input-bordered w-full font-mono"
                            autocomplete="off"
                        />
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Leave blank to keep your current key.</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">
                                OpenAI API key
                                <span class="badge badge-ghost badge-sm ml-1">Backup AI</span>
                            </span>
                            @if($user->openai_api_key)
                                <span class="label-text-alt">
                                    <span class="badge badge-success badge-sm gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Saved
                                    </span>
                                </span>
                            @endif
                        </label>
                        <input
                            type="password"
                            name="openai_api_key"
                            placeholder="{{ $user->openai_api_key ? '••••••••••••••••••••' : 'sk-...' }}"
                            class="input input-bordered w-full font-mono"
                            autocomplete="off"
                        />
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Used as a silent backup if Claude is unavailable.</span>
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button
                            type="submit"
                            x-data="{ loading: false }"
                            @click="loading = true"
                            :class="loading ? 'btn btn-primary loading' : 'btn btn-primary'"
                            :disabled="loading"
                        >
                            <span x-show="!loading">Save settings</span>
                            <span x-show="loading">Saving...</span>
                        </button>

                        {{-- Test connection button --}}
                        <button
                            type="button"
                            x-data="{ loading: false }"
                            @click="
                                loading = true;
                                fetch('{{ route('settings.test') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    loading = false;
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: {
                                            message: data.message,
                                            type: data.success ? 'success' : 'error'
                                        }
                                    }));
                                })
                                .catch(() => {
                                    loading = false;
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: { message: 'Connection test failed. Try again.', type: 'error' }
                                    }));
                                })
                            "
                            :class="loading ? 'btn btn-outline loading' : 'btn btn-outline'"
                            :disabled="loading"
                        >
                            <span x-show="!loading">Test connection</span>
                            <span x-show="loading">Connecting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Cost estimates --}}
        <div class="card bg-base-100 border border-base-300 shadow-sm">
            <div class="card-body">
                <h2 class="font-semibold text-base-content mb-3">Estimated AI costs per operation</h2>
                <p class="text-sm text-base-content/60 mb-4">Based on Claude Sonnet pricing. Actual costs may vary slightly.</p>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="text-base-content/60">What you're doing</th>
                                <th class="text-base-content/60 text-right">Est. cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                ['Research — 10 title angles', '~$0.01'],
                                ['Full chapter outline', '~$0.02'],
                                ['One book chapter', '~$0.03'],
                                ['Full 20-chapter book', '~$0.60'],
                                ['KDP listing pack', '~$0.02'],
                                ['Launch content pack', '~$0.03'],
                                ['Full book end-to-end', '~$0.70'],
                                ['Digital product end-to-end', '~$0.30'],
                            ] as [$op, $cost])
                            <tr>
                                <td class="text-sm text-base-content">{{ $op }}</td>
                                <td class="text-sm font-mono font-medium text-right" style="color: #5A2EC9;">{{ $cost }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-base-content/40 mt-3">A $5 API credit covers roughly 7 complete books or 15 digital products.</p>
            </div>
        </div>

    </div>
</x-app-layout>
