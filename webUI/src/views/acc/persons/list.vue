<template>
  <v-toolbar color="toolbar" :title="$t('drawer.persons')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>

    <v-tooltip :text="$t('dialog.add_new')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-account-plus" color="primary" to="/acc/persons/mod/"></v-btn>
      </template>
    </v-tooltip>
    <import-excel :windows-state="importWindowsState"></import-excel>
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon color="red">
          <v-icon>mdi-file-pdf-box</v-icon>
          <v-tooltip activator="parent" :text="$t('dialog.export_pdf')" location="bottom" />
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_pdf') }}</v-list-subheader>
        <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="print(false)">
          <template v-slot:prepend>
            <v-icon color="green-darken-4" icon="mdi-check"></v-icon>
          </template>
        </v-list-item>
        <v-list-item class="text-dark" :title="$t('dialog.selected_all')" @click="print(true)">
          <template v-slot:prepend>
            <v-icon color="indigo-darken-4" icon="mdi-expand-all"></v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-menu>
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon color="green">
          <v-icon>mdi-file-excel-box</v-icon>
          <v-tooltip activator="parent" :text="$t('dialog.export_excel')" location="bottom" />
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_excel') }}</v-list-subheader>
        <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="excellOutput(false)">
          <template v-slot:prepend>
            <v-icon color="green-darken-4" icon="mdi-check"></v-icon>
          </template>
        </v-list-item>
        <v-list-item class="text-dark" :title="$t('dialog.selected_all')" @click="excellOutput(true)">
          <template v-slot:prepend>
            <v-icon color="indigo-darken-4" icon="mdi-expand-all"></v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-menu>
    <v-tooltip :text="$t('dialog.column_settings')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-table-cog" color="primary" @click="dialogColumns = true" />
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-text-field :loading="loading" color="green" class="mb-0 pt-0 rounded-0" hide-details="auto" density="compact"
    :placeholder="$t('dialog.search_txt')" v-model="searchValue" type="text" clearable>
    <template v-slot:prepend-inner>
      <v-tooltip location="bottom" :text="$t('dialog.search')">
        <template v-slot:activator="{ props }">
          <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
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
          <v-list-item v-for="(type, index) in personTypes" :key="index" class="text-dark">
            <template v-slot:title>
              <v-checkbox v-model="type.checked" :label="type.label" @change="fetchData" hide-details></v-checkbox>
            </template>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-subheader color="primary">وضعیت تراکنش</v-list-subheader>
          <v-list-item v-for="(filter, index) in transactionFilters" :key="'filter-' + index" class="text-dark">
            <template v-slot:title>
              <v-checkbox v-model="filter.checked" :label="filter.label" @change="fetchData" hide-details></v-checkbox>
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
  </v-text-field>

  <v-data-table-server
    v-model:items-per-page="serverOptions.rowsPerPage"
    v-model:page="serverOptions.page"
    v-model:sort-by="serverOptions.sortBy"
    :headers="visibleHeaders"
    :items="items"
    :items-length="totalItems"
    :loading="loading"
    multi-sort
    class="elevation-1"
    :items-per-page-options="[5, 10, 20, 50]"
    item-value="code"
    items-per-page-text="تعداد سطر"
    no-data-text="اطلاعاتی برای نمایش وجود ندارد"
    :header-props="{ class: 'custom-header' }"
  >
    <!-- ستون چک‌باکس دستی -->
    <template v-slot:header.checkbox>
      <v-checkbox
        v-model="selectAll"
        @change="toggleSelectAll"
        hide-details
        density="compact"
      ></v-checkbox>
    </template>
    <template v-slot:item.checkbox="{ item }">
      <v-checkbox
        v-model="selectedItems"
        :value="item"
        hide-details
        density="compact"
        @change="updateSelected"
      ></v-checkbox>
    </template>

    <template v-slot:item.operation="{ item }">
      <v-menu v-if="item">
        <template v-slot:activator="{ props }">
          <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
        </template>
        <v-list>
          <v-list-item :title="$t('dialog.view')" :to="'/acc/persons/card/view/' + item.code">
            <template v-slot:prepend>
              <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
            </template>
          </v-list-item>
          <v-list-item :title="$t('dialog.edit')" :to="'/acc/persons/mod/' + item.code">
            <template v-slot:prepend>
              <v-icon icon="mdi-file-edit"></v-icon>
            </template>
          </v-list-item>
          <v-list-item :title="$t('dialog.delete')" @click="deleteItem(item.code)">
            <template v-slot:prepend>
              <v-icon color="deep-orange-accent-4" icon="mdi-trash-can"></v-icon>
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </template>

    <template v-slot:item.nikename="{ item }">
      <router-link v-if="item" :to="'/acc/persons/card/view/' + item.code">
        <span v-if="item.prelabel">{{ item.prelabel + ' ' + item.nikename }}</span>
        <span v-else>{{ item.nikename }}</span>
      </router-link>
    </template>

    <template v-slot:item.speedAccess="{ item }">
      <v-icon v-if="item && item.speedAccess" color="success">mdi-check</v-icon>
      <v-icon v-else-if="item" color="error">mdi-close</v-icon>
    </template>

    <template v-slot:item.status="{ item }">
      <span v-if="item">
        <span v-if="item.bs > item.bd" class="text-success">بستانکار</span>
        <span v-else-if="item.bs < item.bd" class="text-danger">بدهکار</span>
        <span v-else class="text-info">تراز</span>
      </span>
    </template>

    <template v-slot:item.bs="{ item }">
      <span v-if="item">{{ formatNumber(item.bs) }}</span>
    </template>

    <template v-slot:item.bd="{ item }">
      <span v-if="item">{{ formatNumber(item.bd) }}</span>
    </template>

    <template v-slot:item.balance="{ item }">
      <span v-if="item">{{ formatNumber(item.balance) }}</span>
    </template>
  </v-data-table-server>

  <v-dialog v-model="dialogColumns" max-width="600px">
    <v-card>
      <v-card-title class="text-h6">انتخاب ستون‌ها</v-card-title>
      <v-card-text>
        <v-row>
          <v-col v-for="header in optionalHeaders" :key="header.value" cols="12" sm="4">
            <v-checkbox v-model="header.visible" :label="header.title" @change="saveColumnSettings" hide-details class="mb-2"></v-checkbox>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" text @click="dialogColumns = false">بستن</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import ImportExcel from '../component/importModal/person-import-excel.vue';
