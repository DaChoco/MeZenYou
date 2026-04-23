/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{html,php,js}", "!./node_modules/**", "!./vendor/**"],
  theme: {
    extend: {
      colors: {
        hoverbtnred: '#D10D0D',
        darkgray: '#1E293B',
        normalred: '#DC2626',
        cerulean: "#007BA7",
      },
      fontFamily: {
        sans: ['Roboto', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Helvetica Neue', 'Arial', 'sans-serif'],
    },
  },
  plugins: [],
}
}
