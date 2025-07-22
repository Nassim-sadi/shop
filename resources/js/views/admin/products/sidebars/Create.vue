<script setup>
import { $t } from "@/plugins/i18n";
import useVuelidate from "@vuelidate/core";
import {
    maxLength,
    required,
    numeric,
    minLength,
    requiredIf,
} from "@vuelidate/validators";
import {
    alphaSpace,
    textContentMaxLength,
    validateDecimalFormat,
} from "@/validators/CustomValidators";
import ImageInput from "@/components/admin/products/imagesInput/Index.vue";
import isEqual from "lodash.isequal";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref, toRefs, watch } from "vue";
import { useImageCompression } from "@/utils/useImageCompression";
import productPlaceHolder from "@/assets/images/placeholder.webp";
import { productThumbnailImageSize } from "@/constants/imagesSize/Index";

const activeStep = ref(1);
const previewImage = ref(productPlaceHolder);
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
    currencies: {
        type: Array,
        required: true,
    },
});

const $emit = defineEmits(["update:isOpen", "createItem"]);

const { isOpen, loading, categories, currencies } = toRefs(props);

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

const selectedOptions = ref([]);

const product = ref({
    name: "",
    description: "",
    long_description: "",
    base_price: "",
    listing_price: "",
    base_quantity: "",
    weight: null,
    featured: 0,
    category: null,
    status: 0,
    weight_unit: "g",
});

const weightOptions = [
    { name: "Grams", value: "g" },
    { name: "Kilograms", value: "kg" },
];

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
        textContentLimit: textContentMaxLength(10000),
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
    status: {
        required,
    },
    featured: {
        required,
    },
    weight: {
        numeric: requiredIf((value) => value !== null && value !== ""),
    },
    weight_unit: {
        requiredIf: requiredIf((_, vm) => {
            return vm.weight !== null && vm.weight !== "";
        }),
    },
    category: {
        required,
    },
}));

const v$ = useVuelidate(rules, product);

const imageFile = ref(null);

const imageRules = computed(() => ({
    imageFile: {
        required,
    },
}));

const imageV$ = useVuelidate(imageRules, { imageFile });

const imagesRules = computed(() => ({
    images: {
        required,
        minLength: minLength(1),
    },
}));
const images = ref([]);

const imagesV$ = useVuelidate(imagesRules, { images: images });

let formData = new FormData();
const updatePicture = async (event, size) => {
    const { compressedImage, preview, error } = await useImageCompression(
        event.target.files[0],
        size,
    );
    if (error) {
        console.log(error);
        return;
    }

    previewImage.value = preview;
    imageFile.value = compressedImage;
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
            weight: null,
            featured: 0,
            category: null,
            weight_unit: "g",
            status: 0,
        }) || previewImage.value !== productPlaceHolder
    );
});

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
                weight: null,
                featured: 0,
                category: null,
                weight_unit: "g",
                status: 0,
            };
            //reset image
            imageFile.value = null;
            previewImage.value = productPlaceHolder;
            activeStep.value = 1;
            formData = null;
        } else {
            product.value.currency = currencies.value.find(
                (currency) => currency.code === "DZD",
            );
        }
    },
);

const createItem = () => {
    v$.value.$touch();
    if (v$.value.$invalid) return;
    images.value.forEach((image) => {
        formData.append("images[]", image);
    });
    formData.append("thumbnail_image_path", imageFile.value);
    formData.append("name", product.value.name);
    formData.append("description", product.value.description);

    product.value.long_description = product.value.long_description.replace(
        /&nbsp;/g,
        " ",
    );

    formData.append("long_description", product.value.long_description);
    formData.append("base_price", product.value.base_price);
    formData.append("listing_price", product.value.listing_price);
    formData.append("base_quantity", product.value.base_quantity);
    formData.append("currency_id", product.value.currency.id);
    if (
        product.value.weight !== null &&
        product.value.weight !== "" &&
        product.value.weight !== undefined
    ) {
        formData.append("weight", product.value.weight);
        formData.append("weight_unit", product.value.weight_unit);
    }
    formData.append("featured", product.value.featured);
    formData.append("category_id", product.value.category.id);
    formData.append("status", product.value.status);
    if (selectedOptions.value.length > 0) {
        formData.append("options", JSON.stringify(selectedOptions.value));
    }
    $emit("createItem", formData);
    v$.value.$reset();
};

const nextStep = () => {
    if (activeStep.value == 1) {
        imageV$.value.$touch();
        if (imageV$.value.$invalid) return;
    }

    if (activeStep.value == 2) {
        imagesV$.value.$touch();
        if (imagesV$.value.$invalid) return;
    }
    activeStep.value++;
};

