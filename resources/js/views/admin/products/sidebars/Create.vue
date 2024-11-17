<script setup>
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import { maxLength, required, numeric, minLength } from "@vuelidate/validators";
import {
    alphaSpace,
    validateDecimalFormat,
} from "@/validators/CustomValidators";
import ImageInput from "@/components/admin/products/imagesInput/Index.vue";
import isEqual from "lodash.isequal";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref, toRefs, watch } from "vue";
import { useImageCompression } from "@/utils/useImageCompression";
import productPlaceHolder from "@/assets/images/placeholder.webp";
import {
    productImageSize,
    productThumbnailImageSize,
} from "@/constants/imagesSize/Index";

const activeStep = ref(1);

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
    options: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    progress: {
        type: Number,
        required: false,
        default: 0,
    },
});

const $emit = defineEmits(["update:isOpen", "createItem"]);

const { isOpen, loading, categories, progress } = toRefs(props);

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
});

const rules = computed(() => ({
    name: {
        required,
        alphaSpace,
        maxLength: maxLength(255),
        minLength: minLength(3),
    },
    description: {
        alphaSpace,
        required,
        maxLength: maxLength(255),
        minLength: minLength(10),
    },
    long_description: {
        required,
        alphaSpace,
        maxLength: maxLength(2000),
        minLength: minLength(10),
    },
    base_price: {
        required,
        numeric,
        validateDecimalFormat,
    },
    listing_price: {
        required,
        numeric,
        validateDecimalFormat,
    },
    base_quantity: {
        numeric,
        required,
    },
    category: {
        required,
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
    const { compressedImage, preview, error } = await useImageCompression(
        event.target.files[0],
        size,
    );
    if (error) {
        console.log(error);
        return;
    }
    console.log("success");

    previewImage.value = preview;
    formData.append("thumbnail_image_path", compressedImage);
};

const send = ref(false);

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
        }) || previewImage.value !== productPlaceHolder
    );
});

const addProductImages = (val) => {
    val.forEach((image) => {
        console.log(image);
        formData.append("images[]", image);
    });
    send.value = false;
};

watch(
    () => isOpen.value,
    (val) => {
        if (!val) {
            v$.value.$reset();
            product.value = {
                name: "",
                description: "",
                long_description: "",
                base_price: "",
                listing_price: "",
                base_quantity: "",
                featured: 0,
                category: null,
                status: 0,
            };
        }
    },
);
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('products.create')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :show-close-icon="false"
        block-scroll
        class="large-drawer"
    >
        <Stepper v-model:value="activeStep" :linear="true">
            <StepItem :value="1">
                <Step>{{ $t("products.p_thumbnail") }}</Step>
                <StepPanel>
                    <div
                        class="cursor-pointer mb-5 w-1/2 aspect-[1/1] rounded-xl overflow-hidden relative"
                    >
                        <label
                            for="image"
                            class="w-full absolute top-0 right-0 left-0 bottom-0"
                        >
                            <input
                                type="file"
                                id="image"
                                @change="
                                    updatePicture(
                                        $event,
                                        productThumbnailImageSize,
                                    )
                                "
                                accept="image/*"
                                class="hidden"
                            />
                            <img
                                :src="previewImage || productPlaceHolder"
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
                </StepPanel>
            </StepItem>
            <StepItem :value="2">
                <Step>{{ $t("products.p_images") }}</Step>
                <StepPanel>
                    <ImageInput
                        @add-images="addProductImages"
                        :step="activeStep"
                    />
                </StepPanel>
            </StepItem>
            <StepItem :value="3">
                <Step>{{ $t("products.p_info") }}</Step>
                <StepPanel>
                    <div class="column-1 md:columns-2 gap-5 mb-5">
                        <div class="mb-5">
                            <label for="name" class="mb-5">{{
                                $t("products.name")
                            }}</label>
                            <InputText
                                id="name"
                                v-model="product.name"
                                aria-labelledby="name"
                                class="w-full mb-5"
                            />

                            <div
                                class="text-red-500"
                                v-for="error of v$.name.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="status" class="mb-5">{{
                                $t("products.status")
                            }}</label>

                            <SelectButton
                                :allow-empty="false"
                                id="status"
                                v-model="product.status"
                                aria-labelledby="status"
                                fluid
                                :options="statusOptions"
                                option-label="name"
                                option-value="value"
                                class="toggleStatusBtn"
                            />
                        </div>

                        <div class="mb-5">
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

                            <div
                                class="text-red-500"
                                v-for="error of v$.description.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>
                        </div>

                        <div class="mb-5">
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

                            <div
                                class="text-red-500"
                                v-for="error of v$.long_description.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>
                        </div>

                        <div class="mb-5">
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
                                option-label="name"
                                option-value="value"
                                class="toggleStatusBtn"
                            />
                        </div>

                        <div class="mb-5">
                            <label for="category" class="mb-5">{{
                                $t("products.category")
                            }}</label>

                            <Select
                                v-model="product.category"
                                :options="categories"
                                filter
                                option-label="name"
                                :placeholder="$t('products.categoryQuery')"
                                data-key="id"
                                fluid
                                class="mb-5"
                            >
                                <template #value="slotProps">
                                    <div
                                        v-if="slotProps.value"
                                        class="flex items-center"
                                    >
                                        <div>{{ slotProps.value.name }}</div>
                                    </div>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex items-center">
                                        <div>{{ slotProps.option.name }}</div>
                                    </div>
                                </template>
                            </Select>

                            <div
                                class="text-red-500"
                                v-for="error of v$.category.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="base_quantity" class="mb-5">{{
                                $t("products.base_quantity")
                            }}</label>

                            <InputText
                                :aria-labelledby="$t('products.base_quantity')"
                                id="base_quantity"
                                type="number"
                                v-model="product.base_quantity"
                                fluid
                                class="mb-5"
                            />

                            <div
                                class="text-red-500"
                                v-for="error of v$.base_quantity.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="base_price" class="mb-5">{{
                                $t("products.base_price")
                            }}</label>
                            <InputText
                                :aria-labelledby="$t('products.base_price')"
                                id="base_price"
                                type="number"
                                v-model="product.base_price"
                                fluid
                                class="mb-5"
                            />

                            <div
                                class="text-red-500"
                                v-for="error of v$.base_price.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="listing_price" class="mb-5">{{
                                $t("products.listing_price")
                            }}</label>

                            <InputText
                                :aria-labelledby="$t('products.listing_price')"
                                id="listing_price"
                                type="number"
                                v-model="product.base_price"
                                fluid
                                class="mb-5"
                            />

                            <div
                                class="text-red-500"
                                v-for="error of v$.listing_price.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>
                        </div>
                    </div>
                </StepPanel>
            </StepItem>
        </Stepper>

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
                    :label="$t('common.back')"
                    severity="secondary"
                    @click="activeStep--"
                    v-if="activeStep !== 1"
                />

                <Button
                    :label="$t('common.next')"
                    @click="activeStep++"
                    v-if="activeStep !== 3"
                />

                <Button
                    v-if="activeStep === 3"
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
