<script setup>
import router from "@/router/Index";
import { useCartStore } from "@/store/CartStore";

const { product } = defineProps({
    product: {
        type: Object,
        required: true,
    },
    hideFeatured: {
        type: Boolean,
        default: false,
    },
});

const { addToCart } = useCartStore();

const goToProduct = (product) => {
    router.push({
        name: "product",
        params: {
            categorySlug: product.category.slug, // You need this
            productSlug: product.slug, // And this
        },
    });
};

const toggleWishlist = (product) => {
    console.log("Toggle wishlist for product");
};
</script>

<template>
    <div class="product-card">
        <!-- Wishlist Button -->
        <button class="wishlist-btn" @click.stop="toggleWishlist(product)">
            <svg
                class="heart-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
            >
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                />
            </svg>
        </button>

        <!-- Product Image -->
        <div class="image-container">
            <img
                :src="product.thumbnail_image_path"
                :alt="product.name"
                class="product-image"
            />
        </div>

        <!-- Product Content -->
        <div class="product-content">
            <div class="product-header">
                <!-- Category -->
                <span class="category">{{ product.category.name }}</span>
                <div
                    v-if="product.featured && !hideFeatured"
                    class="featured-badge"
                >
                    {{ $t("products.featured") }}
                </div>
            </div>
            <!-- Product Name -->
            <h3
                class="product-name clickable"
                @click.stop="goToProduct(product)"
            >
                {{ product.name }}
            </h3>

            <!-- Product Meta -->
            <div class="product-meta">
                <div v-if="product.weight" class="weight">
                    {{ product.weight }}{{ product.weight_unit }}
                </div>
                <div
                    v-if="product.options && product.options.length"
                    class="options-count"
                >
                    {{ product.options.length }} options
                </div>
            </div>

            <!-- Price -->
            <div class="price-container">
                <span class="price">{{ product.listing_price }}</span>
                <span class="currency">{{ product.currency.symbol }}</span>
            </div>

            <!-- Add to Cart Button -->
            <button class="add-to-cart-btn" @click.stop="addToCart(product)">
                <svg
                    class="cart-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                >
                    <path
                        d="M9 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM20 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"
                    />
                    <path
                        d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"
                    />
                </svg>
                Add to Cart
            </button>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.product-card {
    position: relative;
    background: var(--white-clr);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(23, 23, 23, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--light-clr);
    display: flex;
    flex-direction: column;
    height: 100%;

    &:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(34, 48, 64, 0.15);
        border-color: var(--primary-contrast-clr);
    }
}

.wishlist-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    background: color-mix(in srgb, var(--white-clr) 90%, transparent);
    backdrop-filter: blur(10px);
    border: none;
    padding: 8px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--primary-contrast-clr);

    &:hover {
        background: var(--white-clr);
        transform: scale(1.1);
    }

    .heart-icon {
        width: 18px;
        height: 18px;
        color: var(--danger-clr);
        stroke-width: 2;
        transition: all 0.2s ease;
    }

    &:hover .heart-icon {
        fill: var(--danger-clr);
    }
}

.image-container {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
    background: var(--light-clr);
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;

    .product-card:hover & {
        transform: scale(1.05);
    }
}

.product-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.product-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.category {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--primary-bright-clr);
    background: color-mix(in srgb, var(--primary-clr) 10%, transparent);
    padding: 4px 10px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: fit-content;
}

.featured-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--accent-clr);
    background: color-mix(in srgb, var(--accent-clr) 10%, transparent);
    padding: 4px 10px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: fit-content;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-clr);
    margin: 0 0 12px 0;
    line-height: 1.5;
    height: 3.3rem; /* Fixed height for 2 lines with proper spacing */
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;

    &.clickable {
        cursor: pointer;
        transition: color 0.2s ease;

        &:hover {
            color: var(--primary-bright-clr);
            text-decoration: underline;
        }
    }
}

.product-meta {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    font-size: 0.875rem;
    color: var(--dark-clr);

    .weight {
        display: flex;
        align-items: center;
        gap: 4px;

        &:before {
            content: "⚖️";
            font-size: 0.75rem;
        }
    }

    .options-count {
        display: flex;
        align-items: center;
        gap: 4px;

        &:before {
            content: "🎨";
            font-size: 0.75rem;
        }
    }
}

.price-container {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 16px;
}

.price {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--accent-clr);
    line-height: 1;
    text-shadow: 0 1px 2px rgba(254, 153, 1, 0.2);
}

.currency {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--accent-bright-clr);
}

.add-to-cart-btn {
    width: 100%;
    background: linear-gradient(
        135deg,
        var(--primary-bright-clr),
        var(--primary-clr)
    );
    color: var(--white-clr);
    border: none;
    padding: 12px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(68, 121, 182, 0.2);
    margin-top: auto; /* Push button to bottom */

    &:hover {
        background: linear-gradient(
            135deg,
            var(--primary-clr),
            var(--darkest-clr)
        );
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(68, 121, 182, 0.3);
    }

    &:active {
        transform: translateY(0);
    }

    .cart-icon {
        width: 18px;
        height: 18px;
        stroke-width: 2;
    }
}

// Responsive adjustments
@media (max-width: 768px) {
    .product-card {
        border-radius: 12px;
    }

    .image-container {
        height: 180px;
    }

    .product-content {
        padding: 16px;
    }

    .product-name {
        font-size: 1rem;
    }

    .price {
        font-size: 1.4rem;
    }
}
</style>
