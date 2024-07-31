import { createRouter, createWebHistory } from "vue-router";
import AppLayout from "@/layout/AppLayout.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/",
            component: AppLayout,
            children: [
                {
                    path: "/",
                    name: "dashboard",
                    component: () => import("@/views/Dashboard.vue"),
                },
            ],
        },
        {
            path: "/landing",
            name: "landing",
            component: () => import("@/views/pages/Landing.vue"),
        },
        {
            path: "/pages/notfound",
            name: "notfound",
            component: () => import("@/views/pages/NotFound.vue"),
        },

        {
            path: "/auth/login",
            name: "login",
            component: () => import("@/views/pages/auth/Login.vue"),
        },
        {
            path: "/auth/access",
            name: "accessDenied",
            component: () => import("@/views/pages/auth/Access.vue"),
        },
        {
            path: "/auth/error",
            name: "error",
            component: () => import("@/views/pages/auth/Error.vue"),
        },
    ],
});

// router.beforeEach((to, from, next) => {
//     // Check if the route requires authentication and if the user is authenticated
//     if (
//         to.matched.some((record) => record.meta.requiresAuth) &&
//         !store.isAuthenticated
//     ) {
//         next("/login"); // redirect to login page
//     } else {
//         next(); // proceed to route
//     }
// });

export default router;
