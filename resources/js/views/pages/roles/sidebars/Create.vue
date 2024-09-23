<script setup>
import { $t } from "@/plugins/i18n";
import { Color } from "@/validators/CustomValidators";
import useVuelidate from "@vuelidate/core";
import { helpers, required } from "@vuelidate/validators";
import { useDebounce } from "@vueuse/core";
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
    permissions: {
        type: Array,
        required: true,
    },
    filteredRoles: {
        type: Array,
        required: true,
    },
});

const $emit = defineEmits(["update:isOpen", "createItem"]);

const { isOpen, permissions, filteredRoles } = toRefs(props);

const role = ref({
    name: "",
    description: "",
    color: "",
    text_color: "",
    permissions: [],
});

function arraysAreEqual(arr1, arr2) {
    if (arr1.length !== arr2.length) return false;
    const sortedArr1 = [...arr1].sort((a, b) => a - b);
    const sortedArr2 = [...arr2].sort((a, b) => a - b);
    return sortedArr1.every((value, index) => value === sortedArr2[index]);
}

function isValidPermission() {
    for (let i = 0; i < filteredRoles.value.length; i++) {
        if (
            arraysAreEqual(
                role.value.permissions,
                filteredRoles.value[i].permissions,
            )
        ) {
            return false;
        }
    }
    return true;
}

const validateColor = (value) => {
    for (let i = 0; i < filteredRoles.value.length; i++) {
        console.log(filteredRoles.value[i].color);
        console.log("color");

        console.log(value);
        if (filteredRoles.value[i].color === value) {
            return false;
        }
    }
    return true;
};

const validateName = (value) => {
    console.log("name");

    console.log(value);

    // check name if same as in filtered roles
    for (let i = 0; i < filteredRoles.value.length; i++) {
        if (filteredRoles.value[i].name === value) {
            return false;
        }
    }
    return true;
};

const rules = computed(() => ({
    name: {
        required,
        isValidName: helpers.withMessage(
            $t("validation.existing_role_name"),
            validateName,
        ),
    },
    color: {
        Color: helpers.withMessage($t("validation.invalid_color"), Color),
        validateColor: helpers.withMessage(
            $t("validation.existing_role_color"),
            validateColor,
        ),
    },
    text_color: {
        Color: helpers.withMessage($t("validation.invalid_color"), Color),
    },
    permissions: {
        required: helpers.withMessage($t("validation.required"), required),
        isValidPermission: helpers.withMessage(
            $t("validation.role_permission_exists"),
            isValidPermission,
        ),
    },
}));

const v$ = useVuelidate(rules, role);

const createItem = () => {
    v$.value.$touch();
    console.log("touching");

    if (v$.value.$invalid) return;
    console.log(role.value);
    $emit("createItem", role.value);
    v$.value.$reset();
};

const getContrastTextColor = (hex) => {
    const r = parseInt(hex.slice(0, 2), 16);
    const g = parseInt(hex.slice(2, 4), 16);
    const b = parseInt(hex.slice(4, 6), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    let textColor = luminance > 0.5 ? "000000" : "ffffff";
    role.value.text_color = textColor;
    return textColor;
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
    return !isEqual(role.value, {
        name: "",
        description: "",
        color: "",
        text_color: "",
    });
});

watch(
    () => isOpen.value,
    (val) => {
        if (!val) {
            v$.value.$reset();
            role.value = {
                name: "",
                description: "",
                color: "",
                text_color: "",
            };
        }
    },
);
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('roles.create')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :showCloseIcon="false"
        block-scroll
        class="!w-full md:!w-[30rem] lg:!w-[25rem]"
    >
        <div class="flex flex-col min-h-full">
            <label for="name" class="mb-5">{{ $t("roles.name") }}</label>
            <InputText
                id="name"
                v-model="role.name"
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

            <label for="description" class="mb-5">{{
                $t("roles.description")
            }}</label>
            <Textarea
                id="description"
                v-model="role.description"
                aria-labelledby="description"
                class="w-full mb-5"
                rows="3"
            />

            <label for="color" class="mb-5">{{ $t("roles.color") }}</label>
            <ColorPicker v-model="role.color" class="mb-5" />
            <div
                class="text-red-500 mb-5"
                v-for="error of v$.color.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <label for="text_color" class="mb-5">
                {{ $t("common.preview") }}
            </label>
            <span
                class="highlight mb-5"
                :style="{
                    color: '#' + getContrastTextColor(role.color),
                    backgroundColor: role.color ? '#' + role.color : '',
                }"
            >
                {{ role.name ? role.name : $t("roles.name") }}
            </span>

            <div
                class="text-red-500 mb-5"
                v-for="error of v$.text_color.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <label for="permissions" class="mb-5">{{
                $t("roles.permissions")
            }}</label>

            <MultiSelect
                id="permissions"
                v-model="role.permissions"
                option-value="id"
                :options="permissions"
                display="chip"
                optionLabel="name"
                class="w-full mb-5"
                :placeholder="$t('roles.select_permission')"
            />

            <div
                class="text-red-500 mb-5"
                v-for="error of v$.permissions.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <div slot="footer" class="mt-auto flex justify-evenly">
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    severity="danger"
                    @click="cancelConfirm"
                    :disabled="loading"
                />

                <Button
                    label="Save"
                    icon="pi pi-check"
                    severity="success"
                    @click="createItem"
                    :loading="loading"
                    :disabled="!isEdited || loading"
                />
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped>
:deep(.p-multiselect-label) {
    flex-wrap: wrap;
}
</style>
