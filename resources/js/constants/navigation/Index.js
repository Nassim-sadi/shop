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
                label: "Categories",
                icon: "ti ti-list",
                to: "/admin/categories",
                color: "#9C27B0",
                access: "category_view",
            },
            {
                label: "Users",
                icon: "ti ti-users",
                to: "/admin/users",
                color: "#3366bb",
                access: "user_view",
            },
            {
                label: "Activity History",
                icon: "ti ti-history",
                to: "/admin/activity-history",
                color: "#830123",
                access: "activities_view",
            },
            {
                label: "Roles",
                icon: "ti ti-lock",
                to: "/admin/roles",
                color: "#238E3A",
                access: "role_view",
            },
            {
                label: "Settings",
                icon: "ti ti-settings",
                to: "/admin/settings",
                color: "#9C27B0",
            },

            // {
            //     label: 'Crud',
            //     icon: 'pi pi-fw pi-pencil',
            //     to: '/pages/crud'
            // },
            // {
            //     label: 'Not Found',
            //     icon: 'pi pi-fw pi-exclamation-circle',
            //     to: '/pages/notfound'
            // },
        ],
    },
];
