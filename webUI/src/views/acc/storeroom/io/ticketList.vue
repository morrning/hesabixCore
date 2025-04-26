<template>
  <v-toolbar color="toolbar" title="حواله‌های انبار">
    <template v-slot:prepend>
      <v-tooltip text="بازگشت" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />

    <v-slide-group show-arrows>
      <v-slide-group-item>
        <v-tooltip text="حواله جدید" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" color="primary" icon="mdi-plus" :to="'/acc/storeroom/new/ticket/type'" />
          </template>
        </v-tooltip>
      </v-slide-group-item>

      <v-slide-group-item>
        <v-tooltip text="تنظیمات ستون‌ها" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-table-cog" color="primary" @click="showColumnDialog = true" />
          </template>
        </v-tooltip>
      </v-slide-group-item>
    </v-slide-group>
  </v-toolbar>

  <v-tabs v-model="activeTab" color="primary" grow class="mb-3">
    <v-tab value="output">
      <v-icon start>mdi-file-export</v-icon>
      حواله‌های خروج
    </v-tab>
    <v-tab value="input">
      <v-icon start>mdi-file-import</v-icon>
      حواله‌های ورود
    </v-tab>
  </v-tabs>

  <v-window v-model="activeTab">
    <!-- تب حواله‌های خروج -->
    <v-window-item value="output">
      <v-text-field v-model="outputSearchValue" prepend-inner-icon="mdi-magnify" label="جستجو" variant="outlined"
          density="compact" hide-details class="mb-1"></v-text-field>

        <v-data-table :headers="visibleHeaders" :items="outputItems" :search="outputSearchValue" :loading="loading"
          hover density="compact"  class="elevation-1 text-center"
          :header-props="{ class: 'custom-header' }">
          <template v-slot:item="{ item }">
            <tr>
              <td v-if="isColumnVisible('operation')" class="text-center">
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
                  </template>
                  <v-list>
                    <v-list-item :to="'/acc/storeroom/ticket/view/' + item.code">
                      <template v-slot:prepend>
                        <v-icon color="success">mdi-eye</v-icon>
                      </template>
                      <v-list-item-title>مشاهده</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="deleteTicket('output', item.code)">
                      <template v-slot:prepend>
                        <v-icon color="error">mdi-delete</v-icon>
                      </template>
                      <v-list-item-title>حذف</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </td>
              <td v-if="isColumnVisible('code')" class="text-center">{{ formatNumber(item.code) }}</td>
              <td v-if="isColumnVisible('date')" class="text-center">{{ item.date }}</td>
              <td v-if="isColumnVisible('doc.code')" class="text-center">{{ item.doc.code }}</td>
              <td v-if="isColumnVisible('person.nikename')" class="text-center">{{ item.person.nikename }}</td>
              <td v-if="isColumnVisible('des')" class="text-center">{{ item.des }}</td>
            </tr>
          </template>
        </v-data-table>
    </v-window-item>

    <!-- تب حواله‌های ورود -->
    <v-window-item value="input">
      <v-text-field v-model="inputSearchValue" prepend-inner-icon="mdi-magnify" label="جستجو" variant="outlined"
          density="compact" hide-details class="mb-1"></v-text-field>

        <v-data-table :headers="visibleHeaders" :items="inputItems" :search="inputSearchValue" :loading="loading" hover
          density="compact"  class="elevation-1 text-center"
          :header-props="{ class: 'custom-header' }">
          <template v-slot:item="{ item }">
            <tr>
              <td v-if="isColumnVisible('operation')" class="text-center">
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
                  </template>
                  <v-list>
                    <v-list-item :to="'/acc/storeroom/ticket/view/' + item.code">
                      <template v-slot:prepend>
                        <v-icon color="success">mdi-eye</v-icon>
                      </template>
                      <v-list-item-title>مشاهده</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="deleteTicket('input', item.code)">
                      <template v-slot:prepend>
                        <v-icon color="error">mdi-delete</v-icon>
                      </template>
                      <v-list-item-title>حذف</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </td>
              <td v-if="isColumnVisible('code')" class="text-center">{{ formatNumber(item.code) }}</td>
              <td v-if="isColumnVisible('date')" class="text-center">{{ item.date }}</td>
              <td v-if="isColumnVisible('doc.code')" class="text-center">{{ item.doc.code }}</td>
              <td v-if="isColumnVisible('person.nikename')" class="text-center">{{ item.person.nikename }}</td>
              <td v-if="isColumnVisible('des')" class="text-center">{{ item.des }}</td>
            </tr>
          </template>
        </v-data-table>
    </v-window-item>
  </v-window>

  <v-dialog v-model="showColumnDialog" max-width="500">
    <v-card>
      <v-toolbar color="toolbar" title="مدیریت ستون‌ها">
        <v-spacer></v-spacer>
        <v-btn icon @click="showColumnDialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col v-for="header in allHeaders" :key="header.key" cols="12" sm="6">
            <v-checkbox v-model="header.visible" :label="header.title" @change="updateColumnVisibility" hide-details />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>

  <v-dialog v-model="deleteDialog.show" max-width="400">
    <v-card>
      <v-card-title class="text-h6">
        تأیید حذف
      </v-card-title>
      <v-card-text>
        آیا برای حذف حواله مطمئن هستید؟
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" variant="text" @click="deleteDialog.show = false">خیر</v-btn>
        <v-btn color="error" variant="text" @click="confirmDelete">بله</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000" location="bottom">
    {{ snackbar.message }}
    <template v-slot:actions>
      <v-btn variant="text" @click="snackbar.show = false">
        بستن
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from "axios";

