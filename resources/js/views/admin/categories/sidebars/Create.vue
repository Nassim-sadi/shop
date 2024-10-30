<script setup>
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import { alphaSpace } from "@/validators/CustomValidators";
import categoryPlaceHolder from "@/assets/images/placeholder.webp";
import isEqual from "lodash.isequal";
import { useConfirm } from "primevue/useconfirm";
import imageCompression from "browser-image-compression";
import { computed, ref, toRefs, watch } from "vue";
const confirm = useConfirm();
const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true,
    },
    loading: {
        type: Boolean,
        required: false,
    },
    parent: {
        required: false,
        default: "not_set",
    },
    progress: {
        type: Number,
        required: false,
    },
});

const $emit = defineEmits(["update:isOpen", "createItem"]);

const { isOpen, loading, parent } = toRefs(props);

const category = ref({
    name: "",
    description: "",
    parent: parent.value | null,
    status: 0,
});

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

const v$ = useVuelidate(rules, category);
let formData = new FormData();
const previewImage = ref(categoryPlaceHolder);

const updateCategoryPicture = async (e) => {
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

const compressImage = async (image) => {
    const options = {
        maxSizeMB: 2,
        maxWidthOrHeight: 1000,
        useWebWorker: true,
    };
    return await imageCompression(image, options);
};

const createItem = () => {
    v$.value.$touch();
    imageV$.value.$touch();
    if (v$.value.$invalid || imageV$.value.$invalid) return;

    if (parent.value != "not_set") {
        formData.append("parent_id", parent.value);
    }
    formData.append("name", category.value.name);
    formData.append("description", category.value.description);
    formData.append("status", category.value.status);
    console.log("status value: ", category.value.status);

    $emit("createItem", formData);
    v$.value.$reset();
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

const isEdited = computed(() => {
    return (
        !isEqual(category.value, {
            name: "",
            description: "",
            status: false,
        }) && previewImage.value !== categoryPlaceHolder
    );
});

watch(
    () => isOpen.value,
    (val) => {
        if (!val) {
            v$.value.$reset();
            category.value = {
                name: "",
                description: "",
                status: 0,
            };
            previewImage.value = categoryPlaceHolder;
        }
    },
);
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('roles.create')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :showCloseIcon="false"
        block-scroll
        class="!w-full md:!w-[30rem] lg:!w-[25rem]"
    >
        {{ category.status }}
        <div class="flex flex-col min-h-full">
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
                        @change="updateCategoryPicture"
                        accept="image/*"
                        class="hidden"
                    />
                    <img
                        :src="previewImage || categoryPlaceHolder"
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
                v-model="category.name"
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
                v-model="category.description"
                aria-labelledby="description"
                class="w-full mb-5"
                rows="3"
            />

            <div slot="footer" class="mt-auto flex justify-evenly">
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    severity="danger"
                    @click="cancelConfirm"
                    :disabled="loading"
                />

                <Button
                    label="Save"
                    icon="pi pi-check"
                    severity="success"
                    @click="createItem"
                    :loading="loading"
                    :disabled="loading"
                />
            </div>
        </div>
    </Drawer>
</template>

<style lang="scss" scoped>
:deep(.p-multiselect-label) {
    flex-wrap: wrap;
}
</style>
