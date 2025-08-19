import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    // server: {
    //     host: "0.0.0.0", // Listens on all network interfaces
    //     port: 5173, // Default, change if conflicted
    //     hmr: {
    //         host: "192.168.100.248", // Your computer's IP for hot module replacement
    //     },
    // },
});
