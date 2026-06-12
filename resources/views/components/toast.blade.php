<div
    x-data="toastManager()"
    x-on:toast.window="addToast($event.detail)"
    class="fixed top-4 right-4 z-50 flex flex-col gap-2"
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
            <button @click="removeToast(toast.id)" class="btn btn-ghost btn-xs ml-auto">✕</button>
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
            const durations = { success: 4000, error: 6000, warning: 0, info: duration };
            const finalDuration = duration !== 4000 ? duration : (durations[type] ?? 4000);
            this.toasts.push({ id, message, ...config[type] });
            if (finalDuration > 0) {
                setTimeout(() => this.removeToast(id), finalDuration);
            }
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }
}
</script>
