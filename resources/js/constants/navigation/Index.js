export default [
    {
        label: "Home",
        items: [
            {
                label: "Dashboard",
                icon: "pi pi-fw pi-home",
                to: "/",
                color: "#238E3A",
            },
        ],
    },

    {
        label: "Pages",
        icon: "pi pi-fw pi-briefcase",
        to: "/pages",
        items: [
            // {
            //     label: "Auth",
            //     icon: "pi pi-fw pi-user",
            //     items: [
            //         {
            //             label: "Login",
            //             icon: "pi pi-fw pi-sign-in",
            //             to: "/auth/login",
            //         },
            //         {
            //             label: "Error",
            //             icon: "pi pi-fw pi-times-circle",
            //             to: "/auth/error",
            //         },
            //         {
            //             label: "Access Denied",
            //             icon: "pi pi-fw pi-lock",
            //             to: "/auth/access",
            //         },
            //     ],
            // },
            {
                label: "Settings",
                icon: "pi pi-fw pi-cog",
                to: "/settings",
                color: "#9C27B0",
            },
            {
                label: "Activity History",
                icon: "pi pi-fw pi-list",
                to: "/activity-history",
                color: "#830123",
            },

            {
                label: "Users",
                icon: "pi pi-fw pi-users",
                to: "/users",
                color: "#3366bb",
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
            // {
            //     label: 'Empty',
            //     icon: 'pi pi-fw pi-circle-off',
            //     to: '/pages/empty'
            // }
        ],
    },
];
