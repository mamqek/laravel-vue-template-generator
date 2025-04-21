import { createRouter, createWebHistory } from "vue-router";
import { useUserStore } from '@/stores/userStore';
import { t } from '@plugins/lang';


const routes = [
    {
        path: "/auth",
        component: () => import("./pages/Authorization.vue"),
        name: 'Authorization',
        meta : {title : 'sign_in'}
    }, 
    {
        path: "/",
        component: () => import("./pages/Home.vue"),
        name: 'Home',
        meta : {title : 'home'}
    }, 
    // {
    //     path: "/soul-map",
    //     component: () => import("./pages/soulmap/SoulMap.vue"),
    //     name: "Soul Map",
    //     meta: { title: 'soul_map', requiresAuth: true, requiredRole: 'soulUser' }, // This route requires the 'admin' role
    //     children: [
    //         {
    //             path: 'clients',
    //             component: () => import("./pages/soulmap/Clients.vue"),
    //             name: "Clients",
    //             meta: {title: 'clients',}
    //         },
    //         {
    //             path: 'new-client',
    //             component : () => import("./pages/soulmap/NewClient.vue"),
    //             name: "New Client",
    //             meta: {title: 'new_client'}
    //         }
    //     ]
    // },
    {
        path: "/unauthorized",
        component: () => import("./pages/Unauthorized.vue"),
        name: 'Unauthorized',
        meta: {title: 'unauthorized'}

    },
    // {
    //     path: "/item/:id",
    //     component: () => import("./pages/ItemPage.vue"),
    // },
];


const router =  createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    document.title = t(to.meta.title) || to.name || 'Default Title'

    const isAuthenticated = useUserStore().authenticated;
    const userRole = useUserStore().user.role;

    if (to.meta.requiresAuth && !isAuthenticated) {
        // Redirect to unauthorized if the route requires auth and the user isn't authenticated
        return next({ 
            name: 'Unauthorized',
            query: {reason: "authentication"}
        });
    }

    if (to.meta.requiredRole && to.meta.requiredRole !== userRole) {
        // Redirect to unauthorized if the user doesn't have the required role
        return next({ 
            name: 'Unauthorized',
            query: {reason: "role"}
        });
    }

    next(); // Proceed to the route
});

export default router;