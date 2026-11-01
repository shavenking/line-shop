import { writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        publicDir: false,
        define: {
            'process.env.MIX_PUSHER_APP_KEY': JSON.stringify(env.MIX_PUSHER_APP_KEY ?? ''),
            'process.env.MIX_PUSHER_APP_CLUSTER': JSON.stringify(env.MIX_PUSHER_APP_CLUSTER ?? ''),
        },
        build: {
            outDir: 'public/js',
            emptyOutDir: false,
            lib: {
                entry: resolve(import.meta.dirname, 'resources/js/app.js'),
                name: 'app',
                formats: ['iife'],
                fileName: () => 'app.js',
            },
            rollupOptions: {
                output: {
                    // pusher-js still refers to the Node global in the browser bundle.
                    intro: 'const global = globalThis;',
                },
            },
        },
        plugins: [
            {
                name: 'mix-manifest',
                closeBundle() {
                    writeFileSync(
                        resolve(import.meta.dirname, 'public/mix-manifest.json'),
                        JSON.stringify({ '/js/app.js': '/js/app.js' }, null, 4) + '\n',
                    );
                },
            },
        ],
    };
});
