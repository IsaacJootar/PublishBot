<div
    x-data="toastManager()"
    x-on:toast.window="addToast($event.detail)"
    style="position:fixed; top:1rem; right:1rem; z-index:9999; display:flex; flex-direction:column; gap:0.5rem; max-width:340px; width:calc(100vw - 2rem);"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            :style="toast.style"
            style="display:flex; align-items:flex-start; gap:0.625rem; padding:0.875rem 1rem; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.18); cursor:pointer;"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 transform translate-x-8"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-180"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            @click="removeToast(toast.id)"
        >
            <span x-html="toast.icon" style="flex-shrink:0; margin-top:1px;"></span>
            <span x-text="toast.message" style="font-size:0.85rem; font-weight:500; flex:1; line-height:1.4; color:#fff;"></span>
            <button style="background:none;border:none;color:rgba(255,255,255,0.6);cursor:pointer;font-size:1rem;line-height:1;padding:0;flex-shrink:0;">✕</button>
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
                    style: 'background:#1E3A5F;',
                    icon: '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
                error: {
                    style: 'background:#7F1D1D;',
                    icon: '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
                warning: {
                    style: 'background:#78350F;',
                    icon: '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
                },
                info: {
                    style: 'background:#1E1A2E;',
                    icon: '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
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
