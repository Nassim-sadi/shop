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
const loadingEdit = ref(false);
const currentIndex = ref(null);
const roles = ref([]);
const nonChangingRoles = ["Super Admin", "User"];

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
            if (params) {
                myFunction(...params);
            } else {
                myFunction();
            }
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

const openDetails = (item) => {
    current.value = item;
    isOpen.value = true;
};

const filteredRolesWithoutCurrent = ref([]);

const openEdit = (item, index) => {
    current.value = item;
    filteredRolesWithoutCurrent.value = filteredRoles.value.filter(
        (role) => role.name !== current.value.name,
    );
    currentIndex.value = index;
    isEditOpen.value = true;
};

const openCreate = () => {
    isCreateOpen.value = true;
};

const createItem = (val) => {
    loadingCreate.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/roles/create", val)
            .then((res) => {
                roles.value.push(res.data.role);
                filterRolesWithPermissionsId();
                isCreateOpen.value = false;
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
            });
    });
};

const deleteItem = (item, index) => {
    if (loadingStates.value[item.id]) return;
    setLoadingState(item.id, true);
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/roles/delete", {
                id: item.id,
            })
            .then((res) => {
                roles.value.splice(index, 1);
                filterRolesWithPermissionsId();
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
                setLoadingState(item.id, false);
            });
    });
};

const editItem = (val) => {
    loadingEdit.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/roles/update", val)
            .then((response) => {
                updateItem(response.data.role);
                filterRolesWithPermissionsId();
                isEditOpen.value = false;

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
            })
            .finally(() => {
                loadingEdit.value = false;
            });
    });
};

const filteredRoles = ref([]);
const filterRolesWithPermissionsId = () => {
    filteredRoles.value = roles.value
        .filter((role) => role.name !== "Super Admin")
        .map((role) => {
            // return  role name and permissions
            return {
                name: role.name,
                color: role.color,
                permissions: role.permissions.map((permission) => {
                    return permission.id;
                }),
            };
        });

    console.log(filteredRoles.value);
};

const updateItem = (data) => {
    roles.value[currentIndex.value] = data;
};

const loadingStates = ref({});

const setLoadingState = (userId, isLoading) => {
    loadingStates.value[userId] = isLoading;
};

onMounted(async () => {
    await getRoles();
    await getPermissions();
    filterRolesWithPermissionsId();
});
</script>

<template>
    <div class="card">
        <Details :current="current" v-model:isOpen="isOpen" />

        <Edit
            v-model:isOpen="isEditOpen"
            @editItem="editItem"
            :loading="loadingEdit"
            :permissions="permissions"
            :filteredRoles="filteredRolesWithoutCurrent"
            :current="current"
        ></Edit>

        <Create
            v-model:isOpen="isCreateOpen"
            @createItem="createItem"
            :loading="loadingCreate"
            :permissions="permissions"
            :filteredRoles="filteredRoles"
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
                                class="highlight text-lime-600 bg-lime-300 border-lime-600 border-2"
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
                        icon="ti ti-eye"
                        rounded
                        size="normal"
                        text
                        severity="info"
                        @click="openDetails(slotProps.data)"
                        v-tooltip.bottom="$t('common.view_details')"
                        class="action-btn"
                    />
                    <template
                        v-if="!nonChangingRoles.includes(slotProps.data.name)"
                    >
                        <Button
                            icon="ti ti-edit"
                            rounded
                            size="normal"
                            text
                            severity="success"
                            @click="openEdit(slotProps.data, slotProps.index)"
                            v-tooltip.bottom="$t('common.edit')"
                            class="action-btn"
                            :loading="loadingStates[slotProps.data.id]"
                        />

                        <Button
                            v-if="
                                !slotProps.data.users_count ||
                                slotProps.data.users_count === 0
                            "
                            icon="ti ti-trash"
                            rounded
                            size="normal"
                            text
                            severity="danger"
                            @click="
                                confirm(deleteItem, [
                                    slotProps.data,
                                    slotProps.index,
                                ])
                            "
                            v-tooltip.bottom="$t('common.delete')"
                            class="action-btn"
                            :loading="loadingStates[slotProps.data.id]"
                        />
                    </template>
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
    </div>
</template>

<style lang="scss" scoped>
.action-btn {
    margin-left: 0.125rem;
    margin-right: 0.125rem;
}
</style>
