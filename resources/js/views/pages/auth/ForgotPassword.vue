<script setup>
import FloatingConfigurator from "@/components/FloatingConfigurator.vue";
import { authStore } from "@/store/AuthStore";
import { ref } from "vue";
import { useRouter } from "vue-router";
const router = useRouter();
const auth = authStore();

import { $t } from "@/plugins/i18n";

const email = ref("nacimbreeze@gmail.com");

const sendOtp = () => {
    auth.forgotPassword(email.value).then((res) => {
        router.push({
            name: "reset-password",
            query: { email: email.value },
        });
    });
};
</script>

<template>
    <FloatingConfigurator />
    <div
        class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden"
    >
        <div class="flex flex-col items-center justify-center">
            <div
                style="
                    border-radius: 56px;
                    padding: 0.3rem;
                    background: linear-gradient(
                        180deg,
                        var(--primary-color) 10%,
                        rgba(33, 150, 243, 0) 30%
                    );
                "
            >
                <div
                    class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20"
                    style="border-radius: 53px"
                >
                    <div class="text-center mb-8">
                        <div
                            class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4"
                        >
                            {{ $t("auth.forgot_password") }}
                        </div>
                        <span class="text-muted-color font-medium">
                            {{ $t("auth.forgot_password_message") }}
                        </span>
                    </div>

                    <div>
                        <label
                            for="email1"
                            class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2"
                            >{{ $t("auth.email") }}</label
                        >
                        <InputText
                            id="email1"
                            type="text"
                            placeholder="Email address"
                            class="w-full md:w-[30rem] mb-8"
                            fluid
                            v-model="email"
                        />

                        <Button
                            :label="$t('auth.send_code')"
                            class="w-full"
                            @click="sendOtp"
                        ></Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>
