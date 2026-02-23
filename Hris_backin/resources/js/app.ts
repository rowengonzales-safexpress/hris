import '../css/app.css';
import './bootstrap';
import './scss/main.scss'

import { createPinia } from 'pinia'
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import SkeletonLoader from './Components/SkeletonLoader.vue'
import { useGlobalStore } from './store/global-store'
import * as mdiIcons from '@mdi/js'



import html2pdf from "html2pdf.js";


const appName =  'SLI-CORE';

const pinia = createPinia()
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Expose MDI icons globally for template usage
        (app.config.globalProperties as any).$mdi = mdiIcons

        app.use(plugin)
           .use(html2pdf)
           .use(pinia)
           .use(ZiggyVue)
           .component('SkeletonLoader', SkeletonLoader)
           .mount(el);

        // Initialize theme on app startup
        const globalStore = useGlobalStore()
        globalStore.initializeTheme()
    },
    progress: {
        color: '#4B5563',
    },
});
