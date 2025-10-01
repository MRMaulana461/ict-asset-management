/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'saipem-primary': '#2a434e',
        'saipem-accent': '#e87722',
      }
    },
  },
  plugins: [],
}