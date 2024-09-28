<script setup>
import { ref, toRefs, watch } from "vue";
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
    roles: {
        type: Array,
        required: true,
    },
});

const $emit = defineEmits(["update:isOpen", "submit"]);
const selectedRole = ref();

const changeRole = () => {
    $emit("submit", { role_id: selectedRole.value, user_id: current.value.id });
};

const { isOpen, current } = toRefs(props);

watch(isOpen, (val) => {
    if (!val) {
        selectedRole.value = null;
    } else {
        selectedRole.value = current.value.role.id;
    }
});

const close = () => {
    $emit("update:isOpen", false);
};
</script>
<template>
    <Dialog
        v-model:visible="isOpen"
        modal
        header="Edit Profile"
        :style="{ width: '25rem' }"
        :closeOnEscape="true"
        :closable="false"
    >
        <template #header>
            <div class="flex justify-between items-baseline w-full">
                <h2 class="font-bold text-xl mb-4">
                    {{ $t("users.change_role") }}
                </h2>
                <Button
                    icon="ti ti-x"
                    text
                    rounded
                    severity="secondary"
                    @click="close"
                    style="font-size: larger"
                    v-tooltip.bottom="$t('common.close')"
                />
            </div>
        </template>

        <div class="mb-4">
            <span class="font-bold mb-2">{{ $t("common.for") }} :</span>
            {{ current.firstname + " " + current.lastname }}
        </div>
        <Select
            v-model="selectedRole"
            :options="roles"
            optionLabel="name"
            optionValue="id"
            :placeholder="$t('user.roleQuery')"
            class="w-full mb-4"
        />

        <div class="flex justify-end gap-2">
            <Button
                type="button"
                label="Cancel"
                severity="secondary"
                @click="close"
            ></Button>
            <Button
                type="button"
                label="Save"
                @click="changeRole"
                :disabled="loading || selectedRole == current.role.id"
            ></Button>
        </div>
    </Dialog>
</template>

<style lang="scss" scoped></style>
