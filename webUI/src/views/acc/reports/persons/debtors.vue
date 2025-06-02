<template>
  <div>
    <v-toolbar color="toolbar" title="گزارش بدهکاران">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
              icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" color="error" variant="text" icon="mdi-file-pdf-box"></v-btn>
        </template>
        <v-list>
          <v-list-item @click="print(false)" title="انتخاب شده‌ها">
            <template v-slot:prepend>
              <v-icon color="error" icon="mdi-file-pdf-box"></v-icon>
            </template>
          </v-list-item>
          <v-list-item @click="print(true)" title="همه موارد">
            <template v-slot:prepend>
              <v-icon color="error" icon="mdi-file-pdf-box-multiple"></v-icon>
            </template>
          </v-list-item>
        </v-list>
      </v-menu>

      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" color="success" variant="text" icon="mdi-file-excel-box"></v-btn>
        </template>
        <v-list>
          <v-list-item @click="exportExcel(false)" title="انتخاب شده‌ها">
            <template v-slot:prepend>
              <v-icon color="success" icon="mdi-file-excel-box"></v-icon>
            </template>
          </v-list-item>
          <v-list-item @click="exportExcel(true)" title="همه موارد">
            <template v-slot:prepend>
              <v-icon color="success" icon="mdi-file-excel-box-multiple"></v-icon>
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-toolbar>

    <v-text-field v-model="searchValue" prepend-inner-icon="mdi-magnify" density="compact" hide-details :rounded="false"
      placeholder="جست و جو ..."></v-text-field>

    <v-data-table :headers="headers" :items="filteredItems" :search="searchValue" :loading="loading"
      :header-props="{ class: 'custom-header' }" hover>
      <template v-slot:item.select="{ item }">
        <v-checkbox
          :model-value="selectedItems.has(item.code)"
          @update:model-value="toggleSelection(item.code)"
          hide-details
          density="compact"
        ></v-checkbox>
      </template>

      <template v-slot:item.nikename="{ item }">
        <router-link :to="'/acc/persons/card/view/' + item.code" class="text-decoration-none">
          {{ item.nikename }}
                    </router-link>
                  </template>

      <template v-slot:item.status="{ item }">
        <v-chip :color="item.balance < 0 ? 'error' : 'success'" size="small">
          {{ item.balance < 0 ? 'بدهکار' : 'بستانکار' }}
        </v-chip>
                  </template>

      <template v-slot:item.bs="{ item }">
        {{ $filters.formatNumber(item.bs) }}
                  </template>

      <template v-slot:item.bd="{ item }">
        {{ $filters.formatNumber(item.bd) }}
                  </template>

      <template v-slot:item.balance="{ item }">
        <span style="direction:ltr;text-align:left;">{{ $filters.formatNumber(item.balance) }}</span>
                  </template>
    </v-data-table>

    <v-card class="mt-4" variant="outlined">
      <v-card-text>
        <v-row>
          <v-col cols="12" sm="6">
            <div class="d-flex align-center">
              <v-icon color="primary" class="ml-2">mdi-cash-multiple</v-icon>
              <span class="text-subtitle-1">بدهی کل:</span>
              <span class="text-primary mr-2">
                {{ $filters.formatNumber(Math.abs(sumTotal)) }}
                            {{ $filters.getActiveMoney().shortName }}
                          </span>
                        </div>
          </v-col>
          <v-col cols="12" sm="6">
            <div class="d-flex align-center">
              <v-icon color="primary" class="ml-2">mdi-cash-check</v-icon>
              <span class="text-subtitle-1">جمع بدهی موارد انتخابی:</span>
              <span class="text-primary mr-2">
                {{ $filters.formatNumber(Math.abs(sumSelected)) }}
                            {{ $filters.getActiveMoney().shortName }}
                          </span>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

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
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import { getApiUrl } from '@/hesabixConfig'

const searchValue = ref('')
const loading = ref(true)
const items = ref([])
const selectedItems = ref(new Set())
const sumSelected = ref(0)
const sumTotal = ref(0)
const snackbar = ref({
  show: false,
  message: '',
  color: 'success'
})

const headers = [
  { title: '', key: 'select', sortable: false },
  { title: 'کد', key: 'code', sortable: true },
  { title: 'نام مستعار', key: 'nikename', sortable: true },
  { title: 'وضعیت حساب', key: 'status', sortable: true },
  { title: 'تراز حساب', key: 'balance', sortable: true },
  { title: 'نام و نام خانوادگی', key: 'name', sortable: true },
  { title: 'تاریخ تولد/ثبت', key: 'birthday', sortable: true },
  { title: 'شرکت', key: 'company', sortable: true },
  { title: 'شناسه ملی', key: 'shenasemeli', sortable: true },
  { title: 'کد اقتصادی', key: 'codeeghtesadi', sortable: true },
  { title: 'شماره ثبت', key: 'sabt', sortable: true },
  { title: 'کشور', key: 'keshvar', sortable: true },
  { title: 'استان', key: 'ostan', sortable: true },
  { title: 'شهر', key: 'shahr', sortable: true },
  { title: 'کد پستی', key: 'postalcode', sortable: true },
  { title: 'تلفن', key: 'tel' },
  { title: 'تلفن همراه', key: 'mobile' },
  { title: 'ایمیل', key: 'email', sortable: true },
  { title: 'وب سایت', key: 'website', sortable: true },
  { title: 'فکس', key: 'fax', sortable: true }
]

