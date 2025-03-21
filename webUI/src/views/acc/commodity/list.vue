<template>
  <v-toolbar color="toolbar" :title="$t('drawer.commodity')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />
    
    <!-- اضافه کردن v-slide-group برای اسکرول افقی -->
    <v-slide-group show-arrows>
      <v-slide-group-item>
        <v-tooltip :text="$t('dialog.add_new')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-package-variant-plus" color="primary" to="/acc/commodity/mod/" />
          </template>
        </v-tooltip>
      </v-slide-group-item>

      <v-slide-group-item>
        <ChangePriceGroup :items="itemsSelected" :windows-state="importWindowsState" />
      </v-slide-group-item>

      <v-slide-group-item>
        <import-excel :windows-state="importWindowsState" />
      </v-slide-group-item>

      <v-slide-group-item>
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="" color="red">
              <v-tooltip activator="parent" :text="$t('dialog.export_pdf')" location="bottom" />
              <v-icon icon="mdi-file-pdf-box" />
            </v-btn>
          </template>
          <v-list>
            <v-list-subheader color="primary">{{ $t('dialog.export_pdf') }}</v-list-subheader>
            <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="exportPDF(false)">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-check" />
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.all')" @click="exportPDF(true)">
              <template v-slot:prepend>
                <v-icon color="indigo-darken-4" icon="mdi-expand-all" />
              </template>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-slide-group-item>

      <v-slide-group-item>
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="" color="green">
              <v-tooltip activator="parent" :text="$t('dialog.export_excel')" location="bottom" />
              <v-icon icon="mdi-file-excel-box" />
            </v-btn>
          </template>
          <v-list>
            <v-list-subheader color="primary">{{ $t('dialog.export_excel') }}</v-list-subheader>
            <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="exportExcel(false)">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-check" />
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.all')" @click="exportExcel(true)">
              <template v-slot:prepend>
                <v-icon color="indigo-darken-4" icon="mdi-expand-all" />
              </template>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-slide-group-item>

      <v-slide-group-item>
        <v-tooltip :text="$t('dialog.delete')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-trash-can" color="danger" @click="deleteGroup" />
          </template>
        </v-tooltip>
      </v-slide-group-item>

      <v-slide-group-item>
        <v-tooltip :text="$t('dialog.column_settings')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-table-cog" color="primary" @click="dialogVisible = true" />
          </template>
        </v-tooltip>
      </v-slide-group-item>
    </v-slide-group>
  </v-toolbar>

  <v-text-field :loading="loading" color="green" class="mb-0 pt-0 rounded-0" hide-details="auto" density="compact"
    :placeholder="$t('dialog.search_txt')" v-model="searchQuery" type="text" @input="debouncedSearch">
    <template v-slot:prepend-inner>
      <v-tooltip location="bottom" :text="$t('dialog.search')">
        <template v-slot:activator="{ props }">
          <v-icon v-bind="props" color="danger" icon="mdi-magnify" />
        </template>
      </v-tooltip>
    </template>
    <template v-slot:append-inner>
      <v-menu :close-on-content-click="false">
        <template v-slot:activator="{ props }">
          <v-icon size="sm" v-bind="props" icon="" color="primary">
            <v-tooltip activator="parent" variant="plain" :text="$t('dialog.filters')" location="bottom" />
            <v-icon icon="mdi-filter" />
          </v-icon>
        </template>
        <v-list>
          <v-list-subheader color="primary">
            <v-icon icon="mdi-filter" />
            {{ $t('dialog.filters') }}
          </v-list-subheader>
          <v-list-item v-for="(item, index) in categories" :key="index" class="text-dark">
            <div class="form-check form-check-inline mx-1">
              <input v-model="item.checked" class="form-check-input" type="checkbox" @change="fetchData" />
              <label class="form-check-label">{{ item.name }}</label>
            </div>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
  </v-text-field>

  <EasyDataTable table-class-name="customize-table" v-model:items-selected="itemsSelected" multi-sort show-index
    alternating :headers="visibleHeaders" :items="items" :loading="loading" :server-items-length="totalItems"
    v-model:server-options="serverOptions" @update:server-options="fetchData" theme-color="#1d90ff"
    header-text-direction="center" body-text-direction="center" rows-per-page-message="تعداد سطر"
    empty-message="اطلاعاتی برای نمایش وجود ندارد" rows-of-page-separator-message="از">
    <template #item-operation="{ code }">
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
        </template>
        <v-list>
          <v-list-item class="text-dark" :title="$t('dialog.edit')" :to="'/acc/commodity/mod/' + code">
            <template v-slot:prepend>
              <v-icon icon="mdi-file-edit" />
            </template>
          </v-list-item>
          <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="deleteItem(code)">
            <template v-slot:prepend>
              <v-icon color="deep-orange-accent-4" icon="mdi-trash-can" />
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <template #item-speedAccess="{ speedAccess }">
      <i v-if="speedAccess" class="fa fa-check text-success" />
    </template>
    <template #item-priceBuy="{ priceBuy }">
      {{ $filters.formatNumber(priceBuy) }}
    </template>
    <template #item-priceSell="{ priceSell }">
      {{ $filters.formatNumber(priceSell) }}
    </template>
    <template #item-count="{ count, khadamat }">
      <label v-if="!khadamat">
        <span class="text-danger" v-if="count < 0">
          <i class="fa fa-arrow-down me-1" />
          {{ $filters.formatNumber(Math.abs(count)) }}
          کمبود موجودی
        </span>
        <span class="text-success" v-if="count > 0">
          <i class="fa fa-arrow-up me-1" />
          {{ $filters.formatNumber(Math.abs(count)) }}
        </span>
        <span class="text-dark" v-if="count === 0">
          {{ $filters.formatNumber(Math.abs(count)) }}
        </span>
      </label>
      <label v-else>آیتم خدماتی</label>
    </template>
    <template #item-khadamat="{ khadamat }">
      <label v-if="!khadamat">کالا و اقلام فیزیکی</label>
      <label v-else>خدمات</label>
    </template>
    <template #item-withoutTax="{ withoutTax }">
      <i v-if="!withoutTax || withoutTax === null" class="fa fa-check text-success" />
      <i v-else class="fa fa-close text-danger" />
    </template>
    <template #item-commodityCountCheck="{ commodityCountCheck }">
      <i v-if="commodityCountCheck" class="fa fa-check text-success" />
      <i v-else class="fa fa-close text-danger" />
    </template>
  </EasyDataTable>

  <!-- دیالوگ تنظیمات ستون‌ها -->
  <v-dialog v-model="dialogVisible" max-width="500">
    <v-card>
      <v-card-title class="text-h6">
        {{ $t('dialog.column_settings') }}
      </v-card-title>
      <v-card-text>
        <p class="text-body-2 text-grey-darken-1">
          ستون‌هایی که می‌خواهید در جدول نمایش داده شوند را انتخاب کنید. تغییرات به صورت خودکار ذخیره می‌شوند.
        </p>
        <v-checkbox v-for="header in allHeaders" :key="header.value" v-model="header.visible" :label="header.text"
          @update:modelValue="updateColumnVisibility" color="primary" hide-details />
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn color="primary" text @click="dialogVisible = false">بستن</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { debounce } from 'lodash';
import ImportExcel from '../component/importModal/commodity-import-excel.vue';
import ChangePriceGroup from '../component/commodity/changePriceGroup.vue';
import { getApiUrl } from '/src/hesabixConfig';

