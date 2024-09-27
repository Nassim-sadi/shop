import navigation from "@/constants/navigation/Index";
import { ability } from "@/plugins/ability";
/**
 * Finds the navigation item based on route name.
 * @param {String} routeName - The name of the route.
 * @returns {Object|null} - The matching navigation item or null if not found.
 */
function findNavigationItem(routeName) {
    for (const group of navigation) {
        for (const item of group.items) {
            if (item.to === `/${routeName}` || item.to === routeName) {
                return item;
            }
        }
    }
    return null;
}

export function canNavigate(routeName) {
    const navigationItem = findNavigationItem(routeName);
    if (!navigationItem || !navigationItem.access) {
        return true;
    }
    const [action, subject] = navigationItem.access.split("_");
    return ability.can(action, subject);
}
