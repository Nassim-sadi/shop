export default [
    {
        label: 'Home',
        items: [{ label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/' }]
    },

    {
        label: 'Pages',
        icon: 'pi pi-fw pi-briefcase',
        to: '/pages',
        items: [
            // {
            //     label: 'Landing',
            //     icon: 'pi pi-fw pi-globe',
            //     to: '/landing'
            // },
            {
                label: 'Auth',
                icon: 'pi pi-fw pi-user',
                items: [
                    {
                        label: 'Login',
                        icon: 'pi pi-fw pi-sign-in',
                        to: '/auth/login'
                    },
                    {
                        label: 'Error',
                        icon: 'pi pi-fw pi-times-circle',
                        to: '/auth/error'
                    },
                    {
                        label: 'Access Denied',
                        icon: 'pi pi-fw pi-lock',
                        to: '/auth/access'
                    }
                ]
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
        ]
    },
]
