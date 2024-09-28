<script setup>
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import {
    helpers,
    minLength,
    not,
    required,
    sameAs,
} from "@vuelidate/validators";

import _ from "lodash";
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
        required: true,
    },
});
const $emit = defineEmits(["update:isOpen", "submit"]);

const oldPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");

const rules = {
    oldPassword: { required, minLength: minLength(8) },
    newPassword: {
        required,
        minLength: minLength(8),
        not: helpers.withMessage(
            "New password must not be the same as old password",
            not(sameAs(oldPassword)),
        ),
    },
    password_confirmation: {
        required,
        minLength: minLength(8),
        sameAs: helpers.withMessage(
            "Passwords must match",
            sameAs(newPassword),
        ),
    },
};

const v$ = useVuelidate(rules, {
    oldPassword,
    newPassword,
    password_confirmation: confirmPassword,
});

const { isOpen } = toRefs(props);

const updateItem = () => {
    v$.value.$touch();
    if (!v$.value.$invalid && isEdited.value) {
        $emit("submit", {
            oldPassword: oldPassword.value,
            newPassword: newPassword.value,
            confirmPassword: confirmPassword.value,
        });
        v$.value.$reset();
    }
};

const cancelEdit = () => {
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
                oldPassword.value = "";
                newPassword.value = "";
                confirmPassword.value = "";
                $emit("update:isOpen", false);
            },
            reject: () => {},
        });
    } else {
        $emit("update:isOpen", false);
    }
};

const isEdited = computed(() => {
    return (
        oldPassword.value !== "" ||
        newPassword.value !== "" ||
        confirmPassword.value !== ""
    );
});

watch(
    () => isOpen.value,
    (val) => {
        if (!val) v$.value.$reset();
    },
);
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('edit.password')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="!isEdited"
        :showCloseIcon="!isEdited"
    >
        <div class="flex flex-col min-h-full">
            <label
                for="oldPassword"
                class="block text-surface-700 dark:text-surface-0 font-medium mb-2"
                >{{ $t("old_password") }}</label
            >
            <Password
                id="oldPassword"
                v-model="oldPassword"
                :placeholder="$t('old_password')"
                :toggleMask="true"
                class="mb-4"
                fluid
                :feedback="false"
            ></Password>

            <div
                class="text-red-500 mb-5"
                v-for="error of v$.oldPassword.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <label
                for="newPassword"
                class="block text-surface-700 dark:text-surface-0 font-medium mb-2"
                >{{ $t("new_password") }}</label
            >
            <Password
                id="newPassword"
                v-model="newPassword"
                :placeholder="$t('new_password')"
                :toggleMask="true"
                class="mb-4"
                fluid
                :feedback="false"
            ></Password>

            <div
                class="text-red-500 mb-5"
                v-for="error of v$.newPassword.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <label
                for="passwordConfirm"
                class="block text-surface-700 dark:text-surface-0 font-medium mb-2"
                >{{ $t("confirm_password") }}</label
            >
            <Password
                id="passwordConfirm"
                v-model="confirmPassword"
                :placeholder="$t('confirm_password')"
                :toggleMask="true"
                class="mb-4"
                fluid
                :feedback="false"
            ></Password>

            <div
                class="text-red-500 mb-5"
                v-for="error of v$.password_confirmation.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>
            <div slot="footer" class="mt-auto flex justify-evenly">
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    severity="danger"
                    @click="cancelEdit"
                    :disabled="loading"
                />
                <Button
                    label="Save"
                    icon="pi pi-check"
                    severity="success"
                    @click="updateItem"
                    :disabled="!isEdited"
                    :loading="loading"
                />
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
