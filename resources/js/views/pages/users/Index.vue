<script setup>
import placeholder from "@/assets/images/avatar/profile-placeholder.png";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { authStore } from "@/store/AuthStore";
import { watchDebounced } from "@vueuse/core";
import { format } from "date-fns";
import { useConfirm } from "primevue/useconfirm";
import { computed, onMounted, ref } from "vue";
import ChangeRole from "./sidebars/ChangeRole.vue";
import Details from "./sidebars/Details.vue";
import Edit from "./sidebars/Edit.vue";
const Confirm = useConfirm();
const users = ref([]);
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
const isChangeRoleOpen = ref(false);

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

const deleted = ref(null);

const deletedOptions = [
    { label: "Non-deleted", value: null },
    { label: "All", value: "with" },
    { label: "Deleted only", value: "only" },
];

const auth = authStore();

const role = ref(null);
const roleOptions = ref([{ label: "All", value: null }]);
const roles = ref([]);
const getRoles = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/roles")
            .then((res) => {
                roles.value = res.data;
                setRoleOptions(res.data);
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {});
    });
};

const setRoleOptions = (data) => {
    roleOptions.value = [
        ...roleOptions.value,
        ...data.map((role) => {
            return {
                label: role.name,
                value: role.id,
            };
        }),
    ];
};

const isSuper = computed(() => {
    return auth.user.roles.name === "Super Admin";
});

const getUsers = async () => {
    if (loading.value) return;
    loadingStates.value = [];
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/users", {
                params: {
                    keyword: keyword.value,
                    page: currentPage.value,
                    per_page: per_page.value,
                    start_date: format(start_date.value, "yyyy-MM-dd"),
                    end_date: format(end_date.value, "yyyy-MM-dd"),
                    status: status.value,
                    role: role.value,
                    deleted: deleted.value,
                },
            })
            .then((res) => {
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
    current.value = {};
    currentIndex.value = null;
    getUsers();
};

const openDetails = () => {
    isOpen.value = true;
};

const openEdit = () => {
    isEditOpen.value = true;
};

const roleColorMap = {
    "Super Admin": "super",
    Admin: "admin",
    User: "user",
};

const roleColor = (role) => roleColorMap[role] || "neutral";

const reset = () => {
    keyword.value = "";
    status.value = null;
    role.value = null;
    deleted.value = null;
    getUsers();
};

const changeStatus = async () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/users/change-status", {
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
            .post("api/admin/users/delete", {
                id: current.value.id,
            })
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

const deleteItemPermanently = () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/users/delete-permanently", {
                id: current.value.id,
            })
            .then((res) => {
                users.value.splice(currentIndex.value, 1);
                total.value--;
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.user.perma_delete"),
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

const restoreItem = () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/users/restore", {
                id: current.value.id,
            })
            .then((res) => {
                if (deleted.value === "only") {
                    users.value.splice(currentIndex.value, 1);
                    total.value--;
                } else {
                    users.value[currentIndex.value].deleted_at = null;
                }
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.user.restore"),
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

const rowClass = computed(() => (data) => {
    return [{ "!bg-red-600/10": data.deleted_at ? true : false }];
});

const loadingStates = ref({}); // Holds loading state for each user

const setLoadingState = (userId, isLoading) => {
    loadingStates.value[userId] = isLoading;
};

const openChangeRole = () => {
    isChangeRoleOpen.value = true;
};

const changeRole = (val) => {
    setLoadingState(current.id, true);
    console.log(val);

    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/users/change-role", val)
            .then((res) => {
                console.log(res.data);

                updateItem(res.data.user);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.user.change_role"),
                    severity: "success",
                });
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                emitter.emit("toast", {
                    summary: $t("status.error.title"),
                    message: $t("status.error.user.change_role"),
                    severity: "error",
                });
                reject(err);
            })
            .finally(() => {
                setLoadingState(current.id, false);
                isChangeRoleOpen.value = false;
            });
    });
};

onMounted(async () => {
    await getRoles();
    start_date.value.setDate(start_date.value.getDate() - 17);
    await getUsers();
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

        <ChangeRole
            :current="current"
            v-model:isOpen="isChangeRoleOpen"
            @submit="changeRole"
            :roles="roles"
            :loading="loadingStates[current.id]"
        ></ChangeRole>

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
            :row-class="rowClass"
        >
            <template #empty>
                <div class="text-center">{{ $t("common.no_data") }}</div>
            </template>

            <template #header>
                <h1 class="text-xl font-bold mb-4">
                    {{ $t("user.page") }}
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
                        :placeholder="$t('user.roleQuery')"
                    />

                    <Select
                        v-if="isSuper"
                        v-model="deleted"
                        :options="deletedOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="$t('user.deletedQuery')"
                    />

                    <Button
                        :label="$t('common.search')"
                        icon="ti ti-search"
                        @click="getUsers"
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
                {{ $t("user.title", total) }}.
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
                    />

                    <Button
                        icon="ti ti-user-edit"
                        rounded
                        size="normal"
                        text
                        severity="warning"
                        :label="$t('users.change_role')"
                        @click="openChangeRole"
                        v-tooltip.bottom="$t('users.change_role')"
                        class="action-btn"
                        :loading="loadingStates[current.id]"
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
                    />
                </template>
                <template
                    v-if="
                        current.deleted_at &&
                        isSuper &&
                        current.id !== auth.user.id
                    "
                >
                    <Button
                        icon="ti ti-trash"
                        rounded
                        size="normal"
                        :label="$t('common.perma_delete')"
                        text
                        severity="danger"
                        @click="confirm(deleteItemPermanently)"
                        v-tooltip.bottom="$t('common.perma_delete')"
                        class="action-btn"
                        :loading="loadingStates[current.id]"
                    />

                    <Button
                        icon="ti ti-restore"
                        rounded
                        size="normal"
                        text
                        :label="$t('common.restore')"
                        severity="success"
                        @click="confirm(restoreItem)"
                        v-tooltip.bottom="$t('common.restore')"
                        class="action-btn"
                        :loading="loadingStates[current.id]"
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
