<script setup>
import { defineEmits, defineProps, onMounted, ref } from "vue";
const { current, visible } = defineProps({
    current: {
        type: Object,
        required: true,
    },
    isOpen: {
        type: Boolean,
        required: true,
    },
});
const emit = defineEmits(["update:isOpen"]);

const currentUser = ref({});

onMounted(() => {
    currentUser.value = { ...current };
});
</script>

<template>
    <Drawer
        :visible="isOpen"
        header="Left Drawer"
        position="right"
        @update:visible="emit('update:isOpen', $event)"
    >
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
    </Drawer>
</template>

<style lang="scss" scoped></style>
