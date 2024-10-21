<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import { $t } from "@/plugins/i18n";
import { computed, toRefs } from "vue";
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
        :header="$t('categories.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="!w-full md:!w-[30rem] lg:!w-[25rem] sidebar"
        blockScroll
    >
        <!-- <Tree :value="current"></Tree> -->

        <div class="mb-4">
            <div class="flex items-center gap-2 mt-2">
                <Avatar
                    :image="current.image || placeholder"
                    shape="circle"
                    size="large"
                ></Avatar>
                {{ current.name }}
            </div>
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("user.status") }} :</p>
            {{ current.status ? $t("common.active") : $t("common.inactive") }}
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
