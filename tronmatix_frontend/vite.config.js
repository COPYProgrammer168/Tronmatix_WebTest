import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const backendUrl = process.env.VITE_API_URL

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
    allowedHosts: [
      'tronmatix-frontend.onrender.com',
    ],

    proxy: {
      '/api': {
        target: backendUrl,
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path,
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
          if (
            id.includes('node_modules/react/') ||
            id.includes('node_modules/react-dom/') ||
            id.includes('node_modules/scheduler/')
          ) return 'vendor-react'

          if (
            id.includes('node_modules/react-router') ||
            id.includes('node_modules/@remix-run/')
          ) return 'vendor-router'

          if (id.includes('node_modules/sweetalert2')) return 'vendor-swal'
          if (id.includes('node_modules/axios')) return 'vendor-axios'
          if (id.includes('node_modules/')) return 'vendor-misc'
        },
      },
    },
  },
})