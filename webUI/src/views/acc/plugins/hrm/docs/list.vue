<template>
  <div>
    <v-toolbar color="toolbar" title="لیست حقوق">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
              icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-tooltip text="افزودن سند حقوق" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-plus" variant="text" color="success" :to="'/acc/hrm/docs/mod/'"></v-btn>
        </template>
      </v-tooltip>
    </v-toolbar>

    <v-text-field v-model="searchValue" prepend-inner-icon="mdi-magnify" density="compact" hide-details :rounded="false"
      placeholder="جست و جو ...">
    </v-text-field>

    <v-data-table :headers="headers" :items="filteredItems" :search="searchValue" :loading="loading"
      :header-props="{ class: 'custom-header' }" hover>
      <template v-slot:item.actions="{ item }">
        <v-tooltip text="مشاهده سند" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon variant="text" color="success" :to="'/acc/hrm/docs/view/' + item.id">
              <v-icon>mdi-eye</v-icon>
            </v-btn>
          </template>
        </v-tooltip>
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
          </template>
          <v-list>
            <v-list-item :title="$t('dialog.view')" :to="'/acc/hrm/docs/view/' + item.id">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
              </template>
            </v-list-item>
            <v-list-item :title="$t('dialog.edit')" :to="'/acc/hrm/docs/mod/' + item.id">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-edit"></v-icon>
              </template>
            </v-list-item>
            <v-list-item title="صدور سند حسابداری" :to="'/acc/hrm/docs/accounting/' + item.id">
              <template v-slot:prepend>
                <v-icon color="primary" icon="mdi-file-document-outline"></v-icon>
              </template>
            </v-list-item>
            <v-list-item :title="$t('dialog.delete')" @click="openDeleteDialog(item)">
              <template v-slot:prepend>
                <v-icon color="deep-orange-accent-4" icon="mdi-trash-can"></v-icon>
              </template>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <!-- دیالوگ تأیید حذف -->
    <v-dialog v-model="deleteDialog" max-width="500">
      <v-card class="rounded-lg">
        <v-card-title class="d-flex align-center pa-4">
          <v-icon color="error" size="large" class="ml-2">mdi-alert-circle-outline</v-icon>
          <span class="text-h5 font-weight-bold">حذف سند حقوق</span>
        </v-card-title>
        
        <v-divider></v-divider>

        <v-card-text class="pa-4">
          <div class="d-flex flex-column">
            <div class="text-subtitle-1 mb-2">آیا مطمئن هستید که می‌خواهید سند زیر را حذف کنید؟</div>
            
            <v-card variant="outlined" class="mt-2">
              <v-card-text>
                <div class="d-flex justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">کد سند:</span>
                  <span>{{ selectedItem?.id?.toLocaleString() }}</span>
                </div>
                <div class="d-flex justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">تاریخ:</span>
                  <span>{{ selectedItem?.date }}</span>
                </div>
                <div class="d-flex justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">کارمند:</span>
                  <span>{{ selectedItem?.employee }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-subtitle-2 font-weight-bold">مبلغ:</span>
                  <span>{{ selectedItem?.amountRaw?.toLocaleString() }}</span>
                </div>
              </v-card-text>
            </v-card>
          </div>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            color="grey-darken-1"
            variant="text"
            @click="deleteDialog = false"
            :disabled="deleteLoading"
          >
            انصراف
          </v-btn>
          <v-btn
            color="error"
            variant="tonal"
            @click="confirmDelete"
            :loading="deleteLoading"
          >
            <template v-slot:prepend>
              <v-icon>mdi-delete</v-icon>
            </template>
            حذف سند
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- اسنک‌بار برای نمایش پیام -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false">
          بستن
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import moment from 'jalali-moment'

const searchValue = ref('')
const loading = ref(true)
const items = ref([])
const deleteDialog = ref(false)
const deleteLoading = ref(false)
const selectedItem = ref(null)
const snackbar = ref({
  show: false,
  message: '',
  color: 'success'
})

const headers = [
  { title: 'عملیات', key: 'actions' },
  { title: 'تاریخ', key: 'date', sortable: true },
  { title: 'ایجاد کننده', key: 'employee', sortable: true },
  { title: 'مبلغ', key: 'amount', sortable: true },
  { title: 'سند حسابداری', key: 'accounting_doc', sortable: true },
  { title: 'توضیحات', key: 'description', sortable: true },
]

const loadData = async () => {
  try {
    loading.value = true;
    const response = await axios.post('/api/hrm/docs/list', {
      search: searchValue.value
    });
    
    if (response.data.success) {
      items.value = response.data.data.map(item => ({
        ...item,
        amount: item.total ? item.total.toLocaleString('fa-IR') : '0',
        amountRaw: item.total || 0,
        employee: item.creator?.name || 'نامشخص',
        status: item.status
      }));
    } else {
      snackbar.value = {
        show: true,
        message: 'خطا در بارگذاری داده‌ها',
        color: 'error'
      };
    }
  } catch (error) {
    console.error('Error loading data:', error);
    snackbar.value = {
      show: true,
      message: 'خطا در بارگذاری داده‌ها',
      color: 'error'
    };
  } finally {
    loading.value = false;
  }
};

const filteredItems = computed(() => {
  return items.value;
});

const openDeleteDialog = (item) => {
  selectedItem.value = item
  deleteDialog.value = true
}

const confirmDelete = async () => {
  try {
    deleteLoading.value = true
    const response = await axios.post('/api/hrm/docs/delete',{id:selectedItem.value.id})
    if (response.data.success) {
      const index = items.value.findIndex(item => item.id === selectedItem.value.id)
      if (index !== -1) {
        items.value.splice(index, 1)
      }
      deleteDialog.value = false
      snackbar.value = {
        show: true,
        message: 'سند با موفقیت حذف شد',
        color: 'success'
      }
    } else {
      snackbar.value = {
        show: true,
        message: response.data.message || 'خطا در حذف سند',
        color: 'error'
      }
    }
  } catch (error) {
    snackbar.value = {
      show: true,
      message: error.response?.data?.message || 'خطا در ارتباط با سرور',
      color: 'error'
    }
  } finally {
    deleteLoading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.v-data-table {
  direction: rtl;
}
</style> 