import { getApiUrl } from '@/hesabixConfig';

const router = useRouter();
const loading = ref(true);
const searchValue = ref('');
const items = ref([]);
const totalItems = ref(0);
const dialogColumns = ref(false);
const serverOptions = ref({
  page: 1,
  rowsPerPage: 10,
  sortBy: [],
});
const personTypes = ref([]);
const transactionFilters = ref([
  { label: 'بدهکاران', value: 'debtors', checked: false },
  { label: 'بستانکاران', value: 'creditors', checked: false },
]);
const importWindowsState = ref({ submited: false });
const selectedItems = ref([]);
const selectAll = ref(false);

// تعریف هدرها با ستون چک‌باکس
const allHeaders = ref([
  { title: '', value: 'checkbox', sortable: false, align: 'center' },
  { title: 'عملیات', value: 'operation', sortable: false, align: 'center' },
  { title: 'کد', value: 'code', sortable: true, visible: true, align: 'center' },
  { title: 'نام مستعار', value: 'nikename', sortable: true, align: 'center' },
  { title: 'نام و نام خانوادگی', value: 'name', sortable: true, visible: true, align: 'center' },
  { title: 'تراز', value: 'balance', sortable: true, visible: true, align: 'center' },
  { title: 'وضعیت', value: 'status', sortable: true, visible: true, align: 'center' },
  { title: 'بستانکار', value: 'bs', sortable: true, visible: true, align: 'center' },
  { title: 'بدهکار', value: 'bd', sortable: true, visible: true, align: 'center' },
  { title: 'دسترسی سریع', value: 'speedAccess', sortable: true, visible: true, align: 'center' },
  { title: 'تاریخ تولد/ثبت', value: 'birthday', sortable: true, visible: true, align: 'center' },
  { title: 'شرکت', value: 'company', sortable: true, visible: true, align: 'center' },
  { title: 'شناسه ملی', value: 'shenasemeli', sortable: true, visible: true, align: 'center' },
  { title: 'کد اقتصادی', value: 'codeeghtesadi', sortable: true, visible: true, align: 'center' },
  { title: 'شماره ثبت', value: 'sabt', sortable: true, visible: true, align: 'center' },
  { title: 'کشور', value: 'keshvar', sortable: true, visible: true, align: 'center' },
  { title: 'استان', value: 'ostan', sortable: true, visible: true, align: 'center' },
  { title: 'شهر', value: 'shahr', sortable: true, visible: true, align: 'center' },
  { title: 'کد پستی', value: 'postalcode', sortable: true, visible: true, align: 'center' },
  { title: 'تلفن', value: 'tel', sortable: true, visible: true, align: 'center' },
  { title: 'موبایل', value: 'mobile', sortable: true, visible: true, align: 'center' },
  { title: 'موبایل دوم', value: 'mobile2', sortable: true, visible: true, align: 'center' },
  { title: 'ایمیل', value: 'email', sortable: true, visible: true, align: 'center' },
  { title: 'وب سایت', value: 'website', sortable: true, visible: true, align: 'center' },
  { title: 'فکس', value: 'fax', sortable: true, visible: true, align: 'center' },
]);

