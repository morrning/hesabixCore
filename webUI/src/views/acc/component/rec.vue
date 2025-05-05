<template>
  <v-btn v-if="totalAmount > 0" icon color="error" class="ml-2" @click="dialog = true">
    <v-icon>mdi-cash</v-icon>
    <v-tooltip activator="parent" location="bottom">ثبت دریافت</v-tooltip>
  </v-btn>

  <v-dialog 
    v-model="dialog" 
    :fullscreen="$vuetify.display.mobile" 
    :max-width="$vuetify.display.mobile ? '' : '950'" 
    persistent
    :class="$vuetify.display.mobile ? 'mobile-dialog' : ''"
  >
    <v-card :rounded="false" class="d-flex flex-column dialog-card">
      <v-toolbar color="toolbar" flat>
        <v-toolbar-title>
          <v-icon color="error" left>mdi-cash</v-icon>
          ثبت دریافت
        </v-toolbar-title>
        <v-spacer></v-spacer>
        
        <v-menu bottom>
          <template v-slot:activator="{ props }">
            <v-btn icon color="success" v-bind="props" :disabled="loading">
              <v-icon>mdi-plus</v-icon>
              <v-tooltip activator="parent" location="bottom">افزودن دریافت</v-tooltip>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="addItem('bank')">
              <v-list-item-title>حساب بانکی</v-list-item-title>
            </v-list-item>
            <v-list-item @click="addItem('cashdesk')">
              <v-list-item-title>صندوق</v-list-item-title>
            </v-list-item>
            <v-list-item @click="addItem('salary')">
              <v-list-item-title>تنخواه گردان</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>

        <v-btn icon color="primary" @click="submit" :disabled="loading">
          <v-icon>mdi-content-save</v-icon>
          <v-tooltip activator="parent" location="bottom">ثبت</v-tooltip>
        </v-btn>
        <v-btn icon @click="dialog = false" :disabled="loading">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text class="flex-grow-1 overflow-y-auto pa-2">
        <v-container class="pa-0">
          <v-row>
            <v-col cols="12" md="5">
              <Hdatepicker v-model="date" label="تاریخ" />
            </v-col>
            <v-col cols="12" md="7">
              <v-text-field v-model="des" label="شرح" outlined clearable class="mb-4"></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-text-field v-model="formattedTotalPays" label="مجموع" readonly outlined></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="formattedRemainingAmount" label="باقی مانده" readonly outlined></v-text-field>
            </v-col>
          </v-row>

          <div v-if="items.length === 0" class="text-center pa-4">
            هیچ دریافتی ثبت نشده است.
          </div>

          <v-row v-else>
            <v-col v-for="(item, index) in items" :key="index" cols="12">
              <v-card :class="{
                'bank-card': item.type === 'bank',
                'cashdesk-card': item.type === 'cashdesk',
                'salary-card': item.type === 'salary',
                'cheque-card': item.type === 'cheque'
              }" border="sm">
                <v-card-item>
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
                    <v-btn-group>
                      <v-btn variant="text" color="primary" density="comfortable" @click="fillWithTotal(item)">
                        <v-icon>mdi-cash-100</v-icon>
                        <v-tooltip activator="parent" location="bottom">کل فاکتور</v-tooltip>
                      </v-btn>
                      <v-btn variant="text" color="error" density="comfortable" @click="deleteItem(index)">
                        <v-icon>mdi-trash-can</v-icon>
                        <v-tooltip activator="parent" location="bottom">حذف</v-tooltip>
                      </v-btn>
                    </v-btn-group>
                  </template>
                </v-card-item>

                <v-card-text>
                  <v-row class="mt-2">
                    <v-col cols="12" sm="6">
                      <v-select 
                        v-if="item.type === 'bank'" 
                        v-model="item.bank" 
                        :items="listBanks" 
                        item-title="name"
                        return-object 
                        label="بانک"
                        variant="outlined"
                      ></v-select>
                      <v-select 
                        v-if="item.type === 'cashdesk'" 
                        v-model="item.cashdesk" 
                        :items="listCashdesks"
                        item-title="name" 
                        return-object 
                        label="صندوق"
                        variant="outlined"
                      ></v-select>
                      <v-select 
                        v-if="item.type === 'salary'" 
                        v-model="item.salary" 
                        :items="listSalarys" 
                        item-title="name"
                        return-object 
                        label="تنخواه گردان"
                        variant="outlined"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <Hnumberinput 
                        v-model="item.bd" 
                        label="مبلغ" 
                        placeholder="0" 
                        @update:modelValue="calc"
                        variant="outlined"
                      />
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field 
                        v-model="item.referral" 
                        label="ارجاع"
                        variant="outlined"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field 
                        v-model="item.des" 
                        label="شرح"
                        variant="outlined"
                      ></v-text-field>
                    </v-col>
                  </v-row>

                  <v-row v-if="item.type === 'cheque'" class="mt-2">
                    <v-col cols="12" sm="6">
                      <v-text-field v-model="item.chequeNum" label="شماره چک" required variant="outlined"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field v-model="item.chequeSayadNum" label="شماره صیاد" required variant="outlined"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field v-model="item.chequeBank" label="بانک صادرکننده" required variant="outlined"></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <Hdatepicker v-model="item.chequeDate" label="تاریخ چک" required />
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

