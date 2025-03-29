<template>
  <v-toolbar color="toolbar" :title="$t('drawer.banks_accounts')">
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
            <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/banks/mod/" />
          </template>
        </v-tooltip>
      </v-slide-group-item>

      <v-slide-group-item>
        <v-tooltip :text="$t('dialog.column_settings')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-table-cog" color="primary" @click="showColumnDialog = true" />
          </template>
        </v-tooltip>
      </v-slide-group-item>
    </v-slide-group>
  </v-toolbar>

  <v-text-field
    v-model="search"
    :loading="loading"
    color="green"
    class="mb-0 pt-0 rounded-0"
    hide-details="auto"
    density="compact"
    :placeholder="$t('dialog.search_txt')"
    clearable
  >
    <template v-slot:prepend-inner>
      <v-tooltip location="bottom" :text="$t('dialog.search')">
        <template v-slot:activator="{ props }">
          <v-icon v-bind="props" color="danger" icon="mdi-magnify" />
        </template>
      </v-tooltip>
    </template>
  </v-text-field>

  <v-data-table
    :headers="visibleHeaders"
    :items="items"
    :loading="loading"
    :search="search"
    class="elevation-1 text-center"
    :header-props="{ class: 'custom-header' }"

  >
    <template v-slot:item="{ item }">
      <tr>
        <td v-if="isColumnVisible('operation')" class="text-center">
          <v-menu>
            <template v-slot:activator="{ props }">
              <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
            </template>
            <v-list>
              <v-list-item :to="'/acc/banks/card/view/' + item.code">
                <template v-slot:prepend>
                  <v-icon color="success" icon="mdi-eye" />
                </template>
                <v-list-item-title>{{ $t('dialog.view') }}</v-list-item-title>
              </v-list-item>
              
              <v-list-item :to="'/acc/banks/mod/' + item.code">
                <template v-slot:prepend>
                  <v-icon icon="mdi-pencil" />
                </template>
                <v-list-item-title>{{ $t('dialog.edit') }}</v-list-item-title>
              </v-list-item>
              
              <v-list-item @click="confirmDelete(item.code)">
                <template v-slot:prepend>
                  <v-icon color="error" icon="mdi-delete" />
            </template>
                <v-list-item-title>{{ $t('dialog.delete') }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </td>
        <td v-if="isColumnVisible('code')" class="text-center">{{ formatNumber(item.code) }}</td>
        <td v-if="isColumnVisible('name')" class="text-center">
          <router-link :to="'/acc/banks/card/view/' + item.code">
            {{ item.name }}
              </router-link>
        </td>
        <td v-if="isColumnVisible('balance')" class="text-center">
          <span :class="Number(item.balance) >= 0 ? 'text-success' : 'text-error'">
            {{ formatNumber(Math.abs(Number(item.balance))) }}
            <span v-if="Number(item.balance) < 0">منفی</span>
          </span>
        </td>
        <td v-if="isColumnVisible('owner')" class="text-center">{{ item.owner }}</td>
        <td v-if="isColumnVisible('cardNum')" class="text-center">{{ formatCardNumber(item.cardNum) }}</td>
        <td v-if="isColumnVisible('shaba')" class="text-center">{{ formatShabaNumber(item.shaba) }}</td>
        <td v-if="isColumnVisible('shobe')" class="text-center">{{ item.shobe }}</td>
        <td v-if="isColumnVisible('mobileInternetBank')" class="text-center">{{ item.mobileInternetBank }}</td>
        <td v-if="isColumnVisible('posNum')" class="text-center">{{ item.posNum }}</td>
      </tr>
            </template>
  </v-data-table>

  <v-dialog v-model="showColumnDialog" max-width="500">
    <v-card>
      <v-toolbar color="toolbar" :title="$t('dialog.manage_columns')">
        <v-spacer></v-spacer>
        <v-btn icon @click="showColumnDialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col v-for="header in allHeaders" :key="header.key" cols="12" sm="6">
            <v-checkbox
              v-model="header.visible"
              :label="header.title"
              @change="updateColumnVisibility"
              hide-details
            />
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
        آیا برای حذف حساب بانکی مطمئن هستید؟
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" variant="text" @click="deleteDialog.show = false">خیر</v-btn>
        <v-btn color="error" variant="text" @click="deleteItem">بله</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-dialog v-model="messageDialog.show" max-width="400">
    <v-card>
      <v-card-title :class="messageDialog.color + ' text-h6'">
        {{ messageDialog.title }}
      </v-card-title>
      <v-card-text class="pt-4">
        {{ messageDialog.message }}
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" variant="text" @click="messageDialog.show = false">قبول</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Refs
const loading = ref(false);
const items = ref([]);
const search = ref('');
const showColumnDialog = ref(false);

// دیالوگ‌ها
const deleteDialog = ref({
  show: false,
  code: null
});

const messageDialog = ref({
  show: false,
  title: '',
  message: '',
  color: 'primary'
});

// تابع فرمت‌کننده اعداد
const formatNumber = (value) => {
  if (!value) return '0';
  return Number(value).toLocaleString('fa-IR');
};

// فرمت‌کننده شماره کارت
const formatCardNumber = (value) => {
  if (!value) return '';
  return value.replace(/(\d{4})(?=\d)/g, '$1-');
};

// فرمت‌کننده شماره شبا
const formatShabaNumber = (value) => {
  if (!value) return '';
  return value.replace(/(.{4})/g, '$1 ').trim();
};

// تعریف همه ستون‌ها با align مرکز
const allHeaders = ref([
  { title: "عملیات", key: "operation", align: 'center', sortable: false, width: 100, visible: true },
  { title: "کد", key: "code", align: 'center', sortable: true, width: 100, visible: true },
  { title: "بانک", key: "name", align: 'center', sortable: true, width: 140, visible: true },
  { title: "موجودی", key: "balance", align: 'center', sortable: true, width: 140, visible: true },
  { title: "صاحب حساب", key: "owner", align: 'center', sortable: true, width: 120, visible: true },
  { title: "شماره کارت", key: "cardNum", align: 'center', sortable: true, width: 120, visible: true },
  { title: "شبا", key: "shaba", align: 'center', sortable: true, width: 160, visible: true },
  { title: "شعبه", key: "shobe", align: 'center', sortable: true, width: 120, visible: true },
  { title: "تلفن اینترنت بانک", key: "mobileInternetBank", align: 'center', sortable: true, width: 120, visible: true },
  { title: "شماره کارتخوان", key: "posNum", align: 'center', sortable: true, width: 100, visible: true },
]);

// ستون‌های قابل نمایش
const visibleHeaders = computed(() => {
  return allHeaders.value.filter(header => header.visible);
});

// بررسی نمایش ستون
const isColumnVisible = (key) => {
  return allHeaders.value.find(header => header.key === key)?.visible;
};

// کلید ذخیره‌سازی در localStorage
const LOCAL_STORAGE_KEY = 'hesabix_bank_table_columns';

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

// نمایش پیام
const showMessage = (message, title = 'پیام', color = 'primary') => {
  messageDialog.value = {
    show: true,
    title,
    message,
    color
  };
};

// تأیید حذف
const confirmDelete = (code) => {
  deleteDialog.value = {
    show: true,
    code
  };
};

// بارگذاری داده‌ها
const loadData = async () => {
  loading.value = true;
  try {
    const response = await axios.post('/api/bank/list');
    items.value = response.data;
  } catch (error) {
    console.error('Error loading data:', error);
    showMessage('خطا در بارگذاری داده‌ها: ' + error.message, 'خطا', 'error');
  } finally {
    loading.value = false;
  }
};

// حذف آیتم
const deleteItem = async () => {
  const code = deleteDialog.value.code;
  deleteDialog.value.show = false;

  if (!code) return;

  try {
    loading.value = true;
    const response = await axios.post(`/api/bank/delete/${code}`);
    
    if (response.data.result === 1) {
      items.value = items.value.filter(item => item.code !== code);
      showMessage('بانک با موفقیت حذف شد.', 'موفقیت', 'success');
    } else if (response.data.result === 2) {
      showMessage('بانک به دلیل داشتن تراکنش و اسناد حسابداری مرتبط قابل حذف نیست.', 'خطا', 'error');
    } else if (response.data.result === 3) {
      showMessage('بانک به دلیل انتخاب به عنوان تسویه کیف پول قابل حذف نیست.', 'خطا', 'error');
    }
  } catch (error) {
    console.error('Error deleting item:', error);
    showMessage('خطا در حذف آیتم: ' + error.message, 'خطا', 'error');
  } finally {
    loading.value = false;
  }
};

// مانت کامپوننت
onMounted(() => {
  loadColumnSettings();
  loadData();
});
</script>

<style>
.v-data-table {
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

/* استایل برای رنگ‌های متن */
.text-success {
  color: #4caf50 !important;
}

.text-error {
  color: #ff5252 !important;
}

/* استایل برای لینک‌ها در جدول */
:deep(.v-data-table__wrapper table td a) {
  text-decoration: none;
  color: #1976d2;
}

:deep(.v-data-table__wrapper table td a:hover) {
  text-decoration: underline;
}
</style>