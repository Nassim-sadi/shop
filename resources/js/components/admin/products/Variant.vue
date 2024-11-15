<script setup>
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import { required } from "@vuelidate/validators";
import { alphaSpace } from "@/validators/CustomValidators";
import isEqual from "lodash.isequal";
import { useConfirm } from "primevue/useconfirm";
import { computed, onMounted, ref, toRefs, watch } from "vue";
import { useImageCompression } from "@/utils/useImageCompression";
import productPlaceHolder from "@/assets/images/placeholder.webp";
import {
    productImageSize,
    productThumbnailImageSize,
} from "@/constants/imagesSize/Index";
import router from "@/router/Index";
const confirm = useConfirm();
// const props = defineProps({
//     product: {
//         type: Object,
//         required: true,
//     },
//     options: {
//         type: Array,
//         required: true,
//     },
//     categories: {
//         type: Array,
//         required: true,
//     },
// });

// const $emit = defineEmits(["update:isOpen", "createItem"]);

// const { isOpen, loading, categories, progress } = toRefs(props);

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

const featuredOptions = [
    {
        name: $t("products.featured"),
        value: 1,
    },
    {
        name: $t("products.not_featured"),
        value: 0,
    },
];

const product = ref({
    name: "",
    description: "",
    long_description: "",
    base_price: "",
    listing_price: "",
    base_quantity: "",
    featured: 0,
    category: null,
    status: 0,
    variants: [],
});

const rules = computed(() => ({
    name: {
        required,
        alphaSpace,
    },
}));

const v$ = useVuelidate(rules, product);

const createItem = () => {
    v$.value.$touch();
    if (v$.value.$invalid) return;
    console.log("status value: ", product.value.status);
    $emit("createItem", product.value);
    v$.value.$reset();
};

const imageFile = ref(null);

const imageRules = computed(() => ({
    imageFile: {
        required,
    },
}));

const imageV$ = useVuelidate(imageRules, { imageFile });

let formData = new FormData();
const previewImage = ref(productPlaceHolder);

const updatePicture = async (event, size) => {
    const { compressedImage, preview } = await useImageCompression(
        event.target.files[0],
        size,
    );
    previewImage.value = preview;
    formData.append("thumbnail_image_path", compressedImage);
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
        !isEqual(product.value, {
            name: "",
            description: "",
            long_description: "",
            base_price: "",
            listing_price: "",
            base_quantity: "",
            featured: 0,
            category: null,
            status: 0,
        }) || previewImage !== productPlaceHolder
    );
});

// watch(
//     () => isOpen.value,
//     (val) => {
//         if (!val) {
//             v$.value.$reset();
//             product.value = {
//                 name: "",
//                 description: "",
//                 long_description: "",
//                 base_price: "",
//                 listing_price: "",
//                 base_quantity: "",
//                 featured: 0,
//                 category: null,
//                 status: 0,
//             };
//         }
//     },
// );

onMounted(() => {
    console.log("mounted variant index");
});
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('products.create')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :showCloseIcon="false"
        block-scroll
        class="large-drawer"
    >
        <div class="column-1 md:columns-2 gap-5 mb-5">
            <label class="mb-5">{{ $t("products.thumbnail") }}</label>
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
                        @change="updatePicture($event, productImageSize)"
                        accept="image/*"
                        class="hidden"
                    />
                    <img
                        :src="previewImage || productPlaceHolder"
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
            <label for="name" class="mb-5">{{ $t("products.name") }}</label>
            <InputText
                id="name"
                v-model="product.name"
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

            <label for="status" class="mb-5">{{ $t("products.status") }}</label>

            <SelectButton
                :allow-empty="false"
                id="status"
                v-model="product.status"
                aria-labelledby="status"
                fluid
                :options="statusOptions"
                optionLabel="name"
                optionValue="value"
                class="toggleStatusBtn mb-5"
            />
            <div>
                <label for="description" class="mb-5">{{
                    $t("products.description")
                }}</label>

                <Textarea
                    id="description"
                    v-model="product.description"
                    rows="4"
                    fluid
                    maxlength="255"
                    class="mb-5"
                />
            </div>

            <label for="long_description" class="mb-5">{{
                $t("products.long_description")
            }}</label>

            <Textarea
                id="long_description"
                v-model="product.long_description"
                rows="6"
                fluid
                maxlength="1000"
                class="mb-5"
            />
            <label for="featured" class="mb-5">{{
                $t("products.featured")
            }}</label>

            <SelectButton
                :allow-empty="false"
                id="featured"
                v-model="product.featured"
                aria-labelledby="featured"
                fluid
                :options="featuredOptions"
                optionLabel="name"
                optionValue="value"
                class="toggleStatusBtn mb-5"
            />

            <label for="category" class="mb-5">{{
                $t("products.category")
            }}</label>

            <Select
                v-model="product.category"
                :options="categories"
                filter
                optionLabel="name"
                :placeholder="$t('products.categoryQuery')"
                data-key="id"
                fluid
                class="mb-5"
            >
                <template #value="slotProps">
                    <div v-if="slotProps.value" class="flex items-center">
                        <div>{{ slotProps.value.name }}</div>
                    </div>
                </template>
                <template #option="slotProps">
                    <div class="flex items-center">
                        <div>{{ slotProps.option.name }}</div>
                    </div>
                </template>
            </Select>

            <label for="base_price" class="mb-5">{{
                $t("products.base_price")
            }}</label>

            <InputText
                :aria-labelledby="$t('products.base_price')"
                id="base_price"
                type="number"
                v-model="product.base_price"
                fluid
            />
        </div>
        <template #footer>
            <div class="flex justify-end gap-5">
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
                    @click="createItem"
                    :loading="loading"
                    :disabled="loading"
                />
            </div>
        </template>
    </Drawer>
</template>

<style lang="scss" scoped></style>
