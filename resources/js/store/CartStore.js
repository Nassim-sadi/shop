import { defineStore } from "pinia";

export const useCartStore = defineStore("cartStore", {
    state: () => ({
        items: [],
        isLoading: false,
    }),

    persist: {
        enabled: true,
        strategies: [
            {
                storage: localStorage,
            },
        ],
    },

    getters: {
        totalItems: (state) =>
            state.items.reduce((sum, item) => sum + item.quantity, 0),
        totalPrice: (state) =>
            state.items.reduce(
                (sum, item) => sum + item.price * item.quantity,
                0,
            ),
        itemCount: (state) => state.items.length,
    },

    actions: {
        addToCart(product) {
            const existingItem = this.items.find(
                (item) => item.id === product.id,
            );

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                this.items.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: 1,
                });
            }
        },

        removeFromCart(productId) {
            this.items = this.items.filter((item) => item.id !== productId);
        },

        updateQuantity(productId, quantity) {
            const item = this.items.find((item) => item.id === productId);
            if (item) {
                item.quantity = quantity;
            }
        },

        clearCart() {
            this.items = [];
        },
    },
});