interface Ticket {
  code: string;
  date: string;
  doc: {
    code: string;
  };
  person: {
    nikename: string;
  };
  des: string;
}

interface Header {
  title: string;
  key: string;
  align: string;
  sortable: boolean;
  width: number;
  visible: boolean;
}

// Refs
const loading = ref(false);
const inputItems = ref<Ticket[]>([]);
const outputItems = ref<Ticket[]>([]);
const inputSearchValue = ref('');
const outputSearchValue = ref('');
const activeTab = ref('output');
const showColumnDialog = ref(false);

// دیالوگ‌ها
const deleteDialog = ref({
  show: false,
  type: null as 'input' | 'output' | null,
  code: null as string | null
});

const snackbar = ref({
  show: false,
  message: '',
  color: 'primary'
});

// تعریف همه ستون‌ها
const allHeaders = ref<Header[]>([
  { title: "عملیات", key: "operation", align: 'center', sortable: false, width: 100, visible: true },
  { title: "شماره", key: "code", align: 'center', sortable: true, width: 100, visible: true },
  { title: "تاریخ", key: "date", align: 'center', sortable: true, width: 120, visible: true },
  { title: "شماره فاکتور", key: "doc.code", align: 'center', sortable: true, width: 120, visible: true },
  { title: "شخص", key: "person.nikename", align: 'center', sortable: true, width: 120, visible: true },
  { title: "توضیحات", key: "des", align: 'center', sortable: true, width: 200, visible: true },
]);

// ستون‌های قابل نمایش
const visibleHeaders = computed(() => {
  return allHeaders.value.filter((header: Header) => header.visible);
});

// بررسی نمایش ستون
const isColumnVisible = (key: string) => {
  return allHeaders.value.find((header: Header) => header.key === key)?.visible;
};

// کلید ذخیره‌سازی در localStorage
const LOCAL_STORAGE_KEY = 'hesabix_storeroom_tickets_table_columns';

// لود تنظیمات ستون‌ها
const loadColumnSettings = () => {
  const savedSettings = localStorage.getItem(LOCAL_STORAGE_KEY);
  if (savedSettings) {
    const visibleColumns = JSON.parse(savedSettings);
    allHeaders.value.forEach(header => {
      header.visible = visibleColumns.includes(header.key);
    });
  }
};

// ذخیره تنظیمات ستون‌ها
const updateColumnVisibility = () => {
  const visibleColumns = allHeaders.value
    .filter(header => header.visible)
    .map(header => header.key);
  localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(visibleColumns));
};

// تابع فرمت‌کننده اعداد
const formatNumber = (value: string | number) => {
  if (!value) return '0';
  return Number(value).toLocaleString('fa-IR');
};

// بارگذاری داده‌ها
const loadData = async () => {
  loading.value = true;
  try {
    const [inputResponse, outputResponse] = await Promise.all([
      axios.post('/api/storeroom/tickets/list/input'),
      axios.post('/api/storeroom/tickets/list/output')
    ]);
    inputItems.value = inputResponse.data;
    outputItems.value = outputResponse.data;
  } catch (error) {
    console.error('Error loading data:', error);
    snackbar.value = {
      show: true,
      message: 'خطا در بارگذاری داده‌ها: ' + error.message,
      color: 'error'
    };
  } finally {
    loading.value = false;
  }
};

// حذف حواله
const deleteTicket = (type: 'input' | 'output', code: string) => {
  deleteDialog.value = {
    show: true,
    type,
    code
  };
};

// تأیید حذف
const confirmDelete = async () => {
  if (!deleteDialog.value?.type || !deleteDialog.value?.code) return;

  const { type, code } = deleteDialog.value;
  deleteDialog.value.show = false;

  try {
    loading.value = true;
    await axios.post('/api/storeroom/ticket/remove/' + code);

    if (type === 'input') {
      inputItems.value = inputItems.value.filter(item => item.code !== code);
    } else {
      outputItems.value = outputItems.value.filter(item => item.code !== code);
    }

    snackbar.value = {
      show: true,
      message: 'حواله انبار حذف شد.',
      color: 'success'
    };
  } catch (error) {
    console.error('Error deleting ticket:', error);
    snackbar.value = {
      show: true,
      message: 'خطا در حذف حواله: ' + error.message,
      color: 'error'
    };
  } finally {
    loading.value = false;
    deleteDialog.value = {
      show: false,
      type: null,
      code: null
    };
  }
};

// مانت کامپوننت
onMounted(() => {
  loadColumnSettings();
  loadData();
});
</script>

<style scoped>
.v-data-table {
  direction: rtl;
  width: 100%;
  overflow-x: auto;
}

/* استایل برای وسط‌چین کردن همه سلول‌های جدول */
:deep(.v-data-table-header th) {
  text-align: center !important;
}

:deep(.v-data-table__wrapper table td) {
  text-align: center !important;
}
</style>