<script setup>
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import { alphaSpace } from "@/validators/CustomValidators";
import isEqual from "lodash.isequal";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref, toRefs, watch } from "vue";
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
    parent: {
        required: false,
        default: "not_set",
    },
    progress: {
        type: Number,
        required: false,
    },
});

const $emit = defineEmits(["update:isOpen", "createItem"]);

const { isOpen, loading, parent } = toRefs(props);

const statusOptions = [
    {
        name: $t("common.active"),
        value: 1,
    },
    {
        name: $t("common.inactive"),
        value: 0,
    },
];

const productOption = ref({
    name: "",
    values: [{ value: "" }], // Initially one empty value field
});

const rules = computed(() => ({
    name: {
        required,
        alphaSpace,
    },
    values: {
        $each: {
            value: {
                required,
                alphaSpace,
            },
        },
    },
}));

const v$ = useVuelidate(rules, productOption);
const addOptionValue = () => {
    // Push the new value first so Vuelidate can see it
    productOption.value.values.push({ value: "" });

    // Now touch and validate the updated values array
    v$.value.values.$touch();

    // Stop if any of the values are invalid
    if (v$.value.values.$invalid) {
        values.value.pop(); // Remove the invalid value if validation fails
        return;
    }
};
const createItem = () => {
    v$.value.$touch();
    if (v$.value.$invalid) return;
    console.log("status value: ", productOption.value.status);
    $emit("createItem", productOption.value);
    v$.value.$reset();
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

const isEdited = computed(() => {
    return !isEqual(productOption.value, {
        name: "",
        status: 0,
    });
});

watch(
    () => isOpen.value,
    (val) => {
        if (!val) {
            v$.value.$reset();
            productOption.value = {
                name: "",
            };
        }
    },
);
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('productOptions.create')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :showCloseIcon="false"
        block-scroll
        class="small-drawer"
    >
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12">
                <label for="name" class="mb-5">{{
                    $t("productOptions.name")
                }}</label>
                <InputText
                    id="name"
                    v-model="productOption.name"
                    aria-labelledby="name"
                    class="w-full mb-5"
                />

                <div
                    class="text-red-500 mb-5"
                    v-for="error of v$.name.$errors"
                    :key="error.$uid"
                >
                    <Message severity="error">{{ error.$message }}</Message>
                </div>
            </div>

            <div
                v-for="(optionValue, index) in productOption.values"
                :key="index"
                class="col-span-12"
            >
                <label :for="'optionValue' + index"
                    >Option Value {{ index + 1 }}</label
                >
                <input
                    :id="'optionValue' + index"
                    v-model="optionValue.value"
                />
                <div
                    class="text-red-500 mb-5"
                    v-for="error of v$.values.$errors"
                    :key="error.$uid"
                >
                    <Message severity="error">{{ error.$message }}</Message>
                </div>
            </div>

            <Button @click="addOptionValue" class="col-span-12">
                Add Option Value
            </Button>
            <pre>
                {{ productOption }}
            </pre>
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
                    :disabled="loading"
                />
            </div>
        </template>
    </Drawer>
</template>

<style lang="scss" scoped></style>
