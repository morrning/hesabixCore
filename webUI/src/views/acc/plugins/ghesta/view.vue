<template>
  <v-toolbar color="toolbar" title="مشاهده جزئیات فروش اقساطی">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
  </v-toolbar>

  <div class="pa-0">
    <v-card>
      <v-card-text v-if="loading">
        <v-progress-linear indeterminate color="primary"></v-progress-linear>
      </v-card-text>

      <v-card-text v-else-if="error">
        <v-alert type="error" variant="tonal" class="mb-0">
          <template v-slot:prepend>
            <v-icon icon="mdi-alert-circle"></v-icon>
          </template>
          {{ error }}
        </v-alert>
      </v-card-text>

      <v-card-text v-else>
        <v-row>
          <v-col cols="12" md="4">
            <v-card variant="outlined" class="mb-4">
              <v-card-title class="text-subtitle-1 font-weight-bold bg-grey-lighten-4">
                <v-icon icon="mdi-information-outline" class="ml-2"></v-icon>
                اطلاعات اصلی
              </v-card-title>
              <v-card-text>
                <v-list density="compact" class="pa-0">
                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon icon="mdi-receipt" color="primary"></v-icon>
                    </template>
                    <v-list-item-title class="text-subtitle-2">شماره فاکتور</v-list-item-title>
                    <v-list-item-subtitle class="text-body-1 font-weight-medium">{{ invoice.code }}</v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon icon="mdi-account" color="primary"></v-icon>
                    </template>
                    <v-list-item-title class="text-subtitle-2">مشتری</v-list-item-title>
                    <v-list-item-subtitle class="text-body-1 font-weight-medium">{{ invoice.person?.nikename }}</v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon icon="mdi-calendar-clock" color="primary"></v-icon>
                    </template>
                    <v-list-item-title class="text-subtitle-2">تعداد اقساط</v-list-item-title>
                    <v-list-item-subtitle class="text-body-1 font-weight-medium">{{ invoice.count }}</v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>

            <v-card variant="outlined">
              <v-card-title class="text-subtitle-1 font-weight-bold bg-grey-lighten-4">
                <v-icon icon="mdi-cash-multiple" class="ml-2"></v-icon>
                اطلاعات مالی
              </v-card-title>
              <v-card-text>
                <v-list density="compact" class="pa-0">
                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon icon="mdi-percent" color="primary"></v-icon>
                    </template>
                    <v-list-item-title class="text-subtitle-2">درصد سود</v-list-item-title>
                    <v-list-item-subtitle class="text-body-1 font-weight-medium">{{ invoice.profitPercent }}%</v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon icon="mdi-currency-usd" color="primary"></v-icon>
                    </template>
                    <v-list-item-title class="text-subtitle-2">مبلغ سود</v-list-item-title>
                    <v-list-item-subtitle class="text-body-1 font-weight-medium">{{ formatCurrency(invoice.profitAmount) }}</v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon icon="mdi-tag-multiple" color="primary"></v-icon>
                    </template>
                    <v-list-item-title class="text-subtitle-2">نوع سود</v-list-item-title>
                    <v-list-item-subtitle class="text-body-1 font-weight-medium">{{ getProfitTypeText(invoice.profitType) }}</v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template v-slot:prepend>
                      <v-icon icon="mdi-calendar-range" color="primary"></v-icon>
                    </template>
                    <v-list-item-title class="text-subtitle-2">جریمه روزانه </v-list-item-title>
                    <v-list-item-subtitle class="text-body-1 font-weight-medium">{{ invoice.daysPay }}</v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" md="8">
            <v-card variant="outlined">
              <v-card-title class="text-subtitle-1 font-weight-bold bg-grey-lighten-4">
                <v-icon icon="mdi-format-list-bulleted" class="ml-2"></v-icon>
                لیست اقساط
              </v-card-title>
              <v-card-text>
                <v-data-table
                  :headers="headers"
                  :items="invoice.items"
                  :items-per-page="-1"
                  class="elevation-0"
                  density="compact"
                  hide-default-footer
                >
                  <template v-slot:item.date="{ item }">
                    <v-chip
                      size="small"
                      color="primary"
                      variant="outlined"
                      class="font-weight-medium"
                    >
                      {{ formatDate(item.date) }}
                    </v-chip>
                  </template>
                  <template v-slot:item.amount="{ item }">
                    <span class="font-weight-medium">{{ formatCurrency(item.amount) }}</span>
                  </template>
                  <template v-slot:item.hesabdariDoc="{ item }">
                    <v-chip
                      v-if="item.hesabdariDoc"
                      color="success"
                      size="small"
                      variant="tonal"
                      class="font-weight-medium"
                    >
                      <v-icon start icon="mdi-check-circle" size="small"></v-icon>
                      {{ item.hesabdariDoc.code }}
                    </v-chip>
                    <v-chip
                      v-else
                      color="warning"
                      size="small"
                      variant="tonal"
                      class="font-weight-medium"
                    >
                      <v-icon start icon="mdi-clock-outline" size="small"></v-icon>
                      پرداخت نشده
                    </v-chip>
                  </template>
                  <template v-slot:item.actions="{ item }">
                    <v-btn
                      v-if="!item.hesabdariDoc"
                      color="primary"
                      size="small"
                      variant="tonal"
                      @click="openPaymentDialog(item)"
                    >
                      <v-icon start icon="mdi-cash" size="small"></v-icon>
                      ثبت قسط
                    </v-btn>
                  </template>
                </v-data-table>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>

  <!-- دیالوگ ثبت قسط -->
  <v-dialog 
    v-model="paymentDialog" 
    :fullscreen="$vuetify.display.mobile" 
    :max-width="$vuetify.display.mobile ? '' : '600'" 
    persistent
    :class="$vuetify.display.mobile ? 'mobile-dialog' : ''"
  >
    <v-card class="d-flex flex-column dialog-card">
      <v-toolbar color="toolbar" flat density="compact">
        <v-toolbar-title class="text-subtitle-1">
          <v-icon color="primary" left>mdi-cash</v-icon>
          ثبت قسط
        </v-toolbar-title>
        <v-spacer></v-spacer>
        
        <v-menu bottom>
          <template v-slot:activator="{ props }">
            <v-btn icon color="success" v-bind="props" :disabled="loading" density="comfortable">
              <v-icon>mdi-plus</v-icon>
              <v-tooltip activator="parent" location="bottom">افزودن دریافت</v-tooltip>
            </v-btn>
          </template>
          <v-list density="compact">
            <v-list-item @click="addPaymentItem('bank')">
              <v-list-item-title>حساب بانکی</v-list-item-title>
            </v-list-item>
            <v-list-item @click="addPaymentItem('cashdesk')">
              <v-list-item-title>صندوق</v-list-item-title>
            </v-list-item>
            <v-list-item @click="addPaymentItem('salary')">
              <v-list-item-title>تنخواه گردان</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>

        <v-btn icon color="primary" @click="submitPayment" :disabled="loading" density="comfortable">
          <v-icon>mdi-content-save</v-icon>
          <v-tooltip activator="parent" location="bottom">ثبت</v-tooltip>
        </v-btn>
        <v-btn icon @click="paymentDialog = false" :disabled="loading" density="comfortable">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text class="flex-grow-1 overflow-y-auto pa-2">
        <v-container class="pa-0">
          <v-row dense>
            <v-col cols="12" md="5">
              <Hdatepicker v-model="paymentDate" label="تاریخ" density="compact" />
            </v-col>
            <v-col cols="12" md="7">
              <v-text-field v-model="paymentDes" label="شرح" outlined clearable density="compact" class="mb-2"></v-text-field>
            </v-col>
          </v-row>

          <v-row dense>
            <v-col cols="12" md="6">
              <v-text-field v-model="formattedTotalPays" label="مجموع" readonly outlined density="compact"></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="formattedRemainingAmount" label="باقی مانده" readonly outlined density="compact"></v-text-field>
            </v-col>
          </v-row>

          <div v-if="paymentItems.length === 0" class="text-center pa-2">
            هیچ دریافتی ثبت نشده است.
          </div>

          <v-row dense>
            <v-col v-for="(item, index) in paymentItems" :key="index" cols="12">
              <v-card :class="{
                'bank-card': item.type === 'bank',
                'cashdesk-card': item.type === 'cashdesk',
                'salary-card': item.type === 'salary',
                'cheque-card': item.type === 'cheque'
              }" border="sm" class="mb-2">
                <v-card-item class="py-1">
                  <template v-slot:prepend>
                    <v-icon :color="item.type === 'bank' ? 'blue' :
                      item.type === 'cashdesk' ? 'green' :
                      item.type === 'salary' ? 'orange' : 'purple'">
                      {{ item.type === 'bank' ? 'mdi-bank' :
                        item.type === 'cashdesk' ? 'mdi-cash-register' :
                        item.type === 'salary' ? 'mdi-wallet' : 'mdi-checkbook' }}
                    </v-icon>
                  </template>

                  <template v-slot:append>
                    <v-btn-group density="compact">
                      <v-btn variant="text" color="primary" density="comfortable" @click="fillWithTotal(item)">
                        <v-icon>mdi-cash-100</v-icon>
                        <v-tooltip activator="parent" location="bottom">کل قسط</v-tooltip>
                      </v-btn>
                      <v-btn variant="text" color="error" density="comfortable" @click="deletePaymentItem(index)">
                        <v-icon>mdi-delete</v-icon>
                        <v-tooltip activator="parent" location="bottom">حذف</v-tooltip>
                      </v-btn>
                    </v-btn-group>
                  </template>
                </v-card-item>

                <v-card-text class="pa-2">
                  <v-row dense>
                    <v-col cols="12" sm="6">
                      <v-select 
                        v-if="item.type === 'bank'" 
                        v-model="item.bank" 
                        :items="listBanks" 
                        item-title="name"
                        return-object 
                        label="بانک"
                        variant="outlined"
                        density="compact"
                      ></v-select>
                      <v-select 
                        v-if="item.type === 'cashdesk'" 
                        v-model="item.cashdesk" 
                        :items="listCashdesks"
                        item-title="name" 
                        return-object 
                        label="صندوق"
                        variant="outlined"
                        density="compact"
                      ></v-select>
                      <v-select 
                        v-if="item.type === 'salary'" 
                        v-model="item.salary" 
                        :items="listSalarys" 
                        item-title="name"
                        return-object 
                        label="تنخواه گردان"
                        variant="outlined"
                        density="compact"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <Hnumberinput 
                        v-model="item.bd" 
                        label="مبلغ" 
                        placeholder="0" 
                        @update:modelValue="calcPayment"
                        variant="outlined"
                        density="compact"
                      />
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field 
                        v-model="item.referral" 
                        label="ارجاع"
                        variant="outlined"
                        density="compact"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field 
                        v-model="item.des" 
                        label="شرح"
                        variant="outlined"
                        density="compact"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-overlay :model-value="loading" contained class="align-center justify-center">
            <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
          </v-overlay>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>

  <v-dialog v-model="successDialog" max-width="400">
    <v-card color="success">
      <v-card-text class="text-center pa-4">
        <v-icon size="large" color="white" class="mb-4">mdi-check-circle</v-icon>
        <div class="text-h6 text-white mb-2">ثبت موفق</div>
        <div class="text-white">{{ successMessage }}</div>
      </v-card-text>
    </v-card>
  </v-dialog>

  <v-dialog v-model="errorDialog" max-width="400">
    <v-card color="error">
      <v-card-text class="text-center pa-4">
        <v-icon size="large" color="white" class="mb-4">mdi-alert-circle</v-icon>
        <div class="text-h6 text-white mb-2">خطا</div>
        <div class="text-white" style="white-space: pre-line">{{ errorMessage }}</div>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="white" variant="text" @click="errorDialog = false">بستن</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { formatNumber, formatCurrency } from '@/utils/number'
