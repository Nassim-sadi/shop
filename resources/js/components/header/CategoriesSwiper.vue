<style lang="scss" scoped></style>
<script setup>
import { Swiper, SwiperSlide } from "swiper/vue";

// Props
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

// Emits
const emit = defineEmits(["update:modelValue"]);

// Methods
const selectCategory = (categoryId) => {
    console.log("🚀 ~ selectCategory ~ categoryId:", categoryId);

    // emit("update:modelValue", categoryId);
};

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
                    @click="selectCategory(category.id)"
                    class="category-chip"
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
