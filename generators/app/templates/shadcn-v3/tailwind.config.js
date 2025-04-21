import containerQueries from '@tailwindcss/container-queries';

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: ["class"],
    safelist: ["dark"],
    content: [
        "./resources/**/*.{vue,js,ts,jsx,tsx}",  // Targets only in resources folder
    ],
    plugins: [
        containerQueries,
        require("tailwindcss-animate")
    ],
    theme: {
        extend: {
            borderRadius: {
                lg: 'var(--shadcn-radius)',
                md: 'calc(var(--shadcn-radius) - 2px)',
                sm: 'calc(var(--shadcn-radius) - 4px)'
            },
            colors: {
                background: 'hsl(var(--shadcn-background))',
                foreground: 'hsl(var(--shadcn-foreground))',
                card: {
                    DEFAULT: 'hsl(var(--shadcn-card))',
                    foreground: 'hsl(var(--shadcn-card-foreground))'
                },
                popover: {
                    DEFAULT: 'hsl(var(--shadcn-popover))',
                    foreground: 'hsl(var(--shadcn-popover-foreground))'
                },
                primary: {
                    DEFAULT: 'hsl(var(--shadcn-primary))',
                    foreground: 'hsl(var(--shadcn-primary-foreground))'
                },
                secondary: {
                    DEFAULT: 'hsl(var(--shadcn-secondary))',
                    foreground: 'hsl(var(--shadcn-secondary-foreground))'
                },
                muted: {
                    DEFAULT: 'hsl(var(--shadcn-muted))',
                    foreground: 'hsl(var(--shadcn-muted-foreground))'
                },
                accent: {
                    DEFAULT: 'hsl(var(--shadcn-accent))',
                    foreground: 'hsl(var(--shadcn-accent-foreground))'
                },
                destructive: {
                    DEFAULT: 'hsl(var(--shadcn-destructive))',
                    foreground: 'hsl(var(--shadcn-destructive-foreground))'
                },
                border: 'hsl(var(--shadcn-border))',
                input: 'hsl(var(--shadcn-input))',
                ring: 'hsl(var(--shadcn-ring))',
                chart: {
                    '1': 'hsl(var(--shadcn-chart-1))',
                    '2': 'hsl(var(--shadcn-chart-2))',
                    '3': 'hsl(var(--shadcn-chart-3))',
                    '4': 'hsl(var(--shadcn-chart-4))',
                    '5': 'hsl(var(--shadcn-chart-5))'
                }
            }
        }
    }
}