// تنظیم پایه URL از hesabixConfig برای درخواست‌های axios
const apiUrl = getApiUrl();
axios.defaults.baseURL = apiUrl;

// Refs
const loading = ref(false);
const items = ref([]);
const itemsSelected = ref([]);
const totalItems = ref(0);
const searchQuery = ref('');
const categories = ref([]);
const importWindowsState = ref({ submited: false });
const dialogVisible = ref(false);

// تعریف همه ستون‌ها
const allHeaders = ref([
  { text: 'عملیات', value: 'operation', width: '100', visible: true },
  { text: 'کد', value: 'code', sortable: true, visible: true },
  { text: 'کالا / خدمات', value: 'khadamat', sortable: true, width: 150, visible: true },
  { text: 'نام کالا و خدمات', value: 'name', sortable: true, width: 150, visible: true },
  { text: 'واحد شمارش', value: 'unit', sortable: true, width: 100, visible: true },
  { text: 'موجودی', value: 'count', sortable: false, width: 150, visible: true },
  { text: 'دسترسی سریع', value: 'speedAccess', width: 100, visible: true },
  { text: 'مالیات', value: 'withoutTax', width: 100, visible: true },
  { text: 'دسته‌بندی', value: 'cat', sortable: true, width: 100, visible: true },
  { text: 'قیمت خرید', value: 'priceBuy', sortable: true, width: 100, visible: true },
  { text: 'قیمت فروش', value: 'priceSell', sortable: true, width: 100, visible: true },
  { text: 'نقطه سفارش', value: 'orderPoint', width: 100, visible: true },
  { text: 'حداقل سفارش', value: 'minOrderCount', width: 100, visible: true },
  { text: 'زمان انتظار', value: 'dayLoading', width: 100, visible: true },
  { text: 'کنترل موجودی', value: 'commodityCountCheck', width: 100, visible: true },
]);

