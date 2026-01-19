import { createRouter, createWebHistory } from "vue-router";
import { useUserStore } from '@/stores/userStore';


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
    scrollBehavior(to, from, savedPosition) {
        // 1) Back/forward – restore saved position
        if (savedPosition) return savedPosition;

        // 2) Hash anchors – scroll to element (optionally smooth)
        if (to.hash) {
            return {
                el: to.hash,
                behavior: 'smooth', // optional
                // top: 64, // optional offset if you have a fixed header
            };
        }

        // 3) Keep scroll if either route opts out (overlays/panels)
        if (to.meta.preserveScroll || from.meta.preserveScroll) return false;

        // 4) Default – go to top (optionally after a delay/transition)
        return new Promise((resolve) => {
        // wait for your route transition (~300ms) or next frame
        setTimeout(() => resolve({ left: 0, top: 0 }), 300);
        // OR (no delay): resolve({ left: 0, top: 0 })
        // OR (2 RAFs to wait until layout settles):
        // requestAnimationFrame(() => requestAnimationFrame(() => resolve({ top: 0 })))
        });
    }
});

router.beforeEach((to, from, next) => {
    document.title = to.meta.title || to.name || 'Default Title'

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