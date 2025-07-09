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
import test from "./test.vue";
import { useHead } from "@vueuse/head";

const MetaData = computed(() => {
    return {
        title: product.value.name,
        meta: [
            {
                name: "description",
                content: product.value.description,
            },
            {
                property: "og:title",
                content: product.value.name,
            },
            {
                property: "og:image",
                content: product.value.thumbnail_image_path,
            },
        ],
    };
});

useHead(MetaData);
</script>

<template>
    <div>
        <test :product="product"></test>
    </div>
</template>

<style lang="scss" scoped></style>
