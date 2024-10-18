<script setup>
import { ability } from "@/plugins/ability";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { authStore } from "@/store/AuthStore";
import { watchDebounced } from "@vueuse/core";
import { format } from "date-fns";
import { useConfirm } from "primevue/useconfirm";
import { computed, onMounted, ref } from "vue";
import Details from "./sidebars/Details.vue";
import Edit from "./sidebars/Edit.vue";
const Confirm = useConfirm();
const categories = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const per_page = ref(10);
const start_date = ref(new Date());
const end_date = ref(new Date());
const current = ref({});
const isOpen = ref(false);
const isEditOpen = ref(false);
const keyword = ref("");
const status = ref(null);
const uploadPercentage = ref(0);
const currentIndex = ref(null);
const actionsPopover = ref();

const togglePopover = ({ event: event, current: data, index: index }) => {
    current.value = data;
    currentIndex.value = index;
    actionsPopover.value.toggle(event);
};
const confirm = (myFunction, params) => {
    Confirm.require({
        message: $t("confirm.message_default"),
        header: $t("confirm.header"),
        icon: "pi pi-exclamation-triangle",
        rejectProps: {
            label: $t("cancel"),
            severity: "secondary",
            outlined: true,
        },
        acceptProps: {
            label: $t("confirm.title"),
        },
        accept: () => {
            myFunction(params ? [...params] : null);
        },
        reject: () => {},
    });
};

const statusOptions = [
    { label: "All", value: null },
    { label: "Active", value: 1 },
    { label: "Inactive", value: 0 },
];

const auth = authStore();

const isSuper = computed(() => {
    return auth.user.roles.name === "Super Admin";
});

const getCategories = async () => {
    if (loading.value) return;
    loadingStates.value = [];
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/categories", {
                params: {
                    keyword: keyword.value,
                    page: currentPage.value,
                    per_page: per_page.value,
                    start_date: format(start_date.value, "yyyy-MM-dd"),
                    end_date: format(end_date.value, "yyyy-MM-dd"),
                    status: status.value,
                },
            })
            .then((res) => {
                console.log("get categories");

                console.log(res.data);

                categories.value = res.data.data;
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
            getCategories();
        }
    },
    { debounce: 1000, maxWait: 1000 },
);

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    per_page.value = event.rows;
    current.value = {};
    currentIndex.value = null;
    getCategories();
};

const openDetails = () => {
    isOpen.value = true;
};

const openEdit = () => {
    isEditOpen.value = true;
};

const reset = () => {
    keyword.value = "";
    status.value = null;
    getCategories();
};

const changeStatus = async () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .patch("api/admin/users/change-status", {
                id: current.value.id,
                status: !current.value.status ? 1 : 0,
            })
            .then((res) => {
                updateItemStatus(res.data.status);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.user.change_status"),
                    severity: "success",
                });
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                setLoadingState(current.value.id, false);
            });
    });
};

const updateItemStatus = (status) => {
    users.value[currentIndex.value].status = status;
};

const deleteItem = () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .delete("api/admin/users/delete/" + current.value.id)
            .then((res) => {
                if (deleted.value !== null) {
                    users.value[currentIndex.value].deleted_at =
                        res.data.deleted_at;
                } else {
                    users.value.splice(currentIndex.value, 1);
                    total.value--;
                }

                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.user.delete"),
                    severity: "success",
                });
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                setLoadingState(current.value.id, false);
            });
    });
};

const editItem = (val) => {
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/users/update", val, {
                onUploadProgress: (progressEvent) => {
                    uploadPercentage.value = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total,
                    );
                },
            })
            .then((response) => {
                uploadPercentage.value = 0;
                isEditOpen.value = false;
                updateItem(response.data.user);
                emitter.emit("toast", {
                    summary: $t("update.success"),
                    message: $t("update.success_message"),
                    severity: "success",
                });
                resolve(response);
            })
            .catch((error) => {
                uploadPercentage.value = 0;
                console.log(error);
                reject(error);
            });
    });
};

const updateItem = (data) => {
    users.value[currentIndex.value] = data;
};

const loadingStates = ref({});

const setLoadingState = (userId, isLoading) => {
    loadingStates.value[userId] = isLoading;
};

onMounted(async () => {
    start_date.value.setDate(start_date.value.getDate() - 17);
    await getCategories();
});
</script>

