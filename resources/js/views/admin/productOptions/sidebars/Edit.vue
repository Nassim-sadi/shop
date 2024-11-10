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
    current: {
        type: Object,
        required: true,
    },
    isOpen: {
        type: Boolean,
        required: true,
    },
    loading: {
        type: Boolean,
        required: false,
    },
});

const $emit = defineEmits(["update:isOpen", "editItem"]);

const { isOpen, loading, current } = toRefs(props);

const isAdding = ref(false);
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

const uniqueValue = (value) => {
    return !edited.value.values.some(
        (option) => option.value.toLowerCase() === value.toLowerCase(),
    );
};

const v$Value = useVuelidate(valueRules, { newOptionValue });

const edited = ref({
    name: "",
    values: [], // Initially one empty value field
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
const v$ = useVuelidate(rules, edited);

const addOptionValue = () => {
    v$Value.value.$touch();
    if (!v$Value.value.$invalid) {
        edited.value.values.push({ value: newOptionValue.value });
        newOptionValue.value = null;
        v$Value.value.$reset();
        isAdding.value = false;
    }
};

const updateItem = () => {
    v$.value.$touch();
    console.log("before emit", edited.value);
    if (v$.value.$invalid) return;
    $emit("editItem", edited.value);
    v$.value.$reset();
};

const editingIndex = ref(-1);
const editedValue = ref("");

const startEditing = (index, currentValue) => {
    editingIndex.value = index;
    editedValue.value = currentValue;
};

const saveEdit = (index) => {
    edited.value.values[index].value = editedValue.value;
    editingIndex.value = null;
    editedValue.value = "";
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
    return !isEqual(edited.value, current.value);
});

watch(
    () => isOpen.value,
    (val) => {
        if (val) {
            edited.value = JSON.parse(JSON.stringify(current.value));
            edited.value.status = current.value.status ? 1 : 0;
        } else {
            v$.value.$reset();
            v$Value.value.$reset();
            edited.value = {};
        }
    },
);
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('productOptions.edit')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :showCloseIcon="false"
        class="large-drawer"
    >
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12">
                <label for="name" class="mb-5">{{
                    $t("productOptions.name")
                }}</label>
                <InputText
                    id="name"
                    v-model="edited.name"
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
                v-model="edited.status"
                aria-labelledby="status"
                fluid
                :options="statusOptions"
                optionLabel="name"
                optionValue="value"
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
                    v-for="(optionValue, index) in edited.values"
                    :key="optionValue"
                    class="custom-list-item col-span-12 mb-5 flex items-center justify-between"
                >
                    <div class="flex items-center">
                        <!-- Display span or input based on editingIndex -->
                        <span v-if="editingIndex !== index">{{
                            optionValue.value
                        }}</span>
                        <InputText
                            v-else
                            v-model="editedValue"
                            class="border p-1 rounded"
                        />
                    </div>
                    <div class="flex gap-2">
                        <Button
                            v-if="editingIndex !== index"
                            icon="ti ti-edit"
                            severity="warn"
                            size="small"
                            @click="startEditing(index, optionValue.value)"
                        />
                        <Button
                            v-else
                            icon="ti ti-check"
                            severity="success"
                            size="small"
                            @click="saveEdit(index)"
                        />
                        <Button
                            icon="ti ti-trash"
                            severity="danger"
                            size="small"
                            @click="edited.values.splice(index, 1)"
                            v-if="
                                optionValue.variants_count == 0 ||
                                optionValue.variants_count == null
                            "
                        />
                    </div>
                </div>
            </transition-group>

            <template v-if="edited.values.length === 0">
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
                    @click="updateItem"
                    :loading="loading"
                    :disabled="loading || !isEdited || isAdding"
                />
            </div>
        </template>
    </Drawer>
</template>

<style lang="scss" scoped></style>
