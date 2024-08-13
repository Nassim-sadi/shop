<script setup>
import FloatingConfigurator from "@/components/FloatingConfigurator.vue";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import router from "@/router/Index";
import { authStore } from "@/store/AuthStore";
import { useVuelidate } from "@vuelidate/core";
import { helpers, minLength, required, sameAs } from "@vuelidate/validators";
import { ref } from "vue";
import { useRoute } from "vue-router";
const route = useRoute();
const auth = authStore();
const reset_status = ref("false");

const password = ref("password2");
const password_confirm = ref("password2");
const token = route.query.token;
const email = route.query.email;

const rules = {
    password: { required, minLength: minLength(8) },
    password_confirmation: {
        required,
        minLength: minLength(8),
        sameAs: helpers.withMessage("Passwords must match", sameAs(password)),
    },
};

const v$ = useVuelidate(rules, {
    password,
    password_confirmation: password_confirm,
});

const resetPassword = () => {
    v$.value.$touch();
    if (!v$.value.$invalid) {
        let data = {
            token: token,
            email: email,
            password: password.value,
            password_confirmation: password_confirm.value,
        };

        emitter.on("reset-password", (data) => {
            if (data.status == 200) {
                reset_status.value = "success";
            } else {
                reset_status.value = "failed";
            }
        });

        auth.resetPassword(data)
            .then((result) => {
                console.log(result);
            })
            .catch((err) => {
                console.log(err);
            });
    }
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
                    v-if="reset_status == 'false'"
                >
                    <div class="text-center mb-8">
                        <div
                            class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4"
                        >
                            {{ $t("reset_password") }}
                        </div>
                        <span class="text-muted-color font-medium">
                            {{ $t("reset_password_message") }}
                        </span>
                        {{ route.query.email }}
                    </div>

                    <div>
                        <label
                            for="password1"
                            class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2"
                            >{{ $t("new_password") }}</label
                        >
                        <Password
                            id="password1"
                            v-model="password"
                            :placeholder="$t('new_password')"
                            :toggleMask="true"
                            class="mb-4"
                            fluid
                            :feedback="false"
                        ></Password>

                        <div
                            class="text-red-500"
                            v-for="error of v$.password.$errors"
                            :key="error.$uid"
                        >
                            <small class="p-error">{{ error.$message }}</small>
                        </div>

                        <label
                            for="password2"
                            class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2"
                            >{{ $t("confirm_password") }}</label
                        >
                        <Password
                            id="password2"
                            v-model="password_confirm"
                            :placeholder="$t('confirm_password')"
                            :toggleMask="true"
                            class="mb-4"
                            fluid
                            :feedback="false"
                        ></Password>

                        <div
                            class="text-red-500"
                            v-for="error of v$.password_confirmation.$errors"
                            :key="error.$uid"
                        >
                            <small class="p-error">{{ error.$message }}</small>
                        </div>

                        <Button
                            :label="$t('auth.reset_password')"
                            class="w-full"
                            @click="resetPassword"
                        ></Button>

                        <Button
                            :label="$t('auth.reset.sign_in')"
                            severity="secondary"
                            class="w-full mt-4"
                            @click="() => router.push({ name: 'login' })"
                        ></Button>
                    </div>
                </div>
                <div
                    class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20"
                    style="border-radius: 53px"
                    v-if="reset_status == 'success'"
                >
                    <div
                        class="text-surface-700 dark:text-surface-0 text-xl font-medium mb-4 max-w-screen-sm w-2/3 block"
                    >
                        {{ $t("auth.password_reset_success") }}
                    </div>
                    <Button
                        label="Sign in"
                        class="w-full"
                        @click="() => router.push({ name: 'login' })"
                    ></Button>
                </div>
                <div
                    class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20"
                    style="border-radius: 53px"
                    v-if="reset_status == 'failed'"
                >
                    <div
                        class="text-surface-700 dark:text-surface-0 text-xl font-medium mb-4 max-w-screen-sm"
                    >
                        {{ $t("auth.password_reset_fail") }}
                    </div>
                    <Button
                        label="Sign in"
                        class="w-full"
                        @click="() => router.push({ name: 'login' })"
                    ></Button>
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
