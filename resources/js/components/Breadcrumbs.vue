<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const breadcrumbs = computed(() => {
    const { name, params } = route;

    const crumbs = [];

    // Always start with Home
    crumbs.push({
        name: "Home",
        path: "/",
        type: "home",
    });

    if (name === "category" || name === "product") {
        // Only show category if we are on a category or product page
        if (params.categorySlug) {
            crumbs.push({
                name: formatCategoryName(params.categorySlug),
                path: `/categories/${params.categorySlug}`,
                type: "category",
            });
        }
    }

    if (name === "product" && params.productSlug) {
        crumbs.push({
            name: getProductName(params.productSlug),
            path: `/categories/${params.categorySlug}/${params.productSlug}`,
            type: "product",
        });
    }

    return crumbs;
});

const formatCategoryName = (slug) => {
    return slug.replace(/-/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getProductName = (productSlug) => {
    const name =
        route.params.productName ||
        route.meta?.productName ||
        formatName(productSlug);
    return truncateText(name, 40);
};

const formatName = (slug) => {
    return slug.replace(/-/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const truncateText = (text, maxLength) => {
    return text.length <= maxLength
        ? text
        : text.substring(0, maxLength).trim() + "...";
};
</script>

<template>
    <nav class="breadcrumb" v-if="breadcrumbs.length > 0">
        <ol class="breadcrumb-list">
            <li
                v-for="(crumb, index) in breadcrumbs"
                :key="index"
                class="breadcrumb-item"
            >
                <!-- Add separator only if not the first item -->
                <span v-if="index !== 0" class="breadcrumb-separator">/</span>
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
