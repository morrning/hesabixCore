<template>
  <div>
    <v-toolbar
      color="toolbar"
      title="اسناد حسابداری"
    >
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    </v-toolbar>

    <v-text-field
      v-model="searchValue"
      prepend-inner-icon="mdi-magnify"
      density="compact"
      hide-details
      :rounded="false"
      placeholder="جست و جو ..."
    ></v-text-field>

    <v-data-table
      :headers="headers"
      :items="filteredItems"
      :search="searchValue"
      :loading="loading"
      :header-props="{ class: 'custom-header' }"
      hover
    >
      <template v-slot:item.state="{ item }">
        <v-icon
          :color="item.type !== 'accounting' ? 'error' : 'success'"
        >
          {{ item.type !== 'accounting' ? 'mdi-lock' : 'mdi-lock-open' }}
        </v-icon>
      </template>

      <template v-slot:item.operation="{ item }">
        <v-tooltip text="مشاهده سند" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn
              v-bind="props"
              icon
              variant="text"
              color="success"
              :to="'/acc/accounting/view/' + item.code"
            >
              <v-icon>mdi-eye</v-icon>
            </v-btn>
          </template>
        </v-tooltip>
      </template>
    </v-data-table>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const searchValue = ref('')
const loading = ref(true)
const items = ref([])

const headers = [
  { title: 'وضعیت', key: 'state', sortable: true },
  { title: 'عملیات', key: 'operation' },
  { title: 'کد', key: 'code', sortable: true },
  { title: 'تاریخ', key: 'date', sortable: true },
  { title: 'شرح', key: 'des', sortable: true },
  { title: 'مبلغ', key: 'amount', sortable: true },
  { title: 'ثبت کننده', key: 'submitter', sortable: true }
]

const loadData = async () => {
  try {
    const response = await axios.post('/api/accounting/search', {
      type: 'all'
    })
    items.value = response.data.map(item => ({
      ...item,
      amount: item.amount.toLocaleString(),
      amountRaw: item.amount
    }))
    loading.value = false
  } catch (error) {
    console.error('Error loading data:', error)
    loading.value = false
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

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.v-data-table {
  direction: rtl;
}
</style>