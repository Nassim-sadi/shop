<script setup>
import FloatingConfigurator from "@/components/FloatingConfigurator.vue";
import { authStore } from "@/store/AuthStore";
import { ref } from "vue";
import { useRoute, useRouter } from "vue-router";
const router = useRouter();
const route = useRoute();

const auth = authStore();

const password = ref("password2");
const password_confirm = ref("password2");
const otp = ref("123456");

const resetPassword = () => {
    let data = {
        otp: otp.value,
        email: route.query.email,
        password: password.value,
        password_confirmation: password_confirm.value,
    };
    auth.resetPassword(data).then((res) => {
        router.push({ name: "login" });
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
                            reset password
                        </div>
                        <span class="text-muted-color font-medium"
                            >enter new password</span
                        >
                        {{ route.query.email }}
                    </div>

                    <div>
                        <label
                            for="otp"
                            class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2"
                            >OTP</label
                        >
                        <!-- <InputOtp
                            id="otp"
                            v-model="otp"
                            integerOnly
                            :length="6"
                        /> -->
                        <InputOtp
                            id="otp"
                            v-model="otp"
                            :length="6"
                            style="gap: 0"
                            integerOnly
                        >
                            <template #default="{ attrs, events, index }">
                                <input
                                    type="text"
                                    v-bind="attrs"
                                    v-on="events"
                                    class="custom-otp-input mb-4"
                                />
                                <div v-if="index === 3" class="px-4">
                                    <i class="pi pi-minus" />
                                </div>
                            </template>
                        </InputOtp>

                        <label
                            for="password1"
                            class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2"
                            >New password</label
                        >
                        <Password
                            id="password1"
                            v-model="password"
                            placeholder="Password"
                            :toggleMask="true"
                            class="mb-4"
                            fluid
                            :feedback="false"
                        ></Password>

                        <label
                            for="password2"
                            class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2"
                            >Password</label
                        >
                        <Password
                            id="password2"
                            v-model="password_confirm"
                            placeholder="Password"
                            :toggleMask="true"
                            class="mb-4"
                            fluid
                            :feedback="false"
                        ></Password>

                        <Button
                            label="Sign In"
                            class="w-full"
                            @click="resetPassword"
                        ></Button>
                        <div class="flex justify-between mt-8 self-stretch">
                            <Button
                                label="Resend Code"
                                link
                                class="p-0"
                            ></Button>
                            <Button label="Submit Code"></Button>
                        </div>
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
.custom-otp-input {
    width: 48px;
    height: 48px;
    font-size: 24px;
    appearance: none;
    text-align: center;
    transition: all 0.2s;
    border-radius: 0;
    border: 1px solid var(--p-inputtext-border-color);
    background: transparent;
    outline-offset: -2px;
    outline-color: transparent;
    border-right: 0 none;
    transition: outline-color 0.3s;
    color: var(--p-inputtext-color);
}

.custom-otp-input:focus {
    outline: 2px solid var(--p-focus-ring-color);
}

.custom-otp-input:first-child,
.custom-otp-input:nth-child(5) {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.custom-otp-input:nth-child(3),
.custom-otp-input:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
    border-right-width: 1px;
    border-right-style: solid;
    border-color: var(--p-inputtext-border-color);
}
</style>
