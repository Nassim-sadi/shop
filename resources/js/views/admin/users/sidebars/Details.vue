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

const changes = computed(() => {
    if (props.current.data && props.current.data.changes) {
        return filterChanges(props.current.data);
    } else {
        return null;
    }
});

const $emit = defineEmits(["update:isOpen"]);

const { isOpen, current } = toRefs(props);

const filterChanges = (data) => {
    const { user, changes } = data;
    const result = {};
    Object.keys(changes).forEach((key) => {
        if (user[key] !== changes[key]) {
            result[key] = {
                prevValue: user[key],
                newValue: changes[key],
            };
        }
    });
    return result;
};

import { PerfectScrollbar } from "vue3-perfect-scrollbar";
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
            <div class="flex items-center gap-2 mt-2">
                <Avatar
                    :image="current.image || placeholder"
                    shape="circle"
                    size="large"
                ></Avatar>
                {{ current.firstname + " " + current.lastname }}
            </div>

            <p class="font-bold mt-2">{{ $t("user.email") }} :</p>
            {{ current.email }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("user.verified_at") }} :</p>
            {{ current.email_verified_at }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("activities.role") }} :</p>
            {{ current.role.name }}
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
