<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="max-w-4xl mx-auto">
        {{-- Welcome card --}}
        <div class="card bg-base-100 shadow-sm border border-base-300 mb-6">
            <div class="card-body">
                <h1 class="text-2xl font-bold text-base-content">Welcome to PublishAI</h1>
                <p class="text-base-content/60 mt-1">Your AI publishing engine. One topic in. Sellable product out.</p>

                <div class="flex flex-wrap gap-3 mt-4">
                    <a href="#" class="btn btn-primary gap-2">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        Start a new project
                    </a>
                    <a href="#" class="btn btn-outline gap-2">
                        <x-heroicon-o-microphone class="w-4 h-4" />
                        Set up My Voice
                    </a>
                </div>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="card border border-base-300" style="background: #F0EBFF;">
                <div class="card-body p-4">
                    <p class="text-xs font-medium" style="color: #5C5470;">Total Projects</p>
                    <p class="text-2xl font-bold mt-1" style="color: #5A2EC9;">0</p>
                </div>
            </div>
            <div class="card border border-base-300" style="background: #ECFDF5;">
                <div class="card-body p-4">
                    <p class="text-xs font-medium" style="color: #5C5470;">Books Complete</p>
                    <p class="text-2xl font-bold mt-1" style="color: #065F46;">0</p>
                </div>
            </div>
            <div class="card border border-base-300" style="background: #FFFBEB;">
                <div class="card-body p-4">
                    <p class="text-xs font-medium" style="color: #5C5470;">Products Created</p>
                    <p class="text-2xl font-bold mt-1" style="color: #92400E;">0</p>
                </div>
            </div>
            <div class="card border border-base-300" style="background: #EFF6FF;">
                <div class="card-body p-4">
                    <p class="text-xs font-medium" style="color: #5C5470;">Words Generated</p>
                    <p class="text-2xl font-bold mt-1" style="color: #1D4ED8;">0</p>
                </div>
            </div>
        </div>

        {{-- Getting started --}}
        <div class="card bg-base-100 shadow-sm border border-base-300">
            <div class="card-body">
                <h2 class="font-semibold text-base-content mb-4">Get started in 3 steps</h2>
                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 rounded-lg" style="background: #F8F7FF;">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0" style="background: #6C3CE1;">1</div>
                        <div>
                            <p class="font-medium text-sm text-base-content">Add your API keys</p>
                            <p class="text-xs text-base-content/60 mt-0.5">Go to Settings and add your Claude API key to power the AI.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-lg" style="background: #F8F7FF;">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0" style="background: #6C3CE1;">2</div>
                        <div>
                            <p class="font-medium text-sm text-base-content">Set up My Voice</p>
                            <p class="text-xs text-base-content/60 mt-0.5">Upload samples of your writing so Claude sounds like you.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-lg" style="background: #F8F7FF;">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0" style="background: #6C3CE1;">3</div>
                        <div>
                            <p class="font-medium text-sm text-base-content">Start your first project</p>
                            <p class="text-xs text-base-content/60 mt-0.5">Choose a book or digital product and let PublishAI do the work.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
