<template>
  <v-toolbar color="toolbar" title="حواله خروج از انبار">
    <template v-slot:prepend>
      <v-tooltip text="بازگشت" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />
    <v-tooltip text="تکمیل خودکار" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn
          v-bind="props"
          @click="autofill"
          variant="text"
          icon="mdi-auto-fix"
          class="mx-2"
        />
      </template>
    </v-tooltip>
    <v-tooltip text="ثبت حواله خروج" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn
          v-bind="props"
          :loading="loading"
          :disabled="loading"
          color="success"
          icon="mdi-content-save"
          @click="submit"
        />
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-container>
    <v-row>
      <v-col cols="12" md="4">
        <v-text-field
          v-model="ticket.date"
          label="تاریخ"
          variant="outlined"
          density="compact"
          readonly
        />
      </v-col>
      <v-col cols="12" md="4">
        <v-text-field
          v-model="ticket.store.des"
          label="انبار"
          variant="outlined"
          density="compact"
          readonly
        />
      </v-col>
      <v-col cols="12" md="4">
        <v-text-field
          v-model="ticket.person.des"
          label="خریدار"
          variant="outlined"
          density="compact"
          readonly
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-text-field
          v-model="ticket.des"
          label="شرح"
          variant="outlined"
          density="compact"
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="3">
        <v-text-field
          v-model="ticket.transfer"
          label="حمل‌و‌نقل"
          variant="outlined"
          density="compact"
        />
      </v-col>
      <v-col cols="12" md="3">
        <v-text-field
          v-model="ticket.receiver"
          label="تحویل"
          variant="outlined"
          density="compact"
        />
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          v-model="ticket.transferType"
          :items="transferTypes"
          item-title="name"
          item-value="id"
          label="روش تحویل"
          variant="outlined"
          density="compact"
        />
      </v-col>
      <v-col cols="12" md="3">
        <v-text-field
          v-model="ticket.referral"
          label="شماره پیگیری"
          variant="outlined"
          density="compact"
        />
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-data-table
          :headers="headers"
          :items="items"
          :loading="loading"
          class="elevation-1 text-center"
          :header-props="{ class: 'custom-header' }"
          density="compact"
        >
          <template v-slot:item.commdityCount="{ item, index }">
            <v-text-field
              v-model="items[index].ticketCount"
              type="number"
              variant="outlined"
              density="compact"
              :min="0"
              :max="item.remain"
              @blur="(event) => { if (items[index].ticketCount === '') { items[index].ticketCount = 0 } }"
              @keypress="isNumber($event)"
            />
          </template>
          <template v-slot:item.des="{ item, index }">
            <v-text-field
              v-model="items[index].des"
              variant="outlined"
              density="compact"
            />
          </template>
          <template v-slot:item.referal="{ item, index }">
            <v-text-field
              v-model="items[index].referral"
              variant="outlined"
              density="compact"
            />
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </v-container>

  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    :timeout="3000"
    location="bottom"
  >
    {{ snackbar.message }}
    <template v-slot:actions>
      <v-btn
        variant="text"
        @click="snackbar.show = false"
      >
        بستن
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

interface TransferType {
  id: number;
  name: string;
}

interface Person {
  des: string;
  mobile?: string;
}

interface Store {
  des: string;
  name: string;
  manager: string;
}

interface Commodity {
  code: string;
  name: string;
  unit: string;
  commdityCount: number;
  docCount: number;
  countBefore: number;
  remain: number;
  ticketCount: number;
  des: string;
  referral: string;
  type: string;
}

interface Ticket {
  type: string;
  typeString: string;
  date: string;
  des: string;
  transfer: string;
  receiver: string;
  code: string;
  store: Store;
  person: Person;
  transferType: TransferType;
  referral: string;
  sms?: boolean;
}

interface Year {
  start: string;
  end: string;
  now: string;
}

const router = useRouter()
const loading = ref(false)

// Refs
const doc = ref({})
const ticket = ref<Ticket>({
  type: 'output',
  typeString: 'حواله خروج',
  date: '',
  des: '',
  transfer: '',
  receiver: '',
  code: '',
  store: {} as Store,
  person: {} as Person,
  transferType: {} as TransferType,
  referral: '',
  sms: false
})