import { formatDate } from '@/utils/date'
import axios from 'axios'
import Hdatepicker from '@/components/forms/Hdatepicker.vue'
import Hnumberinput from '@/components/forms/Hnumberinput.vue'

export default {
  name: 'GhestaView',
  components: {
    Hdatepicker,
    Hnumberinput
  },
  data() {
    return {
      loading: true,
      error: null,
      invoice: {
        items: []
      },
      headers: [
        { 
          title: 'شماره قسط',
          key: 'num',
          align: 'center',
          sortable: true
        },
        { 
          title: 'تاریخ',
          key: 'date',
          align: 'center',
          sortable: true
        },
        { 
          title: 'مبلغ',
          key: 'amount',
          align: 'center',
          sortable: true
        },
        { 
          title: 'سند پرداخت',
          key: 'hesabdariDoc',
          align: 'center',
          sortable: false
        },
        {
          title: 'عملیات',
          key: 'actions',
          align: 'center',
          sortable: false,
          width: '120'
        }
      ],
      // متغیرهای مربوط به دیالوگ ثبت قسط
      paymentDialog: false,
      paymentDate: '',
      paymentDes: '',
      paymentItems: [],
      listBanks: [],
      listSalarys: [],
      listCashdesks: [],
      totalPays: 0,
      successDialog: false,
      successMessage: '',
      errorDialog: false,
      errorMessage: '',
      selectedItem: null
    }
  },
  computed: {
    formattedTotalPays() {
      return this.formatNumber(this.totalPays) || '۰';
    },
    formattedRemainingAmount() {
      return this.formatNumber(this.selectedItem ? this.selectedItem.amount - this.totalPays : 0) || '۰';
    }
  },
  methods: {
    formatNumber,
    formatCurrency,
    formatDate,
    getProfitTypeText(type) {
      const types = {
        'yearly': 'سالانه',
        'monthly': 'ماهانه',
        'daily': 'روزانه'
      }
      return types[type] || type
    },
    async loadInvoice() {
      try {
        this.loading = true
        this.error = null
        const response = await axios.get(`/api/plugins/ghesta/invoices/${this.$route.params.id}`)
        this.invoice = response.data
      } catch (error) {
        this.error = 'خطا در دریافت اطلاعات'
        console.error(error)
      } finally {
        this.loading = false
      }
    },
    // متدهای مربوط به دیالوگ ثبت قسط
    async openPaymentDialog(item) {
      this.selectedItem = item
      this.paymentItems = []
      this.totalPays = 0
      this.paymentDes = `پرداخت قسط ${item.num} فاکتور ${this.invoice.code} - ${this.invoice.person?.nikename}`
      await this.loadPaymentData()
      const response = await axios.get('/api/year/get')
      this.paymentDate = response.data.now
      this.paymentDialog = true
    },
    async loadPaymentData() {
      try {
        const [banks, salarys, cashdesks] = await Promise.all([
          axios.post('/api/bank/list'),
          axios.post('/api/salary/list'),
          axios.post('/api/cashdesk/list')
        ])
        this.listBanks = banks.data
        this.listSalarys = salarys.data
        this.listCashdesks = cashdesks.data
      } catch (error) {
        this.errorMessage = 'خطا در بارگذاری اطلاعات'
        this.errorDialog = true
      }
    },
    addPaymentItem(type) {
      let obj = {}
      let canAdd = true
      const uniqueId = Date.now() + Math.random().toString(36).substr(2, 9)

      if (type === 'bank') {
        if (this.listBanks.length === 0) {
          this.errorMessage = 'ابتدا یک حساب بانکی ایجاد کنید.'
          canAdd = false
        } else {
          obj = { uniqueId, id: '', type: 'bank', bank: null, cashdesk: {}, salary: {}, bs: 0, bd: 0, des: '', table: 5, referral: '' }
        }
      } else if (type === 'cashdesk') {
        if (this.listCashdesks.length === 0) {
          this.errorMessage = 'ابتدا یک صندوق ایجاد کنید.'
          canAdd = false
        } else {
          obj = { uniqueId, id: '', type: 'cashdesk', bank: {}, cashdesk: null, salary: {}, bs: 0, bd: 0, des: '', table: 121, referral: '' }
        }
      } else if (type === 'salary') {
        if (this.listSalarys.length === 0) {
          this.errorMessage = 'ابتدا یک تنخواه گردان ایجاد کنید.'
          canAdd = false
        } else {
          obj = { uniqueId, id: '', type: 'salary', bank: {}, cashdesk: {}, salary: null, bs: 0, bd: 0, des: '', table: 122, referral: '' }
        }
      }

      if (canAdd) {
        this.paymentItems.push(obj)
        this.errorMessage = ''
      } else {
        this.errorDialog = true
      }
    },
    deletePaymentItem(index) {
      this.paymentItems.splice(index, 1)
      this.calcPayment()
    },
    fillWithTotal(item) {
      item.bd = this.selectedItem.amount - this.totalPays
      this.calcPayment()
    },
    calcPayment() {
      this.totalPays = this.paymentItems.reduce((sum, item) => {
        const bd = item.bd !== null && item.bd !== undefined ? Number(item.bd) : 0
        return sum + bd
      }, 0)
    },
    async submitPayment() {
      let errors = []

      if (this.paymentItems.length === 0) {
        this.errorMessage = 'هیچ دریافتی ثبت نشده است.';
        this.errorDialog = true;
        return;
      }

      if (this.selectedItem.amount < this.totalPays) {
        errors.push('مبالغ وارد شده بیشتر از مبلغ قسط است.')
      }

      this.paymentItems.forEach((element, index) => {
        if (element.bd === 0 || element.bd === null || element.bd === undefined) {
          errors.push(`مبلغ صفر در ردیف ${index + 1} نا معتبر است.`)
        }
        if (element.type === 'bank' && (!element.bank || !Object.keys(element.bank).length)) {
          errors.push(`بانک در ردیف ${index + 1} انتخاب نشده است.`)
        }
        if (element.type === 'salary' && (!element.salary || !Object.keys(element.salary).length)) {
          errors.push(`تنخواه گردان در ردیف ${index + 1} انتخاب نشده است.`)
        }
        if (element.type === 'cashdesk' && (!element.cashdesk || !Object.keys(element.cashdesk).length)) {
          errors.push(`صندوق در ردیف ${index + 1} انتخاب نشده است.`)
        }
      })

      if (errors.length > 0) {
        this.errorMessage = errors.join('\n')
        this.errorDialog = true
        return
      }

      this.loading = true
      this.errorMessage = ''
      const rows = [...this.paymentItems].map(element => {
        if (element.type === 'bank') element.id = element.bank.id
        else if (element.type === 'salary') element.id = element.salary.id
        else if (element.type === 'cashdesk') element.id = element.cashdesk.id
        element.des = element.des || this.paymentDes
        return element
      })

      const personRow = {
        id: this.invoice.person.id,
        type: 'person',
        bd: 0,
        bs: this.totalPays,
        table: 3,
        des: this.paymentDes
      }

      rows.push(personRow)

      try {
        const response = await axios.post('/api/accounting/insert', {
          date: this.paymentDate,
          des: this.paymentDes,
          type: 'sell_receive',
          update: null,
          rows,
          related: this.invoice.code
        })

        if (response.data.result === 1) {
          this.successMessage = 'پرداخت قسط با موفقیت ثبت شد'
          this.successDialog = true

          setTimeout(() => {
            this.successDialog = false
            this.paymentDialog = false
            this.loadInvoice() // بارگذاری مجدد اطلاعات
          }, 2000)
        } else {
          this.errorMessage = response.data.msg || 'خطا در ثبت سند'
          this.errorDialog = true
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'خطا در ثبت سند'
        this.errorDialog = true
      } finally {
        this.loading = false
      }
    }
  },
  created() {
    this.loadInvoice()
  }
}
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}

