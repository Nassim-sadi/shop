<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import router from "@/router/Index";
import axios from "@/plugins/axios";
import Product from "@/components/Product.vue";

const route = useRoute();
const category = ref(null);
const products = ref([]);
const loading = ref(false);
const categorySlug = computed(() => route.params.categorySlug);
const page = 1;

const goToProduct = (product) => {
    console.log("clicking");

    router.push({
        name: "product",
        params: {
            categorySlug: categorySlug.value, // You need this
            productSlug: product.slug, // And this
        },
    });
};
const getCategoryData = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get(`/api/categories/${categorySlug.value}`)
            .then((res) => {
                category.value = res.data.category;
                console.log("🚀 ~ .then ~cat res.data:", res.data);

                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            });
    });
};

const getCategoryProducts = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get(
                `/api/categories/${categorySlug.value}/products?page=${page.value}`,
            )
            .then((res) => {
                products.value = res.data.products;
                console.log("🚀 ~ .then ~ products res.data:", res.data);
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            });
    });
};

watch(
    categorySlug,
    (newSlug) => {
        if (newSlug) {
            console.log("🚀 ~ .then ~ newSlug:", newSlug);
            getCategoryData(newSlug);
            getCategoryProducts(newSlug);
        }
    },
    { immediate: true },
);

import { useHead } from "@vueuse/head";

useHead({
    title: category.value?.name,
    meta: [
        {
            name: "description",
            content: category.value?.description,
        },
        {
            property: "og:title",
            content: category.value?.name,
        },
        {
            property: "og:image",
            content: category.value?.image,
        },
    ],
});
</script>

<template>
    <div v-if="loading" class="loading">Loading...</div>

    <div v-else-if="category" class="category-page">
        <!-- CATEGORY HEADER SECTION -->
        <div class="category-header">
            <h1>{{ category.name }}</h1>
            <p v-if="category.description">{{ category.description }}</p>
            <div v-if="category.image" class="category-banner-wrapper">
                <img
                    :src="category.image"
                    :alt="category.name"
                    class="category-banner-bg"
                />
                <img
                    :src="category.image"
                    :alt="category.name"
                    class="category-banner"
                />
            </div>
        </div>

        <!-- PRODUCTS SECTION -->
        <div class="products-section">
            <h2>Products in {{ category.name }}</h2>

            <div v-if="products.length > 0" class="products-grid">
                <template v-for="product in products" :key="product.id">
                    <Product :product="product" @click="goToProduct(product)" />
                </template>
            </div>

            <div v-else class="no-products">
                <p>No products available in this category yet.</p>
            </div>
        </div>
    </div>

    <div v-else class="error">
        <p>Category not found.</p>
    </div>
</template>

<style scoped>
.category-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    width: 100%;
}

/* Category Header Styles */
.category-header {
    text-align: center;
    margin-bottom: 50px;
    padding-bottom: 30px;
    border-bottom: 2px solid #e5e7eb;
}

.category-header h1 {
    font-size: 2.5rem;
    color: #1f2937;
    margin-bottom: 16px;
}

.category-header p {
    font-size: 1.1rem;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto 20px;
}

.category-banner-wrapper {
    position: relative;
    width: 100%;
    max-height: 300px;
    overflow: hidden;
    border-radius: 12px;
}

/* Blurred background */
.category-banner-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: blur(20px) brightness(0.6);
    transform: scale(1.1);
    z-index: 1;
}

/* Foreground image */
.category-banner {
    position: relative;
    z-index: 2;
    display: block;
    margin: auto;
    max-height: 300px;
    object-fit: contain;
}

.products-section h2 {
    font-size: 2rem;
    margin-bottom: 30px;
    color: #1f2937;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}
</style>