const filteredItems = computed(() => {
  if (!searchValue.value) return items.value

  const search = searchValue.value.toLowerCase()
  return items.value.filter(item => {
    return (
      item.code.toLowerCase().includes(search) ||
      item.nikename.toLowerCase().includes(search) ||
      item.name.toLowerCase().includes(search) ||
      item.company?.toLowerCase().includes(search) ||
      item.shenasemeli?.toLowerCase().includes(search) ||
      item.codeeghtesadi?.toLowerCase().includes(search)
    )
  })
})

const calculateSelectedSum = () => {
  let total = 0
  items.value.forEach(item => {
    if (selectedItems.value.has(item.code)) {
      const balance = Number(item.balance || 0)
      total += balance
    }
  })
  sumSelected.value = total
}

const toggleSelection = (code) => {
  if (selectedItems.value.has(code)) {
    selectedItems.value.delete(code)
  } else {
    selectedItems.value.add(code)
  }
  calculateSelectedSum()
}

const calculateTotalSum = () => {
  let total = 0
  items.value.forEach(item => {
    const balance = Number(item.balance || 0)
    total += balance
  })
  sumTotal.value = total
}

watch(selectedItems, () => {
  calculateSelectedSum()
}, { deep: true })

const loadData = async () => {
  try {
    const response = await axios.post('/api/person/list/debtors/0')
    items.value = response.data
    calculateTotalSum()
    loading.value = false
  } catch (error) {
    loading.value = false
    snackbar.value = {
      show: true,
      message: 'خطا در بارگذاری اطلاعات',
      color: 'error'
    }
  }
}

const print = async (allItems = true) => {
  if (!allItems && selectedItems.value.size === 0) {
    Swal.fire({
      text: 'هیچ آیتمی انتخاب نشده است.',
      icon: 'info',
      confirmButtonText: 'قبول'
    })
    return
  }

  try {
    loading.value = true
    const payload = allItems ? { all: true } : { items: Array.from(selectedItems.value) }
    const response = await axios.post('/api/person/list/debtors/print/0', payload)
    
    if (response.data && response.data.id) {
      const pdfResponse = await axios({
        method: 'get',
        url: '/front/print/' + response.data.id,
        responseType: 'arraybuffer'
      })
      
      const fileURL = window.URL.createObjectURL(new Blob([pdfResponse.data]))
      const fileLink = document.createElement('a')
      fileLink.href = fileURL
      fileLink.setAttribute('download', `گزارش_بدهکاران_${new Date().toLocaleDateString('fa-IR')}.pdf`)
      document.body.appendChild(fileLink)
      fileLink.click()
      document.body.removeChild(fileLink)
      window.URL.revokeObjectURL(fileURL)

      snackbar.value = {
        show: true,
        message: 'گزارش با موفقیت دانلود شد',
        color: 'success'
      }
    } else {
      throw new Error('خطا در دریافت شناسه چاپ')
    }
  } catch (error) {
    snackbar.value = {
      show: true,
      message: 'خطا در دانلود گزارش',
      color: 'error'
    }
  } finally {
    loading.value = false
  }
}

const exportExcel = async (allItems = true) => {
  if (!allItems && selectedItems.value.size === 0) {
    Swal.fire({
      text: 'هیچ آیتمی انتخاب نشده است.',
      icon: 'info',
      confirmButtonText: 'قبول'
    })
    return
  }

  try {
    loading.value = true
    const payload = allItems ? { all: true } : { items: Array.from(selectedItems.value) }
    const response = await axios.post('/api/person/list/debtors/excel/0', payload, { 
      responseType: 'blob' 
    })
    
    const fileURL = window.URL.createObjectURL(new Blob([response.data], { 
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    }))
    const fileLink = document.createElement('a')
    fileLink.href = fileURL
    fileLink.setAttribute('download', `گزارش_بدهکاران_${new Date().toLocaleDateString('fa-IR')}.xlsx`)
    document.body.appendChild(fileLink)
    fileLink.click()
    document.body.removeChild(fileLink)
    window.URL.revokeObjectURL(fileURL)

    snackbar.value = {
      show: true,
      message: 'گزارش اکسل با موفقیت دانلود شد',
      color: 'success'
    }
  } catch (error) {
    snackbar.value = {
      show: true,
      message: 'خطا در دانلود گزارش اکسل',
      color: 'error'
    }
  } finally {
    loading.value = false
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