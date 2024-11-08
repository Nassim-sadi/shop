<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import { $t } from "@/plugins/i18n";
import { required } from "@vuelidate/validators";
import useVuelidate from "@vuelidate/core";
import imageCompression from "browser-image-compression";
import { alphaSpace } from "@/validators/CustomValidators";
import isEqual from "lodash.isequal";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref, toRefs, watch } from "vue";
import categoryPlaceHolder from "@/assets/images/placeholder.webp";
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
    loading: {
        type: Boolean,
        required: true,
    },
});

const $emit = defineEmits(["update:isOpen", "editItem"]);

const { isOpen, current } = toRefs(props);

const edited = ref({});
const statusOptions = [
    {
        name: $t("common.active"),
        value: 1,
    },
    {
        name: $t("common.inactive"),
        value: 0,
    },
];

const rules = computed(() => ({
    name: {
        required,
        alphaSpace,
    },
    description: {
        alphaSpace,
        required,
    },
}));

const imageFile = ref(null);

const imageRules = computed(() => ({
    imageFile: {
        required,
    },
}));

const imageV$ = useVuelidate(imageRules, { imageFile });

const v$ = useVuelidate(rules, edited);
let formData = new FormData();
const previewImage = ref(categoryPlaceHolder);

const updatePicture = async (e) => {
    imageFile.value = await compressImage(e.target.files[0]);
    previewImage.value = URL.createObjectURL(imageFile.value);
    const compressedImage = new File(
        [imageFile.value],
        e.target.files[0].name.split(".")[0],
        {
            type: imageFile.value.type,
        },
    );

    formData.append("image", compressedImage);
};

const isEdited = computed(() => {
    return !(
        isEqual(edited.value, current.value) &&
        previewImage.value === current.value.image
    );
});

const editItem = () => {
    v$.value.$touch();
    if (!v$.value.$invalid && isEdited.value) {
        formData.append("id", edited.value.id);
        formData.append("name", edited.value.name);
        formData.append("description", edited.value.description);
        formData.append("status", edited.value.status);
        $emit("editItem", formData);
        v$.value.$reset();
    }
};

const compressImage = async (image) => {
    const options = {
        maxSizeMB: 2,
        maxWidthOrHeight: 1000,
        useWebWorker: true,
    };
    return await imageCompression(image, options);
};

const cancelConfirm = () => {
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
            edited.value.id = current.value.id;
            edited.value.name = current.value.name;
            edited.value.description = current.value.description;
            edited.value.status = current.value.status ? 1 : 0;
            previewImage.value = current.value.image;
        } else {
            v$.value.$reset();
            edited.value = {};
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
        :dismissable="false"
        :showCloseIcon="false"
        class="small-drawer"
    >
        <div class="flex flex-col min-h-full drawer-container">
            <div
                class="cursor-pointer mb-10 w-full aspect-[1/1] rounded-xl overflow-hidden relative"
            >
                <label
                    for="image"
                    class="w-full absolute top-0 right-0 left-0 bottom-0"
                >
                    <input
                        type="file"
                        id="image"
                        @change="updatePicture"
                        accept="image/*"
                        class="hidden"
                    />
                    <img
                        :src="previewImage || categoryPlaceHolder"
                        class="w-full object-cover !h-full"
                    />
                </label>
            </div>
            <div
                class="text-red-500 mb-5"
                v-for="error of imageV$.imageFile.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <label for="name" class="mb-5">{{ $t("roles.name") }}</label>
            <InputText
                id="name"
                v-model="edited.name"
                aria-labelledby="name"
                class="w-full mb-5"
            />

            <div
                class="text-red-500 mb-5"
                v-for="error of v$.name.$errors"
                :key="error.$uid"
            >
                <Message severity="error">{{ error.$message }}</Message>
            </div>

            <label for="description" class="mb-5">{{
                $t("categories.description")
            }}</label>
            <Textarea
                id="description"
                v-model="edited.description"
                aria-labelledby="description"
                class="w-full mb-5"
                rows="3"
            />

            <label for="status" class="mb-5">{{
                $t("categories.status")
            }}</label>

            <SelectButton
                id="status"
                v-model="edited.status"
                aria-labelledby="status"
                fluid
                :options="statusOptions"
                optionLabel="name"
                :allow-empty="false"
                optionValue="value"
                class="toggleStatusBtn"
            />
        </div>
        <template #footer>
            <div class="mt-auto flex justify-evenly">
                <Button
                    :label="$t('common.cancel')"
                    icon="pi pi-times"
                    severity="danger"
                    @click="cancelConfirm"
                    outlined
                    :disabled="loading"
                />

                <Button
                    :label="$t('common.save')"
                    icon="pi pi-check"
                    severity="success"
                    @click="editItem"
                    :loading="loading"
                    :disabled="loading"
                />
            </div>
        </template>
    </Drawer>
</template>

<style lang="scss" scoped></style>
