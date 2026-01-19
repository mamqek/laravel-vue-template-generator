// resources/js/plugins/tailwind.config.cjs
console.log('[tailwind-config] loaded:', __filename);

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.{vue,js,ts,tsx,html}',
  ],
  plugins: [
    require('tailwindcss-fluid-type')({
      settings: {
        // Viewport range: 768px → 1920px
        screenMin: 48,   // rem (768px)
        screenMax: 120,  // rem (1920px)

        // Anchor to Tailwind defaults at 1920px:
        fontSizeMin: 0.95, // size at 768px baseline
        fontSizeMax: 1,    // => text-base = 1rem at 1920px

        // Modular scale for min/max ends
        ratioMin: 1.125,
        ratioMax: 1.2,

        unit: 'rem',

        // Keep Tailwind fixed sizes; use fluid-* classes alongside
        prefix: 'fluid-',
        extendValues: true,
      },

      // Steps chosen so that at 1920px each label equals Tailwind’s default size.
      // Best starting point for fluid typography as on smaller screens OS scaling kicks on and stuff is bigger then it should be.
      // Format: [step, lineHeight]
      values: {
        xs:    [-1.577883, 1.6],  // 0.75rem @1920
        sm:    [-0.732395, 1.6],  // 0.875rem
        base:  [ 0,        1.6],  // 1rem
        lg:    [ 0.646018, 1.6],  // 1.125rem
        xl:    [ 1.223901, 1.2],  // 1.25rem
        '2xl': [ 2.223901, 1.2],  // 1.5rem
        '3xl': [ 3.447802, 1.2],  // 1.875rem
        '4xl': [ 4.447802, 1.1],  // 2.25rem
        '5xl': [ 6.025685, 1.1],  // 3rem
        '6xl': [ 7.249586, 1.1],  // 3.75rem
        '7xl': [ 8.249586, 1.0],  // 4.5rem
        '8xl': [ 9.827469, 1.0],  // 6rem
        '9xl': [11.405352, 1.0],  // 8rem

        // Customs anchored to exact rems at 1920px:
        '2rem': [3.801784, 1.2],  // 2rem
        '5rem': [8.827469, 1.0],  // 5rem
      },
    }),
  ],
};
