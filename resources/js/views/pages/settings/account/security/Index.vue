<script setup></script>

<template>
    <div class="col-span-12 lg:col-span-6 card">
        <div class="font-semibold text-xl col-span-6">
            {{ $t("settings.security") }}
            <div class="w-full flex justify-between">
                <Button class="btn btn-primary">
                    {{ $t("settings.change_password") }}
                </Button>
                <Button class="btn btn-primary">
                    {{ $t("settings.change_email") }}
                </Button>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
