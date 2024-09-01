<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { ref } from "vue";
import Edit from "./sidebars/Edit.vue";
const sidebar = ref(false);

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

    <div class="grid grid-cols-12 col-span-12 md:col-span-6 card gap-8">
        <div
            class="font-semibold text-surface-900 dark:text-surface-0 text-xl col-span-12 flex justify-between items-baseline"
        >
            <h3>
                {{ $t("settings.account") }}
            </h3>
            <Button
                text
                class="bg-blue-100 dark:bg-blue-400/10 rounded-border"
                label="Edit Profile"
                @click="edit"
            />
        </div>
        <div
            class="col-span-3 mx-auto overflow-hidden rounded-xl bg-sky-400 aspect-[1/0.75]"
        >
            <Skeleton class="w-full h-full" v-if="loading" />
            <img
                :src="user?.image || placeholder"
                class="w-full !h-full object-cover"
                alt="Image"
                v-else
            />
        </div>

        <div class="col-span-9">
            <p class="font-semibold">{{ $t("firstname") }} :&#160;</p>
            <p>{{ user.firstname }}</p>

            <p class="font-semibold">{{ $t("lastname") }} :&#160;</p>
            <p>{{ user.lastname }}</p>

            <p class="font-semibold">{{ $t("email") }} :&#160;</p>
            <p>{{ user.email }}</p>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
