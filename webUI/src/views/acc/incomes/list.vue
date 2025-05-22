<template>
  <v-toolbar color="toolbar" :title="$t('drawer.incomes')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />

    <v-tooltip :text="$t('dialog.add_new')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/incomes/mod/" />
      </template>
    </v-tooltip>

    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="" color="red">
          <v-tooltip activator="parent" :text="$t('dialog.export_pdf')" location="bottom" />
          <v-icon icon="mdi-file-pdf-box" />
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_pdf') }}</v-list-subheader>
        <v-list-item :disabled="!hasSelected" class="text-dark" :title="$t('dialog.selected')"
          @click="exportPDF(false)">
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

    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="" color="green">
          <v-tooltip activator="parent" :text="$t('dialog.export_excel')" location="bottom" />
          <v-icon icon="mdi-file-excel-box" />
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_excel') }}</v-list-subheader>
        <v-list-item :disabled="!hasSelected" class="text-dark" :title="$t('dialog.selected')"
          @click="exportExcel(false)">
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

    <v-tooltip :text="$t('dialog.delete')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-trash-can" color="danger" @click="deleteGroup" :disabled="!hasSelected" />
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-text-field :rounded="false" :loading="loading" color="green" class="mb-0 pt-0 rounded-0" hide-details="auto" density="compact"
    :placeholder="$t('dialog.search_txt')" v-model="searchQuery" type="text" clearable>
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
          <v-icon v-bind="props" size="sm" color="primary">
            <v-icon>mdi-filter</v-icon>
            <v-tooltip activator="parent" :text="$t('dialog.filters')" location="bottom" />
          </v-icon>
        </template>
        <v-list>
          <v-list-subheader color="primary">
            <v-icon>mdi-filter</v-icon>
            {{ $t('dialog.filters') }}
          </v-list-subheader>
          
          <!-- فیلتر درختی حساب‌ها -->
          <v-list-item>
            <v-list-item-title class="text-dark mb-2">
              فیلتر مرکز درآمد:
              <v-btn
                v-if="selectedAccountId !== '56'"
                size="small"
                color="primary"
                variant="text"
                class="ms-2"
                @click="resetAccountFilter"
              >
                <v-icon size="small" class="me-1">mdi-refresh</v-icon>
                بازنشانی
              </v-btn>
            </v-list-item-title>
            <hesabdari-tree-view
              v-model="selectedAccountId"
              :show-sub-tree="true"
              :selectable-only="false"
              :initial-account="{ code: '56', name: 'درآمدها' }"
              @select="handleAccountSelect"
              @account-selected="handleAccountSelected"
            />
          </v-list-item>
          
          <v-divider class="my-2"></v-divider>
          
          <!-- فیلتر بازه زمانی -->
          <v-list-item>
            <v-list-item-title class="text-dark mb-2">
            </v-list-item-title>
            <v-row>
              <v-col cols="12">
                <v-checkbox
                  v-model="timeFilters.find(f => f.value === 'custom').checked"
                  label="بازه زمانی"
                  @change="handleCustomDateFilterChange"
                  hide-details
                />
              </v-col>
              <v-col cols="12" v-if="timeFilters.find(f => f.value === 'custom').checked">
                <Hdatepicker
                  v-model="dateRange.from"
                  label="از تاریخ"
                  @update:model-value="handleDateRangeChange"
                />
              </v-col>
              <v-col cols="12" v-if="timeFilters.find(f => f.value === 'custom').checked">
                <Hdatepicker
                  v-model="dateRange.to"
                  label="تا تاریخ"
                  @update:model-value="handleDateRangeChange"
                />
              </v-col>
            </v-row>
          </v-list-item>
          
          <!-- فیلترهای زمانی -->
          <v-list-item v-for="(filter, index) in timeFilters.filter(f => f.value !== 'custom')" :key="index" class="text-dark">
            <template v-slot:title>
              <v-checkbox v-model="filter.checked" :label="filter.label" @change="applyTimeFilter(filter.value)"
                hide-details />
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
  </v-text-field>

  <v-data-table-server :headers="headers" :items="items" :loading="loading" :items-length="totalItems"
    v-model:options="serverOptions" v-model:expanded="expanded" @update:options="fetchData" item-value="code"
    class="elevation-1 data-table-wrapper" :header-props="{ class: 'custom-header' }" show-expand>
    <template #header.checkbox>
      <v-checkbox :model-value="isAllSelected" @change="toggleSelectAll" hide-details density="compact" />
    </template>

    <template #item.checkbox="{ item }">
      <v-checkbox :model-value="selectedItems.has(item.code)" @change="toggleSelection(item.code)" hide-details
        density="compact" />
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
          <v-list-item class="text-dark" :title="$t('dialog.edit')" :to="'/acc/incomes/mod/' + item.code">
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

    <template #expanded-row="{ columns, item }">
      <tr>
        <td :colspan="columns.length" class="expanded-row">
          <v-container class="pa-0 ma-0">
            <v-row>
              <v-col cols="12">
                <v-list density="compact" class="pa-0 ma-0">
                  <v-list-item :border="true" v-for="(center, index) in item.incomeCenters" :key="index">
                    <v-list-item-title>
                      {{ center.name }} ({{ center.code }})
                      {{ $t('dialog.acc_price') }} : {{ $filters.formatNumber(center.amount) }}
                      {{ $t('dialog.des') }} : {{ center.des }}
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item v-if="!item.incomeCenters || item.incomeCenters.length === 0">
                    <v-list-item-title>—</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>
          </v-container>
        </td>
      </tr>
    </template>
  </v-data-table-server>

  <v-container fluid class="pa-0 ma-0 my-3">
    <v-card class="rounded border-start border-success border-3" elevation="2" link href="javascript:void(0)">
      <v-card-text class="bg-body-light pa-4">
        <v-row>
          <v-col cols="12" sm="6">
            <span class="text-dark">
              <v-icon icon="mdi-format-list-bulleted" size="small" class="me-1" />
              مبلغ کل:
            </span>
            <span class="text-primary">
              {{ $filters.formatNumber(totalIncome) }}
              {{ $filters.getActiveMoney().shortName }}
            </span>
          </v-col>

          <v-col cols="12" sm="6">
            <span class="text-dark">
              <v-icon icon="mdi-format-list-checks" size="small" class="me-1" />
              جمع مبلغ موارد انتخابی:
            </span>
            <span class="text-primary">
              {{ $filters.formatNumber(selectedIncome) }}
              {{ $filters.getActiveMoney().shortName }}
            </span>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { debounce } from 'lodash';
