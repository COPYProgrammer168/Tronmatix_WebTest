import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],

  server: {
    port: 5173,
    proxy: {
      // Intercepts requests to /api/* from localhost:5173
      // and forwards them to Laravel at localhost:8000
      // This avoids CORS entirely — browser sees same-origin requests
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
      // Forward /storage/* so images load without LARAVEL_URL prefix
      '/storage': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },

  build: {
    chunkSizeWarningLimit: 600,
    rollupOptions: {
      output: {
        manualChunks(id) {
          // React core — cache forever, changes never
          if (id.includes('node_modules/react/') ||
              id.includes('node_modules/react-dom/') ||
              id.includes('node_modules/scheduler/')) {
            return 'vendor-react'
          }
          // React Router
          if (id.includes('node_modules/react-router') ||
              id.includes('node_modules/@remix-run/')) {
            return 'vendor-router'
          }
          // SweetAlert2 — ~180 kB, only needed on checkout/orders
          if (id.includes('node_modules/sweetalert2')) {
            return 'vendor-swal'
          }
          // Axios
          if (id.includes('node_modules/axios')) {
            return 'vendor-axios'
          }
          // All remaining node_modules
          if (id.includes('node_modules/')) {
            return 'vendor-misc'
          }
        },
      },
    },
  },
})