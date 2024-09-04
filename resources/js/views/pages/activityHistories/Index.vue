<script setup>
import axios from "@/plugins/axios";
import { $t } from "@/plugins/i18n";
import { format } from "date-fns";
import { onMounted, ref } from "vue";
const activities = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const per_page = ref(10);
const start_date = ref("2024-01-01");
const end_date = ref("2025-01-01");

const getActivities = async () => {
    console.log(per_page.value);
    console.log(currentPage.value);

    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/activity-histories", {
                params: {
                    page: currentPage.value,
                    per_page: per_page.value,
                    start_date: start_date.value,
                    end_date: end_date.value,
                },
            })
            .then((res) => {
                console.log(res.data);
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

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    per_page.value = event.rows;
    getActivities();
};

const headers = [
    { text: $t("user.title"), value: "user" },
    { text: $t("activities.model"), value: "model" },
    { text: $t("activities.action"), value: "action" },
    { text: $t("activities.platform"), value: "platform" },
    { text: $t("activities.browser"), value: "browser" },
    { text: $t("activities.created_at"), value: "created_at" },
];

onMounted(() => {
    getActivities();
});
</script>

<template>
    <div class="card">
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
        >
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">{{
                        $t("activities.title")
                    }}</span>
                </div>
            </template>
            <Column
                v-for="header in headers"
                :field="header.value"
                :header="header.text"
            >
                <template #body="slotProps" v-if="header.value === 'user'">
                    <div class="flex items-center gap-2">
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
            <template #footer>
                In total there are
                {{ total }}
                {{ $t("activities.name", total) }}.
            </template>
        </DataTable>
    </div>
</template>

<style lang="scss" scoped></style>
