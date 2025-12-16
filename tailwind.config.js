/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                'fiorentina': {
                    '50': '#f8f5ff',
                    '100': '#f0ebff',
                    '200': '#e1d6ff',
                    '300': '#cab2ff',
                    '400': '#ac7fff',
                    '500': '#904dff',
                    '600': '#7e29f5',
                    '700': '#6c18d8',
                    '800': '#5a16b0',
                    '900': '#4b148f',
                    '950': '#2d0461',
                }
            },
            fontFamily: {
                'display': ['Inter', 'system-ui', 'sans-serif'],
            },
            animation: {
                'gradient-x': 'gradient-x 15s ease infinite',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                'gradient-x': {
                    '0%, 100%': {
                        'background-size': '200% 200%',
                        'background-position': 'left center'
                    },
                    '50%': {
                        'background-size': '200% 200%',
                        'background-position': 'right center'
                    },
                }
            }
        },
    },
    plugins: [],
}
