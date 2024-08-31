<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import {
    alpha,
    helpers,
    maxLength,
    minLength,
    required,
} from "@vuelidate/validators";
import imageCompression from "browser-image-compression";
import _ from "lodash";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref, toRefs, watch } from "vue";

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
    progress: {
        type: Number,
        required: false,
    },
});

const $emit = defineEmits(["update:isOpen", "editItem"]);

const { isOpen, current } = toRefs(props);

const previewImage = ref(null);
let editedUser = ref({});
const formData = new FormData();

const rules = {
    firstname: {
        required: helpers.withMessage("This field cannot be empty", required),
        maxLength: 255,
        minLength: minLength(3),
        alpha,
    },
    lastname: {
        required: helpers.withMessage("This field cannot be empty", required),
        maxLength: 255,
        minLength: minLength(3),
        alpha,
    },
};

const v$ = useVuelidate(rules, editedUser);

const updateProfilePicture = async (e) => {
    let file = await compressImage(e.target.files[0]);
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
    return !(
        _.isEqual(editedUser.value, current.value) &&
        previewImage.value === current.value.image
    );
});

const updateItem = () => {
    v$.value.$touch();
    if (!v$.value.$invalid && isEdited.value) {
        formData.append("id", current.value.id);
        formData.append("firstname", editedUser.value.firstname);
        formData.append("lastname", editedUser.value.lastname);
        $emit("editItem", formData);
        v$.value.$reset();
    }
};

const cancelEdit = () => {
    if (isEdited.value) {
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
                    : placeholder;
            }, 50);
        } else {
            v$.value.$reset();
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
        :dismissable="!isEdited"
        :showCloseIcon="!isEdited"
    >
        <div class="flex flex-col min-h-full">
            <div
                class="cursor-pointer mb-10 w-full aspect-[1/0.75] rounded-xl overflow-hidden relative"
            >
                <label
                    for="image"
                    class="w-full absolute top-0 right-0 left-0 bottom-0"
                >
                    <input
                        type="file"
                        id="image"
                        @change="updateProfilePicture"
                        accept="image/*"
                        class="hidden"
                    />
                    <img
                        :src="previewImage"
                        class="w-full object-cover !h-full"
                    />
                </label>
                <div
                    class="mb-5 absolute z-10 right-2 left-2 bottom-0"
                    v-if="progress > 0"
                >
                    <ProgressBar :value="progress"></ProgressBar>
                </div>
            </div>

            <label
                for="firstname"
                class="mb-5 text-surface-700 dark:text-surface-0"
                >{{ $t("firstname") }}</label
            >
            <InputText
                id="firstname"
                v-model="editedUser.firstname"
                aria-labelledby="firstname"
                class="w-full mb-5"
            />

            <div
                class="text-red-500 mb-5"
                v-for="error of v$.firstname.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <label
                for="lastname"
                class="mb-5 text-surface-700 dark:text-surface-0"
                >{{ $t("lastname") }}</label
            >
            <InputText
                id="lastname"
                v-model="editedUser.lastname"
                aria-labelledby="lastname"
                class="w-full mb-5"
            />
            <div
                class="text-red-500 mb-5"
                v-for="error of v$.lastname.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

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
                    :disabled="!isEdited"
                />
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
