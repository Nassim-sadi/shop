export const isUserLoggedIn = (auth) =>
    auth ? !!(auth.user && auth.token) : false;
