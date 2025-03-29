<template>
  <v-btn
    v-if="totalAmount > 0"
    icon
    color="error"
    class="ml-2"
    @click="dialog = true"
  >
    <v-icon>mdi-cash</v-icon>
    <v-tooltip activator="parent" location="bottom">ثبت دریافت</v-tooltip>
  </v-btn>

  <v-dialog v-model="dialog" max-width="950" persistent>
    <v-card>
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
            <v-list-item @click="addItem('cheque')">
              <v-list-item-title>چک</v-list-item-title>
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

      <v-card-text>
        <v-alert
          v-if="errorMessage"
          type="error"
          dismissible
          @input="errorMessage = ''"
          class="mb-4"
        >
          {{ errorMessage }}
        </v-alert>
        <v-row>
          <v-col cols="12" md="5">
            <Hdatepicker v-model="date" label="تاریخ" />
          </v-col>
          <v-col cols="12" md="7">
            <v-text-field
              v-model="des"
              label="شرح"
              outlined
              clearable
              class="mb-4"
            ></v-text-field>
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="formattedTotalPays"
              label="مجموع"
              readonly
              outlined
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="formattedRemainingAmount"
              label="باقی مانده"
              readonly
              outlined
            ></v-text-field>
          </v-col>
        </v-row>

        <v-data-table
          :headers="headers"
          :items="items"
          :loading="loading"
          class="elevation-1 mt-2"
          :header-props="{ class: 'custom-header' }"
          :items-per-page="-1"
          hide-default-footer
        >
          <template v-slot:item.type="{ item }">
            <v-icon v-if="item.type === 'bank'">mdi-bank</v-icon>
            <v-icon v-if="item.type === 'cashdesk'">mdi-cash-register</v-icon>
            <v-icon v-if="item.type === 'salary'">mdi-wallet</v-icon>
            <v-icon v-if="item.type === 'cheque'">mdi-checkbook</v-icon>
          </template>
          <template v-slot:item.selection="{ item }">
            <v-select
              v-if="item.type === 'bank'"
              v-model="item.bank"
              :items="listBanks"
              item-title="name"
              return-object
              label="بانک"
              outlined
              dense
            ></v-select>
            <v-select
              v-if="item.type === 'cashdesk'"
              v-model="item.cashdesk"
              :items="listCashdesks"
              item-title="name"
              return-object
              label="صندوق"
              outlined
              dense
            ></v-select>
            <v-select
              v-if="item.type === 'salary'"
              v-model="item.salary"
              :items="listSalarys"
              item-title="name"
              return-object
              label="تنخواه گردان"
              outlined
              dense
            ></v-select>
            <template v-if="item.type === 'cheque'">
              <v-text-field
                v-model="item.chequeNum"
                label="شماره چک"
                outlined
                dense
                required
              ></v-text-field>
              <v-text-field
                v-model="item.chequeSayadNum"
                label="شماره صیاد"
                outlined
                dense
                required
              ></v-text-field>
              <v-text-field
                v-model="item.chequeBank"
                label="بانک صادرکننده"
                outlined
                dense
                required
              ></v-text-field>
              <Hdatepicker
                v-model="item.chequeDate"
                label="تاریخ چک"
                required
              />
            </template>
          </template>
          <template v-slot:item.bd="{ item }">
            <Hnumberinput
              v-model="item.bd"
              label="مبلغ"
              outlined
              dense
              placeholder="0"
              @update:modelValue="calc"
            />
          </template>
          <template v-slot:item.referral="{ item }">
            <v-text-field
              v-model="item.referral"
              label="ارجاع"
              outlined
              dense
            ></v-text-field>
          </template>
          <template v-slot:item.des="{ item }">
            <v-text-field
              v-model="item.des"
              label="شرح"
              outlined
              dense
            ></v-text-field>
          </template>
          <template v-slot:item.actions="{ item, index }">
            <v-btn
              variant="plain"
              color="primary"
              @click="fillWithTotal(item)"
            >
              <v-icon>mdi-cash-100</v-icon>
              <v-tooltip activator="parent" location="bottom">کل فاکتور</v-tooltip>
            </v-btn>
            <v-btn
              variant="plain"
              color="error"
              @click="deleteItem(index)"
            >
              <v-icon>mdi-trash-can</v-icon>
              <v-tooltip activator="parent" location="bottom">حذف</v-tooltip>
            </v-btn>
          </template>
          <template v-slot:no-data>
            <v-card-text class="text-center">
              هیچ دریافتی ثبت نشده است.
            </v-card-text>
          </template>
        </v-data-table>
      </v-card-text>

      <v-overlay :model-value="loading" contained class="align-center justify-center">
        <v-progress-circular indeterminate size="64" color="primary"></v-progress-circular>
      </v-overlay>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from 'axios'
import { VDataTable } from 'vuetify/components'
import Hdatepicker from '@/components/forms/Hdatepicker.vue'
import Hnumberinput from '@/components/forms/Hnumberinput.vue'

export default defineComponent({
  name: 'Rec',
  components: {
    VDataTable,
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
    items: [],
    date: '',
    listBanks: [],
    listSalarys: [],
    listCashdesks: [],
    totalPays: 0,
    loading: false,
    errorMessage: ''
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
      return this.formatNumber(this.totalPays)
    },
    formattedRemainingAmount() {
      return this.formatNumber(this.remainingAmount)
    }
  },
  methods: {
    formatNumber(value: number | string): string {
      if (!value) return ''
      const num = parseInt(value.toString().replace(/[^\d]/g, ''))
      return num.toLocaleString('fa-IR')
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
      if (this.totalAmount < this.totalPays) {
        errors.push('مبالغ وارد شده بیشتر از مبلغ فاکتور است.')
      }

      this.items.forEach((element, index) => {
        if (element.bd === 0) errors.push(`مبلغ صفر در ردیف ${index + 1} نا معتبر است.`)
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

      if (this.items.length === 0) {
        this.errorMessage = 'هیچ دریافتی ثبت نشده است.'
        return
      }

      if (errors.length > 0) {
        this.errorMessage = errors.join('\n')
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

      rows.push({
        id: this.person,
        type: 'person',
        bd: 0,
        bs: this.totalPays,
        table: 3,
        des: `دریافت وجه فاکتور شماره ${this.formatNumber(this.originalDoc)}`
      })

      this.des = this.des || `دریافت وجه فاکتور شماره ${this.formatNumber(this.originalDoc)}`

      try {
        const response = await axios.post('/api/accounting/insert', {
          date: this.date,
          des: this.des,
          type: 'sell_receive',
          update: null,
          rows,
          related: this.originalDoc
        })

        if (response.data.result === '1') {
          this.submitedDoc = response.data.doc
          this.windowsState.submited = true
          this.dialog = false
        } else if (response.data.result === '4') {
          this.errorMessage = response.data.msg
        }
      } catch (error) {
        this.errorMessage = 'خطا در ثبت سند'
      } finally {
        this.loading = false
      }
    }
  },
  mounted() {
    this.loadData()
  }
})
</script>

<style scoped>
/* استایل‌های دلخواه */
</style>