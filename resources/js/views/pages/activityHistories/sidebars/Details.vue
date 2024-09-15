<script setup>
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
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('activities.details')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="!w-full md:!w-[30rem] lg:!w-[25rem]"
        blockScroll
    >
        <div class="mb-4">
            <p class="font-bold">{{ $t("activities.by") }} :</p>
            <div class="flex items-center gap-2 mt-2">
                <Avatar
                    :image="current.user.image"
                    shape="circle"
                    size="large"
                ></Avatar>
                {{ current.user.firstname + " " + current.user.lastname }}
            </div>

            <p class="font-bold mt-2">{{ $t("user.email") }} :</p>
            {{ current.user.email }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("activities.role") }} :</p>
            {{ current.user.role.name }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("activities.browser") }} :</p>
            <div class="flex gap-2 items-center mt-2">
                <Image
                    :src="browsers[current.browser]"
                    class="w-8 object-contain block"
                />

                {{ current.browser }}
            </div>
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("activities.os") }} :</p>
            <div class="flex items-center gap-2 mt-2">
                <Image
                    :src="
                        os[
                            current.platform === 'Other'
                                ? 'Other'
                                : current.platform
                        ]
                    "
                    class="w-8 object-contain block"
                />
                {{ current.platform }}
            </div>
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("activities.action_message") }} :</p>
            {{ current.action }} =>
            {{
                current.user.id == current.data.user.id
                    ? $t("activities.self")
                    : $t("activities.models." + current.model)
            }}
        </div>

        <div class="mb-4">
            <p class="font-bold">{{ $t("common.time") }} :</p>
            {{ current.created_at }}
        </div>

        <div class="mb-4" v-if="changes">
            <p class="font-bold">{{ $t("activities.changes") }} :</p>
            <div v-for="(change, key, index) in changes" :key="index">
                <p>{{ $t("common." + key) }}</p>
                <p>
                    <span class="font-bold bg-orange-500 text-white"
                        >{{ $t("common.from") }} :</span
                    >
                    {{ change.prevValue }}
                </p>
                <p>
                    <span class="font-bold bg-green-500 text-white"
                        >{{ $t("common.to") }} :</span
                    >
                    {{ change.newValue }}
                </p>
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
