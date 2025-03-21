<template>
  <v-container fluid class="pa-0">
    <v-toolbar color="toolbar" :title="'دریافت‌ها'">
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
          <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/persons/receive/mod/"></v-btn>
        </template>
      </v-tooltip>
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon color="error">
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
          <v-btn v-bind="props" icon color="success">
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
      <v-tooltip :text="'انتخاب ستون‌ها'" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-table-cog" color="primary" @click="showColumnDialog = true"></v-btn>
        </template>
      </v-tooltip>
    </v-toolbar>
    <v-text-field
      hide-details
      color="green"
      class="pt-0 rounded-0 mb-0"
      density="compact"
      :placeholder="$t('dialog.search_txt')"
      v-model="searchValue"
      type="text"
      clearable
    >
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
            <v-icon size="sm" v-bind="props" icon="" color="primary">
              <v-tooltip activator="parent" variant="plain" :text="$t('dialog.filters')" location="bottom" />
              <v-icon icon="mdi-filter"></v-icon>
            </v-icon>
          </template>
          <v-list>
            <v-list-subheader color="primary">
              <v-icon icon="mdi-filter"></v-icon>
              {{ $t('dialog.filters') }}
            </v-list-subheader>
            <v-list-item>
              <v-select
                class="py-2 my-2"
                v-model="dateFilter"
                :items="dateFilterOptions"
                label="فیلتر تاریخ"
                @update:model-value="loadData"
                dense
              />
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-text-field>
    <v-data-table-server
      v-model:items-per-page="itemsPerPage"
      v-model:page="page"
      :headers="visibleHeaders"
      :items="items"
      :items-length="totalItems"
      :loading="loading"
      item-value="code"
      class="elevation-1"
      :items-per-page-options="[5, 10, 20, 50]"
      items-per-page-text="تعداد سطر در هر صفحه"
      @update:options="loadData"
    >
      <template v-slot:header.select>
        <v-checkbox
          v-model="selectAll"
          @change="toggleSelectAll"
          hide-details
          density="compact"
        ></v-checkbox>
      </template>
      
      <template v-slot:item.select="{ item }">
        <v-checkbox
          :model-value="isSelected(item.code)"
          @change="toggleSelect(item)"
          hide-details
          density="compact"
        ></v-checkbox>
      </template>

      <template v-slot:item.operation="{ item }">
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn variant="text" size="small" icon="mdi-menu" v-bind="props" />
          </template>
          <v-list>
            <v-list-item :to="'/acc/accounting/view/' + item.code">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
              </template>
              <v-list-item-title>سند حسابداری</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{ name: 'person_receive_mod', params: { id: item.code }}">
              <template v-slot:prepend>
                <v-icon icon="mdi-pencil"></v-icon>
              </template>
              <v-list-item-title>ویرایش</v-list-item-title>
            </v-list-item>
            <v-list-item @click="deleteItem(item.code)">
              <template v-slot:prepend>
                <v-icon color="error" icon="mdi-delete"></v-icon>
              </template>
              <v-list-item-title class="text-error">حذف</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
      <template v-slot:item.persons="{ item }">
        <router-link
          v-for="person in item.persons"
          :key="person.code"
          class="me-2"
          :to="'/acc/persons/card/view/' + person.code"
        >
          {{ person.nikename }}
        </router-link>
      </template>
      <template v-slot:item.code="{ item }">
        <span class="text-left">{{ $filters.formatNumber(item.code) }}</span>
      </template>
      <template v-slot:item.amount="{ item }">
        <span class="text-left">{{ $filters.formatNumber(item.amount) }}</span>
      </template>
    </v-data-table-server>
    <v-card variant="" class="my-4">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="6">
              <div class="d-flex align-center">
                <v-icon class="me-2">mdi-format-list-bulleted</v-icon>
                <span>مبلغ کل صفحه:</span>
                <span class="ms-2">
                  {{ $filters.formatNumber(sumTotal) }}
                  {{ $filters.getActiveMoney().shortName }}
                </span>
              </div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="d-flex align-center">
                <v-icon class="me-2">mdi-format-list-checks</v-icon>
                <span>جمع مبلغ موارد انتخابی:</span>
                <span class="ms-2">
                  {{ $filters.formatNumber(sumSelected) }}
                  {{ $filters.getActiveMoney().shortName }}
                </span>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    <v-dialog v-model="showColumnDialog" max-width="400px">
      <v-card>
        <v-toolbar color="toolbar" title="انتخاب ستون‌‌ها" density="compact">
          <v-spacer></v-spacer>
          <v-btn
            variant="text"
            @click="showColumnDialog = false"
            type="icon">
            <v-icon>mdi-close</v-icon>
            <v-tooltip activator="parent" location="bottom">بستن</v-tooltip>
          </v-btn>
        </v-toolbar>

        <v-card-text class="pt-4">
          <v-row dense>
            <v-col cols="12" v-for="header in customizableHeaders" :key="header.key">
              <v-checkbox
                v-model="header.visible"
                :label="header.title"
                @update:model-value="updateColumnVisibility"
                hide-details
                density="comfortable"
              />
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, onMounted, watch, computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import { getApiUrl } from '@/hesabixConfig';