import { getApiUrl } from '/src/hesabixConfig';
import moment from 'jalali-moment';
import HesabdariTreeView from '@/components/forms/HesabdariTreeView.vue';
import Hdatepicker from '@/components/forms/Hdatepicker.vue';

const apiUrl = getApiUrl();
axios.defaults.baseURL = apiUrl;

// Refs
const loading = ref(false);
const items = ref([]);
const selectedItems = ref(new Set());
const totalItems = ref(0);
const searchQuery = ref('');
const timeFilter = ref('all');
const expanded = ref([]);
const selectedAccountId = ref('66');
const dateRange = ref({
  from: moment().locale('fa').subtract(1, 'days').format('YYYY/MM/DD'),
  to: moment().locale('fa').format('YYYY/MM/DD')
});

// فیلترهای زمانی
const timeFilters = ref([
  { label: 'امروز', value: 'today', checked: false },
  { label: 'این هفته', value: 'week', checked: false },
  { label: 'این ماه', value: 'month', checked: false },
  { label: 'بازه زمانی', value: 'custom', checked: false },
  { label: 'همه', value: 'all', checked: true },
]);

// تعریف ستون‌های جدول
const headers = ref([
  { title: '', key: 'checkbox', sortable: false, width: '50', align: 'center' },
  { title: 'ردیف', key: 'index', align: 'center', sortable: false, width: '70' },
  { title: 'عملیات', key: 'operation', align: 'center', sortable: false, width: '100' },
  { title: 'کد', key: 'code', align: 'center', sortable: true },
  { title: 'مبلغ', key: 'amount', align: 'center', sortable: true },
  { title: 'تاریخ', key: 'date', align: 'center', sortable: true },
  { title: 'شرح', key: 'des', align: 'center', sortable: true },
]);

