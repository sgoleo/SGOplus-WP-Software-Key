import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'assets/dist',
    manifest: true,
    emptyOutDir: true,
    cssCodeSplit: false,  // Emit a single CSS file for the whole bundle.
    rollupOptions: {
      input: resolve(__dirname, 'src/main.tsx'),
      output: {
        // IIFE: self-contained, no import/export — works with wp_enqueue_script.
        format: 'iife',
        inlineDynamicImports: true,
        // Predictable filenames so the manifest stays stable.
        entryFileNames: 'assets/main.js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name].[ext]',
      },
    },
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