const prevStep = () => {
    if (activeStep.value > 1) {
        activeStep.value--;
    }
};
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
        class="extra-large-drawer"
    >
        <div
            class="h-full w-full flex items-center justify-center"
            v-if="loading"
        >
            <ProgressBar :value="progress" v-if="progress > 0"></ProgressBar>
            <ProgressSpinner
                stroke-width="8"
                fill="transparent"
                animation-duration=".5s"
                aria-label="Custom ProgressSpinner"
            />
        </div>
        <Stepper v-model:value="activeStep" :linear="true" v-if="!loading">
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
                                class="w-full object-contain !h-full"
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
                    <ImageInput v-model="images" />
                    <div
                        class="text-red-500 mb-5"
                        v-for="error of imagesV$.images.$errors"
                        :key="error.$uid"
                    >
                        <Message severity="error">{{ error.$message }}</Message>
                    </div>
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
                    </div>
                    <div class="mb-5">
                        <label for="long_description" class="mb-5">{{
                            $t("products.long_description")
                        }}</label>

                        <Editor
                            id="long_description"
                            v-model="product.long_description"
                            fluid
                            class="mb-5"
                        >
                            <template #toolbar>
                                <span class="ql-formats">
                                    <select class="ql-header"></select>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-bold"></button>
                                    <button class="ql-italic"></button>
                                    <button class="ql-underline"></button>
                                </span>
                                <span class="ql-formats">
                                    <select class="ql-color"></select>
                                    <select class="ql-background"></select>
                                </span>
                                <span class="ql-formats">
                                    <button
                                        class="ql-list"
                                        value="ordered"
                                    ></button>
                                    <button
                                        class="ql-list"
                                        value="bullet"
                                    ></button>
                                    <button
                                        class="ql-list"
                                        value="check"
                                    ></button>
                                </span>
                                <span class="ql-formats">
                                    <button
                                        class="ql-link"
                                        value="ordered"
                                    ></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-clean"></button>
                                </span>
                            </template>
                        </Editor>

                        <div
                            v-for="error of v$.long_description.$errors"
                            :key="error.$uid"
                        >
                            <Message severity="error">{{
                                error.$message
                            }}</Message>
                        </div>
                    </div>
                    <div class="column-1 md:columns-2 gap-5 mb-5">
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
                            <label for="options" class="mb-5"
                                >{{ $t("products.options") }}
                            </label>

                            <MultiSelect
                                v-model="selectedOptions"
                                display="chip"
                                :options="options"
                                option-label="name"
                                placeholder="Select options"
                                option-value="id"
                                :max-selected-labels="3"
                                :show-toggle-all="true"
                                fluid
                            />
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
                            <label for="weight" class="mb-5">{{
                                $t("products.weight")
                            }}</label>
                            <InputGroup>
                                <InputText
                                    :aria-labelledby="$t('products.weight')"
                                    id="weight"
                                    type="number"
                                    v-model="product.weight"
                                    fluid
                                    class="mb-5"
                                />

                                <Select
                                    :options="weightOptions"
                                    v-model="product.weight_unit"
                                    option-label="name"
                                    option-value="value"
                                    placeholder="Select weight unit"
                                    class="mb-5"
                                ></Select>
                            </InputGroup>

                            <div
                                class="text-red-500"
                                v-for="error of v$.weight.$errors"
                                :key="error.$uid"
                            >
                                <Message severity="error">{{
                                    error.$message
                                }}</Message>
                            </div>

                            <div
                                class="text-red-500"
                                v-for="error of v$.weight_unit.$errors"
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
                                v-model="product.listing_price"
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

                        <div class="currency-input">
                            <label for="currency" class="mb-5">
                                {{ $t("products.currency") }}
                            </label>
                            {{ product.currency }}
                            <Select
                                id="currency"
                                :options="currencies"
                                v-model="product.currency"
                                option-label="code"
                                :placeholder="$t('products.select_currency')"
                                class="mb-5"
                            >
                                <template #option="slotProps">
                                    {{ slotProps.option.code }} -
                                    {{ slotProps.option.name }}
                                </template>

                                <template #value="slotProps">
                                    {{ slotProps.value.code }} -
                                    {{ slotProps.value.name }}
                                </template>
                            </Select>
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
                    @click="prevStep"
                    v-if="activeStep !== 1"
                />

                <Button
                    :label="$t('common.next')"
                    @click="nextStep"
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
