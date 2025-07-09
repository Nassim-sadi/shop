<script setup>
import { $t } from "@/plugins/i18n";
import { ref, computed, toRefs, onMounted, watch, nextTick } from "vue";

// Reactive data
const selectedVariant = ref(0);
const isWishlisted = ref(false);
const rating = ref(4.5);
const reviewCount = ref(2847);

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const { product } = toRefs(props);

const toggleWishlist = () => {
    isWishlisted.value = !isWishlisted.value;
};

const addToCart = () => {
    // Add to cart logic here
    console.log("Added to cart:", {
        product: product.value.name,
        variant: currentVariant.value,
        quantity: quantity.value,
        price: 0,
    });
    alert(
        `Added ${quantity.value} × ${product.value.name} (${currentVariant.value.name}) to cart!`,
    );
};

const discountedPrice = computed(() => {
    if (!currentVariant.value) return 0;
    return (
        parseFloat(currentVariant.value.price) *
        (1 - currentVariant.value.discount / 100)
    ).toFixed(2);
});

const buyNow = () => {
    // Buy now logic here
    console.log("Buy now:", {
        product: product.value.name,
        variant: currentVariant.value.name,
        quantity: quantity.value,
        price: discountedPrice.value,
    });
    alert(
        `Proceeding to checkout with ${quantity.value} × ${product.value.name} (${currentVariant.value.name})`,
    );
};

// Option names mapping
const optionNames = computed(() => {
    const names = {};
    product.value.options?.forEach((option) => {
        names[option.id] = option.name;
    });
    return names;
});

const selectedOptions = ref({});
const currentVariant = ref(null);
const quantity = ref(1);

// Get all unique options grouped by product_option_id
const groupedOptions = computed(() => {
    const grouped = {};

    product.value.variants?.forEach((variant) => {
        variant.option_values.forEach((optionValue) => {
            const optionId = optionValue.product_option_id;
            if (!grouped[optionId]) {
                grouped[optionId] = [];
            }

            // Check if this option value is already added
            const exists = grouped[optionId].find(
                (item) => item.id === optionValue.id,
            );
            if (!exists) {
                grouped[optionId].push(optionValue);
            }
        });
    });

    return grouped;
});

// Find variant based on selected options
const findVariant = (selectedOpts) => {
    return product.value.variants?.find((variant) => {
        const variantOptionIds = variant.option_values
            .map((ov) => ov.id)
            .sort();
        const selectedOptionIds = Object.values(selectedOpts).sort();

        return (
            variantOptionIds.length === selectedOptionIds.length &&
            variantOptionIds.every(
                (id, index) => id === selectedOptionIds[index],
            )
        );
    });
};

// Handle option selection
const selectOption = (optionId, valueId) => {
    selectedOptions.value = {
        ...selectedOptions.value,
        [optionId]: valueId,
    };
    nextTick(() => {
        autoSelectNextAvailableOption(optionId);
    });

    updateCurrentVariant();
};

// Update current variant
const updateCurrentVariant = () => {
    const requiredOptions = Object.keys(groupedOptions.value);
    const selectedOptionKeys = Object.keys(selectedOptions.value);

    if (requiredOptions.length === selectedOptionKeys.length) {
        const variant = findVariant(selectedOptions.value);
        currentVariant.value = variant;
        quantity.value = 1; // Reset quantity when variant changes
    } else {
        currentVariant.value = null;
    }
};

// Auto-select single options
const autoSelectFirstValidVariant = () => {
    const firstVariant = product.value?.variants?.[0];
    if (!firstVariant) return;

    const initialSelection = {};
    firstVariant.option_values.forEach((ov) => {
        initialSelection[ov.product_option_id] = ov.id;
    });

    selectedOptions.value = initialSelection;
    updateCurrentVariant();
};

const autoSelectNextAvailableOption = (justSelectedOptionId) => {
    const optionIds = Object.keys(groupedOptions.value);
    const nextIndex = optionIds.indexOf(justSelectedOptionId) + 1;
    const nextOptionId = optionIds[nextIndex];

    if (!nextOptionId) return;

    const options = groupedOptions.value[nextOptionId];
    const available = options.find((opt) =>
        isOptionAvailable(nextOptionId, opt.id),
    );

    if (available) {
        selectedOptions.value[nextOptionId] = available.id;
        updateCurrentVariant(); // Refresh variant
    }
};

