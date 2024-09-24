<script setup>
import { $t } from "@/plugins/i18n";
import { format } from "date-fns";
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
        :header="$t('roles.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="!w-full md:!w-[30rem] lg:!w-[25rem] sidebar"
        blockScroll
    >
        <div class="mb-4">
            <p class="font-bold mb-2">{{ $t("roles.name") }} :</p>
            <span
                :style="`background-color: #${current.color} ; color : #${current.text_color}`"
                class="highlight"
            >
                {{ current.name }}
            </span>
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("roles.description") }} :</p>
            {{ current.description || $t("roles.no_description") }}
        </div>

        <div class="mb-4">
            <p class="font-bold mb-2">{{ $t("roles.permissions") }} :</p>
            <div class="flex flex-wrap gap-2 font-semibold">
                <template
                    v-if="current.permissions && current.permissions.length > 0"
                    v-for="permission in current.permissions"
                >
                    <span
                        class="highlight text-lime-600 bg-lime-300 border-lime-600 border-2"
                    >
                        {{ permission.name }}
                    </span>
                </template>

                <template v-else>
                    <span v-if="current.name === 'Super Admin'">
                        {{ $t("roles.all_permissions") }}
                    </span>
                    <span v-else>
                        {{ $t("roles.no_permissions") }}
                    </span>
                </template>
            </div>
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("common.created_at") }} :</p>
            {{ format(current.created_at, "yyy-mm-dd hh:mm") }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("common.updated_at") }} :</p>
            {{ format(current.updated_at, "yyy-mm-dd hh:mm") }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("roles.users_count") }} :</p>
            {{ current.users_count }}
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
