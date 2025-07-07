<script setup>
import { $t } from "@/plugins/i18n";
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
});

const $emit = defineEmits(["update:isOpen", "updateItem"]);

const initialVariants = ref([]);
const { isOpen, loading, current, options } = toRefs(props);
const isLoading = ref(false);

const variantRef = ref(null);

// Computed property to check if save should be enabled
const canSave = computed(() => {
    return variantRef.value?.canSave || false;
});

const separateVariants = () => {
    const selectedVariants = variantRef.value?.selects || [];
    const deletedVariants = variantRef.value?.deletedVariants || [];
    console.log("selectedVariants:", selectedVariants);
    console.log("deletedVariants:", deletedVariants);

    // Separate new variants (negative IDs) from existing ones (positive IDs)
    const newVariants = selectedVariants.filter((v) => v.id < 0);
    const updatedVariants = selectedVariants.filter((v) => v.id > 0);

    // Clean temp IDs from new variants
    const cleanNewVariants = newVariants.map(({ id, ...variant }) => variant);

    return {
        newVariants: cleanNewVariants,
        updatedVariants: updatedVariants,
        deletedVariants: deletedVariants,
        selectedVariants,
    };
};

const updateItem = async () => {
    if (!canSave.value) {
        emitter.emit("toast", {
            summary: $t("status.error.title"),
            message: "Please complete all variants before saving",
            severity: "error",
        });
        return;
    }

    const { newVariants, updatedVariants, deletedVariants } =
        separateVariants();

    try {
        isLoading.value = true;

        // Call the update API endpoint
        const response = await updateVariants({
            newVariants,
            updatedVariants,
            deletedVariants,
        });

        emitter.emit("toast", {
            summary: $t("status.success.title"),
            message: `Variants updated successfully. Created: ${response.created_count}, Updated: ${response.updated_count}, Deleted: ${response.deleted_count || 0}`,
            severity: "success",
        });

        $emit("update:isOpen", false);

        // Refresh variants data
        await getVariants();
    } catch (error) {
        console.error("Error updating variants:", error);

        const errorMessage =
            error.response?.data?.message ||
            error.response?.data?.errors ||
            "An error occurred while updating variants";

        emitter.emit("toast", {
            summary: $t("status.error.title"),
            message: errorMessage,
            severity: "error",
        });
    } finally {
        isLoading.value = false;
    }
};

const updateVariants = (data) => {
    return new Promise((resolve, reject) => {
        axios
            .put(`api/admin/products/${current.value.id}/variants`, data)
            .then((res) => {
                console.log("Update response:", res.data);
                $emit("updateItem", res.data.product);
                resolve(res.data);
            })
            .catch((err) => {
                console.error("Update error:", err);
                reject(err);
            });
    });
};

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
            // Reset component state when closing
            initialVariants.value = [];
            isLoading.value = false;
        } else {
            getVariants();
            productOptions.value = getProductOptionsInfo();
        }
    },
);

const getVariants = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get(
            `api/admin/products/${current.value.id}/variants`,
        );
        console.log("Fetched variants:", response.data);
        initialVariants.value = response.data.variants || [];
    } catch (error) {
        console.error("Error fetching variants:", error);
        emitter.emit("toast", {
            summary: $t("status.error.title"),
            message: "Failed to load variants",
            severity: "error",
        });
        initialVariants.value = [];
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('products.variants.manage')"
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
                    :initial-variants="initialVariants"
                    :loading="isLoading"
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
                    :disabled="loading || isLoading"
                />

                <Button
                    :label="$t('common.save')"
                    icon="pi pi-check"
                    severity="success"
                    @click="updateItem"
                    :loading="loading || isLoading"
                    :disabled="loading || isLoading || !canSave"
                />
            </div>
        </template>
    </Drawer>
</template>

<style lang="scss" scoped></style>