const isOptionAvailable = (optionId, valueId) => {
    const optionIds = Object.keys(groupedOptions.value);
    const currentIndex = optionIds.indexOf(optionId);

    const simulated = { [optionId]: valueId };

    // Merge in previous options only
    for (let i = 0; i < currentIndex; i++) {
        const prevId = optionIds[i];
        if (selectedOptions.value[prevId]) {
            simulated[prevId] = selectedOptions.value[prevId];
        }
    }

    // Validate partial combination
    return variantCombinations.value.some((combo) =>
        Object.entries(simulated).every(
            ([optId, valId]) => combo[optId] === valId,
        ),
    );
};

const variantCombinations = computed(() =>
    product.value?.variants?.map((variant) => {
        const combo = {};
        variant.option_values.forEach((ov) => {
            combo[ov.product_option_id] = ov.id;
        });
        return combo;
    }),
);

const getOptionClasses = (optionId, valueId) => {
    const isSelected = selectedOptions.value[optionId] === valueId;
    const isAvailable = isOptionAvailable(optionId, valueId);

    return [
        "px-4 py-2 rounded-lg border-2 flex items-center",
        "transition-all duration-200 ease-in-out",
        "transform", // Enable transform animations
        isSelected && "border-orange-500 text-orange-700 bg-orange-100/70",
        !isSelected &&
            "border-gray-200 text-gray-900 bg-white hover:border-gray-300",
        !isAvailable && "opacity-50 cursor-not-allowed pointer-events-none",
    ];
};
// Update quantity
const updateQuantity = (change) => {
    const newQuantity = quantity.value + change;
    if (
        newQuantity >= 1 &&
        (!currentVariant.value || newQuantity <= currentVariant.value.quantity)
    ) {
        quantity.value = newQuantity;
    }
};

// Get selected option name
const getSelectedOptionName = (optionId) => {
    const selectedId = selectedOptions.value[optionId];
    if (!selectedId) return "Select";

    const option = groupedOptions.value[optionId].find(
        (opt) => opt.id === selectedId,
    );
    return option ? option.value : "Select";
};

// Total price
const totalPrice = computed(() => {
    if (!currentVariant.value) return;
    return (parseFloat(currentVariant.value.price) * quantity.value).toFixed(2);
});

const haveVariants = computed(() => {
    return (
        Array.isArray(product.value?.variants) &&
        product.value.variants.length > 0
    );
});

const images = computed(() => {
    // add thumbnail image at the beginning
    return [
        {
            url: product.value.thumbnail_image_path,
            alt: product.value.name,
        },
        // ...product.value.images,
        // map images url and alt text
        ...(product.value?.images?.map((image) => ({
            url: image.url,
            alt: image.alt,
        })) || []),
    ];
});
const selectedImageIndex = ref(0);
const selectedImage = computed(() => {
    return images.value[selectedImageIndex.value];
});

// Zoom image function
const containerRef = ref(null);
const imgRef = ref(null);
const zoomImage = (e) => {
    const bounds = containerRef.value.getBoundingClientRect();
    const x = ((e.clientX - bounds.left) / bounds.width) * 100;
    const y = ((e.clientY - bounds.top) / bounds.height) * 100;

    imgRef.value.style.transformOrigin = `${x}% ${y}%`;
    imgRef.value.style.transform = "scale(2)";
};

const resetZoom = () => {
    imgRef.value.style.transform = "scale(1)";
    imgRef.value.style.transformOrigin = "center";
};

// Initialize component

