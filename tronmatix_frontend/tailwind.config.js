/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        primary: "#F97316",
        dark: "#111111",
        darker: "#0a0a0a",
        "dark-card": "#1a1a1a",
        "dark-border": "#2a2a2a",
      },
      fontFamily: {
        // Latin / branding fonts
        hurst: ["HurstBagod", "sans-serif"],
        body: ["Rajdhani", "sans-serif"],
        // Khmer heading font (bold, display titles)
        "Kh_Jrung_Thom": ["Kh_Jrung_Thom", "Khmer OS", "sans-serif"],
        // Khmer body font (readable UI text, inputs, labels)
        "Kdam Thmor Pro": ["Kdam Thmor Pro", "Khmer OS", "sans-serif"],
      },
    },
  },
  plugins: [],
};