<script setup>
const { product } = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

import { useCartStore } from "@/store/CartStore";
const { addToCart } = useCartStore();
</script>
<template>
    <div class="product-card">
        <img
            :src="product.thumbnail_image_path"
            :alt="product.thumbnail_image_path"
            class="product-image"
        />
        <h3 class="product-name truncate">{{ product.name }}</h3>
        <p class="price">${{ product.listing_price }}</p>
        <p class="weight" v-if="product.weight">
            {{ product.weight }} {{ product.weight_unit }}
        </p>
        <button class="add-to-cart-btn" @click.stop="addToCart(product)">
            Add to Cart
        </button>
    </div>
</template>

<style lang="scss" scoped>
/* Products Section Styles */

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
