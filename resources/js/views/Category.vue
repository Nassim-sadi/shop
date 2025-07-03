<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "@/plugins/axios";

const route = useRoute();
const router = useRouter();
const category = ref(null);
const products = ref([]);
const loading = ref(false);

const categorySlug = computed(() => route.params.categorySlug);
const page = 1;

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
            getCategoryData(newSlug);
            getCategoryProducts(newSlug);
        }
    },
    { immediate: true },
);
</script>

<template>
    <div v-if="loading" class="loading">Loading...</div>

    <div v-else-if="category" class="category-page">
        <!-- CATEGORY HEADER SECTION -->
        <div class="category-header">
            <h1>{{ category.name }}</h1>
            <p v-if="category.description">{{ category.description }}</p>
            <img
                v-if="category.image"
                :src="category.image"
                :alt="category.name"
                class="category-banner"
            />
        </div>

        <!-- PRODUCTS SECTION -->
        <div class="products-section">
            <h2>Products in {{ category.name }}</h2>

            <div v-if="products.length > 0" class="products-grid">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="product-card"
                >
                    <img
                        :src="product.thumbnail_image_path"
                        :alt="product.thumbnail_image_path"
                        class="product-image"
                    />
                    <h3>{{ product.name }}</h3>
                    <p class="price">${{ product.listing_price }}</p>
                    <p class="weight" v-if="product.weight">
                        {{ product.weight }} {{ product.weight_unit }}
                    </p>
                    <button class="add-to-cart-btn">Add to Cart</button>
                </div>
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

.category-banner {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 12px;
    margin-top: 20px;
}

/* Products Section Styles */
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

.product-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    background: white;
}

.product-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 16px;
}

.product-card h3 {
    font-size: 1.2rem;
    margin-bottom: 12px;
    color: #1f2937;
}

.price {
    font-size: 1.4rem;
    font-weight: bold;
    color: #2563eb;
    margin: 12px 0;
}

.add-to-cart-btn {
    background-color: #10b981;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s ease;
}

.add-to-cart-btn:hover {
    background-color: #059669;
}

.no-products {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.loading,
.error {
    text-align: center;
    padding: 60px 20px;
    font-size: 1.1rem;
}

.error {
    color: #ef4444;
}
</style>
