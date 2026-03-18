import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Use env variable for backend URL — set VITE_DEV_API_URL in your .env file
const backendUrl = process.env.VITE_DEV_API_URL || 'http://127.0.0.1:8000'

export default defineConfig({
  plugins: [react()],

  optimizeDeps: {
    include: [
      'react',
      'react-dom',
      'react-router-dom',
      'axios',
      'sweetalert2',
    ],
  },

  server: {
    port: 5173,

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
    chunkSizeWarningLimit: 600,

    rollupOptions: {
      output: {
        manualChunks(id) {

          // React core
          if (
            id.includes('node_modules/react/') ||
            id.includes('node_modules/react-dom/') ||
            id.includes('node_modules/scheduler/')
          ) {
            return 'vendor-react'
          }

          // Router
          if (
            id.includes('node_modules/react-router') ||
            id.includes('node_modules/@remix-run/')
          ) {
            return 'vendor-router'
          }

          // SweetAlert2
          if (id.includes('node_modules/sweetalert2')) {
            return 'vendor-swal'
          }

          // Axios
          if (id.includes('node_modules/axios')) {
            return 'vendor-axios'
          }

          // All other node_modules
          if (id.includes('node_modules/')) {
            return 'vendor-misc'
          }
        },
      },
    },
  },
})