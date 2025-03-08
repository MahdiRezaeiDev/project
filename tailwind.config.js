/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./views/**/*.{php,html,js}", // All PHP, HTML, and JS files in views directory and subdirectories
    "./layouts/**/*.{php,html,js}", // All PHP, HTML, and JS files in layouts directory and subdirectories
    "./utilities/**/*.{php,html,js}", // All PHP, HTML, and JS files in layouts directory and subdirectories
    "./**/*.{php,html,js}", // All PHP, HTML, and JS files in the root and subdirectories
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
