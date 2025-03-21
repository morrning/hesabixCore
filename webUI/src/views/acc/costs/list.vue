<template>
  <v-toolbar color="toolbar" :title="$t('drawer.costs')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />
    
    <v-slide-group show-arrows>
      <v-slide-group-item>
        <v-tooltip :text="$t('dialog.add_new')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/costs/mod/" />
          </template>
        </v-tooltip>
      </v-slide-group-item>

      <v-slide-group-item>
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn 
              v-bind="props" 
              icon="" 
              color="red"
            >
              <v-tooltip activator="parent" :text="$t('dialog.export_pdf')" location="bottom" />
              <v-icon icon="mdi-file-pdf-box" />
            </v-btn>
          </template>
          <v-list>
            <v-list-subheader color="primary">{{ $t('dialog.export_pdf') }}</v-list-subheader>
            <v-list-item :disabled="!hasSelected" class="text-dark" :title="$t('dialog.selected')" @click="exportPDF(false)">
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
            <v-btn 
              v-bind="props" 
              icon="" 
              color="green"
            >
              <v-tooltip activator="parent" :text="$t('dialog.export_excel')" location="bottom" />
              <v-icon icon="mdi-file-excel-box" />
            </v-btn>
          </template>
          <v-list>
            <v-list-subheader color="primary">{{ $t('dialog.export_excel') }}</v-list-subheader>
            <v-list-item :disabled="!hasSelected" class="text-dark" :title="$t('dialog.selected')" @click="exportExcel(false)">
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
            <v-btn 
              v-bind="props" 
              icon="mdi-trash-can" 
              color="danger" 
              @click="deleteGroup"
              :disabled="!hasSelected"
            />
          </template>
        </v-tooltip>
      </v-slide-group-item>
    </v-slide-group>
  </v-toolbar>

  <v-text-field
    :loading="loading"
    color="green"
    class="mb-0 pt-0 rounded-0"
    hide-details="auto"
    density="compact"
    :placeholder="$t('dialog.search_txt')"
    v-model="searchQuery"
    type="text"
    @input="debouncedSearch"
  >
    <template v-slot:prepend-inner>
      <v-tooltip location="bottom" :text="$t('dialog.search')">
        <template v-slot:activator="{ props }">
          <v-icon v-bind="props" color="danger" icon="mdi-magnify" />
        </template>
      </v-tooltip>
            </template>
  </v-text-field>

  <v-data-table-server
    :headers="headers"
    :items="items"
    :loading="loading"
    :items-length="totalItems"
    v-model:options="serverOptions"
    @update:options="fetchData"
    item-value="code"
    class="elevation-1 data-table-wrapper"
    :header-props="{ class: 'custom-header' }"
  >
    <template #header.checkbox>
      <v-checkbox
        :model-value="isAllSelected"
        @change="toggleSelectAll"
        hide-details
        density="compact"
      />
    </template>

    <template #item.checkbox="{ item }">
      <v-checkbox
        :model-value="selectedItems.has(item.code)"
        @change="toggleSelection(item.code)"
        hide-details
        density="compact"
      />
            </template>

    <template #item.operation="{ item }">
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
        </template>
        <v-list>
          <v-list-item class="text-dark" :title="$t('dialog.view')" :to="'/acc/accounting/view/' + item.code">
            <template v-slot:prepend>
              <v-icon icon="mdi-file" color="primary" />
            </template>
          </v-list-item>
          <v-list-item class="text-dark" :title="$t('dialog.edit')" :to="'/acc/costs/mod/' + item.code">
            <template v-slot:prepend>
              <v-icon icon="mdi-pencil" />
            </template>
          </v-list-item>
          <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="deleteItem(item.code)">
            <template v-slot:prepend>
              <v-icon color="deep-orange-accent-4" icon="mdi-trash-can" />
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <template #item.amount="{ item }">
      {{ $filters.formatNumber(item.amount) }}
    </template>
  </v-data-table-server>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { debounce } from 'lodash';
import { getApiUrl } from '/src/hesabixConfig';

// تنظیم پایه URL از hesabixConfig
const apiUrl = getApiUrl();
axios.defaults.baseURL = apiUrl;

// Refs
const loading = ref(false);
const items = ref([]);
const selectedItems = ref(new Set());
const totalItems = ref(0);
const searchQuery = ref('');

// تعریف ستون‌های جدول
const headers = ref([
  { 
    title: '', 
    key: 'checkbox',
    sortable: false,
    width: '50',
    align: 'center'
  },
  { 
    title: 'ردیف', 
    key: 'index', 
    align: 'center', 
    sortable: false, 
    width: '70' 
  },
  { title: 'عملیات', key: 'operation', align: 'center', sortable: false, width: '100' },
  { title: 'کد', key: 'code', align: 'center', sortable: true },
  { title: 'تاریخ', key: 'date', align: 'center', sortable: true },
  { title: 'شرح', key: 'des', align: 'center', sortable: true },
  { title: 'مبلغ', key: 'amount', align: 'center', sortable: true },
]);

