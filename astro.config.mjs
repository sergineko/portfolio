import { defineConfig } from 'astro/config';
export default defineConfig({
  site: 'https://sergiotech.es',
  output: 'static',
  trailingSlash: 'always',
  build: { inlineStylesheets: 'always' },
});