<template>
    <div class="card">
        <Details :current="current ? current : {}" v-model:isOpen="isOpen" />

        <Edit
            :current="current"
            v-model:isOpen="isEditOpen"
            @editItem="editItem"
        ></Edit>

        <DataTable
            :value="categories"
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
                    {{ $t("categories.page") }}
                </h1>
                <div class="flex flex-wrap gap-2 mb-4 w-full">
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
                            @keyup.enter="getCategories"
                        />
                    </IconField>

                    <Select
                        v-model="status"
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="$t('user.statusQuery')"
                    />

                    <Button
                        :label="$t('common.search')"
                        icon="ti ti-search"
                        @click="getCategories"
                        :disabled="!start_date || !end_date"
                        :loading="loading"
                        class="bold-label"
                    />

                    <Button
                        :label="$t('common.reset')"
                        icon="ti ti-restore"
                        @click="reset"
                        :loading="loading"
                        class="bold-label"
                        v-tooltip.bottom="$t('common.reset')"
                        :disabled="!start_date || !end_date"
                        severity="secondary"
                    />
                </div>
            </template>
            <Column :header="$t('categories.order')">
                <template #body="slotProps">
                    {{ slotProps.data.order }}
                </template>
            </Column>
            <Column :header="$t('categories.name')">
                <template #body="slotProps">
                    <div class="flex items-center gap-2 font-semibold">
                        <Avatar
                            shape="circle"
                            size="large"
                            :image="slotProps.data.image"
                        />
                        {{ slotProps.data.name }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('common.status')">
                <template #body="slotProps">
                    <span
                        :class="
                            slotProps.data.status
                                ? 'text-green-500'
                                : 'text-red-500'
                        "
                        class="font-bold"
                    >
                        {{
                            slotProps.data.status
                                ? $t("common.active")
                                : $t("common.inactive")
                        }}
                    </span>
                </template>
            </Column>

            <!--   <Column :header="$t('activities.role')">
                <template #body="slotProps">
                    <div
                        class="highlight"
                        :style="`background-color: #${slotProps.data.role.color} ; color : #${slotProps.data.role.text_color}`"
                    >
                        {{ slotProps.data.role.name }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('user.email')">
                <template #body="slotProps">
                    {{ slotProps.data.email }}
                    <span
                        class="ti ti-rosette-discount-check-filled text-green-500 font-bold"
                        v-if="slotProps.data.email_verified_at"
                        v-tooltip.bottom="
                            $t('user.verified_at') +
                            ' ' +
                            slotProps.data.email_verified_at
                        "
                    ></span>
                </template>
            </Column>
           -->
            <Column :header="$t('common.created_at')" field="created_at">
            </Column>

            <Column :header="$t('common.updated_at')" field="created_at">
            </Column>

            <Column :header="$t('activities.action')">
                <template #body="slotProps">
                    <Button
                        icon="ti ti-dots-vertical"
                        rounded
                        text
                        size="large"
                        @click="
                            togglePopover({
                                event: $event,
                                current: slotProps.data,
                                index: slotProps.index,
                            })
                        "
                    >
                    </Button>
                </template>
            </Column>

            <template #footer>
                {{ $t("activities.total") }}
                <span class="font-bold text-primary">
                    {{ total }}
                </span>
                {{ $t("categories.title", total) }}.
            </template>
        </DataTable>

        <Popover ref="actionsPopover" class="popover" position="right">
            <div class="content">
                <Button
                    icon="ti ti-eye"
                    rounded
                    size="normal"
                    text
                    :label="$t('common.view_details')"
                    severity="info"
                    @click="openDetails"
                    v-tooltip.bottom="$t('common.view_details')"
                    class="action-btn"
                    :loading="loadingStates[current.id]"
                    v-if="ability.can('category', 'view')"
                />

                <Button
                    icon="ti ti-edit"
                    rounded
                    size="normal"
                    text
                    :label="$t('common.edit')"
                    severity="success"
                    @click="openEdit"
                    v-tooltip.bottom="$t('common.edit')"
                    class="action-btn"
                    :loading="loadingStates[current.id]"
                    v-if="ability.can('category', 'edit')"
                />

                <template
                    v-if="current.id !== auth.user.id && !current.deleted_at"
                >
                    <Button
                        icon="ti ti-status-change"
                        rounded
                        size="normal"
                        text
                        severity="help"
                        :label="$t('common.change_status')"
                        @click="confirm(changeStatus)"
                        v-tooltip.bottom="$t('common.change_status')"
                        class="action-btn"
                        :loading="loadingStates[current.id]"
                        v-if="ability.can('category', 'changeStatus')"
                    />
                    <Button
                        icon="ti ti-trash"
                        rounded
                        size="normal"
                        text
                        severity="danger"
                        :label="$t('common.delete')"
                        @click="confirm(deleteItem)"
                        v-tooltip.bottom="$t('common.delete')"
                        class="action-btn"
                        :loading="loadingStates[current.id]"
                        v-if="ability.can('category', 'delete')"
                    />
                </template>
            </div>
        </Popover>
    </div>
</template>

<style lang="scss" scoped>
.popover .content {
    display: flex !important;
    flex-direction: column;
    align-items: flex-start;
    width: 100%;
}

.popover .content .action-btn {
    margin-left: 0 !important;
    margin-right: 0 !important;
    margin: 0 !important;
    width: 100%;
    justify-content: flex-start;
}
</style>
