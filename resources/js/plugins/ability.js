import { defineAbility, PureAbility } from "@casl/ability";

// Create a default empty ability (to be updated later)
export const ability = new PureAbility([]);

// Function to define abilities based on the user
export function defineAbilitiesFor(user) {
    return defineAbility((can) => {
        if (user.roles.name === "Super Admin") {
            can("manage", "all");
            return;
        }
        if (!user.roles.permissions || user.roles.permissions.length === 0) {
            return;
        }
        user.roles.permissions.forEach((permission) => {
            const [subject, action] = permission.name.split("_");
            can(subject, action);
        });
    });
}