// ستون‌های قابل نمایش (فیلتر شده بر اساس visible)
const visibleHeaders = computed(() => allHeaders.value.filter(header => header.visible));

// کلید منحصربه‌فرد برای localStorage
const LOCAL_STORAGE_KEY = 'hesabix_commodity_table_columns';

// لود تنظیمات ستون‌ها از localStorage
const loadColumnSettings = () => {
  const savedSettings = localStorage.getItem(LOCAL_STORAGE_KEY);
  if (savedSettings) {
    const visibleColumns = JSON.parse(savedSettings);
    allHeaders.value.forEach(header => {
      header.visible = visibleColumns.includes(header.value);
    });
  } else {
    // اگر تنظیمی وجود نداشت، همه ستون‌ها قابل نمایش باشند
    allHeaders.value.forEach(header => (header.visible = true));
  }
};

// ذخیره تنظیمات ستون‌ها در localStorage
const saveColumnSettings = () => {
  const visibleColumns = allHeaders.value
    .filter(header => header.visible)
    .map(header => header.value);
  localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(visibleColumns));
};

// آپدیت تنظیمات ستون‌ها هنگام تغییر چک‌باکس
const updateColumnVisibility = () => {
  saveColumnSettings();
};

// فچ کردن داده‌ها از سرور
const serverOptions = ref({
  page: 1,
  rowsPerPage: 10,
  sortBy: 'code',
  sortDesc: true, // مقدار پیش‌فرض بولین
});

const fetchData = async () => {
  try {
    loading.value = true;

    const selectedCategories = categories.value
      .filter((cat) => cat.checked)
      .map((cat) => cat.id);

    const filters = {};
    if (searchQuery.value.trim()) {
      filters.search = { value: searchQuery.value.trim() };
    }
    if (selectedCategories.length > 0) {
      filters.cat = { operator: '=', value: selectedCategories };
    }

    const payload = {
      filters,
      pagination: {
        page: serverOptions.value.page,
        limit: serverOptions.value.rowsPerPage,
      },
      sort: {
        sortBy: serverOptions.value.sortBy,
        sortDesc: serverOptions.value.sortDesc === undefined ? true : serverOptions.value.sortDesc, // اصلاح مقدار undefined
      },
    };

    const response = await axios.post('/api/commodities/search', payload);

    items.value = response.data.results;
    totalItems.value = response.data.pagination.total_items;
  } catch (error) {
    console.error('Error fetching data:', error);
    Swal.fire({
      text: 'خطا در بارگذاری داده‌ها: ' + (error.response?.data?.detail || error.message),
      icon: 'error',
      confirmButtonText: 'قبول',
    });
  } finally {
    loading.value = false;
  }
};

