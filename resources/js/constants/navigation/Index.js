export default [
    {
        label: "navigation.home",
        items: [
            {
                label: "navigation.dashboard",
                icon: "pi pi-fw pi-home",
                to: "/",
                color: "#238E3A",
            },
        ],
    },

    {
        label: "navigation.pages",
        icon: "ti ti-briefcase",
        items: [
            {
                label: "navigation.users",
                icon: "ti ti-users",
                to: "/admin/users",
                color: "#3366bb",
                access: "user_access",
            },
            {
                label: "navigation.activity_history",
                icon: "ti ti-history",
                to: "/admin/activity-history",
                color: "#830123",
                access: "activities_access",
            },
            {
                label: "navigation.roles",
                icon: "ti ti-lock",
                to: "/admin/roles",
                color: "#238E3A",
                access: "role_access",
            },
            {
                label: "navigation.settings",
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
