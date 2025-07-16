<script setup>
import { $t } from "@/plugins/i18n";
import axios from "@/plugins/axios";
import { onMounted, ref } from "vue";
import router from "@/router/Index";

const categories = ref([]);
const loading = ref(false);

// Simplified async function
const getCategories = async () => {
    loading.value = true;
    try {
        const res = await axios.get("api/categories/leaf");
        categories.value = res.data;
        return res.data;
    } catch (err) {
        console.error("Error fetching categories:", err);
        throw err;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    getCategories();
});

const goToCategory = (category) => {
    router.push({
        name: "category",
        params: { categorySlug: category.slug },
    });
};
</script>

<template>
    <div class="by-category">
        <h2>{{ $t("home.sections.by_category") }}</h2>

        <div v-if="loading" class="loading">Loading categories...</div>

        <div class="category-grid" v-else>
            <div
                v-for="category in categories"
                :key="category.id"
                class="category-card"
                @click="goToCategory(category)"
            >
                <div class="category-image-container">
                    <img
                        :src="category.image"
                        :alt="category.name"
                        class="category-image"
                    />
                    <div class="category-title-overlay">
                        <h3>
                            {{ category.name }}
                        </h3>
                        <p class="category-desc">{{ category.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.by-category {
    padding: 1rem;
}

h2 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: bold;
}

.loading {
    text-align: center;
    padding: 20px;
    font-size: 1rem;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (min-width: 768px) {
    .category-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.category-card {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.category-card:hover {
    transform: translateY(-4px);
}

/* Maintain square aspect on small screens */
.category-image-container {
    position: relative;
    width: 100%;
    padding-top: 100%; /* 1:1 aspect ratio */
}

/* Use 4:3 rectangle on wider screens */
@media (min-width: 768px) {
    .category-image-container {
        padding-top: 75%; /* 4:3 aspect ratio */
    }
}

.category-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-title-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 20%;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1rem;
    color: var(--white-clr);
    font-size: 1rem;
    font-weight: bold;
    border-radius: 0 0 8px 8px;
    box-sizing: border-box;
    overflow: hidden;
    transition:
        height 0.3s ease,
        background 0.3s ease;
}

.category-title-overlay h3 {
    margin: 0;
    color: var(--white-clr);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    z-index: 2;
}

.category-title-overlay .category-desc {
    color: var(--white-clr);

    margin-top: 0.5rem;
    font-size: 0.875rem;
    font-weight: normal;
    opacity: 0;
    max-height: 0;
    overflow: hidden;
    transition: opacity 0.3s ease 0.1s;
}

/* Hover effect triggers on the parent card */
.category-card:hover .category-title-overlay {
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    border-radius: 0 0 8px 8px;
}

.category-card:hover .category-title-overlay .category-desc {
    opacity: 1;
    max-height: 100px; /* or enough to show full text */
}
</style>