// فچ کردن دسته‌بندی‌ها
const fetchCategories = async () => {
  try {
    const response = await axios.post('/api/commodity/cat/get/line');
    categories.value = response.data.map((cat) => ({
      id: cat.id,
      name: cat.name,
      checked: false,
    }));
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

const exportPDF = async (all = false) => {
  try {
    loading.value = true;
    if (!all && !itemsSelected.value.length) {
      Swal.fire({
        text: 'هیچ آیتمی برای خروجی انتخاب نشده است',
        icon: 'warning',
        confirmButtonText: 'قبول',
      });
      return;
    }
    const payload = all ? { all: true } : { items: itemsSelected.value };
    const response = await axios.post('/api/commodity/list/print', payload);
    const printId = response.data.id;
    const apiUrl = getApiUrl();
    window.open(`${apiUrl}/front/print/${printId}`, '_blank');
  } catch (error) {
    console.error('Error exporting PDF:', error);
    Swal.fire({
      text: 'خطا در خروجی PDF: ' + (error.response?.data?.detail || error.message),
      icon: 'error',
      confirmButtonText: 'قبول',
    });
  } finally {
    loading.value = false;
  }
};

const exportExcel = async (all = false) => {
  try {
    loading.value = true;
    if (!all && !itemsSelected.value.length) {
      Swal.fire({
        text: 'هیچ آیتمی برای خروجی انتخاب نشده است',
        icon: 'warning',
        confirmButtonText: 'قبول',
      });
      return;
    }
    const payload = all ? { all: true } : { items: itemsSelected.value };
    const response = await axios.post('/api/commodity/list/excel', payload, { responseType: 'blob' });
    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'commodities.xlsx');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting Excel:', error);
    Swal.fire({
      text: 'خطا در خروجی Excel: ' + (error.response?.data?.detail || error.message),
      icon: 'error',
      confirmButtonText: 'قبول',
    });
  } finally {
    loading.value = false;
  }
};

const deleteItem = async (code) => {
  const result = await Swal.fire({
    text: 'آیا از حذف این آیتم اطمینان دارید؟',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'بله',
    cancelButtonText: 'خیر',
  });
  if (result.isConfirmed) {
    try {
      loading.value = true;
      const response = await axios.delete(`/api/commodities/${code}`);
      if (response.data.result === 1) {
        Swal.fire({
          text: 'آیتم با موفقیت حذف شد',
          icon: 'success',
          confirmButtonText: 'قبول',
        });
        fetchData(); // به‌روزرسانی لیست پس از حذف
      } else if (response.data.result === 2) {
        Swal.fire({
          text: 'این کالا در اسناد حسابداری یا انبار استفاده شده و قابل حذف نیست',
          icon: 'error',
          confirmButtonText: 'قبول',
        });
      }
    } catch (error) {
      console.error('Error deleting item:', error);
      Swal.fire({
        text: 'خطا در حذف آیتم: ' + (error.response?.data?.detail || error.message),
        icon: 'error',
        confirmButtonText: 'قبول',
      });
    } finally {
      loading.value = false;
    }
  }
};

const deleteGroup = async () => {
  if (!itemsSelected.value.length) {
    Swal.fire({
      text: 'هیچ آیتمی برای حذف انتخاب نشده است',
      icon: 'warning',
      confirmButtonText: 'قبول',
    });
    return;
  }
  const result = await Swal.fire({
    text: 'آیا از حذف آیتم‌های انتخاب‌شده اطمینان دارید؟',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'بله',
    cancelButtonText: 'خیر',
  });
  if (result.isConfirmed) {
    try {
      loading.value = true;
      const codes = itemsSelected.value.map(item => item.code);
      const response = await axios.post('/api/commodity/deletegroup', { codes });
      if (response.data.Success) {
        Swal.fire({
          text: response.data.result.ignored
            ? 'برخی آیتم‌ها به دلیل استفاده در اسناد حذف نشدند'
            : 'آیتم‌ها با موفقیت حذف شدند',
          icon: response.data.result.ignored ? 'warning' : 'success',
          confirmButtonText: 'قبول',
        });
        itemsSelected.value = [];
        fetchData();
      }
    } catch (error) {
      console.error('Error deleting group:', error);
      Swal.fire({
        text: 'خطا در حذف گروهی: ' + (error.response?.data?.detail || error.message),
        icon: 'error',
        confirmButtonText: 'قبول',
      });
    } finally {
      loading.value = false;
    }
  }
};

// دیبونس برای جستجو
const debouncedSearch = debounce(() => fetchData(true), 500);

// Watchers
watch(
  () => importWindowsState.value.submited,
  (newValue) => {
    if (newValue) {
      importWindowsState.value.submited = false;
      fetchData();
    }
  }
);

// OnMounted
onMounted(() => {
  loadColumnSettings(); // لود تنظیمات ستون‌ها هنگام شروع
  fetchCategories();
  fetchData();
});
</script>

<style scoped>
/* استایل‌های مورد نیاز */
</style>