<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from 'axios'
import { VDataTable as DataTable } from 'vuetify/components'
import Hdatepicker from '@/components/forms/Hdatepicker.vue'
import Hnumberinput from '@/components/forms/Hnumberinput.vue'

interface Item {
  id: string | number;
  type: string;
  bd: number;
  bs: number;
  table: number;
  des: string;
  bank?: any;
  cashdesk?: any;
  salary?: any;
  chequeNum?: string;
  chequeSayadNum?: string;
  chequeBank?: string;
  chequeDate?: string;
  referral?: string;
}

export default defineComponent({
  name: 'Rec',
  components: {
    DataTable,
    Hdatepicker,
    Hnumberinput
  },
  props: {
    totalAmount: Number,
    originalDoc: String,
    person: [String, Number],
    windowsState: Object
  },
  setup() {
    const dialog = ref(false)
    return { dialog }
  },
  data: () => ({
    submitedDoc: {},
    des: '',
    items: [] as Item[],
    date: '',
    listBanks: [],
    listSalarys: [],
    listCashdesks: [],
    totalPays: 0,
    loading: false,
    errorMessage: '',
    successDialog: false,
    successMessage: '',
    errorDialog: false
  }),
  computed: {
    headers() {
      return [
        { title: 'نوع', key: 'type', sortable: false, width: '5%' },
        { title: 'انتخاب', key: 'selection', sortable: false, width: '25%' },
        { title: 'مبلغ', key: 'bd', sortable: false, width: '20%' },
        { title: 'ارجاع', key: 'referral', sortable: false, width: '15%' },
        { title: 'شرح', key: 'des', sortable: false, width: '25%' },
        { title: 'عملیات', key: 'actions', sortable: false, width: '10%' }
      ]
    },
    remainingAmount() {
      return this.totalAmount - this.totalPays
    },
    formattedTotalPays() {
      return this.formatNumber(this.totalPays) || '۰';
    },
    formattedRemainingAmount() {
      return this.formatNumber(this.remainingAmount) || '۰';
    }
  },
  methods: {
    formatNumber(value: number | string): string {
      if (value === undefined || value === null || value === '') return '۰';
      const num = parseInt(value.toString().replace(/[^\d]/g, ''));
      return num.toLocaleString('fa-IR') || '۰';
    },
    fillWithTotal(pay) {
      pay.bd = this.totalAmount - this.totalPays
      this.calc()
    },
    addItem(type) {
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
      } else if (type === 'cheque') {
        obj = {
          uniqueId,
          id: '', type: 'cheque', bank: {}, cashdesk: {}, salary: {}, bs: 0, bd: 0, des: '', table: 125, referral: '',
          chequeBank: '', chequeDate: '', chequeSayadNum: '', chequeNum: '', chequeType: 'input', chequeOwner: this.person
        }
      }

      if (canAdd) {
        this.items.push(obj)
        this.errorMessage = ''
      }
    },
    deleteItem(index) {
      this.items.splice(index, 1)
      this.calc()
    },
    async loadData() {
      this.loading = true
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
      } finally {
        this.loading = false
      }
    },
    calc() {
      this.totalPays = this.items.reduce((sum, item) => {
        const bd = item.bd !== null && item.bd !== undefined ? Number(item.bd) : 0
        return sum + bd
      }, 0)
    },
    async submit() {
      let errors = []

      if (this.items.length === 0) {
        this.errorMessage = 'هیچ دریافتی ثبت نشده است.';
        this.errorDialog = true;
        return;
      }

      if (this.totalAmount && this.totalPays && this.totalAmount < this.totalPays) {
        errors.push('مبالغ وارد شده بیشتر از مبلغ فاکتور است.')
      }

      this.items.forEach((element: any, index: number) => {
        if (element.bd === 0 || element.bd === null || element.bd === undefined) {
          errors.push(`مبلغ صفر در ردیف ${index + 1} نا معتبر است.`)
        }
        if (element.type === 'cheque' && (!element.chequeBank || !element.chequeNum || !element.chequeDate)) {
          errors.push(`موارد الزامی ثبت چک در ردیف ${index + 1} وارد نشده است.`)
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
      const rows = [...this.items].map(element => {
        if (element.type === 'bank') element.id = element.bank.id
        else if (element.type === 'salary') element.id = element.salary.id
        else if (element.type === 'cashdesk') element.id = element.cashdesk.id
        element.des = element.des || `دریافت وجه فاکتور شماره ${this.originalDoc}`
        return element
      })

      if (!this.person) {
        throw new Error('شخص انتخاب نشده است')
      }

      const personRow = {
        id: this.person,
        type: 'person',
        bd: 0,
        bs: this.totalPays,
        table: 3,
        des: `دریافت وجه فاکتور شماره ${this.formatNumber(this.originalDoc || '')}`
      }

      rows.push(personRow)

      this.des = this.des || `دریافت وجه فاکتور شماره ${this.formatNumber(this.originalDoc || '')}`

      try {
        const response = await axios.post('/api/accounting/insert', {
          date: this.date,
          des: this.des,
          type: 'sell_receive',
          update: null,
          rows,
          related: this.originalDoc
        })

        if (response.data.result === 1) {
          this.submitedDoc = response.data.doc

          if (this.windowsState) {
            this.windowsState.submited = true
          }

          this.successMessage = 'سند دریافت با موفقیت ثبت شد'
          this.successDialog = true

          setTimeout(() => {
            this.successDialog = false
            this.dialog = false
          }, 2000)

          this.$emit('submit-success', response.data.doc)
        } else {
          this.errorMessage = response.data.msg || 'خطا در ثبت سند'
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'خطا در ثبت سند'
      } finally {
        this.loading = false
      }
    }
  },
  async mounted() {
    await this.loadData()
    const response = await axios.get('/api/year/get')
    this.date = response.data.now // تاریخ جاری شمسی
  }
})
</script>

<style scoped>
.v-card.success {
  background-color: #4caf50 !important;
}

/* استایل‌های جدید برای کارت‌ها */
.v-card {
  transition: all 0.3s ease;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

.v-card-text {
  overflow-x: hidden;
  padding: 16px;
}

/* رنگ‌های متفاوت برای انواع مختلف کارت‌ها */
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

/* استایل‌های جدید برای رفع مشکل اسکرول */
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
  height: calc(100vh - 64px) !important; /* 64px ارتفاع toolbar */
}

.mobile-dialog :deep(.v-overlay__content) {
  width: 100% !important;
  height: 100% !important;
}

:deep(.v-card-text .container) {
  max-width: 100% !important;
}
</style>