// توابع کمکی
const formatNumber = (num) => {
  if (!num) return '0';
  return Number(num).toLocaleString('fa-IR');
};

const loadColumnSettings = () => {
  const saved = localStorage.getItem('personTableColumns');
  if (saved) {
    const settings = JSON.parse(saved);
    allHeaders.value.forEach((header) => {
      if (header.value !== 'checkbox' && header.value !== 'operation' && header.value !== 'nikename') {
        header.visible = settings[header.value] !== false;
      }
    });
  }
};

const saveColumnSettings = () => {
  const settings = {};
  allHeaders.value.forEach((header) => {
    settings[header.value] = header.visible;
  });
  localStorage.setItem('personTableColumns', JSON.stringify(settings));
};

const fetchPersonTypes = async () => {
  try {
    const response = await axios.post('/api/person/types/get');
    personTypes.value = response.data.map((type) => ({
      ...type,
      checked: false,
    }));
  } catch (error) {
    console.error('Error fetching person types:', error);
  }
};

let debounceTimer;
const fetchData = async () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(async () => {
    loading.value = true;
    try {
      const selectedTypes = personTypes.value
        .filter((type) => type.checked)
        .map((type) => type.code);
      const selectedTransactionFilters = transactionFilters.value
        .filter((filter) => filter.checked)
        .map((filter) => filter.value);
      const sortBy = serverOptions.value.sortBy.map((sort) => ({
        key: sort.key,
        order: sort.order === 'asc' ? 'ASC' : 'DESC',
      }));
      const response = await axios.post('/api/person/list', {
        page: serverOptions.value.page,
        itemsPerPage: serverOptions.value.rowsPerPage,
        search: searchValue.value,
        types: selectedTypes.length > 0 ? selectedTypes : null,
        transactionFilters: selectedTransactionFilters.length > 0 ? selectedTransactionFilters : null,
        sortBy: sortBy.length > 0 ? sortBy : null,
      });
      items.value = response.data.items || [];
      totalItems.value = response.data.total || 0;
      resetSelections();
    } catch (error) {
      console.error('Error fetching data:', error);
      items.value = [];
      totalItems.value = 0;
    } finally {
      loading.value = false;
    }
  }, 1000);
};

const deleteItem = (code) => {
  Swal.fire({
    text: 'آیا برای حذف شخص مطمئن هستید؟',
    showCancelButton: true,
    confirmButtonText: 'بله',
    cancelButtonText: 'خیر',
  }).then((result) => {
    if (result.isConfirmed) {
      axios.post(`/api/person/delete/${code}`).then((response) => {
        if (response.data.result === 1) {
          fetchData();
          Swal.fire({
            text: 'شخص با موفقیت حذف شد.',
            icon: 'success',
            confirmButtonText: 'قبول',
          });
        } else if (response.data.result === 2) {
          Swal.fire({
            text: 'شخص به دلیل داشتن تراکنش مرتبط قابل حذف نیست.',
            icon: 'warning',
            confirmButtonText: 'قبول',
          });
        }
      });
    }
  });
};

