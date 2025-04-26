<template>
  <v-toolbar color="toolbar" title="حواله انبار جدید">
    <template v-slot:prepend>
      <v-tooltip text="بازگشت" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />
    <v-tooltip text="ثبت" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn
          v-bind="props"
          :loading="loading"
          :disabled="loading"
          color="primary"
          icon="mdi-content-save"
          @click="submit"
        />
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-container>
    <v-row>
      <!-- ستون سمت راست - انتخاب انبار -->
      <v-col cols="12" md="4">
        <v-card variant="outlined" class="h-100">
          <v-card-title class="text-subtitle-1 font-weight-bold">
            <v-icon start>mdi-warehouse</v-icon>
            انتخاب انبار
          </v-card-title>
          <v-card-text>
            <v-select
              v-model="item.storeroom"
              :items="storerooms"
              item-title="name"
              item-value="id"
              label="انبار"
              variant="outlined"
              density="compact"
              :loading="loading"
              return-object
              class="mb-4"
            >
              <template v-slot:no-data>
                <v-list-item>
                  <v-list-item-title>
                    نتیجه‌ای یافت نشد!
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-select>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- ستون وسط - نوع حواله -->
      <v-col cols="12" md="4">
        <v-card variant="outlined" class="h-100">
          <v-card-title class="text-subtitle-1 font-weight-bold">
            <v-icon start>mdi-file-document-edit</v-icon>
            نوع حواله
          </v-card-title>
          <v-card-text>
            <v-radio-group v-model="item.type" class="mt-0">
              <v-radio value="sell" color="success">
                <template v-slot:label>
                  <div class="d-flex align-center">
                    <v-icon color="success" class="ml-2">mdi-cart-arrow-down</v-icon>
                    <span>حواله برای فاکتور فروش</span>
                  </div>
                </template>
              </v-radio>
              <v-radio value="buy" color="primary">
                <template v-slot:label>
                  <div class="d-flex align-center">
                    <v-icon color="primary" class="ml-2">mdi-cart-arrow-up</v-icon>
                    <span>حواله برای فاکتور خرید</span>
                  </div>
                </template>
              </v-radio>
              <v-radio value="rfbuy" color="warning">
                <template v-slot:label>
                  <div class="d-flex align-center">
                    <v-icon color="warning" class="ml-2">mdi-cart-remove</v-icon>
                    <span>حواله برای فاکتور برگشت از خرید</span>
                  </div>
                </template>
              </v-radio>
              <v-radio value="rfsell" color="error">
                <template v-slot:label>
                  <div class="d-flex align-center">
                    <v-icon color="error" class="ml-2">mdi-cart-remove</v-icon>
                    <span>حواله برای فاکتور برگشت از فروش</span>
                  </div>
                </template>
              </v-radio>
            </v-radio-group>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- ستون سمت چپ - انتخاب فاکتور -->
      <v-col cols="12" md="4">
        <v-card variant="outlined" class="h-100">
          <v-card-title class="text-subtitle-1 font-weight-bold">
            <v-icon start>mdi-file-document</v-icon>
            انتخاب فاکتور
          </v-card-title>
          <v-card-text>
            <v-select
              v-if="item.type === 'buy'"
              v-model="item.docBuy"
              :items="buys"
              item-title="des"
              item-value="code"
              label="فاکتور خرید"
              variant="outlined"
              density="compact"
              :loading="loading"
              return-object
              class="mb-4"
            >
              <template v-slot:no-data>
                <v-list-item>
                  <v-list-item-title>
                    نتیجه‌ای یافت نشد!
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-select>

            <v-select
              v-if="item.type === 'sell'"
              v-model="item.docSell"
              :items="sells"
              item-title="des"
              item-value="code"
              label="فاکتور فروش"
              variant="outlined"
              density="compact"
              :loading="loading"
              return-object
              class="mb-4"
            >
              <template v-slot:no-data>
                <v-list-item>
                  <v-list-item-title>
                    نتیجه‌ای یافت نشد!
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-select>

            <v-select
              v-if="item.type === 'rfsell'"
              v-model="item.docRfsell"
              :items="rfsells"
              item-title="des"
              item-value="code"
              label="فاکتور برگشت از فروش"
              variant="outlined"
              density="compact"
              :loading="loading"
              return-object
              class="mb-4"
            >
              <template v-slot:no-data>
                <v-list-item>
                  <v-list-item-title>
                    نتیجه‌ای یافت نشد!
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-select>

            <v-select
              v-if="item.type === 'rfbuy'"
              v-model="item.docRfbuy"
              :items="rfbuys"
              item-title="des"
              item-value="code"
              label="فاکتور برگشت از خرید"
              variant="outlined"
              density="compact"
              :loading="loading"
              return-object
              class="mb-4"
            >
              <template v-slot:no-data>
                <v-list-item>
                  <v-list-item-title>
                    نتیجه‌ای یافت نشد!
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-select>

            <v-checkbox
              v-model="item.removeBeforeTickets"
              :disabled="!item.removeBeforeTicketsEnable"
              label="حذف حواله‌های انباری که قبلا برای این فاکتور صادر شده است"
              color="warning"
              hide-details
            />
          </v-card-text>
        </v-card>
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
import { ref, watch } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

interface Storeroom {
  id: number;
  name: string;
  manager: string;
}

