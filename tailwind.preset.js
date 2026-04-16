/**
 * Webfloo Tailwind Preset
 *
 * This preset provides custom animations and typography extensions.
 * Color theming is handled by FlyonUI - see resources/css/themes/bitfloo.css
 */
module.exports = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['var(--font-sans, Inter)', 'system-ui', 'sans-serif'],
        heading: ['var(--font-heading, Poppins)', 'var(--font-sans, Inter)', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out forwards',
        'fade-up': 'fadeUp 0.5s ease-out forwards',
        'slide-in-left': 'slideInLeft 0.5s ease-out forwards',
        'slide-in-right': 'slideInRight 0.5s ease-out forwards',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        fadeUp: {
          '0%': { opacity: '0', transform: 'translateY(1rem)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideInLeft: {
          '0%': { opacity: '0', transform: 'translateX(-2rem)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        slideInRight: {
          '0%': { opacity: '0', transform: 'translateX(2rem)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
      },
    },
  },
}
