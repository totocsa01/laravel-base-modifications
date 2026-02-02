import { defineConfig, loadEnv } from 'vite'
import fs from 'fs'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd())
    const protocol = env.VITE_PROTOCOL
    const host = env.VITE_HOST
    const port = Number(env.VITE_PORT)

    return {
        server: {
            host,
            port,
            strictPort: env.VITE_STRICT_PORT === 'true',
            https: env.VITE_PROTOCOL === 'https'
                ? {
                    key: fs.readFileSync(env.VITE_HTTPS_KEY),
                    cert: fs.readFileSync(env.VITE_HTTPS_CERT),
                } : false,
            hmr: {
                protocol: protocol === 'https' ? 'wss' : 'ws',
                host,
                port,
            },
            cors: {
                origin: true,
                credentials: true,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
        plugins: [
            laravel({
                server: {
                    origin: `${protocol}://${host}:${port}`,
                },
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
    }
})
