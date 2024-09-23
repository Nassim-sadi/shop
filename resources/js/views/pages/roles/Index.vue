<script setup>
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";
import { authStore } from "@/store/AuthStore";
import { format } from "date-fns";
import { useConfirm } from "primevue/useconfirm";
import { computed, onMounted, ref } from "vue";
import Create from "./sidebars/Create.vue";
import Details from "./sidebars/Details.vue";
import Edit from "./sidebars/Edit.vue";
const Confirm = useConfirm();
const permissions = ref([]);
const loading = ref(false);
const current = ref({});
const isOpen = ref(false);
const isEditOpen = ref(false);
const isCreateOpen = ref(false);
const loadingCreate = ref(false);
const currentIndex = ref(null);
const actionsPopover = ref();
const roles = ref([]);
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

const auth = authStore();

const getRoles = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/roles")
            .then((res) => {
                roles.value = res.data;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {});
    });
};

const isSuper = computed(() => {
    return auth.user.roles.name === "Super Admin";
});

const getPermissions = async () => {
    if (loading.value) return;
    loadingStates.value = [];
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/roles/permissions")
            .then((res) => {
                permissions.value = res.data.permissions;
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

const openDetails = () => {
    isOpen.value = true;
};

const openEdit = () => {
    isEditOpen.value = true;
};

const openCreate = () => {
    isCreateOpen.value = true;
};

const createItem = (val) => {
    console.log(val);
    loadingCreate.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/roles/create", val)
            .then((res) => {
                console.log(res);
                roles.value.unshift(res.data.role);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.role.create"),
                    severity: "success",
                });
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                loadingCreate.value = false;
                isCreateOpen.value = false;
            });
    });
};

const deleteItem = () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/roles/delete", {
                id: current.value.id,
            })
            .then((res) => {
                roles.value.splice(currentIndex.value, 1);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.role.delete"),
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
            .post("api/admin/permissions/update", val, {
                onUploadProgress: (progressEvent) => {
                    uploadPercentage.value = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total,
                    );
                },
            })
            .then((response) => {
                uploadPercentage.value = 0;
                isEditOpen.value = false;
                console.log(response.data);
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
    permissions.value[currentIndex.value] = data;
};

const loadingStates = ref({}); // Holds loading state for each user

const setLoadingState = (userId, isLoading) => {
    loadingStates.value[userId] = isLoading;
};

onMounted(async () => {
    await getRoles();
    await getPermissions();
});
</script>

<template>
    <div class="card">
        <Details :current="current" v-model:isOpen="isOpen" />

        <Edit
            :current="current"
            v-model:isOpen="isEditOpen"
            @editItem="editItem"
        ></Edit>

        <Create
            v-model:isOpen="isCreateOpen"
            @createItem="createItem"
            :loading="loadingCreate"
            :permissions="permissions"
        />

        <DataTable
            :value="roles"
            tableStyle="min-width: 50rem"
            :loading="loading"
            dataKey="id"
            :rowHover="true"
        >
            <template #empty>
                <div class="text-center">{{ $t("common.no_data") }}</div>
            </template>

            <template #header>
                <div class="flex justify-between align-center">
                    <h1 class="text-xl font-bold mb-4">
                        {{ $t("roles.page") }}
                    </h1>

                    <Button
                        v-if="isSuper"
                        icon="ti ti-plus"
                        @click="openCreate"
                        :label="$t('common.create')"
                        severity="success"
                        v-tooltip.bottom="$t('common.create')"
                    />
                </div>
            </template>

            <Column :header="$t('roles.name')">
                <template #body="slotProps">
                    <div
                        class="flex items-center gap-2 highlight"
                        :style="`background-color: #${slotProps.data.color} ; color : #${slotProps.data.text_color}`"
                    >
                        {{ slotProps.data.name }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('roles.description')">
                <template #body="slotProps">
                    {{
                        slotProps.data.description
                            ? slotProps.data.description
                            : $t("roles.no_description")
                    }}
                </template>
            </Column>

            <Column :header="$t('roles.permissions')">
                <template #body="slotProps">
                    <div class="flex flex-wrap gap-2 font-semibold">
                        <template
                            v-if="
                                slotProps.data.permissions &&
                                slotProps.data.permissions.length > 0
                            "
                            v-for="permission in slotProps.data.permissions"
                        >
                            <span
                                class="highlight text-green-700 bg-lime-300 border-green-800 border-2"
                            >
                                {{ permission.name }}
                            </span>
                        </template>

                        <template v-else>
                            <span v-if="slotProps.data.name === 'Super Admin'">
                                {{ $t("roles.all_permissions") }}
                            </span>
                            <span v-else>
                                {{ $t("roles.no_permissions") }}
                            </span>
                        </template>
                    </div>
                </template>
            </Column>

            <Column :header="$t('common.created_at')" field="created_at">
                <template #body="slotProps">
                    {{ format(slotProps.data.created_at, "yyy-mm-dd hh:mm") }}
                </template>
            </Column>

            <Column :header="$t('common.updated_at')" field="created_at">
                <template #body="slotProps">
                    {{ format(slotProps.data.updated_at, "yyy-mm-dd hh:mm") }}
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
                    {{ roles.length }}
                </span>
                {{ $t("roles.title", roles.length) }}.
            </template>
        </DataTable>

        <Popover ref="actionsPopover" class="popover">
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
                />

                <template v-if="current.id !== auth.user.roles.id">
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

                    <Button
                        v-if="current.users_count === 0"
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
                        @click="
                            confirm(deleteItemPermanently, [
                                current,
                                currentIndex,
                            ])
                        "
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
