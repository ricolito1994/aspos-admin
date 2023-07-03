import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default ({mode}) => {
    process.env = {...process.env, ...loadEnv(mode, process.cwd())}

    return defineConfig({
        server :{
            host : '0.0.0.0',
            hmr : {
                clientPort: 5173,
                host: process.env.VITE_APP_HOST,
                protocol: 'ws',
            },
            port : 5173,
            watch : {
                usePolling : true,
            }
        },
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: { 
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
    });
}