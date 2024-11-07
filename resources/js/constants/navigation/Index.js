export default [
    {
        label: "navigation.home",
        items: [
            {
                label: "navigation.dashboard",
                icon: "pi pi-fw pi-home",
                to: "/admin",
                color: "#238E3A",
            },
        ],
    },

    {
        label: "navigation.pages",
        icon: "ti ti-briefcase",
        items: [
            {
                label: "Products",
                icon: "ti ti-packages",
                to: "/admin/products",
                access: "product_view",
            },
            {
                label: "Categories",
                icon: "ti ti-list",
                to: "/admin/categories",
                access: "category_view",
            },
            {
                label: "navigation.users",
                icon: "ti ti-users",
                to: "/admin/users",
                access: "user_view",
            },
            {
                label: "navigation.activity_history",
                icon: "ti ti-history",
                to: "/admin/activity-history",
                access: "activities_view",
            },
            {
                label: "navigation.roles",
                icon: "ti ti-lock",
                to: "/admin/roles",
                access: "role_view",
            },
            {
                label: "navigation.settings",
                icon: "ti ti-settings",
                to: "/admin/settings",
            },
        ],
    },
];
