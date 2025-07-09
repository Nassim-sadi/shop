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
import Create from "./sidebars/Create.vue";
import Details from "./sidebars/Details.vue";
import Edit from "./sidebars/Edit.vue";
import Variants from "./sidebars/variants/Index.vue";

const Confirm = useConfirm();
const products = ref([]);
const loading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const per_page = ref(10);
const start_date = ref(new Date());
const end_date = ref(new Date());
const current = ref({});
const isDetailsOpen = ref(false);
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const keyword = ref("");
const status = ref(null);
const uploadPercentage = ref(0);
const currentIndex = ref(null);
const actionsPopover = ref();
const categories = ref([]);
const productOptions = ref([]);
const isVariantsOpen = ref(false);
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

const getProductOptions = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/product-options/all")
            .then((res) => {
                productOptions.value = res.data;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {});
    });
};

const getProductImages = async () => {
    loadingStates.value[current.value.id] = true;
    return new Promise((resolve, reject) => {
        axios
            .get(`api/admin/products/${current.value.id}/images`)
            .then((res) => {
                current.value.images = res.data.images;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            })
            .finally(() => {
                loadingStates.value[current.value.id] = false;
            });
    });
};

const getProducts = async () => {
    if (loading.value) return;
    loadingStates.value = [];
    loading.value = true;
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/products", {
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
                products.value = res.data.data;
                console.log("🚀 ~ .then ~  products.value:", products.value);
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
            getProducts();
        }
    },
    { debounce: 1000, maxWait: 1000 },
);

const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    per_page.value = event.rows;
    current.value = {};
    currentIndex.value = null;
    getProducts();
};

const openDetails = () => {
    getProductImages();
    isDetailsOpen.value = true;
};

const openCreate = () => {
    isCreateOpen.value = true;
};

const openEdit = () => {
    getProductImages();
    isEditOpen.value = true;
};

const reset = () => {
    keyword.value = "";
    status.value = null;
    getProducts();
};

