import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'assets/dist',
    manifest: true,
    rollupOptions: {
      input: resolve(__dirname, 'src/main.tsx'),
    },
    emptyOutDir: true,
  },
  server: {
    cors: true,
    strictPort: true,
    port: 5173,
    hmr: {
      host: 'localhost',
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, './src'),
    },
  },
});
