import AppLayout from "@/layout/AppLayout.vue";
import { ability, defineAbilitiesFor } from "@/plugins/ability";
import { canNavigate } from "@/plugins/canNavigate";
import { authStore } from "@/store/AuthStore";
import { createRouter, createWebHistory } from "vue-router";
import { isUserLoggedIn } from "./utils";

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
                {
                    path: "/settings",
                    name: "settings",
                    component: () => import("@/views/pages/settings/Index.vue"),
                },
                {
                    path: "/activity-history",
                    name: "activity-history",
                    component: () =>
                        import("@/views/pages/activityHistories/Index.vue"),
                },
                {
                    path: "/users",
                    name: "users",
                    component: () => import("@/views/pages/users/Index.vue"),
                },
                {
                    path: "/roles",
                    name: "roles",
                    component: () => import("@/views/pages/roles/Index.vue"),
                },
            ],
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
            path: "/auth/forgot-password",
            name: "forgot-password",
            component: () => import("@/views/pages/auth/ForgotPassword.vue"),
        },
        {
            path: "/auth/reset-password",
            name: "reset-password",
            component: () => import("@/views/pages/auth/ResetPassword.vue"),
        },
        {
            path: "/NotFound",
            name: "notFound",
            component: () => import("@/views/pages/NotFound.vue"),
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

router.beforeEach((to, from, next) => {
    const auth = authStore();
    ability.update(auth.permissions);
    if (
        to.name !== "login" &&
        to.name !== "forgot-password" &&
        to.name !== "reset-password"
    ) {
        if (isUserLoggedIn(auth)) {
            if (canNavigate(to.name)) {
                next();
            } else {
                next({ name: "accessDenied" });
            }
        } else {
            next({ name: "login" });
        }
    } else {
        if (isUserLoggedIn(auth)) {
            next("/");
        } else {
            next();
        }
    }
});
export default router;
