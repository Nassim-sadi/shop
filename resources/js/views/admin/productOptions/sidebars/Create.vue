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
        type: String,
        default: "not_set",
    },
    progress: {
        type: Number,
        default: 0,
        required: false,
    },
});

const $emit = defineEmits(["update:isOpen", "createItem"]);

const { isOpen, loading } = toRefs(props);

const isAdding = ref(false);

const newOptionValue = ref(null);

const valueRules = computed(() => ({
    newOptionValue: {
        required,
        alphaSpace,
        uniqueValue: {
            $message: $t("validation.uniqueValue"),
            $validator: uniqueValue,
        },
    },
}));

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

const uniqueValue = (value) => {
    return !edited.value.values.some(
        (option) => option.value.toLowerCase() === value.toLowerCase(),
    );
};
const v$Value = useVuelidate(valueRules, { newOptionValue });

const productOption = ref({
    name: "",
    values: [],
    status: 0,
});

const rules = computed(() => ({
    name: {
        required,
        alphaSpace,
    },
    values: {
        atLeastTwoValues: {
            $message: $t("validation.atLeastTwoValues"),
            $validator: atLeastTwoValues,
        },
    },
}));

const atLeastTwoValues = (values) => {
    return values.length >= 2;
};
const v$ = useVuelidate(rules, productOption);

const addOptionValue = () => {
    v$Value.value.$touch();
    if (!v$Value.value.$invalid) {
        productOption.value.values.push(newOptionValue.value);
        newOptionValue.value = null;
        v$Value.value.$reset();
        isAdding.value = false;
    }
};

const createItem = () => {
    v$.value.$touch();
    if (v$.value.$invalid) return;
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
        values: [],
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
                values: [],
                status: 0,
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
        :show-close-icon="false"
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
                    class="w-full"
                />

                <div
                    class="text-red-500 mt-5 col-span-12"
                    v-for="error of v$.name.$errors"
                    :key="error.$uid"
                >
                    <Message severity="error">{{ error.$message }}</Message>
                </div>
            </div>

            <label for="status" class="mb-5">{{
                $t("productOptions.status")
            }}</label>

            <SelectButton
                id="status"
                v-model="productOption.status"
                aria-labelledby="status"
                fluid
                :options="statusOptions"
                option-label="name"
                option-value="value"
                class="toggleStatusBtn col-span-12"
            />

            <div class="col-span-12 flex items-center justify-between">
                <span>{{ $t("productOptions.values") }} </span>
                <Button
                    @click="isAdding = true"
                    class="col-span-12"
                    icon="ti ti-plus"
                    severity="success"
                    size="small"
                    v-tooltip.bottom="$t('productOptions.add_value')"
                />
            </div>

            <transition-group
                name="list"
                tag="div"
                class="list-container col-span-12"
            >
                <div
                    v-for="(optionValue, index) in productOption.values"
                    :key="optionValue"
                    class="custom-list-item col-span-12 mb-5 flex items-center justify-between"
                >
                    <span>{{ optionValue }}</span>
                    <Button
                        icon="ti ti-trash"
                        severity="danger"
                        class="float-right"
                        size="small"
                        @click="productOption.values.splice(index, 1)"
                    />
                </div>
            </transition-group>

            <template v-if="productOption.values.length === 0">
                <div class="col-span-12">
                    <p>{{ $t("productOptions.no_values") }}</p>
                </div>
            </template>

            <div
                class="text-red-500 mt-5 col-span-12"
                v-for="error of v$.values.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <template v-if="isAdding">
                <InputText v-model="newOptionValue" class="col-span-12" />
                <div
                    class="text-red-500 mb-5 col-span-12"
                    v-for="error of v$Value.newOptionValue.$errors"
                    :key="error.$uid"
                >
                    <Message severity="error">{{ error.$message }}</Message>
                </div>

                <Button
                    @click="isAdding = false"
                    class="col-span-6"
                    outlined
                    severity="danger"
                    :label="$t('common.cancel')"
                />

                <Button
                    @click="addOptionValue"
                    class="col-span-6"
                    :label="$t('productOptions.add_value')"
                />
            </template>
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
                    :disabled="loading || !isEdited || isAdding"
                />
            </div>
        </template>
    </Drawer>
</template>
