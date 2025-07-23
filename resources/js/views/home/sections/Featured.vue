<script setup>
import axios from "@/plugins/axios";
import { ref, onMounted } from "vue";
import Product from "@/components/Product.vue";

const products = ref([]);
const loading = ref(false);

const getFeatured = async () => {
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/products/featured")
            .then((res) => {
                console.log("🚀 res.data:", res.data);
                products.value = res.data;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                loading.value = false;
            });
    });
};

onMounted(() => {
    getFeatured();
});
</script>
<template>
    <div class="section-card">
        <h2>{{ $t("home.sections.featured") }}</h2>
        <div class="home-featured-products">
            <div v-if="loading" class="loading">Loading products...</div>
            <div v-if="!loading && products.length > 0" class="products-grid">
                <template v-for="product in products" :key="product.id">
                    <Product :product="product" :hide-featured="true" />
                </template>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