// تنظیمات سرور
const serverOptions = ref({
  page: 1,
  itemsPerPage: 10,
  sortBy: [],
  sortDesc: [],
});

// Computed properties
const hasSelected = computed(() => selectedItems.value.size > 0);
const isAllSelected = computed(() => selectedItems.value.size === items.value.length);

const totalIncome = computed(() => {
  return items.value.reduce((sum, item) => sum + Number(item.amount || 0), 0);
});

const selectedIncome = computed(() => {
  return items.value
    .filter((item) => selectedItems.value.has(item.code))
    .reduce((sum, item) => sum + Number(item.amount || 0), 0);
});

// متدهای مدیریت فیلتر حساب
const handleAccountSelect = (account) => {
  if (account) {
    selectedAccountId.value = account.code;
    fetchData();
  }
};

const handleAccountSelected = (account) => {
  if (account) {
    fetchData();
  }
};

// متد ریست کردن فیلتر حساب
const resetAccountFilter = () => {
  selectedAccountId.value = '66';
  fetchData();
};

// دیبونس برای جستجو
const debouncedSearch = debounce(() => fetchData(), 500);

// دیبونس برای تغییر تاریخ
const debouncedFetchData = debounce(() => {
  fetchData();
}, 500);

// اصلاح متد handleDateRangeChange
const handleDateRangeChange = () => {
  if (dateRange.value.from && dateRange.value.to) {
    // تبدیل تاریخ‌های شمسی به آبجکت moment
    const fromDate = moment(dateRange.value.from, 'jYYYY/jMM/jDD').locale('fa');
    const toDate = moment(dateRange.value.to, 'jYYYY/jMM/jDD').locale('fa');

    // بررسی اعتبار بازه زمانی
    if (fromDate.isAfter(toDate)) {
      Swal.fire({
        text: 'تاریخ شروع نمی‌تواند بعد از تاریخ پایان باشد',
        icon: 'error',
        confirmButtonText: 'قبول'
      });
      // پاک کردن تاریخ‌ها
      dateRange.value = {
        from: null,
        to: null
      };
      return;
    }

    // ارسال درخواست با تاخیر
    debouncedFetchData();
  }
};

// اصلاح متد handleCustomDateFilterChange
const handleCustomDateFilterChange = (checked) => {
  if (checked) {
    // غیرفعال کردن سایر فیلترها
    timeFilters.value.forEach(filter => {
      if (filter.value !== 'custom') {
        filter.checked = false;
      }
    });
    timeFilter.value = 'custom';
    
    // تنظیم تاریخ‌های پیش‌فرض
    dateRange.value = {
      from: moment().locale('fa').subtract(1, 'days').format('YYYY/MM/DD'),
      to: moment().locale('fa').format('YYYY/MM/DD')
    };
    
    // ارسال درخواست با تاریخ‌های پیش‌فرض
    debouncedFetchData();
  } else {
    // اگر چک‌باکس بازه زمانی غیرفعال شد، فیلتر "همه" را فعال کن
    timeFilters.value.forEach(filter => {
      filter.checked = filter.value === 'all';
    });
    timeFilter.value = 'all';
    // پاک کردن تاریخ‌ها
    dateRange.value = {
      from: null,
      to: null
    };
    debouncedFetchData();
  }
};

// اصلاح متد applyTimeFilter
const applyTimeFilter = (value) => {
  timeFilters.value.forEach((filter) => {
    filter.checked = filter.value === value;
  });
  timeFilter.value = value;
  
  // اگر فیلتر بازه زمانی غیرفعال شد، تاریخ‌ها را پاک کن
  if (value !== 'custom') {
    dateRange.value = {
      from: null,
      to: null
    };
  }
  
  fetchData();
};

