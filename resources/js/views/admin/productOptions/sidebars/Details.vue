<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import browsers from "@/constants/images/browsers";
import os from "@/constants/images/os";
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
        :header="$t('productOptions.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="small-drawer"
        blockScroll
    >
        <div class="mb-4">
            <p class="font-bold">{{ $t("productOptions.name") }} :</p>
            {{ current.name }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("productOptions.products_count") }} :</p>
            {{ current.products_count }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("productOptions.values") }} :</p>
            <span v-for="(value, index) in current.values">
                {{ value.value }}

                <template v-if="current.values.length > index + 1">
                    ,
                </template>
            </span>
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