interface Document {
  code: string;
  des: string;
}

interface Item {
  storeroom: Storeroom | null;
  type: 'sell' | 'buy' | 'rfbuy' | 'rfsell' | 'wastage' | 'used';
  title: string;
  docSell: Document | null;
  docBuy: Document | null;
  docRfsell: Document | null;
  docRfbuy: Document | null;
  removeBeforeTickets: boolean;
  removeBeforeTicketsEnable: boolean;
}

const router = useRouter()
const loading = ref(false)

// Refs
const storerooms = ref<Storeroom[]>([])
const buys = ref<Document[]>([])
const sells = ref<Document[]>([])
const rfsells = ref<Document[]>([])
const rfbuys = ref<Document[]>([])

const item = ref<Item>({
  storeroom: null,
  type: 'sell',
  title: 'فروش',
  docSell: null,
  docBuy: null,
  docRfsell: null,
  docRfbuy: null,
  removeBeforeTickets: true,
  removeBeforeTicketsEnable: true
})

const snackbar = ref({
  show: false,
  message: '',
  color: 'primary' as 'primary' | 'error' | 'success' | 'warning'
})

// Watchers
watch(() => item.value.type, (newValue) => {
  if (newValue === 'sell') {
    item.value.title = 'فروش'
    item.value.removeBeforeTicketsEnable = true
  } else if (newValue === 'buy') {
    item.value.title = 'خرید'
    item.value.removeBeforeTicketsEnable = true
  } else if (newValue === 'rfbuy') {
    item.value.title = 'برگشت از خرید'
    item.value.removeBeforeTicketsEnable = true
  } else if (newValue === 'rfsell') {
    item.value.title = 'برگشت از فروش'
    item.value.removeBeforeTicketsEnable = true
  } else if (newValue === 'wastage') {
    item.value.title = 'ضایعات'
    item.value.removeBeforeTicketsEnable = false
    item.value.removeBeforeTickets = false
  } else if (newValue === 'used') {
    item.value.title = 'مصرف مستقیم'
    item.value.removeBeforeTicketsEnable = false
    item.value.removeBeforeTickets = false
  }
})

// Methods
const loadData = async () => {
  loading.value = true
  try {
    const [storeroomsResponse, docsResponse] = await Promise.all([
      axios.post('/api/storeroom/list'),
      axios.post('/api/storeroom/docs/get')
    ])

    storerooms.value = storeroomsResponse.data.data.map((element: Storeroom) => ({
      ...element,
      name: `${element.name} انباردار : ${element.manager}`
    }))

    if (storerooms.value.length > 0) {
      item.value.storeroom = storerooms.value[0]
    }

    buys.value = docsResponse.data.buys
    sells.value = docsResponse.data.sells
    rfsells.value = docsResponse.data.rfsells
    rfbuys.value = docsResponse.data.rfbuys
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

const submit = async () => {
  loading.value = true
  try {
    if (!item.value.storeroom) {
      snackbar.value = {
        show: true,
        message: 'انبار انتخاب نشده است',
        color: 'error'
      }
      return
    }

    // بررسی وجود فاکتور بر اساس نوع حواله
    let selectedDoc: Document | null = null
    switch (item.value.type) {
      case 'sell':
        selectedDoc = item.value.docSell
        if (!selectedDoc) {
          snackbar.value = {
            show: true,
            message: 'فاکتور فروش انتخاب نشده است',
            color: 'error'
          }
          return
        }
        break
      case 'buy':
        selectedDoc = item.value.docBuy
        if (!selectedDoc) {
          snackbar.value = {
            show: true,
            message: 'فاکتور خرید انتخاب نشده است',
            color: 'error'
          }
          return
        }
        break
      case 'rfbuy':
        selectedDoc = item.value.docRfbuy
        if (!selectedDoc) {
          snackbar.value = {
            show: true,
            message: 'فاکتور برگشت از خرید انتخاب نشده است',
            color: 'error'
          }
          return
        }
        break
      case 'rfsell':
        selectedDoc = item.value.docRfsell
        if (!selectedDoc) {
          snackbar.value = {
            show: true,
            message: 'فاکتور برگشت از فروش انتخاب نشده است',
            color: 'error'
          }
          return
        }
        break
      default:
        snackbar.value = {
          show: true,
          message: 'نوع حواله نامعتبر است',
          color: 'error'
        }
        return
    }

    // Navigate to appropriate route
    const routes = {
      sell: { name: 'storeroom_new_ticket_sell', params: { doc: selectedDoc.code, storeID: item.value.storeroom.id } },
      buy: { name: 'storeroom_new_ticket_buy', params: { doc: selectedDoc.code, storeID: item.value.storeroom.id } },
      rfbuy: { name: 'storeroom_new_ticket_rfbuy', params: { doc: selectedDoc.code, storeID: item.value.storeroom.id } },
      rfsell: { name: 'storeroom_new_ticket_rfsell', params: { doc: selectedDoc.code, storeID: item.value.storeroom.id } }
    } as const

    type RouteType = keyof typeof routes
    const routeType = item.value.type as RouteType
    router.push(routes[routeType])
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

// Lifecycle hooks
loadData()
</script>

<style scoped>
.v-card {
  border-radius: 8px;
  transition: all 0.3s ease;
}

.v-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.v-radio :deep(.v-label) {
  font-size: 0.9rem;
}
</style>