<script setup>
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import imageCompression from "browser-image-compression";
import _ from "lodash";
import { useConfirm } from "primevue/useconfirm";
import { computed, defineEmits, defineProps, ref, toRefs, watch } from "vue";

const confirm = useConfirm();

const props = defineProps({
    current: {
        type: Object,
        required: true,
    },
    isOpen: {
        type: Boolean,
        required: true,
    },
});

const $emit = defineEmits(["update:isOpen", "editItem"]);

const { isOpen, current } = toRefs(props);

const previewImage = ref(null);
let editedUser = ref({});
const formData = new FormData();

const rules = {
    firstname: { required },
    lastname: { required },
};

const v$ = useVuelidate(rules, editedUser);

const updateProfilePicture = async (e) => {
    let file = await compressImage(e.target.files[0]);
    console.log(e.target.files[0]);

    previewImage.value = URL.createObjectURL(file);

    const compressedImage = new File(
        [file],
        e.target.files[0].name.split(".")[0],
        {
            type: file.type,
        },
    );

    formData.append("image", compressedImage);
};

const compressImage = async (image) => {
    const options = {
        maxSizeMB: 2,
        maxWidthOrHeight: 1000,
        useWebWorker: true,
    };
    return await imageCompression(image, options);
};

const isEdited = computed(() => {
    return (
        _.isEqual(editedUser.value, current.value) &&
        previewImage.value === current.value.image
    );
});

const updateItem = () => {
    v$.value.$touch();
    if (!v$.value.$invalid && !isEdited.value) {
        formData.append("id", current.value.id);
        formData.append("firstname", editedUser.value.firstname);
        formData.append("lastname", editedUser.value.lastname);
        console.log(formData);

        $emit("editItem", formData);
        v$.value.$reset();
        $emit("update:isOpen", false);
    }
};

const cancelEdit = () => {
    console.log("closing edit");
    console.log(isEdited.value);

    if (!isEdited.value) {
        confirm.require({
            header: $t("cancel.edit"),
            message: $t("cancel.edit.message"),
            icon: "pi pi-exclamation-triangle",
            rejectProps: {
                label: $t("no"),
                severity: "secondary",
                outlined: true,
            },
            acceptProps: {
                label: $t("yes"),
                severity: "danger",
            },
            accept: () => {
                $emit("update:isOpen", false);
            },
            reject: () => {},
        });
    } else {
        $emit("update:isOpen", false);
    }
};

watch(
    () => isOpen.value,
    (val) => {
        if (val) {
            setTimeout(() => {
                editedUser.value = { ...current.value };
                previewImage.value = current.value.image
                    ? current.value.image
                    : "https://placehold.co/600x400";
            }, 50);
        } else {
            editedUser.value = {};
        }
    },
);
</script>

<template>
    <Drawer
        :visible="isOpen"
        header="Edit Profile"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="isEdited"
        @close="cancelEdit"
    >
        <div class="flex flex-col min-h-full">
            <div
                class="cursor-pointer mb-10 w-full aspect-[1/0.75] rounded-xl overflow-hidden"
            >
                <label for="image" class="w-full">
                    <input
                        type="file"
                        id="image"
                        @change="updateProfilePicture"
                        accept="image/*"
                        class="hidden"
                    />
                    <Image :src="previewImage" class="object-cover w-full" />
                </label>
            </div>

            <FloatLabel class="mb-10">
                <InputText
                    id="firstname"
                    v-model="editedUser.firstname"
                    aria-labelledby="firstname"
                    class="w-full"
                />
                <label for="firstname">{{ $t("firstname") }}</label>
            </FloatLabel>

            <p
                v-for="error of v$.firstname.$silentErrors"
                :key="error.$uid"
                class="text-red-500"
            >
                {{ error.$message }}
            </p>
            <FloatLabel class="mb-10">
                <InputText
                    id="lastname"
                    v-model="editedUser.lastname"
                    aria-labelledby="lastname"
                    class="w-full"
                />
                <label for="lastname">{{ $t("lastname") }}</label>
            </FloatLabel>
            <p
                v-for="error of v$.lastname.$silentErrors"
                :key="error.$uid"
                class="text-red-500"
            >
                {{ error.$message }}
            </p>

            <div slot="footer" class="mt-auto flex justify-evenly">
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    severity="danger"
                    @click="cancelEdit"
                />
                <Button
                    label="Save"
                    icon="pi pi-check"
                    severity="success"
                    @click="updateItem"
                    :disabled="isEdited"
                />
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
