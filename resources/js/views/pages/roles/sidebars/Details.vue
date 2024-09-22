<script setup>
import { $t } from "@/plugins/i18n";
import { toRefs } from "vue";
const props = defineProps({
    current: {
        required: true,
    },
    isOpen: {
        required: true,
    },
});

const $emit = defineEmits(["update:isOpen"]);
const { isOpen, current } = toRefs(props);
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('users.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="!w-full md:!w-[30rem] lg:!w-[25rem] sidebar"
        blockScroll
    >
        <div class="mb-4">
            <p class="font-bold">{{ $t("roles.name") }} :</p>
            {{ current.name }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("common.created_at") }} :</p>
            {{ current.created_at }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("common.updated_at") }} :</p>
            {{ current.updated_at }}
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
