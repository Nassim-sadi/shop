import AppLayout from "@/layout/admin/AppLayout.vue";
import Layout from "@/layout/Layout.vue";
import { ability } from "@/plugins/ability";
import { canNavigate } from "@/plugins/canNavigate";
import { authStore } from "@/store/AuthStore";
import { createRouter, createWebHistory } from "vue-router";
import { isUserLoggedIn } from "./utils";
import { $t } from "@/plugins/i18n";
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "/",
            component: Layout,
            meta: { requiresAuth: false },
            children: [
                {
                    path: "",
                    name: "home",
                    component: () => import("@/views/Home.vue"),
                },
                {
                    path: "Categories",
                    name: "categories",
                    component: () => import("@/views/Categories.vue"),
                },
                {
                    path: "about",
                    name: "about",
                    component: () => import("@/views/About.vue"),
                },
                {
                    path: "contact",
                    name: "contact",
                    component: () => import("@/views/Contact.vue"),
                },
                {
                    path: "/:catchAll(.*)",
                    name: "404",
                    component: () => import("@/views/404.vue"),
                },
            ],
        },
        // admin routes
        {
            path: "/admin",
            component: AppLayout,
            meta: { requiresAuth: true, isAdmin: true },
            children: [
                {
                    path: "",
                    name: "dashboard",
                    meta: { title: $t("navigation.dashboard") },
                    component: () => import("@/views/admin/Dashboard.vue"),
                },
                {
                    path: "products",
                    name: "products",
                    meta: { title: $t("navigation.products") },
                    component: () => import("@/views/admin/products/Index.vue"),
                },
                {
                    path: "product-options",
                    name: "product-options",
                    meta: { title: $t("navigation.productOptions") },
                    component: () =>
                        import("@/views/admin/productOptions/Index.vue"),
                },
                {
                    path: "categories",
                    name: "categories",
                    meta: { title: $t("navigation.categories") },
                    component: () =>
                        import("@/views/admin/categories/Index.vue"),
                },
                {
                    path: "settings",
                    name: "settings",
                    meta: { title: $t("navigation.settings") },
                    component: () => import("@/views/admin/settings/Index.vue"),
                },
                {
                    path: "activity-history",
                    name: "activity-history",
                    meta: { title: $t("navigation.activity_history") },
                    component: () =>
                        import("@/views/admin/activityHistories/Index.vue"),
                },
                {
                    path: "users",
                    name: "users",
                    meta: { title: $t("navigation.users") },
                    component: () => import("@/views/admin/users/Index.vue"),
                },
                {
                    path: "roles",
                    name: "roles",
                    meta: { title: $t("navigation.roles") },
                    component: () => import("@/views/admin/roles/Index.vue"),
                },
            ],
        },
        {
            path: "/admin/:catchAll(.*)",
            name: "notfound",
            meta: { title: $t("navigation.not_found") },
            component: () => import("@/views/admin/NotFound.vue"),
        },
        {
            path: "/auth/login",
            name: "login",
            meta: {
                title: $t("navigation.login"),
            },
            component: () => import("@/views/admin/auth/Login.vue"),
        },
        {
            path: "/auth/forgot-password",
            name: "forgot-password",
            meta: {
                title: $t("navigation.forgot_password"),
            },
            component: () => import("@/views/admin/auth/ForgotPassword.vue"),
        },
        {
            path: "/auth/reset-password",
            name: "reset-password",
            meta: {
                title: $t("navigation.reset_password"),
            },
            component: () => import("@/views/admin/auth/ResetPassword.vue"),
        },
        {
            path: "/auth/access",
            name: "accessDenied",
            meta: {
                title: $t("navigation.access_denied"),
            },
            component: () => import("@/views/admin/auth/Access.vue"),
        },
        {
            path: "/auth/error",
            name: "error",
            meta: {
                title: $t("navigation.error"),
            },
            component: () => import("@/views/admin/auth/Error.vue"),
        },
    ],
});

router.beforeEach((to, from, next) => {
    const auth = authStore();
    ability.update(auth.permissions);
    if (to.meta.requiresAuth && !isUserLoggedIn(auth)) {
        return next({ name: "login" });
    }
    if (to.meta.isAdmin && !canNavigate(to.name)) {
        return next({ name: "accessDenied" });
    }
    const publicPages = ["login", "forgot-password", "reset-password"];
    if (publicPages.includes(to.name)) {
        if (isUserLoggedIn(auth)) {
            return next({ name: "dashboard" });
        }
        return next();
    }

    if (to.meta.title) {
        document.title = to.meta.title;
    }

    next();
});

export default router;