watch(
    () => product.value,
    (val) => {
        if (val) {
            autoSelectFirstValidVariant();
        }
    },
    { immediate: true },
);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <pre>{{ product }}</pre>
        {{ variantCombinations }}
        <!-- Breadcrumb -->
        <div class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 py-3">
                <nav class="text-sm text-gray-600">
                    <span class="hover:text-orange-600 cursor-pointer"
                        >Home</span
                    >
                    <span class="mx-2">&gt;</span>
                    <span class="hover:text-orange-600 cursor-pointer">{{
                        product.category?.name
                    }}</span>
                    <span class="mx-2">&gt;</span>
                    <span class="text-gray-900">{{ product.name }}</span>
                </nav>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="lg:sticky lg:top-6 lg:self-start">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div
                            class="aspect-square bg-gray-100 relative group"
                            ref="containerRef"
                        >
                            <img
                                :src="selectedImage.url"
                                :alt="selectedImage.alt"
                                class="w-full h-full object-contain"
                                ref="imgRef"
                                @mousemove="zoomImage"
                                @mouseleave="resetZoom"
                            />
                            <div
                                class="absolute top-4 right-4 flex flex-col gap-2"
                            >
                                <button
                                    @click="toggleWishlist"
                                    :class="[
                                        'w-10 aspect-square rounded-full shadow-md hover:shadow-lg transition-all',
                                        isWishlisted
                                            ? 'bg-red-500 text-white'
                                            : 'bg-white text-gray-600',
                                    ]"
                                >
                                    <i
                                        class="ti ti-heart"
                                        :style="{
                                            color: isWishlisted
                                                ? 'currentColor'
                                                : 'none',
                                        }"
                                    ></i>
                                </button>

                                <button
                                    class="w-10 aspect-square bg-white text-gray-600 rounded-full shadow-md hover:shadow-lg transition-all"
                                >
                                    <i class="ti ti-share"></i>
                                </button>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex gap-2 overflow-x-auto">
                                <button
                                    v-for="(image, index) in images"
                                    :key="image.id"
                                    @click="selectedImageIndex = index"
                                    @mouseenter="selectedImageIndex = index"
                                    :class="[
                                        'flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all',
                                        selectedImageIndex === index
                                            ? 'border-orange-500'
                                            : 'border-gray-200 hover:border-gray-300',
                                    ]"
                                >
                                    <img
                                        :src="image.url"
                                        :alt="image.alt_text"
                                        class="w-full h-full object-cover"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="space-y-4">
                            <h1
                                class="text-2xl lg:text-3xl font-bold text-gray-900"
                            >
                                {{ product.name }}
                            </h1>
                            <div>
                                <template v-if="haveVariants">
                                    <h2 class="text-2xl font-bold mb-6">
                                        {{ $t("products.options") }}
                                    </h2>

                                    <!-- Price Display -->
                                    <div class="mb-6">
                                        <div
                                            v-if="currentVariant"
                                            class="space-y-2"
                                        >
                                            <div
                                                class="flex items-baseline gap-2"
                                            >
                                                <span
                                                    class="text-3xl font-bold text-green-600"
                                                >
                                                    ${{
                                                        parseFloat(
                                                            currentVariant.price,
                                                        ).toFixed(2)
                                                    }}
                                                </span>

                                                <span
                                                    v-if="
                                                        currentVariant.discount
                                                    "
                                                    class="text-lg text-gray-500 line-through"
                                                >
                                                    ${{
                                                        (
                                                            parseFloat(
                                                                currentVariant.price,
                                                            ) /
                                                            (1 -
                                                                currentVariant.discount /
                                                                    100)
                                                        ).toFixed(2)
                                                    }}
                                                </span>
                                                <span
                                                    v-if="
                                                        currentVariant.discount
                                                    "
                                                    class="text-sm bg-red-100 text-red-800 px-2 py-1 rounded"
                                                >
                                                    -{{
                                                        currentVariant.discount
                                                    }}%
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                Price includes GST. FREE
                                                delivery available.
                                            </p>
                                        </div>
                                        <div v-else class="text-gray-500">
                                            Select all options to see price
                                        </div>
                                    </div>
                                    <!-- Option Selectors -->
                                    <div class="space-y-6">
                                        <div
                                            v-for="(
                                                options, optionId
                                            ) in groupedOptions"
                                            :key="optionId"
                                            class="space-y-3"
                                        >
                                            <h3
                                                class="text-lg font-semibold capitalize"
                                            >
                                                {{ optionNames[optionId] }}
                                            </h3>

                                            <div class="flex gap-2 flex-wrap">
                                                <button
                                                    v-for="option in options"
                                                    :key="option.id"
                                                    @click="
                                                        selectOption(
                                                            optionId,
                                                            option.id,
                                                        )
                                                    "
                                                    :disabled="
                                                        !isOptionAvailable(
                                                            optionId,
                                                            option.id,
                                                        )
                                                    "
                                                    :class="
                                                        getOptionClasses(
                                                            optionId,
                                                            option.id,
                                                        )
                                                    "
                                                >
                                                    <span>{{
                                                        option.value
                                                    }}</span>

                                                    <transition
                                                        name="fade-scale"
                                                    >
                                                        <span
                                                            v-if="
                                                                selectedOptions[
                                                                    optionId
                                                                ] === option.id
                                                            "
                                                            class="ml-2 inline-block"
                                                        >
                                                            ✓
                                                        </span>
                                                    </transition>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Quantity Selector -->
                                <div
                                    v-if="currentVariant"
                                    class="mt-6 space-y-4"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center">
                                            <span
                                                class="text-sm font-medium mr-3"
                                                >Qty:</span
                                            >
                                            <div
                                                class="flex items-center border border-gray-300 rounded-lg"
                                            >
                                                <button
                                                    @click="updateQuantity(-1)"
                                                    :disabled="quantity <= 1"
                                                    class="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                                >
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M20 12H4"
                                                        />
                                                    </svg>
                                                </button>
                                                <span
                                                    class="px-4 py-2 text-center min-w-[50px]"
                                                    >{{ quantity }}</span
                                                >
                                                <button
                                                    @click="updateQuantity(1)"
                                                    :disabled="
                                                        quantity >=
                                                        currentVariant.quantity
                                                    "
                                                    class="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                                >
                                                    <svg
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-600">
                                            {{ currentVariant.quantity }}
                                            available
                                        </span>
                                    </div>

                                    <!-- Total Price -->
                                    <div class="text-right">
                                        <span class="text-lg font-semibold">
                                            Total: ${{ totalPrice }}
                                        </span>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex gap-3">
                                            <button
                                                @click="addToCart"
                                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 px-6 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2"
                                            >
                                                <svg
                                                    class="w-5 h-5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2-2m2 2l2 2M7 13v6a2 2 0 002 2h6a2 2 0 002-2v-6"
                                                    />
                                                </svg>
                                                Add to Cart
                                            </button>
                                            <button
                                                @click="buyNow"
                                                class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white py-3 px-6 rounded-lg font-semibold transition-colors"
                                            >
                                                Buy Now
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Variant Info (for debugging) -->
                                <div
                                    v-if="currentVariant"
                                    class="mt-6 p-4 bg-gray-50 rounded-lg"
                                >
                                    <h4 class="font-semibold mb-2">
                                        Selected Variant:
                                    </h4>
                                    <div class="text-sm text-gray-600">
                                        <p>
                                            Variant ID: {{ currentVariant.id }}
                                        </p>
                                        <p>
                                            Price: ${{ currentVariant.price }}
                                        </p>
                                        <p>
                                            Stock: {{ currentVariant.quantity }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-600">
                                        {{ product.listing_price }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t"
                            >
                                <div class="flex items-center gap-2 text-sm">
                                    <svg
                                        class="w-5 h-5 text-green-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                        />
                                    </svg>
                                    <span>Free Delivery</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <svg
                                        class="w-5 h-5 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        />
                                    </svg>
                                    <span>2 Year Warranty</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <svg
                                        class="w-5 h-5 text-purple-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        />
                                    </svg>
                                    <span>Easy Returns</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-bold mb-4">
                            {{ $t("products.details") }}
                        </h2>
                        <div class="space-y-4">
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"
                            >
                                <div>
                                    <span class="font-medium"
                                        >{{ $t("products.weight") }} :
                                    </span>
                                    <span class="ml-2"
                                        >{{ product.weight
                                        }}{{ product.weight_unit }}</span
                                    >
                                </div>
                                <div>
                                    <span class="font-medium"
                                        >{{ $t("products.category") }}:</span
                                    >
                                    <span class="ml-2">{{
                                        product.category?.name
                                    }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">SKU:</span>
                                    <span class="ml-2">{{
                                        product.slug?.toUpperCase()
                                    }}</span>
                                </div>
                                <!-- <div>
                                    <span class="font-medium">Stock:</span>
                                    <span class="ml-2 text-green-600"
                                        >{{
                                            currentVariant.quantity
                                        }}
                                        available</span
                                    >
                                </div> -->
                            </div>

                            <div class="pt-4">
                                <h3 class="font-semibold mb-2">
                                    {{ $t("products.description") }}
                                </h3>
                                <p class="text-gray-600">
                                    {{ product.description }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-lg shadow-sm p-6 prose"
                        v-html="product.long_description"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add Tailwind CSS classes or custom styles here */
.prose {
    max-width: none;
}

.prose h3 {
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.prose ul {
    margin-top: 0.5rem;
    margin-bottom: 1rem;
}

.prose li {
    margin-top: 0.25rem;
    margin-bottom: 0.25rem;
}

/* Sticky positioning for larger screens */
@media (min-width: 1024px) {
    .lg\:sticky {
        position: sticky;
    }

    .lg\:top-6 {
        top: 1.5rem;
    }

    .lg\:self-start {
        align-self: flex-start;
    }
}

.fade-scale-enter-active {
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fade-scale-enter-from {
    opacity: 0;
    transform: scale(0.5);
}

.fade-scale-enter-to {
    opacity: 1;
    transform: scale(1);
}
</style>
