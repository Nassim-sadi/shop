

<script setup>
import { ref, watch , toRefs} from "vue";
import { useImageCompression } from "@/utils/useImageCompression";
import { productImageSize } from "@/constants/imagesSize/Index";
const previewImages = ref([]);
// let formData = new FormData();
const images = []
const props = defineProps({
    send : {
        type: Boolean,
        required: true
    }
})

const { send } = toRefs(props);
const $emit = defineEmits(["add-images"]);


const updatePicture = async (event) => {
    const files = Array.from(event.target.files);
    console.log("files");

    console.log(event);

    for (const file of files) {
        const result = await useImageCompression(file, productImageSize);
        if (result) {
            const { compressedImage, preview } = result;
            previewImages.value.push(preview);
            // formData.append("images[]", compressedImage);
            images.push(compressedImage)
            // $emit("addImage", compressedImage);
        } else {
            console.warn(`Skipping file: ${file.name}`);
        }
    }
};

const removeImage = (index) => {
    previewImages.value.splice(index, 1);
    formData = new FormData();
    previewImages.value.forEach((_, i) => {
        formData.append("images[]", previewImages.value[i]);
    });
};

watch(send, (val) => {
    if (val) {
        console.log('sending');
        $emit("add-images", images);
    }
});
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

            <div
                v-for="(image, index) in previewImages"
                :key="index"
                class="relative group"
            >
                <img
                    :src="image"
                    class="w-full aspect-[1/1] object-cover rounded-xl"
                ></img>
                <button
                    @click="removeImage(index)"
                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>
</template>