const transferTypes = ref<TransferType[]>([])
const year = ref<Year>({} as Year)
const items = ref<Commodity[]>([])

const headers = [
  { title: "کد", key: "commodity.code" },
  { title: "کالا", key: "commodity.name", sortable: true },
  { title: "واحد", key: "commodity.unit", sortable: true },
  { title: "مورد نیاز", key: "docCount" },
  { title: "از قبل", key: "countBefore" },
  { title: "باقی‌مانده", key: "remain" },
  { title: "تعداد", key: "commdityCount", sortable: true },
  { title: "ارجاع", key: "referal", sortable: true },
  { title: "توضیحات", key: "des" },
]

const snackbar = ref({
  show: false,
  message: '',
  color: 'primary' as 'primary' | 'error' | 'success' | 'warning'
})

// Methods
const submit = async () => {
  loading.value = true
  try {
    const errors: string[] = []
    let rowsWithZeroCount = 0
    let totalCount = 0

    items.value.forEach((element, index) => {
      if (element.ticketCount === 0) {
        rowsWithZeroCount++
      } else if (element.ticketCount === undefined || element.ticketCount === null) {
        errors.push(`تعداد کالا در ردیف ${index + 1} وارد نشده است.`)
      } else {
        totalCount += element.ticketCount
      }
    })

    if (totalCount === 0) {
      errors.push('تعداد تمام کالاها صفر است!')
    }

    if (errors.length !== 0) {
      snackbar.value = {
        show: true,
        message: errors.join('\n'),
        color: 'error'
      }
      return
    }

    const response = await axios.post('/api/storeroom/ticket/insert', {
      doc: doc.value,
      ticket: {
        ...ticket.value,
        senderTel: ticket.value.person.mobile || ''
      },
      items: items.value
    })

    if (response.data.result === 0) {
      snackbar.value = {
        show: true,
        message: 'حواله انبار با موفقیت ثبت شد.',
        color: 'success'
      }
      setTimeout(() => {
        router.push('/acc/storeroom/tickets/list')
      }, 1000)
    }
  } catch (error) {
    console.error('Error submitting form:', error)
    snackbar.value = {
      show: true,
      message: 'خطا در ثبت اطلاعات',
      color: 'error'
    }
  } finally {
    loading.value = false
  }
}

const autofill = () => {
  items.value.forEach((element, index) => {
    const remain = Math.max(0, items.value[index].remain)
    items.value[index].ticketCount = remain
    items.value[index].des = remain > 0 ? `تعداد ${remain} مورد تحویل شد.` : ''
    items.value[index].type = 'output'
  })
}

const isNumber = (evt: KeyboardEvent): void => {
  const keysAllowed: string[] = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']
  const keyPressed: string = evt.key
  if (!keysAllowed.includes(keyPressed)) {
    evt.preventDefault()
  }
}

const loadData = async () => {
  loading.value = true
  try {
    const [docResponse, storeResponse, yearResponse, transferTypesResponse] = await Promise.all([
      axios.post(`/api/storeroom/doc/get/info/${router.currentRoute.value.params.doc}`),
      axios.post(`/api/storeroom/info/${router.currentRoute.value.params.storeID}`),
      axios.post('/api/year/get'),
      axios.post('/api/storeroom/transfertype/list')
    ])

    doc.value = docResponse.data
    ticket.value.person = docResponse.data.person
    ticket.value.des = `حواله خروج از انبار برای فاکتور برگشت از خرید شماره # ${docResponse.data.code}`
    items.value = docResponse.data.commodities.map((element: Commodity) => ({
      ...element,
      ticketCount: 0,
      docCount: element.commdityCount,
      des: '',
      type: 'output'
    }))

    ticket.value.store = storeResponse.data
    ticket.value.store.des = `${storeResponse.data.name} انباردار : ${storeResponse.data.manager}`

    year.value = yearResponse.data
    ticket.value.date = yearResponse.data.now

    transferTypes.value = transferTypesResponse.data
    ticket.value.transferType = transferTypesResponse.data[0]
  } catch (error) {
    console.error('Error loading data:', error)
    snackbar.value = {
      show: true,
      message: 'خطا در بارگذاری داده‌ها',
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
  border-radius: 8px;
}
</style>