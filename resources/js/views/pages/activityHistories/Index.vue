<script setup>
import axios from "@/plugins/axios";
import { $t } from "@/plugins/i18n";
import { watchDebounced } from "@vueuse/core";
import { format } from "date-fns";
import { usePrimeVue } from "primevue/config";
import { onMounted, ref } from "vue";
import Details from "./sidebars/Details.vue";
const activities = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const per_page = ref(10);
const start_date = ref(new Date());
const end_date = ref(new Date());
const current = ref(null);
const isOpen = ref(false);
const keyword = ref("");

const getActivities = async () => {
    if (loading.value) return;
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/activity-histories", {
                params: {
                    keyword: keyword.value,
                    page: currentPage.value,
                    per_page: per_page.value,
                    start_date: format(start_date.value, "yyyy-MM-dd"),
                    end_date: format(end_date.value, "yyyy-MM-dd"),
                },
            })
            .then((res) => {
                activities.value = res.data.data.activity_histories;
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
            });
    });
};

watchDebounced(
    keyword,
    () => {
        if (keyword.value.length >= 3 || keyword.value.length == 0) {
            getActivities();
        }
    },
    { debounce: 1000, maxWait: 1000 },
);

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    per_page.value = event.rows;
    getActivities();
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

onMounted(() => {
    start_date.value.setDate(start_date.value.getDate() - 17);
    getActivities();
    const primevue = usePrimeVue();
    primevue.config.locale.today = $t("common.today");
    primevue.config.locale.clear = $t("common.clear");
});
</script>

<template>
    <div class="card">
        <Details :current="current" v-model:isOpen="isOpen" />
        <DataTable
            :value="activities"
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
                            @keyup.enter="getActivities"
                        />
                    </IconField>

                    <Button
                        :label="$t('common.search')"
                        icon="pi pi-search"
                        @click="getActivities"
                        :disabled="!start_date || !end_date"
                        :loading="loading"
                        class="bold-label"
                    />
                </div>
            </template>

            <Column :header="$t('user.title')">
                <template #body="slotProps">
                    <div class="flex items-center gap-2 font-semibold">
                        <Avatar
                            shape="circle"
                            size="large"
                            :image="slotProps.data.user.image"
                        />
                        {{
                            slotProps.data.user.firstname +
                            " " +
                            slotProps.data.user.lastname
                        }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('activities.role')">
                <template #body="slotProps">
                    <div
                        :class="`${roleColor(slotProps.data.user.role.name)} highlight`"
                    >
                        {{ slotProps.data.user.role.name }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('activities.model')">
                <template #body="slotProps">
                    {{
                        slotProps.data.user.id == slotProps.data.data.user.id
                            ? $t("activities.self")
                            : $t("activities.models." + slotProps.data.model)
                    }}
                </template>
            </Column>

            <Column :header="$t('activities.action')">
                <template #body="slotProps">
                    <div
                        :class="`${actionColor(slotProps.data.action)} action`"
                    >
                        {{ slotProps.data.action }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('activities.platform')">
                <template #body="slotProps">
                    {{ slotProps.data.platform }}
                </template>
            </Column>

            <Column :header="$t('activities.browser')">
                <template #body="slotProps">
                    {{ slotProps.data.browser }}
                </template>
            </Column>

            <Column :header="$t('activities.created_at')" field="created_at">
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

<style lang="scss" scoped>
.action {
    @apply border-solid border-[1px] capitalize font-bold py-1 px-2 text-center rounded-lg;
}

.create {
    @apply bg-green-500 text-surface-900;
}

.update {
    @apply bg-yellow-500/50 border-yellow-500  text-surface-900;
}

.delete {
    @apply bg-red-500 text-surface-900;
}

.neutral {
    @apply bg-surface-500 text-surface-900;
}

.super {
    @apply bg-red-500 text-surface-0;
}

.admin {
    @apply bg-surface-900 text-surface-0;
}

.user {
    @apply bg-surface-900 text-surface-0;
}

.highlight {
    @apply font-bold py-1 px-2 text-center rounded-lg;
}
</style>
