<script setup>
import { onMounted, ref, watch } from "vue";

import { useConfirm } from "primevue/useconfirm";

import { ability } from "@/plugins/ability";
import axios from "@/plugins/axios";
import emitter from "@/plugins/emitter";
import { $t } from "@/plugins/i18n";

import ChangeOrder from "./sidebars/ChangeOrder.vue";
import Create from "./sidebars/Create.vue";
import Details from "./sidebars/Details.vue";
import Edit from "./sidebars/Edit.vue";

const Confirm = useConfirm();
const categories = ref([]);
const loading = ref(false);
const current = ref({});
const isDetailsOpen = ref(false);
const isEditOpen = ref(false);
const isCreateOpen = ref(false);
const actionsPopover = ref({ visible: false });

const togglePopover = ({ event: event, current: data }) => {
    current.value = data;
    actionsPopover.value.toggle(event);
};

watch(
    () => actionsPopover.value.visible,
    (val) => {
        if (!val && !loading.value && !loadingStates.value[current.value.id]) {
            current.value = {};
        }
    },
);

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

const getCategories = async () => {
    if (loading.value) return;
    loadingStates.value = [];
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/categories")
            .then((res) => {
                categories.value = res.data;
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
    isDetailsOpen.value = true;
};

const openEdit = () => {
    isEditOpen.value = true;
};

const createItem = (data) => {
    if (loading.value) return;
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/categories/create", data)
            .then((res) => {
                isCreateOpen.value = false;
                addItem(res.data.category);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.category.create"),
                    severity: "success",
                });
                resolve(res.data);
            })
            .catch((err) => {
                if (err.response.status == 432) {
                    emitter.emit("toast", {
                        summary: $t("status.error.title"),
                        message: $t("status.error.category.slug"),
                        severity: "error",
                    });
                }
                reject(err);
            })
            .finally(() => {
                loading.value = false;
                current.value = {};
            });
    });
};

const addItem = (data) => {
    if (current.value.id) {
        current.value.children.push(data);
    } else {
        categories.value.push(data);
    }
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
                current.value = {};
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
                current.value = {};
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
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/categories/update", val)
            .then((res) => {
                console.log(res.data);

                updateCategoryById(
                    categories.value,
                    res.data.category.id,
                    res.data.category,
                );
                current.value = {};
                isEditOpen.value = false;
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.category.update"),
                    severity: "success",
                });
                resolve(res);
            })
            .catch((error) => {
                console.log(error);
                reject(error);
            })
            .finally(() => {
                loading.value = false;
            });
    });
};

const updateCategoryById = (categories, idToUpdate, newData) => {
    categories.forEach((category, index) => {
        if (category.id === idToUpdate) {
            Object.assign(category, newData);
        } else if (category.children && category.children.length > 0) {
            updateCategoryById(category.children, idToUpdate, newData);
        }
    });
};

const loadingStates = ref({});

const setLoadingState = (userId, isLoading) => {
    loadingStates.value[userId] = isLoading;
};

const updateOrder = (data) => {
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .patch("api/admin/categories/update-order", { categories: data })
            .then(async (res) => {
                loading.value = false;
                await getCategories();
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.category.update_order"),
                    severity: "success",
                });
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                isChangeOrderOpen.value = false;
                loading.value = false;
            });
    });
};

onMounted(async () => {
    await getCategories();
});

const isChangeOrderOpen = ref(false);
const selectedCategories = ref([]);
const openChangeOrder = () => {
    if (current.value && current.value.id) {
        const category = findCategoryById(categories.value, current.value.id);
        if (category && category.children && category.children.length > 1) {
            selectedCategories.value = category.children;
        } else {
            selectedCategories.value = [];
        }
    } else {
        selectedCategories.value = categories.value;
    }
    isChangeOrderOpen.value = true;
};

const findCategoryById = (categories, id) => {
    for (const category of categories) {
        if (category.id === id) {
            return category;
        }
        if (category.children && category.children.length > 0) {
            const found = findCategoryById(category.children, id);
            if (found) return found;
        }
    }
    return null;
};
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
            :loading="loading"
        />

        <Create
            v-model:isOpen="isCreateOpen"
            @createItem="createItem"
            :loading="loading"
            :parent="current ? current.id : {}"
        />

        <ChangeOrder
            v-model:isOpen="isChangeOrderOpen"
            @submit="updateOrder"
            :current="selectedCategories"
            :loading="loading"
            :parentName="current.name || ''"
        />

        <TreeTable
            :value="categories"
            tableStyle="min-width: 50rem"
            :loading="loading"
            dataKey="id"
            :rowHover="true"
            size="small"
        >
            <template #empty>
                <div class="text-center">{{ $t("common.no_data") }}</div>
            </template>

            <template #header>
                <div class="flex justify-between items-center flex-wrap mb-4">
                    <h1
                        class="text-xl font-bold mb-0 text-surface-900 dark:text-surface-0"
                    >
                        {{ $t("categories.page") }}
                    </h1>
                    <div class="flex gap-2 flex-wrap">
                        <Button
                            icon="ti ti-sort-descending-2"
                            :label="$t('categories.change_order')"
                            @click="openChangeOrder"
                            class="bold-label"
                            v-tooltip.bottom="
                                $t('categories.change_order_tooltip')
                            "
                            severity="warn"
                            outlined
                            v-if="ability.can('category', 'update')"
                        ></Button>
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
                </div>
            </template>

            <Column :header="$t('categories.order')" expander>
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
                            slotProps.node.status == 1
                                ? 'text-green-500'
                                : 'text-red-500'
                        "
                        class="font-bold"
                    >
                        {{
                            slotProps.node.status == 1
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
                    icon="ti ti-plus"
                    rounded
                    size="normal"
                    text
                    :label="$t('categories.add_sub')"
                    severity="success"
                    @click="openAdd"
                    v-tooltip.bottom="$t('categories.add_sub')"
                    class="action-btn"
                    :loading="loadingStates[current.id]"
                    v-if="ability.can('category', 'add')"
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
                        current.children &&
                        !current.children.length
                    "
                />

                <Button
                    icon="ti ti-sort-descending-2"
                    rounded
                    size="normal"
                    text
                    severity="warn"
                    :label="$t('categories.change_order')"
                    @click="openChangeOrder"
                    v-tooltip.bottom="$t('categories.change_order_tooltip')"
                    class="action-btn"
                    :loading="loadingStates[current.id]"
                    v-if="
                        ability.can('category', 'changeOrder') &&
                        current.children &&
                        current.children.length > 1
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

.change-order-btns > Button {
    margin: 0;
    padding: 0;
}
</style>
