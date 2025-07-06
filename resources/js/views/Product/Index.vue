<script setup>
//get slug from route param
import router from "@/router/Index";
import { computed, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import axios from "@/plugins/axios";

const route = useRoute();
const productSlug = computed(() => route.params.productSlug);
const product = ref({});

const getProductBySlug = () => {
    return new Promise((resolve, reject) => {
        axios
            .get(`/api/products/${productSlug.value}`)
            .then((res) => {
                product.value = res.data.product;
                console.log("🚀 ~ .then ~ product res.data:", res.data);
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            });
    });
};

onMounted(async () => {
    await getProductBySlug();
});
</script>

<template>
    <div>
        <pre>

        {{ product }}
      </pre
        >
    </div>
</template>

<style lang="scss" scoped></style>