const router = useRouter();
const loading = ref(false);
const searchValue = ref('');
const page = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(0);
const items = ref([]);
const dateFilter = ref('all');
const showColumnDialog = ref(false);
const selectAll = ref(false);
const selectedItems = ref([]);
const sumTotal = ref(0);
const sumSelected = ref(0);

const allHeaders = reactive([
  { title: '', key: 'select', sortable: false, visible: true, customizable: false },
  { title: 'عملیات', key: 'operation', sortable: false, visible: true },
  { title: 'کد', key: 'code', visible: true },
  { title: 'اشخاص', key: 'persons', sortable: true, visible: true },
  { title: 'تاریخ', key: 'date', visible: true },
  { title: 'شرح', key: 'des', visible: true },
  { title: 'مبلغ', key: 'amount', visible: true },
]);

const customizableHeaders = computed(() => 
  allHeaders.filter(h => h.customizable !== false && h.key !== 'operation')
);
const visibleHeaders = computed(() => 
  allHeaders.filter(h => h.customizable === false || h.visible)
);

const dateFilterOptions = [
  { title: 'همه', value: 'all' },
  { title: 'امروز', value: 'today' },
  { title: 'این هفته', value: 'thisWeek' },
  { title: 'این ماه', value: 'thisMonth' },
];

const loadData = async (options = null) => {
  if (loading.value) return;
  loading.value = true;

  try {
    const params = {
      page: options?.page || page.value,
      itemsPerPage: options?.itemsPerPage || itemsPerPage.value,
      search: searchValue.value,
      dateFilter: dateFilter.value,
    };


    const response = await axios.post('/api/person/receive/list/search', params);
    

    if (response.data) {
      if (Array.isArray(response.data)) {
        items.value = response.data;
        totalItems.value = response.data.length;
      } else if (response.data.items && Array.isArray(response.data.items)) {
        items.value = response.data.items;
        totalItems.value = response.data.total || response.data.items.length;
      }

      items.value = items.value.map(item => ({
        ...item,
        code: parseInt(item.code),
        amount: parseFloat(item.amount)
      }));

     

      sumTotal.value = items.value.reduce((acc, item) => acc + parseFloat(item.amount), 0);
    }
  } catch (error) {
    console.error('Error loading data:', error);
    items.value = [];
    totalItems.value = 0;
  } finally {
    loading.value = false;
  }
};

const deleteItem = async (code) => {
  const result = await Swal.fire({
    text: 'آیا برای این سند مطمئن هستید؟',
    showCancelButton: true,
    confirmButtonText: 'بله',
    cancelButtonText: 'خیر',
  });
  if (result.isConfirmed) {
    try {
      const response = await axios.post('/api/accounting/remove', { code });
      if (response.data.result === 1) {
        items.value = items.value.filter(item => item.code !== code);
        Swal.fire({
          text: 'سند با موفقیت حذف شد.',
          icon: 'success',
          confirmButtonText: 'قبول',
        });
      }
    } catch (error) {
      console.error('Error deleting item:', error);
    }
  }
};

