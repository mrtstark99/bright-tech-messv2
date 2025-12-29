/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/views/**/*.php",
        "./resources/js/**/*.js",
        "./public/**/*.html"
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                // SaaS Design System Colors
                primary: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                surface: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                },
                sidebar: {
                    bg: '#1e1b4b',
                    hover: '#312e81',
                    active: '#4338ca',
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05)',
                'card-hover': '0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05)',
            },
            borderRadius: {
                'card': '12px',
            },
            spacing: {
                'sidebar': '72px',
                'sidebar-local': '280px',
            }
        },
    },
    plugins: [],
}
