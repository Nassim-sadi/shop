<script setup>
import browsers from "@/constants/images/browsers";
import os from "@/constants/images/os";
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
        <div class="mb-4">
            <p>{{ $t("activities.by") }} :</p>
            {{ current.user.firstname + " " + current.user.lastname }}
        </div>
        <div class="mb-4">
            <p>{{ $t("activities.browser") }} :</p>
            <Image
                :src="browsers[current.browser]"
                class="w-8 object-contain block"
            />

            {{ current.browser }}
        </div>

        <div class="mb-4">
            <p>{{ $t("activities.os") }} :</p>
            <div class="flex items-center gap-4">
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

        <pre>{{ current }}</pre>
    </Drawer>
</template>

<style lang="scss" scoped></style>
