<script setup>
import { $t } from "@/plugins/i18n";
import { defineProps, ref } from "vue";
import Edit from "./sidebars/Edit.vue";

const editDrawer = ref(false);
defineProps({
    user: Object,
});

const edit = () => {
    editDrawer.value = true;
};
</script>

<template>
    <Edit :current="user" v-model:isOpen="editDrawer" />

    <div class="col-span-12 grid grid-cols-12 lg:col-span-6 card gap-8">
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
            class="col-span-4 mx-auto aspect-square overflow-hidden flex justify-center items-center rounded-xl"
        >
            <Image
                :src="
                    user.avatar
                        ? user.avatar
                        : 'https://images.pexels.com/photos/26347258/pexels-photo-26347258/free-photo-of-homme-portrait-jeune-homme-arriere-plan-rouge.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
                "
                class="col-span-12"
                rounded
            ></Image>
        </div>
        <!-- <Avatar
                :icon="user.avatar ? '' : 'pi pi-user'"
                class="col-span-12 mb-4"
                size="xlarge"
                shape="circle"
                :image="user.avatar ? user.avatar : ''"
                style="
                    background-color: var(--primary-color);
                    color: var(--surface-ground);
                "
            /> -->
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
