<template>
    <nav class="breadcrumb" v-if="breadcrumbs.length > 0">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item">
                <router-link to="/" class="breadcrumb-link">{{
                    $t("navigation.home")
                }}</router-link>
            </li>
            <li
                v-for="(crumb, index) in breadcrumbs"
                :key="index"
                class="breadcrumb-item"
            >
                <span class="breadcrumb-separator">/</span>
                <router-link
                    v-if="index < breadcrumbs.length - 1"
                    :to="crumb.path"
                    class="breadcrumb-link"
                >
                    {{ crumb.name }}
                </router-link>
                <span v-else class="breadcrumb-current">{{ crumb.name }}</span>
            </li>
        </ol>
    </nav>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { $t } from "@/plugins/i18n";

const route = useRoute();
const breadcrumbs = computed(() => {
    const pathArray = route.path.split("/").filter((path) => path);
    const crumbs = [];

    if (pathArray.length === 0) return [];

    let currentPath = "";

    pathArray.forEach((segment, index) => {
        currentPath += `/${segment}`;

        if (segment === "categories" || segment === "category") {
            return;
        }

        if (segment === "products" || segment === "product") {
            return;
        }

        if (index === 0) {
            crumbs.push({
                name: formatCategoryName(segment),
                path: currentPath,
                type: "category",
            });
        } else if (index === 1) {
            crumbs.push({
                name: getProductName(segment),
                path: currentPath,
                type: "product",
            });
        } else {
            crumbs.push({
                name: formatName(segment),
                path: currentPath,
                type: "page",
            });
        }
    });
    return crumbs;
});

const formatCategoryName = (slug) => {
    return slug.replace(/-/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getProductName = (productSlug) => {
    let name = "";
    if (route.params.productName) {
        name = route.params.productName;
    } else if (route.meta?.productName) {
        name = route.meta.productName;
    } else {
        name = formatName(productSlug);
    }

    const truncated = truncateText(name, 40);
    return truncated;
};

const truncateText = (text, maxLength) => {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + "...";
};

const formatName = (slug) => {
    return slug.replace(/-/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<style scoped>
.breadcrumb {
    margin-bottom: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    list-style: none;
    padding: 0;
    margin: 0;
    flex-wrap: wrap;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-separator {
    margin: 0 0.5rem;
    color: #9ca3af;
    font-size: 0.875rem;
}

.breadcrumb-link {
    color: #6b7280;
    text-decoration: none;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.breadcrumb-link:hover {
    color: var(--accent-clr);
    font-weight: bold;
    background-color: var(--primary-clr);
}

.breadcrumb-current {
    color: #111827;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Mobile responsive */
@media (max-width: 640px) {
    .breadcrumb-list {
        font-size: 0.8rem;
    }

    .breadcrumb-separator {
        margin: 0 0.25rem;
    }

    .breadcrumb-current {
        max-width: 120px; /* Shorter on mobile */
    }
}
</style>
