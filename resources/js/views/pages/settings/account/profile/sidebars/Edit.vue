<script setup>
import emitter from "@/plugins/emitter";
import imageCompression from "browser-image-compression";
import { defineProps, ref, toRefs, watch } from "vue";
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

const { isOpen, current } = toRefs(props);

const previewImage = ref(null);
let currentUser = ref({});

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
            currentUser.value = current.value;
            previewImage.value = current.value.image
                ? current.value.image
                : "https://placehold.co/600x400";
        } else {
            currentUser.value = {};
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
        class="md:!w-80 lg:!w-[30rem]"
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
                    v-model="currentUser.firstname"
                />
            </div>
            <div class="field">
                <label for="lastname">lastname</label>
                <InputText
                    id="lastname"
                    type="text"
                    v-model="currentUser.lastname"
                />
            </div>
        </div>
        <div slot="footer">
            <Button
                label="Cancel"
                icon="pi pi-times"
                @click="$emit('update:isOpen', false)"
                severity="danger"
            />
            <Button
                label="Save"
                icon="pi pi-check"
                severity="success"
                @click="$emit('update:isOpen', false)"
            />
        </div>
    </Drawer>
</template>

<style lang="scss" scoped></style>
