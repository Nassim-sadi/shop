<script setup>
import { $t } from "@/plugins/i18n";
import { ref, toRefs } from "vue";
const props = defineProps({
    current: {
        required: true,
        type: Object,
    },
    isOpen: {
        required: true,
        type: Boolean,
    },
    loading: {
        type: Boolean,
        required: false,
    },
});

const $emit = defineEmits(["update:isOpen"]);

const { isOpen, current } = toRefs(props);
import { Swiper, SwiperSlide } from "swiper/vue";
import { Navigation, Pagination } from "swiper/modules";

const currentIndex = ref(0);
const onSwiper = (swiper) => {
    currentIndex.value = swiper.activeIndex;
};
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('products.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="small-drawer"
        block-scroll
    >
        <div class="mb-4">
            <p class="font-bold">{{ $t("products.thumbnail_image") }}</p>
            <div
                class="flex items-center gap-2 mt-2 rounded-xl overflow-hidden"
            >
                <img
                    :src="current.thumbnail_image_path"
                    shape="circle"
                    size="large"
                    class="w-full object-cover"
                />
            </div>
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.name") }} :</p>
            {{ current.name }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.description") }} :</p>
            {{ current.description }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.long_description") }} :</p>
            {{ current.long_description }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.status") }} :</p>
            {{ current.status ? $t("common.active") : $t("common.inactive") }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.featured") }} :</p>
            {{
                current.featured
                    ? $t("products.featured")
                    : $t("products.not_featured")
            }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.base_quantity") }} :</p>
            {{ current.base_quantity }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.base_price") }} :</p>
            {{ current.base_price }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.listing_price") }} :</p>
            {{ current.listing_price }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.featured") }} :</p>
            {{
                current.featured
                    ? $t("products.featured")
                    : $t("products.not_featured")
            }}
        </div>
        <div class="mb-4">
            <p class="font-bold">{{ $t("common.created_at") }} :</p>
            {{ current.created_at }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("common.updated_at") }} :</p>
            {{ current.updated_at }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("products.images") }}</p>
            <Swiper
                :slides-per-view="1"
                :space-between="10"
                @swiper="onSwiper"
                :modules="[Navigation, Pagination]"
                :pagination="{
                    el: '.swiper-pagination',
                    clickable: true,
                    type: 'bullets',
                    bulletActiveClass: 'swiper-pagination-bullet-active',
                }"
                :navigation="{
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                    clickable: true,
                }"
                class="mySwiper"
            >
                <SwiperSlide v-for="item in current.images" :key="item.id">
                    <img
                        :src="item.url"
                        class="w-full aspect-square bg-cover"
                    />
                </SwiperSlide>
                <div class="swiper-controller">
                    <Button
                        class="swiper-button-prev"
                        icon="ti ti-chevron-left"
                        rounded
                    ></Button>
                    <div class="swiper-pagination"></div>

                    <Button
                        class="swiper-button-next"
                        icon="ti ti-chevron-right"
                        rounded
                    ></Button>
                </div>
            </Swiper>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped>
.mySwiper {
    aspect-ratio: 1;
    width: 100%;
    border-radius: 1rem;
}
.swiper-controller {
    position: relative;
    bottom: 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.mySwiper .swiper-button-next,
.mySwiper .swiper-button-prev {
    --swiper-navigation-color: var(--primary-color);
    width: 2.5rem;
    &:after {
        display: none;
    }
}

:deep(.swiper-pagination) {
    position: relative;
    bottom: 1rem;
    flex: 1;

    .swiper-pagination-bullet {
        filter: drop-shadow(0px 8px 24px rgba(18, 28, 53, 0.1));
        width: 0.5rem;
    }

    .swiper-pagination-bullet-active {
        background: var(--primary-color);
    }
}
</style>
