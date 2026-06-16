import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  base: '/dashboard/',
  plugins: [
    vue({
      template: {
        transformAssetUrls: false
      }
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    outDir: '../../../../public/dashboard',
    emptyOutDir: true,
    rollupOptions: {
      external: ['/dashboard/images/*']
    }
  },
})