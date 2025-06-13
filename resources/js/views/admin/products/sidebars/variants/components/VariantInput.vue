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
});

const variants = ref([]);

// Initialize variants with existing data or empty array
const initializeVariants = () => {
    if (props.initialVariants && props.initialVariants.length > 0) {
        // Load existing variants (they already have real DB IDs)
        variants.value = [...props.initialVariants];
    } else {
        variants.value = [];
    }
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
        id: generateTempId(), // More unique temp ID
        options: {},
        price: null,
        quantity: null,
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
    variants.value.splice(index, 1);
};

// Computed property to check if save button should be enabled
const canSave = computed(() => {
    // Must have at least one variant
    if (variants.value.length === 0) {
        return false;
    }

    // All variants must be complete
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

    return !hasDuplicates;
});

// Expose to parent
defineExpose({
    selects: variants,
    canSave,
    isVariantComplete,
    isDuplicateCombination,
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

    const headers = [...props.options.map((o) => o.name), "Price", "Quantity"];

    const rows = variants.value.map((variant) => {
        const row = [];

        for (const option of props.options) {
            const valId = variant.options[option.id];
            const name = computedOptionMap.value[option.id]?.[valId] ?? "";
            row.push(name);
        }

        row.push(variant.price ?? "");
        row.push(variant.quantity ?? "");
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
        <div class="flex justify-end">
            <Button
                icon="pi pi-plus"
                label="Add Variant"
                severity="success"
                @click="addVariant"
                :disabled="options.length === 0"
            />

            <Button
                icon="pi pi-download"
                label="Export CSV"
                class="ml-2"
                severity="info"
                @click="exportCSV"
                :disabled="variants.length === 0"
            />
        </div>

        <div v-if="variants.length > 0" class="overflow-auto">
            <table class="w-full border text-sm">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th
                            v-for="option in options"
                            :key="option.id"
                            class="px-3 py-2 border"
                        >
                            {{ option.name }}
                        </th>
                        <th class="px-3 py-2 border">Price</th>
                        <th class="px-3 py-2 border">Quantity</th>
                        <th class="px-3 py-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(variant, i) in variants"
                        :key="variant.id"
                        class="hover:bg-gray-50"
                    >
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
                            />
                        </td>
                        <td class="px-3 py-2 border">
                            <InputText
                                v-model="variant.price"
                                type="number"
                                class="w-full"
                                :class="{ 'p-invalid': isPriceInvalid(i) }"
                            />
                        </td>
                        <td class="px-3 py-2 border">
                            <InputText
                                v-model="variant.quantity"
                                type="number"
                                class="w-full"
                                :class="{ 'p-invalid': isQuantityInvalid(i) }"
                            />
                        </td>
                        <td class="px-3 py-2 border text-center">
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                @click="removeVariant(i)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="text-gray-500 text-center py-6">
            No variants added.
        </div>
    </div>
</template>
