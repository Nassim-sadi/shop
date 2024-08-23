<script setup>
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import imageCompression from "browser-image-compression";
import _ from "lodash";
import { useConfirm } from "primevue/useconfirm";
import {
    computed,
    defineEmits,
    defineProps,
    nextTick,
    ref,
    toRefs,
    watch,
} from "vue";

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

const $emit = defineEmits(["update:isOpen"]);

const { isOpen, current } = toRefs(props);

const previewImage = ref(null);
let editedUser = ref({});

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

    const formData = new FormData();
    formData.append("image", compressedImage);
    emitter.emit("update-profile-picture", formData);
};

const compressImage = async (image) => {
    const options = {
        maxSizeMB: 2,
        maxWidthOrHeight: 1000,
        useWebWorker: true,
    };
    return await imageCompression(image, options);
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

const isEdited = computed(() => {
    return _.isEqual(editedUser.value, current.value);
});

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
</script>

<template>
    <Drawer
        :visible="isOpen"
        header="Edit Profile"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        class="sm:!w-1/2 md:!w-90 lg:!w-[30rem]"
        :dismissable="isEdited"
    >
        <div>
            <!-- input image here -->
            <label for="image">
                <input
                    type="file"
                    id="image"
                    @change="updateProfilePicture"
                    accept="image/*"
                    class="hidden"
                />
                <img
                    :src="previewImage"
                    class="w-24 h-24 object-cover rounded-full"
                />
            </label>
        </div>
        <div class="p-fluid">
            <div class="field">
                <label for="firstname">firstname</label>
                <InputText
                    id="firstname"
                    type="text"
                    v-model="editedUser.firstname"
                />
            </div>
            <div class="field">
                <label for="lastname">lastname</label>
                <InputText
                    id="lastname"
                    type="text"
                    v-model="editedUser.lastname"
                />
            </div>
        </div>
        <div slot="footer">
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
                @click="$emit('update:isOpen', false)"
                :disabled="isEdited"
            />
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