.v-list-item {
  min-height: 48px;
}

.v-data-table {
  border-radius: 8px;
  overflow: hidden;
}

.v-chip {
  min-width: 100px;
  justify-content: center;
}

/* استایل‌های مربوط به دیالوگ ثبت قسط */
.v-card.success {
  background-color: #4caf50 !important;
}

.v-card.bank-card {
  border-right: 4px solid #1976d2;
}

.v-card.cashdesk-card {
  border-right: 4px solid #4caf50;
}

.v-card.salary-card {
  border-right: 4px solid #ff9800;
}

.v-card.cheque-card {
  border-right: 4px solid #9c27b0;
}

.v-card-item {
  background-color: rgba(0, 0, 0, 0.02);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.v-btn-group .v-btn {
  margin: 0 2px;
}

.mobile-dialog {
  overflow: hidden !important;
}

.mobile-dialog :deep(.v-card) {
  height: 100vh !important;
  max-height: 100vh !important;
}

.mobile-dialog :deep(.v-card-text) {
  padding: 8px !important;
  overflow-y: auto !important;
  height: calc(100vh - 64px) !important;
}

.mobile-dialog :deep(.v-overlay__content) {
  width: 100% !important;
  height: 100% !important;
}

:deep(.v-card-text .container) {
  max-width: 100% !important;
}

/* استایل‌های جدید */
.dialog-card {
  max-height: 85vh;
  display: flex;
  flex-direction: column;
}

.dialog-card .v-card-text {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
}

.dialog-card .v-card {
  margin-bottom: 8px;
}

.dialog-card .v-card:last-child {
  margin-bottom: 0;
}

.dialog-card .v-row {
  margin: 0;
}

.dialog-card .v-col {
  padding: 4px;
}

.dialog-card .v-text-field,
.dialog-card .v-select {
  margin-bottom: 0;
}

.dialog-card .v-card-item {
  padding: 4px 8px;
}

.dialog-card .v-card-text {
  padding: 8px;
}

.dialog-card .v-btn-group {
  display: flex;
  gap: 2px;
}
</style> 