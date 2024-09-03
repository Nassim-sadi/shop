<script setup>
import axios from "@/plugins/axios";
import { ref, onMounted } from "vue";
import { format } from "date-fns";

const activities = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const per_page = ref(10);
const start_date = ref("2024-01-01");
const end_date = ref("2025-01-01");

const getActivities = async () => {
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
                activities.value = res.data.data.activity_histories;
                total.value = res.data.total;
                currentPage.value = res.data.current_page;
                per_page.value = res.data.per_page;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
            })
            .finally(() => {
                loading.value = false;
            });
    });
};

const headers = [
    { text: "User", value: "user" },
    { text: "Action", value: "action" },
    { text: "Platform", value: "platform" },
    { text: "Browser", value: "browser" },
    { text: "Created", value: "created_at" },
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
            responsiveLayout="scroll"
            :loading="loading"
            :rows="per_page"
            :paginator="true"
            :totalRecords="total"
            @page="getActivities"
            :dataKey="'id'"
            :rowHover="true"
        >
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xl font-bold">Activities</span>
                    <Button icon="pi pi-refresh" rounded raised />
                </div>
            </template>
            <Column
                v-for="header in headers"
                :field="header.value"
                :header="header.text"
            >
                <template #body="slotProps" v-if="header.value === 'user'">
                    <Avatar
                        shape="circle"
                        size="xlarge"
                        :image="slotProps.data.user.image"
                    />
                    {{ slotProps.data.user.name }}
                </template>
            </Column>

            <template #footer>
                In total there are
                {{ activities ? activities.length : 0 }} activities.
            </template>
        </DataTable>
    </div>
</template>

<style lang="scss" scoped></style>
