/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary:        '#6C3CE1',
                'primary-dark': '#5A2EC9',
                'primary-light':'#F0EBFF',
                accent:         '#F59E0B',
            },
        },
    },
    plugins: [
        require('daisyui'),
    ],
    daisyui: {
        themes: [
            {
                publishai: {
                    'primary':           '#6C3CE1',
                    'primary-content':   '#ffffff',
                    'secondary':         '#F59E0B',
                    'secondary-content': '#ffffff',
                    'accent':            '#10B981',
                    'accent-content':    '#ffffff',
                    'neutral':           '#1A0D33',
                    'base-100':          '#ffffff',
                    'base-200':          '#F8F7FF',
                    'base-300':          '#E4E0F0',
                    'base-content':      '#0F0A1E',
                    'info':              '#6C3CE1',
                    'success':           '#10B981',
                    'warning':           '#F59E0B',
                    'error':             '#EF4444',
                },
            },
        ],
        darkTheme: false,
    },
}
