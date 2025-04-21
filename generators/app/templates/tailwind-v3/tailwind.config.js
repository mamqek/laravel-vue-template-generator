import containerQueries from '@tailwindcss/container-queries';

/** @type {import('tailwindcss').Config} */
module.exports = {
	darkMode: ["class"],
	safelist: ["dark"],
    content: [
        "./resources/**/*.{vue,js,ts,jsx,tsx}",  // Targets only in resources folder
    ],
    plugins: [
        containerQueries
    ],
}