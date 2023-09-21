import {createRouter, createWebHashHistory, createWebHistory} from 'vue-router'
import test from '../controllers/test.vue'
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/tms',
            name: 'app_home',
            component: test
        }
    ]
})

export default router
