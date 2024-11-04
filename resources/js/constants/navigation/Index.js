export default [
    {
        label: "Home",
        items: [
            {
                label: "Dashboard",
                icon: "pi pi-fw pi-home",
                to: "/admin",
                color: "#238E3A",
            },
        ],
    },

    {
        label: "Pages",
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
                label: "Users",
                icon: "ti ti-users",
                to: "/admin/users",
                access: "user_view",
            },
            {
                label: "Activity History",
                icon: "ti ti-history",
                to: "/admin/activity-history",
                access: "activities_view",
            },
            {
                label: "Roles",
                icon: "ti ti-lock",
                to: "/admin/roles",
                access: "role_view",
            },
            {
                label: "Settings",
                icon: "ti ti-settings",
                to: "/admin/settings",
            },
        ],
    },
];