// تنظیمات سرور
const serverOptions = ref({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
  sortDesc: [],
});

// اضافه کردن computed property برای کنترل وضعیت دکمه‌های عملیات
const hasSelected = computed(() => selectedItems.value.size > 0);
const isAllSelected = computed(() => selectedItems.value.size === items.value.length);

// فچ کردن داده‌ها از سرور
const fetchData = async () => {
  try {
    loading.value = true;

    const filters = {};
    if (searchQuery.value.trim()) {
      filters.search = { value: searchQuery.value.trim() };
    }

    const sortBy = serverOptions.value.sortBy?.[0] || 'code';
    const sortDesc = serverOptions.value.sortDesc?.[0] ?? true;

    const payload = {
      filters,
      pagination: {
        page: serverOptions.value.page,
        limit: serverOptions.value.itemsPerPage,
      },
      sort: {
        sortBy,
        sortDesc,
      },
    };

    const response = await axios.post('/api/cost/list/search', {
      type: 'cost',
      ...payload
    });

    if (response.data?.items) {
      // اضافه کردن شماره ردیف به هر آیتم
      const startIndex = (serverOptions.value.page - 1) * serverOptions.value.itemsPerPage;
      items.value = response.data.items.map((item, index) => ({
        ...item,
        index: startIndex + index + 1
      }));
      totalItems.value = response.data.total; // استفاده از total از پاسخ سرور
    } else {
      items.value = [];
      totalItems.value = 0;
    }

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

// دیبونس برای جستجو
const debouncedSearch = debounce(() => fetchData(), 500);

// حذف یک آیتم
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
      const response = await axios.post('/api/accounting/remove', { code });
      if (response.data.result === 1) {
        Swal.fire({
          text: 'آیتم با موفقیت حذف شد',
          icon: 'success',
          confirmButtonText: 'قبول',
        });
        fetchData();
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

// تابع toggleSelection را به این صورت تغییر می‌دهیم
const toggleSelection = (code) => {
  const item = items.value.find(i => i.code === code);
  if (!item) return;
  
  if (selectedItems.value.has(code)) {
    selectedItems.value.delete(code);
  } else {
    selectedItems.value.add(code);
  }
};

// تابع toggleSelectAll را به این صورت تغییر می‌دهیم
const toggleSelectAll = () => {
  if (selectedItems.value.size === items.value.length) {
    selectedItems.value.clear();
  } else {
    items.value.forEach(item => {
      selectedItems.value.add(item.code);
    });
  }
};

// تغییر توابع export
const exportPDF = async (all = false) => {
  try {
    loading.value = true;
    if (!all && selectedItems.value.size === 0) {
      Swal.fire({
        text: 'هیچ آیتمی برای خروجی انتخاب نشده است',
        icon: 'warning',
        confirmButtonText: 'قبول',
      });
      return;
    }

    // ایجاد آرایه‌ای از آیتم‌های انتخاب شده
    const selectedItemsArray = all 
      ? items.value 
      : items.value.filter(item => selectedItems.value.has(item.code));

    const payload = all ? { all: true } : { items: selectedItemsArray };
    const response = await axios.post('/api/costs/list/print', payload);
    const printId = response.data.id;
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
    if (!all && selectedItems.value.size === 0) {
      Swal.fire({
        text: 'هیچ آیتمی برای خروجی انتخاب نشده است',
        icon: 'warning',
        confirmButtonText: 'قبول',
      });
      return;
    }

    // ایجاد آرایه‌ای از آیتم‌های انتخاب شده
    const selectedItemsArray = all 
      ? items.value 
      : items.value.filter(item => selectedItems.value.has(item.code));

    const payload = all ? { all: true } : { items: selectedItemsArray };
    const response = await axios.post('/api/costs/list/excel', payload, { 
      responseType: 'blob' 
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data], { 
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    }));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'costs.xlsx');
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

// تغییر تابع deleteGroup
const deleteGroup = async () => {
  if (selectedItems.value.size === 0) {
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
      const selectedCodes = Array.from(selectedItems.value);
      const promises = selectedCodes.map(code => 
        axios.post('/api/accounting/remove', { code })
      );
      
      await Promise.all(promises);
      
      Swal.fire({
        text: 'آیتم‌ها با موفقیت حذف شدند',
        icon: 'success',
        confirmButtonText: 'قبول',
      });
      
      selectedItems.value.clear();
      fetchData();
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

// اضافه کردن watch برای پاک کردن انتخاب‌ها هنگام تغییر صفحه
watch(() => serverOptions.value.page, () => {
  selectedItems.value.clear();
});

// OnMounted
onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.v-data-table ::v-deep .v-data-table__checkbox {
  margin-right: 0;
  margin-left: 0;
}
</style>