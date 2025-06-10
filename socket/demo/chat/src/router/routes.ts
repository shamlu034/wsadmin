import {RouteRecordRaw} from 'vue-router';

const chat = () => import('../view/Chat.vue')
const admin = () => import('../view/Admin.vue')

const routes: Array<RouteRecordRaw> = [
    {
        path: '/chat',
        component: chat,
    },
    {
        path: '/admin',
        component: admin,
    },
]
export default routes;

