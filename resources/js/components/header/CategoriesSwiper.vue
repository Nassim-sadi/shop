<style lang="scss" scoped></style>
<script setup>
import { Swiper, SwiperSlide } from "swiper/vue";
import router from "@/router/Index";
const goToCategory = (category) => {
    router.push({
        name: "category",
        params: { categorySlug: category.slug },
    });
};

const { categories, modelValue } = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [Number, String],
        default: null,
    },
});

// Swiper configuration
const swiperOptions = {
    slidesPerView: "auto",
    spaceBetween: 12,
    freeMode: true,
    grabCursor: true,
};
</script>

<template>
    <div class="category-swiper">
        <Swiper v-bind="swiperOptions" class="swiper-container">
            <SwiperSlide
                v-for="category in categories"
                :key="category.id"
                class="slide-auto"
            >
                <div
                    @click="goToCategory(category)"
                    class="custom-btn"
                    :class="{ active: modelValue === category.id }"
                    role="button"
                    tabindex="0"
                    @keydown.enter="selectCategory(category.id)"
                    @keydown.space.prevent="selectCategory(category.id)"
                >
                    {{ category.name }}
                </div>
            </SwiperSlide>
        </Swiper>
    </div>
</template>

<style lang="scss" scoped>
.category-swiper {
    width: 100%;
    height: auto;
    .swiper-container {
        width: 100%;
        padding: 0 0 1rem 0;
    }

    .slide-auto {
        width: auto !important;
        flex-shrink: 0;
    }

    .category-chip {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border: 1px solid var(--dark-clr); // slate-600
        border-radius: var(--border-radius);
        background: var(--darker-clr); // gray-800
        color: var(--white-clr); // gray-300
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        touch-action: pan-x;

        &:hover {
            background: var(--dark-clr); // blue-900
        }

        &.active {
            background: var(--accent-clr);
            border-color: var(--accent-clr);
            color: var(--white-clr);
        }
    }
}
</style>
