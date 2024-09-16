<script setup>
import axios from "@/plugins/axios";
import { $t } from "@/plugins/i18n";
import { watchDebounced } from "@vueuse/core";
import { format } from "date-fns";
import { usePrimeVue } from "primevue/config";
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import { onMounted, ref } from "vue";
import Details from "./sidebars/Details.vue";
const users = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const per_page = ref(10);
const start_date = ref(new Date());
const end_date = ref(new Date());
const current = ref(null);
const isOpen = ref(false);
const keyword = ref("");
const status = ref(null);
const statusOptions = [
    { label: "All", value: null },
    { label: "Active", value: 1 },
    { label: "Inactive", value: 0 },
];

const role = ref(null);
const roleOptions = ref([{ label: "All", value: null }]);

const getRoles = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/roles")
            .then((res) => {
                console.log(res.data);
                roleOptions.value = [
                    ...roleOptions.value,
                    ...res.data.roles.map((role) => {
                        return {
                            label: role.name,
                            value: role.id,
                        };
                    }),
                ];
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                console.log("done getting roles");
            });
    });
};

const getUsers = async () => {
    if (loading.value) return;
    let params = {
        keyword: keyword.value,
        page: currentPage.value,
        per_page: per_page.value,
        start_date: format(start_date.value, "yyyy-MM-dd"),
        end_date: format(end_date.value, "yyyy-MM-dd"),
    };

    if (status.value !== null) {
        params.status = status.value;
    }

    if (role.value !== null) {
        params.role = role.value;
    }
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/users", {
                params: params,
            })
            .then((res) => {
                console.log(res.data);
                users.value = res.data.data;
                total.value = res.data.total;
                currentPage.value = res.data.current_page;
                per_page.value = res.data.per_page;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                loading.value = false;
                console.log("done getting users");
            });
    });
};

watchDebounced(
    keyword,
    () => {
        if (keyword.value.length >= 3 || keyword.value.length == 0) {
            getUsers();
        }
    },
    { debounce: 1000, maxWait: 1000 },
);

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    per_page.value = event.rows;
    getUsers();
};

const openDetails = (data) => {
    current.value = data;
    isOpen.value = true;
};

const actionColorMap = {
    create: "create",
    update: "update",
    delete: "delete",
};

const actionColor = (action) => {
    return actionColorMap[action] || "neutral";
};

const roleColorMap = {
    "Super Admin": "super",
    Admin: "admin",
    User: "user",
};

const roleColor = (role) => roleColorMap[role] || "neutral";

onMounted(async () => {
    await getRoles();
    start_date.value.setDate(start_date.value.getDate() - 17);
    await getUsers();
});
</script>

<template>
    <div class="card">
        <Details :current="current" v-model:isOpen="isOpen" />
        <DataTable
            :value="users"
            tableStyle="min-width: 50rem"
            :loading="loading"
            :rows="per_page"
            :paginator="true"
            :totalRecords="total"
            @page="onPageChange"
            dataKey="id"
            :lazy="true"
            :rowHover="true"
            :rowsPerPageOptions="[5, 10, 20, 30]"
        >
            <template #empty>
                <div class="text-center">{{ $t("common.no_data") }}</div>
            </template>

            <template #header>
                <h1 class="text-xl font-bold mb-4">
                    {{ $t("activities.title") }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 mb-4 w-full">
                    <div class="flex gap-2 items-baseline">
                        <span>{{ $t("common.from") }}</span>
                        <DatePicker
                            showIcon
                            v-model="start_date"
                            date-format="yy-mm-dd"
                            :max-date="
                                end_date
                                    ? new Date(end_date)
                                    : new Date(2025, 0, 1)
                            "
                            :min-date="new Date(2024, 0, 1)"
                            showButtonBar
                            @clear-click="() => (start_date = new Date())"
                        />
                    </div>

                    <div class="flex gap-2 items-baseline">
                        <span>{{ $t("common.to") }}</span>
                        <DatePicker
                            showIcon
                            v-model="end_date"
                            date-format="yy-mm-dd"
                            :max-date="new Date()"
                            :min-date="
                                start_date
                                    ? new Date(start_date)
                                    : new Date(2024, 0, 1)
                            "
                            showButtonBar
                            @clear-click="() => (end_date = new Date())"
                        />
                    </div>

                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="keyword"
                            :placeholder="$t('common.search')"
                            @keyup.enter="getUsers"
                        />
                    </IconField>

                    <Select
                        v-model="status"
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="$t('user.statusQuery')"
                    />

                    <Select
                        v-model="role"
                        :options="roleOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="$t('user.statusQuery')"
                    />

                    <Button
                        :label="$t('common.search')"
                        icon="pi pi-search"
                        @click="getUsers"
                        :disabled="!start_date || !end_date"
                        :loading="loading"
                        class="bold-label"
                    />
                </div>
            </template>

            <Column :header="$t('user.name')">
                <template #body="slotProps">
                    <div class="flex items-center gap-2 font-semibold">
                        <Avatar
                            shape="circle"
                            size="large"
                            :image="slotProps.data.image || placeholder"
                        />
                        {{
                            slotProps.data.firstname +
                            " " +
                            slotProps.data.lastname
                        }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('activities.role')">
                <template #body="slotProps">
                    <div
                        :class="`${roleColor(slotProps.data.role.name)} highlight`"
                    >
                        {{ slotProps.data.role.name }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('user.email')">
                <template #body="slotProps">
                    {{ slotProps.data.email }}
                    <span
                        class="pi pi-verified text-green-500 font-bold"
                        v-if="slotProps.data.email_verified_at"
                        v-tooltip.bottom="
                            $t('user.verified_at') +
                            ' ' +
                            slotProps.data.email_verified_at
                        "
                    ></span>
                </template>
            </Column>

            <Column :header="$t('user.status')">
                <template #body="slotProps">
                    <span
                        :class="
                            slotProps.data.status
                                ? 'text-green-500'
                                : 'text-red-500'
                        "
                        class="font-bold"
                    >
                        {{ slotProps.data.status }}
                        {{
                            slotProps.data.status
                                ? $t("common.active")
                                : $t("common.inactive")
                        }}
                    </span>
                </template>
            </Column>

            <!--    <Column :header="$t('activities.platform')">
                <template #body="slotProps">
                    {{ slotProps.data.platform }}
                </template>
            </Column>

            <Column :header="$t('activities.browser')">
                <template #body="slotProps">
                    {{ slotProps.data.browser }}
                </template>
            </Column>-->

            <Column :header="$t('common.created_at')" field="created_at">
            </Column>

            <Column :header="$t('common.updated_at')" field="created_at">
            </Column>

            <Column :header="$t('activities.action')">
                <template #body="slotProps">
                    <Button
                        icon="pi pi-eye"
                        rounded
                        size="large"
                        text
                        severity="info"
                        @click="openDetails(slotProps.data)"
                        v-tooltip.bottom="$t('common.view_details')"
                        class="action-btn"
                    />
                </template>
            </Column>

            <template #footer>
                {{ $t("activities.total") }}
                <span class="font-bold text-primary">
                    {{ total }}
                </span>
                {{ $t("activities.name", total) }}.
            </template>
        </DataTable>
    </div>
</template>

<style lang="scss" scoped></style>