const isSelected = (code) => {
  return selectedItems.value.some(item => item.code === code);
};

const toggleSelect = (item) => {
  const index = selectedItems.value.findIndex(selected => selected.code === item.code);
  if (index === -1) {
    selectedItems.value.push(item);
  } else {
    selectedItems.value.splice(index, 1);
  }
  selectAll.value = items.value.length > 0 && selectedItems.value.length === items.value.length;
  updateSelectedSum();
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedItems.value = [...items.value];
  } else {
    selectedItems.value = [];
  }
  updateSelectedSum();
};

const updateSelectedSum = () => {
  if (selectedItems.value.length > 0) {
    sumSelected.value = selectedItems.value.reduce((acc, item) => 
      acc + parseFloat(item.amount), 0);
  } else {
    sumSelected.value = 0;
  }
};

const print = async (allItems = true) => {
  if (!allItems && selectedItems.value.length === 0) {
    Swal.fire({
      text: 'هیچ آیتمی انتخاب نشده است.',
      icon: 'info',
      confirmButtonText: 'قبول'
    });
    return;
  }

  try {
    let response;
    if (allItems) {
      response = await axios.post('/api/person/receive/list/print');
    } else {
      response = await axios.post('/api/person/receive/list/print', { items: selectedItems.value });
    }
    
    const printID = response.data.id;
    window.open(`${getApiUrl()}/front/print/${printID}`, '_blank', 'noreferrer');
  } catch (error) {
    console.error('Error printing:', error);
    Swal.fire({
      text: 'خطا در تولید PDF',
      icon: 'error',
      confirmButtonText: 'قبول'
    });
  }
};

const excellOutput = async (allItems = true) => {
  if (!allItems && selectedItems.value.length === 0) {
    Swal.fire({
      text: 'هیچ آیتمی انتخاب نشده است.',
      icon: 'info',
      confirmButtonText: 'قبول'
    });
    return;
  }

  try {
    let response;
    if (allItems) {
      response = await axios({
        method: 'post',
        url: '/api/person/receive/list/excel',
        responseType: 'arraybuffer'
      });
    } else {
      response = await axios({
        method: 'post',
        url: '/api/person/receive/list/excel',
        responseType: 'arraybuffer',
        data: { items: selectedItems.value }
      });
    }

    const blob = new Blob([response.data]);
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'persons-receive-list.xlsx');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Error generating excel:', error);
    Swal.fire({
      text: 'خطا در تولید فایل Excel',
      icon: 'error',
      confirmButtonText: 'قبول'
    });
  }
};

const updateColumnVisibility = () => {
  localStorage.setItem('receiveTableColumns', JSON.stringify(allHeaders));
};

const loadColumnSettings = () => {
  const savedColumns = localStorage.getItem('receiveTableColumns');
  if (savedColumns) {
    const parsedColumns = JSON.parse(savedColumns);
    parsedColumns.forEach(savedHeader => {
      const header = allHeaders.find(h => h.key === savedHeader.key);
      if (header) {
        header.visible = savedHeader.visible;
      }
    });
  }
};

watch([searchValue, page, itemsPerPage, dateFilter], () => {
  loadData();
});

watch(page, () => {
  selectedItems.value = [];
  selectAll.value = false;
});

watch([page, itemsPerPage], () => {
  loadData();
}, { deep: true });

onMounted(() => {
  loadColumnSettings();
  loadData();
});
</script>

<style scoped>
.custom-header {
  background-color: #f5f5f5;
}

.v-data-table {
  direction: rtl;
}

.text-left {
  text-align: left !important;
}

.v-data-table :deep(th) {
  font-weight: bold !important;
  white-space: nowrap;
}

.v-data-table :deep(td) {
  padding: 8px 16px !important;
}

.v-dialog .v-toolbar-title {
  font-size: 1rem;
}

.v-dialog .v-card-text {
  max-height: 400px;
  overflow-y: auto;
}

.v-btn .v-icon {
  font-size: 20px;
}
</style>
