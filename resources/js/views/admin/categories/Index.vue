<script setup>
import { ability } from "@/plugins/ability";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { watchDebounced } from "@vueuse/core";
import { format } from "date-fns";
import { useConfirm } from "primevue/useconfirm";
import { onMounted, ref } from "vue";
import Details from "./sidebars/Details.vue";
import Edit from "./sidebars/Edit.vue";
import Create from "./sidebars/Create.vue";
const Confirm = useConfirm();
const categories = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const per_page = ref(10);
const start_date = ref(new Date());
const end_date = ref(new Date());
const current = ref({});
const isDetailsOpen = ref(false);
const isEditOpen = ref(false);
const isCreateOpen = ref(false);
const keyword = ref("");
const status = ref(null);
const uploadPercentage = ref(0);
const actionsPopover = ref();

const togglePopover = ({ event: event, current: data }) => {
    current.value = data;
    actionsPopover.value.toggle(event);
};

const openAdd = () => {
    isCreateOpen.value = true;
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
    getCategories();
};

const openDetails = () => {
    isDetailsOpen.value = true;
};

const openEdit = () => {
    isEditOpen.value = true;
};

const reset = () => {
    keyword.value = "";
    status.value = null;
    getCategories();
};

const createItem = (data) => {
    if (loading.value) return;
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/categories/create", data)
            .then((res) => {
                categories.value.push(res.data.category);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.category.create"),
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

const changeStatus = async () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);
    return new Promise((resolve, reject) => {
        axios
            .patch("api/admin/categories/change-status", {
                id: current.value.id,
                status: current.value.status ? 0 : 1,
            })
            .then((res) => {
                console.log(res.data.status);
                updateItemStatus(res.data.status);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.category.change_status"),
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
    current.value.status = status;
};

const deleteItem = () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .delete("api/admin/categories/delete/" + current.value.id)
            .then((res) => {
                if (current.value && current.value.id) {
                    removeCategoryById(categories.value, current.value.id);
                }
                if (!current.value.parent_id) {
                    console.log(current);
                    total.value--;
                }
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.category.delete"),
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

const removeCategoryById = (categories, idToRemove) => {
    categories.forEach((category, index) => {
        if (category.id === idToRemove) {
            categories.splice(index, 1);
        } else if (category.children && category.children.length > 0) {
            removeCategoryById(category.children, idToRemove);
        }
    });
};

const editItem = (val) => {
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/categories/update", val, {
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
                    summary: $t("status.success.title"),
                    message: $t("status.success.category.update"),
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
    current.value = data;
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
        <Details
            :current="current ? current : {}"
            v-model:isOpen="isDetailsOpen"
        />

        <Edit
            :current="current"
            v-model:isOpen="isEditOpen"
            @editItem="editItem"
        ></Edit>

        <Create v-model:isOpen="isCreateOpen" @createItem="createItem"></Create>

        <TreeTable
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
            size="small"
        >
            <template #empty>
                <div class="text-center">{{ $t("common.no_data") }}</div>
            </template>

            <template #header>
                <div class="flex justify-between items-center mb-4">
                    <h1
                        class="text-xl font-bold mb-0 text-surface-900 dark:text-surface-0"
                    >
                        {{ $t("categories.page") }}
                    </h1>
                    <Button
                        :label="$t('categories.create')"
                        icon="ti ti-plus"
                        @click="openAdd"
                        class="bold-label"
                        v-tooltip.bottom="$t('categories.create')"
                        v-if="ability.can('category', 'create')"
                        severity="success"
                    />
                </div>
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

            <Column expander> </Column>

            <Column :header="$t('categories.order')">
                <template #body="slotProps">
                    {{ slotProps.node.order }}
                </template>
            </Column>
            <Column :header="$t('categories.name')">
                <template #body="slotProps">
                    <div class="flex items-center gap-2 font-semibold">
                        <Avatar
                            shape="circle"
                            size="large"
                            :image="slotProps.node.image"
                        />
                        {{ slotProps.node.name }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('common.status')">
                <template #body="slotProps">
                    <span
                        :class="
                            slotProps.node.status
                                ? 'text-green-500'
                                : 'text-red-500'
                        "
                        class="font-bold"
                    >
                        {{
                            slotProps.node.status
                                ? $t("common.active")
                                : $t("common.inactive")
                        }}
                    </span>
                </template>
            </Column>

            <Column :header="$t('common.created_at')" field="created_at">
                <template #body="slotProps">
                    {{ slotProps.node.created_at }}
                </template>
            </Column>

            <Column :header="$t('common.updated_at')">
                <template #body="slotProps">
                    {{ slotProps.node.updated_at }}
                </template>
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
                                current: slotProps.node,
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
        </TreeTable>

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
                    v-if="
                        ability.can('category', 'delete') &&
                        !current.children.length
                    "
                />
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
