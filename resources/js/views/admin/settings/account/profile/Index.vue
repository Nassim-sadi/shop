<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { ref } from "vue";
import Edit from "./sidebars/Edit.vue";

const sidebar = ref(false);
const popover = ref();

defineProps({
    user: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    },
});

const emit = defineEmits(["update:user"]);

const edit = () => {
    sidebar.value = true;
};

const uploadPercentage = ref(0);

const togglePopover = (event) => {
    popover.value.toggle(event);
};

const editItem = (val) => {
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/update", val, {
                onUploadProgress: (progressEvent) => {
                    uploadPercentage.value = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total,
                    );
                },
            })
            .then((response) => {
                uploadPercentage.value = 0;
                sidebar.value = false;
                emitter.emit("toast", {
                    summary: $t("update.success"),
                    message: $t("update.success_message"),
                    severity: "success",
                });
                emit("update:user", response.data.user);
                resolve(response);
            })
            .catch((error) => {
                uploadPercentage.value = 0;
                console.log(error);
                reject(error);
            });
    });
};
</script>

<template>
    <Edit
        :current="user"
        v-model:isOpen="sidebar"
        @editItem="editItem"
        :progress="uploadPercentage"
    />

    <div
        class="font-semibold text-surface-900 dark:text-surface-0 text-xl col-span-12 flex justify-between items-baseline"
    >
        <h3 class="text-surface-900 dark:text-surface-0">
            {{ $t("settings.personnel") }}
        </h3>

        <div class="hidden lg:block">
            <Button
                text
                class="bg-blue-100 dark:bg-blue-400/10 rounded-border"
                :label="$t('user.edit_profile')"
                icon="pi pi-user-edit"
                @click="edit"
            />
        </div>

        <div class="block lg:hidden">
            <Button text icon="pi pi-ellipsis-v" @click="togglePopover" />
        </div>

        <Popover ref="popover">
            <Button
                text
                icon="pi pi-user-edit"
                class="bg-blue-100 dark:bg-blue-400/10 rounded-border"
                :label="$t('user.edit_profile')"
                @click="edit"
            />
        </Popover>
    </div>

    <div class="flex items-center gap-4">
        <div class="flex items-center justify-center">
            <Skeleton
                class="mb-4 md:mb-0 custom-avatar"
                v-if="loading"
                shape="circle"
            />

            <Avatar
                :image="user?.image || placeholder"
                alt="Image"
                shape="circle"
                size="xlarge"
                class="mb-4 md:mb-0 custom-avatar"
                v-else
            />
        </div>
        <div>
            <p>
                <span> {{ $t("firstname") }}:&#160; </span>
                <span class="font-semibold">
                    {{ user.firstname }}
                </span>
            </p>

            <p>
                <span> {{ $t("lastname") }}:&#160; </span>
                <span class="font-semibold">
                    {{ user.lastname }}
                </span>
            </p>
            <p>
                <span> {{ $t("email") }}:&#160; </span>
                <span class="font-semibold">
                    {{ user.email }}
                </span>
            </p>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.custom-avatar {
    width: 6rem !important;
    height: 6rem !important;
    aspect-ratio: 1 / 1;
    @media screen and (max-width: 576px) {
        width: 4rem !important;
        height: 4rem !important;
    }
}
</style>
