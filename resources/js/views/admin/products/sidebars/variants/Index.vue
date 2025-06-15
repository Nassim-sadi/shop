<script setup>
import { $t } from "@/plugins/i18n";
import { maxLength, required, numeric, minLength } from "@vuelidate/validators";
import {
    alphaSpace,
    validateDecimalFormat,
} from "@/validators/CustomValidators";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref, toRefs, watch } from "vue";
import VariantInput from "./components/VariantInput.vue";
import emitter from "@/plugins/emitter";
import axios from "@/plugins/axios";

const confirm = useConfirm();
const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    loading: {
        type: Boolean,
        required: false,
    },
    options: {
        type: Array,
        required: true,
    },
    current: {
        required: true,
        type: Object,
    },
    existingVariants: {
        type: Array,
        default: () => [],
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
});

const $emit = defineEmits(["update:isOpen", "createItem"]);

const { isOpen, loading, current, options, existingVariants, isEditing } =
    toRefs(props);

const variantRef = ref(null);

// Computed property to check if save should be enabled
const canSave = computed(() => {
    return variantRef.value?.canSave || false;
});

const createItem = () => {
    if (!canSave.value) {
        emitter.emit("toast", {
            summary: $t("status.error.title"),
            message: "Please complete all variants before saving",
            severity: "error",
        });
        return;
    }

    const selectedVariants = variantRef.value?.selects || [];
    console.log("selectedVariants:", selectedVariants);

    // Separate new variants (negative IDs) from existing ones (positive IDs)
    const newVariants = selectedVariants.filter((v) => v.id < 0);
    const updatedVariants = selectedVariants.filter((v) => v.id > 0);

    // Clean temp IDs from new variants
    const cleanNewVariants = newVariants.map(({ id, ...variant }) => variant);

    // Emit to parent with separated data
    $emit("createItem", {
        newVariants: cleanNewVariants,
        updatedVariants: updatedVariants,
        isEditing: isEditing.value,
    });
};

const rules = computed(() => ({
    name: {
        required,
        alphaSpace,
        maxLength: maxLength(255),
        minLength: minLength(3),
    },
    description: {
        alphaSpace,
        required,
        maxLength: maxLength(255),
        minLength: minLength(10),
    },
    long_description: {
        required,
        alphaSpace,
        maxLength: maxLength(2000),
        minLength: minLength(10),
    },
    base_price: {
        required,
        numeric,
        validateDecimalFormat,
    },
    listing_price: {
        required,
        numeric,
        validateDecimalFormat,
    },
    base_quantity: {
        numeric,
        required,
    },
    category: {
        required,
    },
}));

// const v$ = useVuelidate(rules, product);

const cancelConfirm = () => {
    if (isEdited.value) {
        confirm.require({
            header: $t("cancel.edit"),
            message: $t("cancel.edit.message"),
            icon: "pi pi-exclamation-triangle",
            rejectProps: {
                label: $t("no"),
                severity: "secondary",
                outlined: true,
            },
            acceptProps: {
                label: $t("yes"),
                severity: "danger",
            },
            accept: () => {
                $emit("update:isOpen", false);
            },
            reject: () => {},
        });
    } else {
        $emit("update:isOpen", false);
    }
};

// Updated isEdited to include variant validation
const isEdited = computed(() => {
    return canSave.value; // Now it's based on whether variants are properly configured
});

const productOptions = ref([]);

const getProductOptionsInfo = () => {
    let t = options.value.filter((option) => {
        return current.value.options.some((p) => p.id === option.id);
    });
    return t;
};

watch(
    () => isOpen.value,
    (val) => {
        if (!val) {
            // Reset variant component if needed
            // v$.value.$reset();
        } else {
            getVariants();
            productOptions.value = getProductOptionsInfo();
        }
    },
);

const getVariants = () => {
    return new Promise((resolve, reject) => {
        axios
            .get(`api/admin/products/${current.value.id}/variants`)
            .then((res) => {
                console.log(res.data);
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            });
    });
};
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('products.variants.create')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :show-close-icon="false"
        block-scroll
        class="extra-large-drawer"
    >
        <div class="h-full w-full">
            <div class="flex justify-between">
                <h3>
                    {{ current.name }}
                </h3>
            </div>

            <div class="mt-4">
                <VariantInput
                    :options="productOptions"
                    :initial-variants="existingVariants"
                    ref="variantRef"
                />
            </div>
        </div>

        <template #footer>
            <div class="mt-auto flex justify-evenly">
                <Button
                    :label="$t('common.cancel')"
                    icon="pi pi-times"
                    severity="danger"
                    @click="cancelConfirm"
                    outlined
                    :disabled="loading"
                />

                <Button
                    :label="$t('common.save')"
                    icon="pi pi-check"
                    severity="success"
                    @click="createItem"
                    :loading="loading"
                    :disabled="loading || !canSave"
                />
            </div>
        </template>
    </Drawer>
</template>

<style lang="scss" scoped></style>
