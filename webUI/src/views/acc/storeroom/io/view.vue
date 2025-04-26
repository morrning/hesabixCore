<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

interface Business {
  legal_name: string
}

interface Storeroom {
  manager: string
}

interface Ticket {
  id: number
  date: string | null
  code: string | null
  des: string
  type: string
  typeString: string
  storeroom: Storeroom
}

interface Person {
  nikename: string | null
  mobile: string
  address: string
  tel: string
  codeeqtesadi: string
  keshvar: string
  ostan: string
  shahr: string
  postalcode: string
}

interface Commodity {
  code: string
  name: string
  unit: {
    name: string
  }
}

interface Row {
  commodity: Commodity
  count: number
  hesabdariCount: number
  remain: number
  des: string
  referal: string
}

interface Item {
  ticket: Ticket
  rows: Row[]
  person: Person
  transferType: any
}

const router = useRouter()
const loading = ref(false)
const bid = ref<Business>({ legal_name: '' })
const item = ref<Item>({
  ticket: {
    id: 0,
    date: null,
    code: null,
    des: '',
    type: '',
    typeString: '',
    storeroom: {
      manager: ''
    }
  },
  rows: [],
  person: {
    nikename: null,
    mobile: '',
    address: '',
    tel: '',
    codeeqtesadi: '',
    keshvar: '',
    ostan: '',
    shahr: '',
    postalcode: ''
  },
  transferType: null
})

const headers = [
  { title: "", key: "data-table-expand" },
  { title: "کالا", key: "commodity" },
  { title: "تعداد", key: "count" },
  { title: "مورد نیاز", key: "hesabdariCount" },
  { title: "باقی‌مانده", key: "remain" },
]

const loadData = async () => {
  loading.value = true
  try {
    const [ticketResponse, businessResponse] = await Promise.all([
      axios.post(`/api/storeroom/tickets/info/${router.currentRoute.value.params.id}`),
      axios.post(`/api/business/get/info/${localStorage.getItem('activeBid')}`)
    ])

    item.value.ticket = ticketResponse.data.ticket
    item.value.person = ticketResponse.data.person
    item.value.transferType = ticketResponse.data.transferType
    item.value.rows = ticketResponse.data.commodities

    bid.value = businessResponse.data
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
}

const printInvoice = async () => {
  try {
    const response = await axios.post('/api/storeroom/print/ticket', {
      code: router.currentRoute.value.params.id,
      type: item.value.ticket.type
    })
    window.open(`${import.meta.env.VITE_API_URL}/front/print/${response.data.id}`, '_blank', 'noreferrer')
  } catch (error) {
    console.error('Error printing invoice:', error)
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <v-toolbar color="toolbar" title="مشاهده و چاپ حواله انبار">
    <template v-slot:prepend>
      <v-tooltip text="بازگشت" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />
    <v-tooltip text="چاپ" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn
          v-bind="props"
          @click="printInvoice"
          color="primary"
          icon="mdi-printer"
        />
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-container>
    <v-card variant="outlined" class="mb-4">
      <v-card-title class="text-subtitle-1 font-weight-bold">
        <v-icon start>mdi-information</v-icon>
        اطلاعات حواله انبار
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="2">
            <v-text-field
              v-model="item.ticket.code"
              label="شماره"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-text-field
              v-model="item.ticket.typeString"
              label="نوع"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
          <v-col cols="12" md="2">
            <v-text-field
              v-model="item.ticket.date"
              label="تاریخ"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="item.ticket.des"
              label="شرح"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-card variant="outlined" class="mb-4">
      <v-card-title class="text-subtitle-1 font-weight-bold">
        <v-icon start>mdi-account</v-icon>
        طرف حساب
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="item.person.nikename"
              label="نام"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="item.person.mobile"
              label="موبایل"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field
              v-model="item.person.tel"
              label="تلفن"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="item.person.postalcode"
              label="کد پستی"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
          <v-col cols="12" md="9">
            <v-text-field
              v-model="item.person.address"
              label="آدرس"
              variant="outlined"
              density="compact"
              readonly
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-card variant="outlined">
      <v-card-title class="text-subtitle-1 font-weight-bold">
        <v-icon start>mdi-package-variant</v-icon>
        اقلام
      </v-card-title>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="item.rows"
          :loading="loading"
           class="elevation-1 text-center"
    :header-props="{ class: 'custom-header' }"
          density="compact"
          show-expand
        >
          <template v-slot:item.count="{ item }">
            {{ item.count }} {{ item.commodity.unit.name }}
          </template>
          <template v-slot:item.commodity="{ item }">
            {{ item.commodity.code }} {{ item.commodity.name }}
          </template>
          <template v-slot:expanded-row="{ columns, item }">
            <tr>
              <td :colspan="columns.length">
                <div class="pa-2 text-right">
                  <div>شرح: {{ item.des }}</div>
                  <div>ارجاع: {{ item.referal }}</div>
                </div>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped>
@media print {
  @page {
    margin-top: 0;
    margin-bottom: 0;
  }
  body {
    padding-top: 72px;
    padding-bottom: 72px;
  }
}
</style>