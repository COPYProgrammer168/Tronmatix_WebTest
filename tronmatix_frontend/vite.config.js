// vite.config.js

import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react-oxc' // faster than plugin-react (no Babel)

// Backend URL for dev proxy — set in .env.local:
//   VITE_DEV_API_URL=http://127.0.0.1:8000
const backendUrl = process.env.VITE_DEV_API_URL || 'http://127.0.0.1:8000'

export default defineConfig({
  plugins: [react()],

  // Pre-bundle deps to avoid re-optimization on every cold start
  optimizeDeps: {
    include: [
      'react',
      'react-dom',
      'react-router-dom',
      'axios',
      'sweetalert2',
      'qrcode.react',
    ],
  },

  server: {
    port: 5173,

    // Dev proxy — forwards /api/* and /storage/* to Laravel backend
    // This avoids CORS issues in development
    proxy: {
      '/api': {
        target: backendUrl,
        changeOrigin: true,
        secure: false,
      },
      '/storage': {
        target: backendUrl,
        changeOrigin: true,
        secure: false,
      },
    },
  },

  build: {
    // Output to dist/ (Render Static Site publish directory)
    outDir: 'dist',
    chunkSizeWarningLimit: 600,

    rollupOptions: {
      output: {
        // Split vendor chunks for better browser caching
        manualChunks(id) {
          if (
            id.includes('node_modules/react/') ||
            id.includes('node_modules/react-dom/') ||
            id.includes('node_modules/scheduler/')
          ) {
            return 'vendor-react'
          }
          if (
            id.includes('node_modules/react-router') ||
            id.includes('node_modules/@remix-run/')
          ) {
            return 'vendor-router'
          }
          if (id.includes('node_modules/sweetalert2')) {
            return 'vendor-swal'
          }
          if (id.includes('node_modules/axios')) {
            return 'vendor-axios'
          }
          if (id.includes('node_modules/qrcode')) {
            return 'vendor-qr'
          }
          if (id.includes('node_modules/')) {
            return 'vendor-misc'
          }
        },
      },
    },
  },
})