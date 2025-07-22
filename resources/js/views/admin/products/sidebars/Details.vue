<script setup>
import { $t } from "@/plugins/i18n";
import { ref, toRefs, watch } from "vue";
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
import emitter from "@/plugins/emitter";
import axios from "@/plugins/axios";
const currentIndex = ref(0);
const onSwiper = (swiper) => {
    currentIndex.value = swiper.activeIndex;
};

const isLoading = ref(false);
const currentVariants = ref([]);
const getVariants = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get(`api/admin/products/${current.value.id}/variants`)
            .then((res) => {
                console.log("Fetched variants:", res.data);
                currentVariants.value = res.data.variants || [];
                resolve(res.data);
            })
            .catch((err) => {
                console.error("Error fetching variants:", err);
                emitter.emit("toast", {
                    summary: $t("status.error.title"),
                    message: "Failed to load variants",
                    severity: "error",
                });
                currentVariants.value = [];
                reject(err);
            })
            .finally(() => {
                isLoading.value = false;
            });
    });
};

watch(
    () => isOpen.value,
    (val) => {
        if (val) {
            getVariants();
        }
    },
);
</script>
<template>
    <Drawer
        :visible="isOpen"
        :header="$t('products.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="enhanced-drawer large-drawer"
        block-scroll
    >
        <div class="drawer-content">
            <!-- Media Section -->
            <div class="media-section">
                <div class="section-header">
                    <h3 class="section-title">
                        {{ $t("products.thumbnail_image") }}
                    </h3>
                </div>
                <div class="media-container">
                    <img
                        :src="current.thumbnail_image_path"
                        alt="Thumbnail"
                        class="media-image"
                    />
                    <div class="media-overlay"></div>
                </div>
            </div>

            <!-- Basic Info Section -->
            <div class="info-section">
                <div class="section-header">
                    <h3 class="section-title">Product Information</h3>
                </div>
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.name") }}
                        </div>
                        <div class="info-value">{{ current.name }}</div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.description") }}
                        </div>
                        <div class="info-value">{{ current.description }}</div>
                    </div>

                    <div class="info-card full-width">
                        <div class="info-label">
                            {{ $t("products.long_description") }}
                        </div>
                        <div
                            v-html="current.long_description"
                            class="info-value prose overflow-x-scroll"
                        />
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.status") }}
                        </div>
                        <div class="info-value">
                            <span
                                :class="[
                                    'status-badge',
                                    current.status ? 'success' : 'danger',
                                ]"
                            >
                                {{
                                    current.status
                                        ? $t("common.active")
                                        : $t("common.inactive")
                                }}
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.category") }}
                        </div>
                        <div class="info-value category-tag">
                            {{ current.category.name }}
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.featured") }}
                        </div>
                        <div class="info-value">
                            <span
                                :class="[
                                    'info-badge',
                                    current.featured ? 'warning' : 'success',
                                ]"
                            >
                                {{
                                    current.featured
                                        ? $t("products.featured")
                                        : $t("products.not_featured")
                                }}
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.base_quantity") }}
                        </div>
                        <div class="info-value">
                            {{ current.base_quantity }}
                        </div>
                    </div>

                    <div v-if="current.weight" class="info-card">
                        <div class="info-label">
                            {{ $t("products.weight") }}
                        </div>
                        <div class="info-value">
                            {{ current.weight }}
                            <span>
                                {{ current.weight_unit }}
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.base_price") }}
                        </div>
                        <div class="info-value success-value">
                            {{ current.base_price }}

                            <span class="currency">
                                {{ current.currency.code }}
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.listing_price") }}
                        </div>
                        <div class="info-value primary-value large">
                            {{ current.listing_price }}
                            {{ current.currency.code }}
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("products.variants_count") }}
                        </div>
                        <div class="info-value primary-value large">
                            {{ current.variants_count }}
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("common.created_at") }}
                        </div>
                        <div class="info-value secondary-value">
                            {{ current.created_at }}
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-label">
                            {{ $t("common.updated_at") }}
                        </div>
                        <div class="info-value secondary-value">
                            {{ current.updated_at }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Images Gallery -->
            <div class="gallery-section">
                <div class="section-header">
                    <h3 class="section-title">
                        {{ $t("products.images") }}
                    </h3>
                </div>
                <div class="gallery-container">
                    <Swiper
                        :slides-per-view="1"
                        :space-between="10"
                        @swiper="onSwiper"
                        :modules="[Navigation, Pagination]"
                        :pagination="{
                            el: '.swiper-pagination',
                            clickable: true,
                            type: 'bullets',
                            bulletActiveClass:
                                'swiper-pagination-bullet-active',
                        }"
                        :navigation="{
                            nextEl: '.swiper-button-next',
                            prevEl: '.swiper-button-prev',
                            clickable: true,
                        }"
                        class="mySwiper"
                    >
                        <Swiper-slide
                            v-for="item in current.images"
                            :key="item.id"
                        >
                            <img :src="item.url" class="swiper-img" />
                        </Swiper-slide>
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
            </div>

            <div class="mb-4">
                <!-- Skeleton when loading -->
                <div v-if="isLoading">
                    <div
                        v-for="n in 2"
                        :key="n"
                        class="border rounded-lg p-4 mb-3 bg-gray-50 animate-pulse"
                    >
                        <div class="h-4 bg-gray-300 rounded w-1/3 mb-2"></div>
                        <div class="h-4 bg-gray-300 rounded w-1/2 mb-1"></div>
                        <div class="h-4 bg-gray-300 rounded w-1/4 mb-1"></div>
                        <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                    </div>
                </div>

                <!-- Actual content when not loading -->
                <div v-else-if="currentVariants?.length">
                    <p class="font-bold mb-2 capitalize">
                        {{ $t("products.variants.title", 2) }}
                    </p>

                    <div
                        v-for="(variant, index) in currentVariants"
                        :key="variant.id"
                        class="border rounded-lg p-4 mb-3 bg-gray-50"
                    >
                        <p class="text-sm text-gray-500 mb-1">
                            {{ $t("products.variant") }} {{ index + 1 }}
                        </p>

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="font-medium"
                                    >{{ $t("products.price") }}:</span
                                >
                                {{ variant.price }} {{ current.currency.code }}
                            </div>
                            <div>
                                <span class="font-medium"
                                    >{{ $t("products.quantity") }}:</span
                                >
                                {{ variant.quantity }}
                            </div>
                            <div>
                                <span class="font-medium"
                                    >{{ $t("products.status") }} :
                                </span>
                                <span
                                    :class="
                                        variant.status
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{
                                        variant.status
                                            ? $t("common.active")
                                            : $t("common.inactive")
                                    }}
                                </span>
                            </div>
                            <div class="col-span-2">
                                <span class="font-medium"
                                    >{{ $t("products.option_values") }} :
                                </span>
                                <span class="mt-1 text-gray-700">
                                    {{
                                        variant.option_values
                                            .map((opt) => opt.value)
                                            .join(", ")
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped>
.mySwiper {
    aspect-ratio: 1;
    width: 100%;
    border-radius: 1rem;

    .swiper-img {
        width: 100%;
        aspect-ratio: 1/1;
        object-fit: contain;
    }
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
