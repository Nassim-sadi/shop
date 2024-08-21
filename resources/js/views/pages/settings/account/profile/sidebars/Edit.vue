<script setup>
import emitter from "@/plugins/emitter";
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

const updateProfilePicture = (e) => {
    let file = e.target.files[0];
    const image = e.target.files[0];
    const reader = new FileReader();
    reader.readAsDataURL(image);
    reader.onload = () => {
        previewImage.value = reader.result;
    };
    const formData = new FormData();
    formData.append("image", file);
    emitter.emit("update-profile-picture", formData);
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
        header="Left Drawer"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
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
        <pre>{{ currentUser }}</pre>
    </Drawer>
</template>

<style lang="scss" scoped></style>
