import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import { createApp } from 'vue'
import router from './vue/router/router.js'
import {createRouter, createWebHashHistory, createWebHistory} from 'vue-router'


// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

// assets/app.js
import { registerVueControllerComponents } from '@symfony/ux-vue';
import App from "./vue/views/App.vue";

// Registers Vue.js controller components to allow loading them from Twig
//
// Vue.js controller components are components that are meant to be rendered
// from Twig. These component can then rely on other components that won't be
// called directly from Twig.
//
// By putting only controller components in `vue/controllers`, you ensure that
// internal components won't be automatically included in your JS built file if
// they are not necessary.
registerVueControllerComponents(require.context('./vue/views', true, /\.vue$/));

// If you prefer to lazy-load your Vue.js controller components, in order to keep the JavaScript bundle the smallest as possible,
// and improve performance, you can use the following line instead:
//registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/, 'lazy'));
const app = createApp(App);
app.use(router);
app.mount('#page-container')

registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));