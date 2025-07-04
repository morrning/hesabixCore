<template>
  <div>
    <v-toolbar color="toolbar" title="اسناد حسابداری">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
              icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-tooltip v-if="isPluginActive('accpro')" text="افزودن سند حسابداری" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-plus" variant="text" color="success" :to="'/acc/accounting/mod'"></v-btn>
        </template>
      </v-tooltip>
    </v-toolbar>

    <v-text-field v-model="searchValue" prepend-inner-icon="mdi-magnify" density="compact" hide-details :rounded="false"
      placeholder="جست و جو ...">
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
                فیلتر حساب:
                <v-btn
                  v-if="selectedAccountId"
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

    <v-data-table :headers="headers" :items="filteredItems" :search="searchValue" :loading="loading"
      :header-props="{ class: 'custom-header' }" hover>
      <template v-slot:item.state="{ item }">
        <v-icon :color="item.type !== 'calc' ? 'error' : 'success'">
          {{ item.type !== 'calc' ? 'mdi-lock' : 'mdi-lock-open' }}
        </v-icon>
      </template>

      <template v-slot:item.operation="{ item }">
        <v-tooltip v-if="!isPluginActive('accpro') || item.type !== 'calc'" text="مشاهده سند" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon variant="text" color="success" :to="'/acc/accounting/view/' + item.code">
              <v-icon>mdi-eye</v-icon>
            </v-btn>
          </template>
        </v-tooltip>
        <v-menu v-else-if="item.type === 'calc' && isPluginActive('accpro')">
          <template v-slot:activator="{ props }">
            <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
          </template>
          <v-list>
            <v-list-item :title="$t('dialog.view')" :to="'/acc/accounting/view/' + item.code">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
              </template>
            </v-list-item>
            <v-list-item :title="$t('dialog.edit')" :to="'/acc/accounting/mod/' + item.id">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-edit"></v-icon>
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
          <span class="text-h5 font-weight-bold">حذف سند حسابداری</span>
        </v-card-title>
        
        <v-divider></v-divider>

        <v-card-text class="pa-4">
          <div class="d-flex flex-column">
            <div class="text-subtitle-1 mb-2">آیا مطمئن هستید که می‌خواهید سند زیر را حذف کنید؟</div>
            
            <v-card variant="outlined" class="mt-2">
              <v-card-text>
                <div class="d-flex justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">کد سند:</span>
                  <span>{{ selectedItem?.code?.toLocaleString() }}</span>
                </div>
                <div class="d-flex justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">تاریخ:</span>
                  <span>{{ selectedItem?.date }}</span>
                </div>
                <div class="d-flex justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">شرح:</span>
                  <span>{{ selectedItem?.des }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-subtitle-2 font-weight-bold">مبلغ:</span>
                  <span>{{ selectedItem?.amountRaw?.toLocaleString() }}</span>
                </div>
              </v-card-text>
            </v-card>

            <v-alert
              v-if="selectedItem?.type !== 'calc'"
              type="warning"
              variant="tonal"
              class="mt-4"
            >
              <template v-slot:prepend>
                <v-icon color="warning">mdi-alert</v-icon>
              </template>
              این سند قفل شده است و امکان حذف آن وجود ندارد.
            </v-alert>
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
            :disabled="selectedItem?.type !== 'calc'"
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
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import moment from 'jalali-moment'
import HesabdariTreeView from '@/components/forms/HesabdariTreeView.vue'
import Hdatepicker from '@/components/forms/Hdatepicker.vue'

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
const plugins = ref({})

// فیلترهای زمانی
const timeFilters = ref([
  { label: 'امروز', value: 'today', checked: false },
  { label: 'این هفته', value: 'week', checked: false },
  { label: 'این ماه', value: 'month', checked: false },
  { label: 'بازه زمانی', value: 'custom', checked: false },
  { label: 'همه', value: 'all', checked: true },
])

const timeFilter = ref('all')
const selectedAccountId = ref(null)
const dateRange = ref({
  from: moment().locale('fa').subtract(1, 'days').format('YYYY/MM/DD'),
  to: moment().locale('fa').format('YYYY/MM/DD')
})

const headers = [
  { title: 'وضعیت', key: 'state', sortable: true },
  { title: 'عملیات', key: 'operation' },
  { title: 'کد', key: 'code', sortable: true },
  { title: 'تاریخ', key: 'date', sortable: true },
  { title: 'شرح', key: 'des', sortable: true },
  { title: 'مبلغ', key: 'amount', sortable: true },
  { title: 'ثبت کننده', key: 'submitter', sortable: true }
]

const isPluginActive = (plugName) => {
  return plugins.value[plugName] !== undefined
}

// متدهای مدیریت فیلتر حساب
const handleAccountSelect = (account) => {
  if (account) {
    selectedAccountId.value = account.code
    loadData()
  }
}

const handleAccountSelected = (account) => {
  if (account) {
    loadData()
  }
}

// متد ریست کردن فیلتر حساب
const resetAccountFilter = () => {
  selectedAccountId.value = null
  loadData()
}

// متدهای مدیریت فیلتر زمانی
const handleDateRangeChange = () => {
  if (dateRange.value.from && dateRange.value.to) {
    const fromDate = moment(dateRange.value.from, 'jYYYY/jMM/jDD').locale('fa')
    const toDate = moment(dateRange.value.to, 'jYYYY/jMM/jDD').locale('fa')

    if (fromDate.isAfter(toDate)) {
      snackbar.value = {
        show: true,
        message: 'تاریخ شروع نمی‌تواند بعد از تاریخ پایان باشد',
        color: 'error'
      }
      dateRange.value = {
        from: null,
        to: null
      }
      return
    }

    loadData()
  }
}

const handleCustomDateFilterChange = (checked) => {
  if (checked) {
    timeFilters.value.forEach(filter => {
      if (filter.value !== 'custom') {
        filter.checked = false
      }
    })
    timeFilter.value = 'custom'
    
    dateRange.value = {
      from: moment().locale('fa').subtract(1, 'days').format('YYYY/MM/DD'),
      to: moment().locale('fa').format('YYYY/MM/DD')
    }
    
    loadData()
  } else {
    timeFilters.value.forEach(filter => {
      filter.checked = filter.value === 'all'
    })
    timeFilter.value = 'all'
    dateRange.value = {
      from: null,
      to: null
    }
    loadData()
  }
}

const applyTimeFilter = (value) => {
  timeFilters.value.forEach((filter) => {
    filter.checked = filter.value === value
  })
  timeFilter.value = value
  
  if (value !== 'custom') {
    dateRange.value = {
      from: null,
      to: null
    }
  }
  
  loadData()
}

const loadData = async () => {
  try {
    loading.value = true

    const filters = {}
    if (searchValue.value.trim()) {
      filters.search = { value: searchValue.value.trim() }
    }
    if (timeFilter.value) {
      filters.timeFilter = timeFilter.value

      if (timeFilter.value === 'custom' && dateRange.value.from && dateRange.value.to) {
        const fromDate = moment(dateRange.value.from, 'jYYYY/jMM/jDD').locale('fa').format('YYYY/MM/DD')
        const toDate = moment(dateRange.value.to, 'jYYYY/jMM/jDD').locale('fa').format('YYYY/MM/DD')
        
        filters.date = {
          from: fromDate,
          to: toDate
        }
      } else {
        const today = moment().locale('fa').format('YYYY/MM/DD')
        switch (timeFilter.value) {
          case 'today':
            filters.date = {
              from: today,
              to: today
            }
            break
          case 'week':
            filters.date = {
              from: moment().locale('fa').subtract(6, 'days').format('YYYY/MM/DD'),
              to: today
            }
            break
          case 'month':
            filters.date = {
              from: moment().locale('fa').startOf('jMonth').format('YYYY/MM/DD'),
              to: today
            }
            break
        }
      }
    }
    
    if (selectedAccountId.value) {
      filters.account = selectedAccountId.value
    }

    const response = await axios.post('/api/accounting/search', {
      type: 'all',
      filters,
      pagination: {
        page: 1,
        limit: 100
      },
      sort: {
        sortBy: 'id',
        sortDesc: true
      }
    })

    items.value = response.data.items.map(item => ({
      ...item,
      amount: item.amount.toLocaleString(),
      amountRaw: item.amount
    }))
    loading.value = false
  } catch (error) {
    console.error('Error loading data:', error)
    loading.value = false
    snackbar.value = {
      show: true,
      message: 'خطا در بارگذاری داده‌ها: ' + (error.response?.data?.detail || error.message),
      color: 'error'
    }
  }
}

const loadPlugins = async () => {
  try {
    const response = await axios.post('/api/plugin/get/actives')
    plugins.value = response.data
  } catch (error) {
    console.error('Error loading plugins:', error)
  }
}

const filteredItems = computed(() => {
  if (!searchValue.value) return items.value

  const search = searchValue.value.toLowerCase()
  const searchWithoutComma = search.replace(/,/g, '')
  const searchNumber = parseInt(searchWithoutComma)
  const isNumberSearch = !isNaN(searchNumber)

  return items.value.filter(item => {
    const formattedAmount = item.amount.toLocaleString()

    return (
      item.code.toLowerCase().includes(search) ||
      item.date.toLowerCase().includes(search) ||
      item.des.toLowerCase().includes(search) ||
      item.submitter.toLowerCase().includes(search) ||
      (isNumberSearch && (
        item.amount.toString().includes(searchWithoutComma) ||
        formattedAmount.includes(search)
      ))
    )
  })
})

const openDeleteDialog = (item) => {
  selectedItem.value = item
  deleteDialog.value = true
}

const confirmDelete = async () => {
  try {
    deleteLoading.value = true
    const response = await axios.delete(`/api/hesabdari/direct/doc/delete/${selectedItem.value.id}`)
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

// اضافه کردن watch برای تغییرات تاریخ‌ها
watch([() => dateRange.value.from, () => dateRange.value.to], () => {
  if (timeFilter.value === 'custom') {
    handleDateRangeChange()
  }
}, { deep: true })

onMounted(() => {
  loadData()
  loadPlugins()
})
</script>

<style scoped>
.v-data-table {
  direction: rtl;
}
</style>