const changeStatus = async () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .patch("api/admin/products/change-status", {
                id: current.value.id,
                status: !current.value.status ? 1 : 0,
            })
            .then((res) => {
                updateItemStatus(res.data.status);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.product.change_status"),
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
    products.value[currentIndex.value].status = status;
};

const deleteItem = () => {
    if (loadingStates.value[current.value.id]) return;
    setLoadingState(current.value.id, true);

    return new Promise((resolve, reject) => {
        axios
            .delete("api/admin/products/delete/" + current.value.id)
            .then((res) => {
                products.value.splice(currentIndex.value, 1);
                total.value--;

                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.product.delete"),
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

const loadingEdit = ref(false);
const editItem = (val) => {
    loadingEdit.value = true;

    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/products/update", val, {
                onUploadProgress: (progressEvent) => {
                    uploadPercentage.value = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total,
                    );
                },
            })
            .then((response) => {
                uploadPercentage.value = 0;
                isEditOpen.value = false;
                updateItem(response.data.product);
                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.product.update"),
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
const updateItem = (data) => {
    products.value[currentIndex.value] = data;
};

const rowClass = computed(() => (data) => {
    return [{ "!bg-red-600/10": data.deleted_at ? true : false }];
});

const loadingStates = ref({}); // Holds loading state for each user

const setLoadingState = (userId, isLoading) => {
    loadingStates.value[userId] = isLoading;
};
const loadingCreate = ref(false);
const currencies = ref([]);

const getCurrencies = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/currencies")
            .then((res) => {
                currencies.value = res.data;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            });
    });
};

const createItem = (val) => {
    loadingCreate.value = true;
    return new Promise((resolve, reject) => {
        axios
            .post("api/admin/products/create", val, {
                onUploadProgress: (progressEvent) => {
                    uploadPercentage.value = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total,
                    );
                },
            })
            .then((res) => {
                products.value.push(res.data.product);
                uploadPercentage.value = 0;
                total.value++;
                isCreateOpen.value = false;

                emitter.emit("toast", {
                    summary: $t("status.success.title"),
                    message: $t("status.success.product.create"),
                    severity: "success",
                });
                resolve(res);
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

const getCategories = async () => {
    return new Promise((resolve, reject) => {
        axios
            .get("api/admin/categories/get-children")
            .then((res) => {
                categories.value = res.data;
                resolve(res.data);
            })
            .catch((err) => {
                console.log(err);
                reject(err);
            });
    });
};

const openVariants = () => {
    isVariantsOpen.value = true;
};

onMounted(async () => {
    await getCategories();
    await getProductOptions();
    await getCurrencies();
    start_date.value.setDate(start_date.value.getDate() - 17);
    await getProducts();
});
</script>

<template>
    <div class="card">
        <Details
            :current="current ? current : {}"
            v-model:is-open="isDetailsOpen"
            :loading="loadingStates[current.id]"
        />

        <Create
            v-model:is-open="isCreateOpen"
            @create-item="createItem"
            :options="productOptions"
            :categories="categories"
            :loading="loadingCreate"
            :progress="uploadPercentage"
            :currencies="currencies"
        />

        <Variants
            :current="current ? current : null"
            :loading="loadingStates[current.id]"
            :options="productOptions"
            v-model:is-open="isVariantsOpen"
            @update-item="updateItem"
        />

        <Edit
            :current="current"
            v-model:is-open="isEditOpen"
            @edit-item="editItem"
            :options="productOptions"
            :categories="categories"
            :loading="loadingEdit"
            :loading-images="loadingStates[current.id]"
            :progress="uploadPercentage"
            :currencies="currencies"
        ></Edit>

        <DataTable
            :value="products"
            table-style="min-width: 50rem"
            :loading="loading"
            :rows="per_page"
            :paginator="true"
            :total-records="total"
            @page="onPageChange"
            data-key="id"
            :lazy="true"
            :row-hover="true"
            :rows-per-page-options="[5, 10, 20, 30]"
            :row-class="rowClass"
        >
            <template #empty>
                <div class="text-center text-surface-900 dark:text-surface-0">
                    {{ $t("common.no_data") }}
                </div>
            </template>

            <template #header>
                <div class="table-title-header">
                    <h1
                        class="text-xl font-bold mb-4 text-surface-900 dark:text-surface-0"
                    >
                        {{ $t("products.page") }}
                    </h1>

                    <Button
                        icon="ti ti-plus"
                        @click="openCreate"
                        :label="$t('common.create')"
                        severity="success"
                        v-tooltip.bottom="$t('common.create')"
                        class="bold-label"
                        v-if="ability.can('user', 'create')"
                    />
                </div>
                <div class="flex flex-wrap gap-2 mb-4 w-full">
                    <div class="flex gap-2 items-baseline">
                        <span>{{ $t("common.from") }}</span>
                        <DatePicker
                            show-icon
                            v-model="start_date"
                            date-format="yy-mm-dd"
                            :max-date="
                                end_date
                                    ? new Date(end_date)
                                    : new Date(2025, 0, 1)
                            "
                            :min-date="new Date(2024, 0, 1)"
                            show-button-bar
                            @clear-click="() => (start_date = new Date())"
                        />
                    </div>

                    <div class="flex gap-2 items-baseline">
                        <span>{{ $t("common.to") }}</span>
                        <DatePicker
                            show-icon
                            v-model="end_date"
                            date-format="yy-mm-dd"
                            :max-date="new Date()"
                            :min-date="
                                start_date
                                    ? new Date(start_date)
                                    : new Date(2024, 0, 1)
                            "
                            show-button-bar
                            @clear-click="() => (end_date = new Date())"
                        />
                    </div>

                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="keyword"
                            :placeholder="$t('common.search')"
                            @keyup.enter="getProducts"
                        />
                    </IconField>

                    <Select
                        v-model="status"
                        :options="statusOptions"
                        option-label="label"
                        option-value="value"
                        :placeholder="$t('user.statusQuery')"
                    />

                    <!-- <Select
                        v-model="role"
                        :options="roleOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="$t('user.roleQuery')"
                    />
 -->

                    <Button
                        :label="$t('common.search')"
                        icon="ti ti-search"
                        @click="getProducts"
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
                        severity="info"
                    />
                </div>
            </template>

            <Column :header="$t('products.name')">
                <template #body="slotProps">
                    <div class="flex items-center gap-2 font-semibold">
                        {{ slotProps.data.name }}
                    </div>
                </template>
            </Column>

            <Column :header="$t('products.thumbnail')">
                <template #body="slotProps">
                    <div class="flex items-center gap-2 font-semibold">
                        <img
                            :src="slotProps.data.thumbnail_image_path"
                            class="admin-product_img"
                        />
                    </div>
                </template>
            </Column>

            <Column :header="$t('products.status')">
                <template #body="slotProps">
                    <span
                        :class="
                            slotProps.data.status == 1
                                ? 'text-green-500'
                                : 'text-red-500'
                        "
                        class="font-bold"
                    >
                        {{
                            slotProps.data.status == 1
                                ? $t("common.active")
                                : $t("common.inactive")
                        }}
                    </span>
                </template>
            </Column>

            <Column :header="$t('products.featured')">
                <template #body="slotProps">
                    <span class="font-bold">
                        {{
                            slotProps.data.featured == 1
                                ? $t("products.featured")
                                : $t("products.not_featured")
                        }}
                    </span>
                </template>
            </Column>

            <Column :header="$t('common.created_at')" field="created_at">
            </Column>

            <Column :header="$t('common.updated_at')" field="updated_at">
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
                {{ $t("products.title", total) }}.
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
                    v-tooltip.bottom="$t('common.view_details')"
                    severity="info"
                    @click="openDetails"
                    class="action-btn"
                    :loading="loadingStates[current.id]"
                    v-if="ability.can('product', 'view')"
                />

                <Button
                    icon="ti ti-box-multiple"
                    rounded
                    size="normal"
                    text
                    v-tooltip.bottom="$t('products.manage_variants')"
                    :label="$t('products.manage_variants')"
                    severity="warn"
                    @click="openVariants"
                    class="action-btn"
                    :loading="loadingStates[current.id]"
                    v-if="ability.can('product', 'edit')"
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
                    v-if="ability.can('product', 'edit')"
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
                    v-if="ability.can('product', 'changeStatus')"
                />
                <!-- <Button
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
                    v-if="ability.can('product', 'changeRole')"
                /> -->

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
                    v-if="ability.can('product', 'delete')"
                />
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
                        v-if="ability.can('product', 'permaDelete')"
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
                        v-if="ability.can('product', 'restore')"
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

.admin-product_img {
    width: 5rem;
    height: auto;
    aspect-ratio: 1/1;
    border-radius: 0.25rem;
    object-fit: contain;
}
</style>