// فچ کردن داده‌ها از سرور
const fetchData = async () => {
  try {
    loading.value = true;

    const filters = {};
    if (searchQuery.value.trim()) {
      filters.search = { value: searchQuery.value.trim() };
    }
    if (timeFilter.value) {
      filters.timeFilter = timeFilter.value;

      if (timeFilter.value === 'custom' && dateRange.value.from && dateRange.value.to) {
        // تبدیل تاریخ‌های شمسی به فرمت مورد نیاز
        const fromDate = moment(dateRange.value.from, 'jYYYY/jMM/jDD').locale('fa').format('YYYY/MM/DD');
        const toDate = moment(dateRange.value.to, 'jYYYY/jMM/jDD').locale('fa').format('YYYY/MM/DD');
        
        filters.date = {
          from: fromDate,
          to: toDate
        };
      } else {
        const today = moment().locale('fa').format('YYYY/MM/DD');
        switch (timeFilter.value) {
          case 'today':
            filters.date = {
              from: today,
              to: today
            };
            break;
          case 'week':
            filters.date = {
              from: moment().locale('fa').subtract(6, 'days').format('YYYY/MM/DD'),
              to: today
            };
            break;
          case 'month':
            filters.date = {
              from: moment().locale('fa').startOf('jMonth').format('YYYY/MM/DD'),
              to: today
            };
            break;
        }
      }
    }
    
    // اضافه کردن فیلتر حساب
    if (selectedAccountId.value) {
      filters.account = selectedAccountId.value;
    }

    const sortByArray = Array.isArray(serverOptions.value.sortBy) ? serverOptions.value.sortBy : [];
    const sortDescArray = Array.isArray(serverOptions.value.sortDesc) ? serverOptions.value.sortDesc : [];
    const sortBy = sortByArray.length > 0 ? sortByArray[0].key : 'code';
    const sortDesc = sortDescArray.length > 0 ? sortDescArray[0] : true;

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

    console.log('Request payload:', payload); // برای دیباگ

    const response = await axios.post('/api/income/list/search', {
      type: 'income',
      ...payload,
    });

    if (response.data?.items) {
      const startIndex = (serverOptions.value.page - 1) * serverOptions.value.itemsPerPage;
      items.value = response.data.items.map((item, index) => ({
        ...item,
        index: startIndex + index + 1,
      }));
      totalItems.value = response.data.total;
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

// انتخاب و لغو انتخاب
const toggleSelection = (code) => {
  if (selectedItems.value.has(code)) {
    selectedItems.value.delete(code);
  } else {
    selectedItems.value.add(code);
  }
};

const toggleSelectAll = () => {
  if (selectedItems.value.size === items.value.length) {
    selectedItems.value.clear();
  } else {
    items.value.forEach((item) => selectedItems.value.add(item.code));
  }
};

// خروجی PDF
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

    const selectedItemsArray = all
      ? items.value
      : items.value.filter((item) => selectedItems.value.has(item.code));

    const payload = all ? { all: true } : { items: selectedItemsArray };
    const response = await axios.post('/api/incomes/list/print', payload);
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

// خروجی Excel
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

    const selectedItemsArray = all
      ? items.value
      : items.value.filter((item) => selectedItems.value.has(item.code));

    const payload = all ? { all: true } : { items: selectedItemsArray };
    const response = await axios.post('/api/incomes/list/excel', payload, {
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(
      new Blob([response.data], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      })
    );
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'incomes.xlsx');
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

// حذف گروهی
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
      const promises = selectedCodes.map((code) =>
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

// اضافه کردن watch برای تغییرات تاریخ‌ها
watch([() => dateRange.value.from, () => dateRange.value.to], () => {
  if (timeFilter.value === 'custom') {
    handleDateRangeChange();
  }
}, { deep: true });

// Watchers
watch(() => serverOptions.value.page, () => {
  selectedItems.value.clear();
});
watch(searchQuery, () => debouncedSearch());

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

.expanded-row {
  background-color: #f5f5f5;
  padding: 10px;
}
</style>