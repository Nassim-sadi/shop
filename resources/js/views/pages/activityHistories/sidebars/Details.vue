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
        :header="$t('activities.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="!w-full md:!w-100 lg:!w-[50rem]"
    >
        <div class="flex flex-col min-h-full">
            <pre>{{ current }}</pre>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
