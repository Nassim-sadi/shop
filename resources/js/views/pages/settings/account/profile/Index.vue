<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { defineEmits, defineProps, ref } from "vue";
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

const editItem = (val) => {
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/update", val)
            .then((response) => {
                emitter.emit("toast", {
                    summary: $t("update.success"),
                    message: $t("update.success_message"),
                    severity: "success",
                });

                emit("update:user", response.data.user);
                resolve(response);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            });
    });
};
</script>

<template>
    <Edit :current="user" v-model:isOpen="sidebar" @editItem="editItem" />

    <div class="col-span-12 grid grid-cols-12 xl:col-span-6 card gap-8">
        <div
            class="font-semibold text-surface-900 dark:text-surface-0 text-xl col-span-12 flex justify-between"
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
            class="col-span-4 mx-auto overflow-hidden flex justify-center items-center rounded-xl bg-sky-400 w-full aspect-[1/0.75]"
        >
            <ProgressSpinner
                v-if="loading"
                style="width: 50px; height: 50px"
                strokeWidth="8"
                fill="transparent"
                animationDuration=".5s"
            />
            <Image
                :src="user.image ? user.image : placeholder"
                class="object-scale-down"
                v-else
                alt="Image"
            ></Image>
        </div>

        <div class="col-span-8">
            <div class="col-span-12 flex">
                <p class="font-semibold text-surface-900 dark:text-surface-0">
                    <i class="pi pi-envelope"></i>
                    {{ $t("auth.email") }} :&#160;
                </p>
                <p>{{ user.email }}</p>
            </div>

            <div class="col-span-12 flex">
                <p class="">
                    <i class="pi pi-user"></i>
                    {{ $t("firstname") }} :&#160;
                </p>
                <p>{{ user.firstname }}</p>
            </div>

            <div class="col-span-12 flex">
                <p class="">
                    <i class="pi pi-user"></i>

                    {{ $t("lastname") }} :&#160;
                </p>
                <p>{{ user.lastname }}</p>
            </div>

            <div class="col-span-12 flex">
                <p class="">
                    <i class="pi pi-"></i>

                    {{ $t("role") }} :&#160;
                </p>
                <p>{{ user.role }}</p>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
