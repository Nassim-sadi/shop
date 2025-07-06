<script setup>
import { toRefs } from "vue";

const props = defineProps({
    image: {
        type: String || null,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    },
});

const { image, loading } = toRefs(props);

const emit = defineEmits(["removeImage"]);
const removeImage = () => {
    emit("removeImage");
};
</script>
<template>
    <div class="relative group">
        <div
            v-if="loading"
            class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-50"
        >
            <ProgressSpinner
                stroke-width="8"
                fill="transparent"
                animation-duration=".5s"
                aria-label="Custom ProgressSpinner"
            />
        </div>

        <img
            v-else-if="image"
            :src="image"
            class="w-full aspect-[1/1] object-contain rounded-xl"
        />

        <Button
            v-if="!loading"
            @click="removeImage"
            class="!absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition"
            icon="ti ti-x"
            severity="danger"
            rounded
            size="small"
        />
    </div>
</template>
