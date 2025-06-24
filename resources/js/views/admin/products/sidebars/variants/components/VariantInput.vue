<script setup>
import { computed, ref, watch } from "vue";
import useVuelidate from "@vuelidate/core";
import { required, numeric } from "@vuelidate/validators";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";

const props = defineProps({
    options: {
        type: Array,
        required: true,
    },
    initialVariants: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const variants = ref([]);
const deletedVariantIds = ref([]); // Track IDs of deleted existing variants

// Transform backend data to component format
const transformVariantFromBackend = (backendVariant) => {
    const options = {};

    // Convert optionValues array to options object format
    if (
        backendVariant.option_values &&
        backendVariant.option_values.length > 0
    ) {
        backendVariant.option_values.forEach((optionValue) => {
            if (optionValue.product_option_id) {
                options[optionValue.product_option_id] = optionValue.id;
            }
        });
    }

    return {
        id: backendVariant.id, // Keep original DB ID
        options: options,
        price: parseFloat(backendVariant.price) || 0,
        quantity: parseInt(backendVariant.quantity) || 0,
        status: backendVariant.status,
        isExisting: true, // Flag to identify existing variants
        originalData: { ...backendVariant }, // Keep original data for comparison
    };
};

// Check if variant data has been modified
const isVariantModified = (variant) => {
    if (!variant.isExisting || !variant.originalData) {
        return false; // New variants are always considered "modified"
    }

    const original = variant.originalData;

    // Check if basic fields changed
    if (
        parseFloat(variant.price) !== parseFloat(original.price) ||
        parseInt(variant.quantity) !== parseInt(original.quantity) ||
        variant.status !== original.status
    ) {
        return true;
    }

    // Check if option values changed
    const originalOptions = {};
    if (original.option_values && original.option_values.length > 0) {
        original.option_values.forEach((optionValue) => {
            if (optionValue.product_option_id) {
                originalOptions[optionValue.product_option_id] = optionValue.id;
            }
        });
    }

    for (const optionId of Object.keys(variant.options)) {
        if (variant.options[optionId] !== originalOptions[optionId]) {
            return true;
        }
    }

    return false;
};

// Initialize variants with existing data or empty array
const initializeVariants = () => {
    if (props.initialVariants && props.initialVariants.length > 0) {
        // Transform existing variants from backend format
        variants.value = props.initialVariants.map(transformVariantFromBackend);
    } else {
        variants.value = [];
    }
    // Reset deleted variants when initializing
    deletedVariantIds.value = [];
};

// Initialize when component mounts
initializeVariants();

// Watch for changes to initialVariants prop
watch(
    () => props.initialVariants,
    () => {
        initializeVariants();
    },
    { deep: true },
);

const rules = computed(() => {
    const optionRules = {};
    for (const option of props.options) {
        optionRules[option.id] = { required };
    }

    return {
        variants: {
            $each: {
                options: {
                    $each: optionRules,
                },
                price: { required, numeric },
                quantity: { required, numeric },
            },
        },
    };
});

const v$ = useVuelidate(rules, { variants });

watch(
    variants,
    () => {
        v$.value.$validate();
    },
    { deep: true },
);

// Helper function to check if a variant is completely filled
const isVariantComplete = (variant) => {
    // Check if all options are selected
    for (const option of props.options) {
        if (!variant.options[option.id]) {
            return false;
        }
    }
    // Check if price and quantity are filled
    return (
        variant.price !== null &&
        variant.price !== "" &&
        variant.quantity !== null &&
        variant.quantity !== ""
    );
};

// Helper function to check if combination already exists
const isDuplicateCombination = (newVariant, excludeIndex = -1) => {
    return variants.value.some((variant, index) => {
        if (index === excludeIndex) return false;

        // Compare option combinations
        let allMatch = true;
        for (const option of props.options) {
            if (variant.options[option.id] !== newVariant.options[option.id]) {
                allMatch = false;
                break;
            }
        }
        return allMatch;
    });
};

// Generate unique temp ID with negative numbers to avoid DB conflicts
let tempIdCounter = 0;
const generateTempId = () => {
    return -++tempIdCounter; // Negative IDs for temp, positive for DB
};

const addVariant = async () => {
    // If there are existing variants, check if the last one is complete
    if (variants.value.length > 0) {
        const lastVariant = variants.value[variants.value.length - 1];

        if (!isVariantComplete(lastVariant)) {
            emitter.emit("toast", {
                summary: $t("status.error.title"),
                message:
                    "Please complete the current variant before adding a new one",
                severity: "error",
            });
            return;
        }
    }

    // Create new variant
    const variant = {
        id: generateTempId(), // Temp ID for new variants
        options: {},
        price: null,
        quantity: null,
        status: 1, // Default to active status
        isExisting: false, // Flag for new variants
        originalData: null, // No original data for new variants
    };

    for (const option of props.options) {
        variant.options[option.id] = null;
    }

    variants.value.push(variant);
};

// Watch for changes in variant options to validate combinations
watch(
    () => variants.value.map((v) => ({ ...v.options })),
    (newOptions, oldOptions) => {
        if (!oldOptions) return;

        variants.value.forEach((variant, index) => {
            const allOptionsSelected = props.options.every(
                (option) =>
                    variant.options[option.id] !== null &&
                    variant.options[option.id] !== undefined,
            );

            if (allOptionsSelected) {
                const oldVariantOptions = oldOptions[index];
                const currentVariantOptions = variant.options;

                // Check if any option changed for this variant
                const optionsChanged = props.options.some(
                    (option) =>
                        oldVariantOptions?.[option.id] !==
                        currentVariantOptions[option.id],
                );

                if (optionsChanged && isDuplicateCombination(variant, index)) {
                    emitter.emit("toast", {
                        summary: $t("status.error.title"),
                        message:
                            "This combination already exists. Please choose different options.",
                        severity: "error",
                    });

                    // Find the option that was just changed and reset it
                    const changedOption = props.options.find(
                        (option) =>
                            oldVariantOptions?.[option.id] !==
                            currentVariantOptions[option.id],
                    );

                    if (changedOption) {
                        variant.options[changedOption.id] = null;
                    }
                }
            }
        });
    },
    { deep: true },
);

const removeVariant = (index) => {
    const variant = variants.value[index];

    // If it's an existing variant (from database), track its ID for deletion
    if (variant.isExisting && variant.id > 0) {
        deletedVariantIds.value.push(variant.id);

        emitter.emit("toast", {
            summary: $t("status.info.title"),
            message: "Variant marked for deletion. Save to confirm.",
            severity: "info",
        });
    }

    // Remove from variants array
    variants.value.splice(index, 1);
};

// Toggle variant status
const toggleVariantStatus = (index) => {
    variants.value[index].status = variants.value[index].status ? 0 : 1;
};

// Computed property to check if save button should be enabled
const canSave = computed(() => {
    // Must have at least one variant OR have deleted variants to process
    if (variants.value.length === 0 && deletedVariantIds.value.length === 0) {
        return false;
    }

    // If we only have deleted variants, we can save
    if (variants.value.length === 0 && deletedVariantIds.value.length > 0) {
        return true;
    }

    // All remaining variants must be complete
    const allVariantsComplete = variants.value.every((variant) =>
        isVariantComplete(variant),
    );
    if (!allVariantsComplete) {
        return false;
    }

    // No duplicate combinations allowed
    const hasDuplicates = variants.value.some((variant, index) =>
        isDuplicateCombination(variant, index),
    );

    if (hasDuplicates) {
        return false;
    }

    // Check if there are any changes to save
    const hasNewVariants = variants.value.some(
        (variant) => !variant.isExisting,
    );
    const hasModifiedVariants = variants.value.some(
        (variant) => variant.isExisting && isVariantModified(variant),
    );
    const hasDeletedVariants = deletedVariantIds.value.length > 0;

    return hasNewVariants || hasModifiedVariants || hasDeletedVariants;
});

// Computed properties to separate new and updated variants
const newVariants = computed(() => {
    return variants.value
        .filter((variant) => !variant.isExisting)
        .map((variant) => ({
            options: variant.options,
            price: parseFloat(variant.price),
            quantity: parseInt(variant.quantity),
            status: variant.status,
        }));
});

const updatedVariants = computed(() => {
    return variants.value
        .filter((variant) => variant.isExisting && isVariantModified(variant))
        .map((variant) => ({
            id: variant.id,
            options: variant.options,
            price: parseFloat(variant.price),
            quantity: parseInt(variant.quantity),
            status: variant.status,
        }));
});

const deletedVariants = computed(() => {
    return deletedVariantIds.value;
});

// Computed property for variants that have changes
const hasUnsavedChanges = computed(() => {
    return (
        variants.value.some(
            (variant) => !variant.isExisting || isVariantModified(variant),
        ) || deletedVariantIds.value.length > 0
    );
});

// Expose to parent with separated data
defineExpose({
    selects: variants,
    newVariants,
    updatedVariants,
    deletedVariants,
    canSave,
    hasUnsavedChanges,
    isVariantComplete,
    isDuplicateCombination,
    isVariantModified,
});

const isOptionInvalid = (i, optionId) => {
    return (
        v$.value.variants?.$each?.[i]?.options?.$each?.[optionId]?.$invalid ??
        false
    );
};

const isPriceInvalid = (i) => {
    return v$.value.variants?.$each?.[i]?.price?.$invalid ?? false;
};

const isQuantityInvalid = (i) => {
    return v$.value.variants?.$each?.[i]?.quantity?.$invalid ?? false;
};

const computedOptionMap = computed(() => {
    const map = {};
    for (const option of props.options) {
        map[option.id] = option.values.reduce((acc, val) => {
            acc[val.id] = val.value;
            return acc;
        }, {});
    }
    return map;
});

const exportCSV = () => {
    if (!variants.value.length) {
        emitter.emit("toast", {
            summary: $t("status.error.title"),
            message: $t("status.error.variant.csv_noData"),
            severity: "error",
        });
        return;
    }

    const headers = [
        "ID",
        "Type",
        ...props.options.map((o) => o.name),
        "Price",
        "Quantity",
        "Status",
        "Modified",
    ];

    const rows = variants.value.map((variant) => {
        const row = [];

        // Add ID and type
        row.push(variant.isExisting ? variant.id : "NEW");
        row.push(variant.isExisting ? "Existing" : "New");

        // Add option values
        for (const option of props.options) {
            const valId = variant.options[option.id];
            const name = computedOptionMap.value[option.id]?.[valId] ?? "";
            row.push(name);
        }

        // Add other fields
        row.push(variant.price ?? "");
        row.push(variant.quantity ?? "");
        row.push(variant.status ? "Active" : "Inactive");
        row.push(
            variant.isExisting
                ? isVariantModified(variant)
                    ? "Yes"
                    : "No"
                : "New",
        );

        return row;
    });

    const csvContent = [headers, ...rows]
        .map((e) => e.map((cell) => `"${cell}"`).join(","))
        .join("\n");

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");

    link.setAttribute("href", url);
    link.setAttribute("download", "variants.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- Header with action buttons -->
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
                <span
                    v-if="variants.length > 0 || deletedVariantIds.length > 0"
                >
                    {{ $t("common.total") }}:
                    {{
                        variants.length +
                        " " +
                        $t("products.variants.title", variants.length)
                    }}
                    <span v-if="newVariants.length > 0" class="text-green-600">
                        ({{ newVariants.length }} new)
                    </span>
                    <span
                        v-if="updatedVariants.length > 0"
                        class="text-blue-600"
                    >
                        ({{ updatedVariants.length }} modified)
                    </span>
                    <span
                        v-if="deletedVariantIds.length > 0"
                        class="text-red-600"
                    >
                        ({{
                            deletedVariantIds.length +
                            " " +
                            $t(
                                "products.variants.to_delete",
                                deletedVariantIds.length,
                            )
                        }}
                        )
                    </span>
                </span>
            </div>

            <div class="flex gap-2">
                <Button
                    icon="pi pi-plus"
                    label="Add Variant"
                    severity="success"
                    @click="addVariant"
                    :disabled="options.length === 0 || loading"
                />

                <Button
                    icon="pi pi-download"
                    label="Export CSV"
                    severity="info"
                    @click="exportCSV"
                    :disabled="variants.length === 0 || loading"
                />
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="text-center py-6">
            <ProgressSpinner style="width: 50px; height: 50px" />
            <p class="mt-2 text-gray-600">Loading variants...</p>
        </div>

        <!-- Variants table -->
        <div v-else-if="variants.length > 0" class="overflow-auto">
            <table class="w-full border text-sm">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="px-3 py-2 border">Type</th>
                        <th
                            v-for="option in options"
                            :key="option.id"
                            class="px-3 py-2 border"
                        >
                            {{ option.name }}
                        </th>
                        <th class="px-3 py-2 border">Price</th>
                        <th class="px-3 py-2 border">Quantity</th>
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(variant, i) in variants"
                        :key="variant.id"
                        class="hover:bg-gray-50"
                        :class="{
                            'bg-blue-50':
                                variant.isExisting &&
                                !isVariantModified(variant),
                            'bg-green-50': !variant.isExisting,
                            'bg-yellow-50':
                                variant.isExisting &&
                                isVariantModified(variant),
                        }"
                    >
                        <!-- Type indicator -->
                        <td class="px-3 py-2 border">
                            <span
                                v-if="!variant.isExisting"
                                class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs"
                            >
                                New
                            </span>
                            <span
                                v-else-if="isVariantModified(variant)"
                                class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs"
                            >
                                Modified
                            </span>
                            <span
                                v-else
                                class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs"
                            >
                                Existing
                            </span>
                        </td>

                        <!-- Option columns -->
                        <td
                            v-for="option in options"
                            :key="option.id"
                            class="px-3 py-2 border"
                        >
                            <Select
                                v-model="variant.options[option.id]"
                                :options="option.values"
                                option-label="value"
                                option-value="id"
                                placeholder="Select"
                                class="w-full"
                                :class="{
                                    'p-invalid': isOptionInvalid(i, option.id),
                                }"
                                :disabled="loading"
                            />
                        </td>

                        <!-- Price -->
                        <td class="px-3 py-2 border">
                            <InputText
                                v-model="variant.price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full"
                                :class="{ 'p-invalid': isPriceInvalid(i) }"
                                :disabled="loading"
                            />
                        </td>

                        <!-- Quantity -->
                        <td class="px-3 py-2 border">
                            <InputText
                                v-model="variant.quantity"
                                type="number"
                                min="0"
                                class="w-full"
                                :class="{ 'p-invalid': isQuantityInvalid(i) }"
                                :disabled="loading"
                            />
                        </td>

                        <!-- Status -->
                        <td class="px-3 py-2 border text-center">
                            <Button
                                :icon="
                                    variant.status
                                        ? 'pi pi-eye'
                                        : 'pi pi-eye-slash'
                                "
                                :severity="
                                    variant.status ? 'success' : 'secondary'
                                "
                                :label="
                                    variant.status
                                        ? $t('common.active')
                                        : $t('common.inactive')
                                "
                                size="small"
                                @click="toggleVariantStatus(i)"
                                :disabled="loading"
                            />
                        </td>

                        <!-- Actions -->
                        <td class="px-3 py-2 border text-center">
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                @click="removeVariant(i)"
                                :disabled="loading"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty state -->
        <div v-else class="text-gray-500 text-center py-6">
            {{ $t("products.variants.noVariants") }}
        </div>
    </div>
</template>
