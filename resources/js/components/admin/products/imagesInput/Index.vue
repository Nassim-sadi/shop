<script setup>
import { ref, computed } from "vue";
import { useImageCompression } from "@/utils/useImageCompression";
import { productImageSize } from "@/constants/imagesSize/Index";
import { $t } from "@/plugins/i18n";
import emitter from "@/plugins/emitter";
import ProductImage from "./ProductImage.vue";
const previewImages = ref([]);
const loading = ref([]); // Array to track loading state for each image
const existingFiles = ref([]);
const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const $emit = defineEmits(["update:modelValue"]);

const images = computed({
    get: () => props.modelValue,
    set: (val) => $emit("update:modelValue", val),
});

const updatePicture = async (event) => {
    const files = Array.from(event.target.files);

    for (const file of files) {
        if (existingFiles.value.includes(file.name)) {
            emitter.emit("toast", {
                summary: $t("validation.file_already_exist"),
                message: file.name,
                severity: "error",
            });
            continue;
        }
        const tempIndex = previewImages.value.length;
        existingFiles.value.push(file.name);
        previewImages.value.push("null");
        loading.value.push(true);

        try {
            const result = await useImageCompression(file, productImageSize);
            if (result) {
                const { compressedImage, preview } = result;
                previewImages.value[tempIndex] = preview;
                images.value[tempIndex] = compressedImage;
            } else {
                console.warn(`Skipping file: ${file.name}`);
                previewImages.value.splice(tempIndex, 1);
                loading.value.splice(tempIndex, 1);
            }
        } finally {
            loading.value[tempIndex] = false;
        }
    }
    event.target.value = "";
};

const removeImage = (index) => {
    existingFiles.value.splice(index, 1);
    previewImages.value.splice(index, 1);
    loading.value.splice(index, 1);
    images.value.splice(index, 1);
};
</script>
<template>
    <div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
                class="cursor-pointer mb-5 w-full aspect-[1/1] rounded-xl overflow-hidden relative"
            >
                <label class="w-full absolute top-0 right-0 left-0 bottom-0">
                    <input
                        type="file"
                        @change="updatePicture($event, productImageSize)"
                        accept="image/*"
                        class="hidden"
                        multiple
                    />
                    <div
                        class="w-full aspect-[1/1] rounded-xl flex items-center justify-center bg-blue-100"
                    >
                        <i class="ti ti-plus text-xl"></i>
                    </div>
                </label>
            </div>
            <template v-for="(image, index) in previewImages" :key="index">
                <ProductImage
                    :image="image"
                    :loading="loading[index]"
                    @remove-image="removeImage(index)"
                />
            </template>
        </div>
    </div>
</template>
