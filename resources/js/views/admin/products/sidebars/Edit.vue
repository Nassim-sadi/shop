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
    validateDecimalFormat,
    textContentMaxLength,
} from "@/validators/CustomValidators";
import ImageInput from "@/components/admin/products/imagesInput/Index.vue";
import isEqual from "lodash.isequal";
import { useConfirm } from "primevue/useconfirm";
import { computed, ref, toRefs, watch } from "vue";
import { useImageCompression } from "@/utils/useImageCompression";
import productPlaceHolder from "@/assets/images/placeholder.webp";
import { productThumbnailImageSize } from "@/constants/imagesSize/Index";

const optionsChanged = computed(() => {
    const currentOptionIds = current.value.options
        .map((option) => option.id)
        .sort();
    const editedOptionIds = [...edited.value.options].sort();

    return !isEqual(currentOptionIds, editedOptionIds);
});

const activeStep = ref(1);
const previewImage = ref(productPlaceHolder);
const confirm = useConfirm();
const props = defineProps({
    current: {
        required: true,
        type: Object,
    },
    isOpen: {
        type: Boolean,
        required: true,
    },
    loading: {
        type: Boolean,
        required: false,
    },
    categories: {
        type: Array,
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
    progress: {
        type: Number,
        required: false,
        default: 0,
    },
    loadingImages: {
        type: Boolean,
        required: false,
        default: false,
    },
    currencies: {
        type: Array,
        required: true,
    },
});

const $emit = defineEmits(["update:isOpen", "editItem"]);

const { isOpen, loading, categories, current, loadingImages, currencies } =
    toRefs(props);

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

const weightOptions = [
    {
        name: $t("products.weights.kg"),
        value: "kg",
    },
    {
        name: $t("products.weights.grams"),
        value: "g",
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

const edited = ref({
    name: "",
    description: "",
    long_description: "",
    base_price: "",
    listing_price: "",
    base_quantity: "",
    featured: 0,
    weight: null,
    weight_unit: null,
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

const v$ = useVuelidate(rules, edited);

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

const imagesV$ = useVuelidate(imagesRules, edited);

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
        !isEqual(edited.value, {
            name: "",
            description: "",
            long_description: "",
            base_price: "",
            listing_price: "",
            base_quantity: "",
            featured: 0,
            category: null,
            weight: null,
            weight_unit: null,
            status: 0,
        }) || previewImage.value !== productPlaceHolder
    );
});

const editItem = () => {
    v$.value.$touch();
    if (v$.value.$invalid) return;
    edited.value.images.forEach((image, index) => {
        if (image instanceof File) {
            // Add file directly to FormData
            formData.append(`images[${index}][file]`, image);
        } else if (typeof image === "object") {
            // Add object properties to FormData
            console.log("image", image);

            Object.entries(image).forEach(([key, value]) => {
                formData.append(`images[${index}][${key}]`, value);
            });
        }
    });

    if (imageFile.value !== current.value.thumbnail_image_path) {
        formData.append("thumbnail_image_path", imageFile.value);
    }
    formData.append("id", edited.value.id);
    formData.append("name", edited.value.name);
    formData.append("description", edited.value.description);
    formData.append("long_description", edited.value.long_description);
    formData.append("base_price", edited.value.base_price);
    formData.append("listing_price", edited.value.listing_price);
    formData.append("currency_id", edited.value.currency.id);
    if (!haveVariants.value) {
        formData.append("base_quantity", edited.value.base_quantity);
    }

    if (
        edited.value.weight !== null &&
        edited.value.weight !== "" &&
        edited.value.weight !== undefined
    ) {
        formData.append("weight", edited.value.weight);
        formData.append("weight_unit", edited.value.weight_unit);
    }
    formData.append("featured", edited.value.featured);
    console.log("option changed", optionsChanged.value);

    if (optionsChanged.value) {
        formData.append("options", JSON.stringify(edited.value.options));
    }

    formData.append("category_id", edited.value.category.id);
    formData.append("status", edited.value.status);

    $emit("editItem", formData);
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

const resetForm = () => {
    v$.value.$reset();
    imagesV$.value.$reset();
    imageV$.value.$reset();
    edited.value = {
        name: "",
        description: "",
        long_description: "",
        base_price: "",
        listing_price: "",
        base_quantity: "",
        featured: 0,
        category: null,
        weight: null,
        status: 0,
        images: [],
    };
    imageFile.value = null;
    previewImage.value = productPlaceHolder;
    activeStep.value = 1;
    formData = null;
};

watch(
    () => isOpen.value,
    (val) => {
        if (val) {
            console.log(current.value);

            // edited.value = { ...current.value };
            edited.value.id = current.value.id;
            edited.value.name = current.value.name;
            edited.value.description = current.value.description;
            edited.value.long_description = current.value.long_description;
            edited.value.base_price = current.value.base_price;
            edited.value.currency = current.value.currency;
            edited.value.listing_price = current.value.listing_price;
            edited.value.base_quantity = current.value.base_quantity;
            edited.value.weight = current.value.weight;
            edited.value.weight_unit = current.value.weight_unit;
            edited.value.category = current.value.category;
            edited.value.status = current.value.status ? 1 : 0;
            edited.value.featured = current.value.featured ? 1 : 0;
            edited.value.options = current.value.options.map(
                (option) => option.id,
            );
            previewImage.value = current.value.thumbnail_image_path;
            imageFile.value = current.value.thumbnail_image_path;
            console.log("done copying");
            formData = new FormData();
        } else {
            resetForm();
            console.log("done resetting");
        }
    },
);

watch(
    () => loadingImages.value,
    (val) => {
        if (isOpen.value && !val) {
            edited.value.images = JSON.parse(
                JSON.stringify(current.value.images),
            );
            console.log("done copying images");
        }
    },
);

const haveVariants = computed(() => {
    return (
        current.value.variants_count !== null &&
        current.value.variants_count > 0
    );
});
</script>

<template>
    <Drawer
        :visible="isOpen"
        :header="$t('products.edit')"
        position="right"
        @update:visible="$emit('update:isOpen', $event)"
        :dismissable="false"
        :show-close-icon="false"
        block-scroll
        class="extra-large-drawer"
    >
        >
        <div
            class="h-full w-full flex items-center justify-center"
            v-if="loading || loadingImages"
        >
            <ProgressBar :value="progress" v-if="progress > 0"></ProgressBar>
            <ProgressSpinner
                stroke-width="8"
                fill="transparent"
                animation-duration=".5s"
                aria-label="Custom ProgressSpinner"
            />
        </div>
        <Stepper
            v-model:value="activeStep"
            :linear="true"
            v-if="!loading && !loadingImages"
        >
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
                                :src="previewImage"
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
                    <ImageInput v-model="edited.images" />
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
                                v-model="edited.name"
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
                                v-model="edited.status"
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
                                v-model="edited.description"
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
                            v-model="edited.long_description"
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
                                v-model="edited.featured"
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
                                v-model="edited.category"
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
                                v-model="edited.options"
                                display="chip"
                                :options="options"
                                option-label="name"
                                placeholder="Select options"
                                option-value="id"
                                :max-selected-labels="3"
                                :show-toggle-all="true"
                                fluid
                                :disabled="haveVariants"
                            />

                            <div
                                v-if="haveVariants"
                                class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg"
                            >
                                <p
                                    class="text-sm text-amber-800 flex items-start"
                                >
                                    <i
                                        class="pi pi-exclamation-triangle mr-2 mt-0.5 text-amber-500"
                                    ></i>
                                    <span>{{
                                        $t(
                                            "products.variants_count_options_warning",
                                        )
                                    }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="mb-5 break-inside-avoid">
                            <label for="base_quantity" class="mb-5">{{
                                $t("products.base_quantity")
                            }}</label>

                            <InputText
                                :aria-labelledby="$t('products.base_quantity')"
                                id="base_quantity"
                                type="number"
                                v-model="edited.base_quantity"
                                fluid
                                :disabled="haveVariants"
                            />

                            <div
                                v-if="haveVariants"
                                class="mt-3 p-3 bg-blue-100 border border-blue-300 rounded"
                            >
                                <p class="text-sm text-blue-700">
                                    <i class="pi pi-info-circle mr-1"></i>
                                    {{
                                        $t(
                                            "products.variants_count_quantity_warning",
                                        )
                                    }}
                                    {{
                                        $t(
                                            "products.variants.title",
                                            current.variants_count,
                                        )
                                    }}
                                </p>
                            </div>

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
                                    v-model="edited.weight"
                                    fluid
                                    class="mb-5"
                                />

                                <Select
                                    :options="weightOptions"
                                    v-model="edited.weight_unit"
                                    option-label="name"
                                    option-value="value"
                                    placeholder="Select weight unit"
                                    class="mb-5"
                                    clearable
                                    clear-icon="pi pi-times"
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
                        </div>

                        <div class="mb-5">
                            <label for="base_price" class="mb-5">{{
                                $t("products.base_price")
                            }}</label>
                            <InputText
                                :aria-labelledby="$t('products.base_price')"
                                id="base_price"
                                type="number"
                                v-model="edited.base_price"
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
                                v-model="edited.listing_price"
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
                            <Select
                                id="currency"
                                :options="currencies"
                                v-model="edited.currency"
                                :placeholder="$t('products.select_currency')"
                                option-label="code"
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
                    @click="editItem"
                    :loading="loading"
                    :disabled="loading"
                />
            </div>
        </template>
    </Drawer>
</template>

<style lang="scss" scoped></style>
