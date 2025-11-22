import plugin from 'tailwindcss/plugin'
import colors from 'tailwindcss/colors'
import defaultTheme from 'tailwindcss/defaultTheme'

export default {
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                // Cores customizadas da Aeronáutica
                primary: {
                    50: '#e6f0ff',
                    100: '#cce0ff',
                    200: '#99c2ff',
                    300: '#66a3ff',
                    400: '#3385ff',
                    500: '#0066cc', // Azul Céu
                    600: '#003DA5', // Azul FAB (principal)
                    700: '#003380',
                    800: '#002a66',
                    900: '#00204d',
                    950: '#002366', // Azul Escuro
                },
                'aero-blue': {
                    primary: '#003DA5',
                    sky: '#0066CC',
                    light: '#4A90E2',
                    dark: '#002366',
                },
                'aero-gold': '#FFD700',
                'aero-silver': '#C0C0C0',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [
        plugin(function({ addUtilities }) {
            addUtilities({
                '.size-8': {
                    width: '2rem',
                    height: '2rem',
                },
                '.size-10': {
                    width: '2.5rem',
                    height: '2.5rem',
                },
                '.size-12': {
                    width: '3rem',
                    height: '3rem',
                },
            })
        }),
    ],
}