// مدیریت انتخاب‌ها
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedItems.value = [...items.value];
  } else {
    selectedItems.value = [];
  }
};

const updateSelected = () => {
  selectAll.value = selectedItems.value.length === items.value.length;
};

const resetSelections = () => {
  selectedItems.value = [];
  selectAll.value = false;
};

// توابع خروجی
const excellOutput = (allItems = true) => {
  
  if (!allItems) {
    if (selectedItems.value.length === 0) {
      Swal.fire({
        text: 'هیچ آیتمی انتخاب نشده است.',
        icon: 'info',
        confirmButtonText: 'قبول',
      });
      return;
    }
    
    // ارسال id و code به سرور
    const selected = selectedItems.value.map(item => ({ id: item.id, code: item.code }));
    axios({
      method: 'post',
      url: '/api/person/list/excel',
      data: { items: selected },
      responseType: 'arraybuffer',
    }).then((response) => {
      const fileURL = window.URL.createObjectURL(new Blob([response.data]));
      const fileLink = document.createElement('a');
      fileLink.href = fileURL;
      fileLink.setAttribute('download', 'persons-list.xlsx');
      document.body.appendChild(fileLink);
      fileLink.click();
      fileLink.remove();
    }).catch((error) => {
      console.error('خطا در خروجی اکسل:', error);
      Swal.fire({
        text: 'خطا در خروجی اکسل.',
        icon: 'error',
        confirmButtonText: 'قبول',
      });
    });
  } else {
    axios({
      method: 'get',
      url: '/api/person/list/excel',
      responseType: 'arraybuffer',
    }).then((response) => {
      const fileURL = window.URL.createObjectURL(new Blob([response.data]));
      const fileLink = document.createElement('a');
      fileLink.href = fileURL;
      fileLink.setAttribute('download', 'persons-list.xlsx');
      document.body.appendChild(fileLink);
      fileLink.click();
      fileLink.remove();
    });
  }
};

const print = (allItems = true) => {
  
  if (!allItems) {
    if (selectedItems.value.length === 0) {
      Swal.fire({
        text: 'هیچ آیتمی انتخاب نشده است.',
        icon: 'info',
        confirmButtonText: 'قبول',
      });
      return;
    }
    
    // ارسال id و code به سرور
    const selected = selectedItems.value.map(item => ({ id: item.id, code: item.code }));
    axios.post('/api/person/list/print', { items: selected }).then((response) => {
      const printID = response.data.id;
      const apiUrl = getApiUrl();
      window.open(`${apiUrl}/front/print/${printID}`, '_blank', 'noreferrer');
    }).catch((error) => {
      console.error('خطا در خروجی PDF:', error);
      Swal.fire({
        text: 'خطا در خروجی PDF.',
        icon: 'error',
        confirmButtonText: 'قبول',
      });
    });
  } else {
    axios.post('/api/person/list/print', {}).then((response) => {
      const printID = response.data.id;
      const apiUrl = getApiUrl();
      window.open(`${apiUrl}/front/print/${printID}`, '_blank', 'noreferrer');
    });
  }
};

// Computed Properties
const visibleHeaders = computed(() => {
  return allHeaders.value.filter(
    (h) => h.value === 'checkbox' || h.value === 'operation' || h.value === 'nikename' || h.visible
  );
});

const optionalHeaders = computed(() => {
  return allHeaders.value.filter((h) => h.value !== 'checkbox' && h.value !== 'operation' && h.value !== 'nikename');
});

// Watchers
watch(searchValue, () => fetchData());
watch(serverOptions, () => fetchData(), { deep: true });

// Lifecycle Hooks
onMounted(() => {
  loadColumnSettings();
  fetchPersonTypes();
  fetchData();
});
</script>

<style>

</style>