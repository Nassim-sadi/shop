<script setup>
import { nextTick, ref, toRefs, watch } from "vue";
const props = defineProps({
    current: {
        type: Array,
        required: true,
    },
    parentName: {
        type: String,
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

const $emit = defineEmits(["update:isOpen", "submit"]);

const changeOrder = () => {
    let data = [];
    selectedCategories.value.forEach((item, index) => {
        data.push({ id: item.id, order: index + 1 });
    });
    $emit("submit", data);
};

const { isOpen, current } = toRefs(props);
const selectedCategories = ref([]);
watch(isOpen, (val) => {
    if (!val) {
        selectedCategories.value = [];
    } else {
        selectedCategories.value = [...current.value];
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
        :draggable="false"
    >
        <template #header>
            <div class="flex justify-between items-baseline w-full">
                <h2
                    class="font-bold text-xl mb-4 text-surface-900 dark:text-surface-0"
                >
                    {{ $t("categories.change_order") }}
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
        <h2 class="text-xl mb-4 text-surface-900 dark:text-surface-0">
            {{ parentName }}
        </h2>
        <OrderList
            v-model="selectedCategories"
            dataKey="id"
            breakpoint="575px"
            class="mb-4"
        >
            <template #option="{ option }">
                {{ option.name }}
            </template>
        </OrderList>

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
                @click="changeOrder"
                :disabled="loading"
            ></Button>
        </div>
    </Dialog>
</template>

<style lang="scss" scoped>
:deep .p-listbox {
    flex: 1;
}
</style>
