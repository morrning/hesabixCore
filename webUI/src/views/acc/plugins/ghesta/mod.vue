<template>
 <v-toolbar color="toolbar" title="فروش اقساطی" class="mb-0">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-tooltip text="ثبت فاکتور" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-content-save" color="success" @click="saveInvoice" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
    <v-tooltip v-if="$route.params.id" text="حذف فاکتور" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-delete" color="error" @click="deleteInvoice" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-tabs
    v-model="activeTab"
    color="primary"
    class="tabs-container"
    height="48"
    grow
  >
    <v-tab value="invoice" class="text-none">اطلاعات فاکتور</v-tab>
    <v-tab value="installments" class="text-none">اطلاعات اقساط</v-tab>
    <v-tab value="settings" class="text-none">تنظیمات جانبی</v-tab>
  </v-tabs>

  <v-window v-model="activeTab" class="window-container">
    <v-window-item value="invoice">
      <v-container fluid class="pa-4">
        <v-card class="rounded-lg">
          <v-card-text class="pa-4">
    <v-row>
      <v-col cols="12" sm="12" md="6">
                <Hdocsearch label="جستجوی فاکتور" doc-type="sell" v-model="selectedInvoice" :return-object="true" />
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field
                  v-model="invoiceCode"
                  label="شماره فاکتور"
                  readonly
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field
                  v-model="invoiceDate"
                  label="تاریخ فاکتور"
                  readonly
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field
                  v-model="invoiceDes"
                  label="توضیحات"
                  readonly
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field
                  v-model="invoiceAmount"
                  label="مبلغ فاکتور"
                  readonly
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field
                  v-model="invoiceStatus"
                  label="وضعیت تسویه"
                  readonly
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field
                  v-model="customerName"
                  label="نام مشتری"
                  readonly
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field
                  v-model="customerNikename"
                  label="نام مستعار مشتری"
                  readonly
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-container>
    </v-window-item>

    <v-window-item value="installments">
      <v-container fluid class="pa-2">
        <v-card class="rounded-lg">
          <v-card-text class="pa-2">
            <v-row dense>
              <!-- بخش اطلاعات مبالغ و سود -->
              <v-col cols="12">
                <v-card variant="outlined" class="mb-2">
                  <v-card-text class="pa-2">
                    <v-row dense>
                      <v-col cols="12" md="2">
                        <v-text-field
                          :model-value="formatNumber(selectedInvoice?.amount || 0)"
                          label="مبلغ کل فاکتور"
                          readonly
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-text-field
                          :model-value="formatNumber(totalPaidAmount)"
                          label="مبلغ پرداختی قبلی"
                          readonly
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-text-field
                          :model-value="formatNumber(remainingAmount)"
                          label="مبلغ باقیمانده"
                          readonly
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-text-field
                          :model-value="formatNumber(totalInterest)"
                          label="سود کل"
                          readonly
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-text-field
                          :model-value="formatNumber(remainingAmount + totalInterest)"
                          label="مبلغ کل (فاکتور + سود)"
                          readonly
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-text-field
                          :model-value="formatNumber(monthlyInterest)"
                          label="سود هر ماه"
                          readonly
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>

              <!-- بخش تنظیمات اقساط و سود -->
              <v-col cols="12">
                <v-card variant="outlined" class="mb-2">
                  <v-card-text class="pa-2">
                    <v-row dense>
                      <v-col cols="12" md="2">
                        <Hdatepicker
                          v-model="installmentData.firstDate"
                          label="تاریخ اولین قسط"
                          density="compact"
                          hide-details
                        />
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-select
                          v-model="installmentData.calculationType"
                          :items="[
                            { title: 'محاسبه بر اساس تعداد', value: 'count' },
                            { title: 'محاسبه بر اساس مبلغ هر قسط', value: 'amount' }
                          ]"
                          label="نوع محاسبه"
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-select>
                      </v-col>
                      <v-col cols="12" md="2" v-if="installmentData.calculationType === 'count'">
                        <Hnumberinput
                          v-model="installmentData.count"
                          label="تعداد اقساط"
                          density="compact"
                          hide-details
                        />
                      </v-col>
                      <v-col cols="12" md="2" v-if="installmentData.calculationType === 'amount'">
                        <Hnumberinput
                          v-model="installmentData.installmentAmount"
                          label="مبلغ هر قسط"
                          density="compact"
                          hide-details
                        />
                      </v-col>
                      <v-col cols="12" md="2">
                        <v-select
                          v-model="installmentData.interestType"
                          :items="interestTypes"
                          label="نوع محاسبه سود"
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-select>
                      </v-col>
                      <v-col cols="12" md="2">
                        <Hnumberinput
                          v-model="installmentData.interestRate"
                          label="نرخ سود (درصد)"
                          density="compact"
                          hide-details
                        />
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>

              <!-- بخش پیش پرداخت و جریمه -->
              <v-col cols="12">
                <v-card variant="outlined" class="mb-2">
                  <v-card-text class="pa-2">
                    <v-row dense>
                      <v-col cols="12" md="3">
                        <Hnumberinput
                          v-model="installmentData.prepayment"
                          label="پیش پرداخت"
                          density="compact"
                          hide-details
                        />
                      </v-col>
                      <v-col cols="12" md="3">
                        <Hnumberinput
                          v-model="installmentData.latePaymentPenalty"
                          label="جریمه تاخیر (درصد روزانه)"
                          density="compact"
                          hide-details
                        />
                      </v-col>
                      <v-col cols="12" md="3">
                        <v-text-field
                          :model-value="formatNumber(dailyPenaltyAmount)"
                          label="مبلغ جریمه روزانه"
                          readonly
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="3" class="d-flex align-center">
                        <v-btn
                          color="primary"
                          @click="calculateInstallments"
                          :disabled="!canCalculate"
                          :loading="isCalculating"
                          block
                        >
                          محاسبه و نمایش اقساط
                        </v-btn>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
          <v-divider></v-divider>
          <v-card-text class="pa-2">
            <v-data-table
              :headers="installmentHeaders"
              :items="installments"
              :loading="loading"
              class="elevation-0 rounded-lg"
              density="compact"
            >
              <template v-slot:item.amount="{ item }">
                {{ formatNumber(item.amount) }}
              </template>
              <template v-slot:item.dueDate="{ item }">
                {{ formatDate(item.dueDate) }}
              </template>
              <template v-slot:item.status="{ item }">
                <v-chip
                  :color="item.status === 'پرداخت شده' ? 'success' : 'warning'"
                  size="small"
                >
                  {{ item.status }}
                </v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn
                  icon="mdi-pencil"
                  variant="text"
                  size="small"
                  color="primary"
                  class="me-2"
                  @click="editInstallment(item)"
                ></v-btn>
                <v-btn
                  icon="mdi-delete"
                  variant="text"
                  size="small"
                  color="error"
                  @click="deleteInstallment(item)"
                ></v-btn>
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-container>
    </v-window-item>

    <v-window-item value="settings">
      <v-container fluid class="pa-4">
        <v-row>
          <v-col cols="12" md="6">
            <v-card class="rounded-lg">
              <v-card-text class="pa-4">
                <v-switch
                  v-model="settings.sendNotification"
                  label="ارسال اعلان سررسید"
                  color="primary"
                  hide-details
                  class="mb-4"
                ></v-switch>
                <v-text-field
                  v-model="settings.notificationDays"
                  label="تعداد روز قبل از سررسید برای اعلان"
                  type="number"
                  variant="outlined"
                  density="compact"
                  hide-details
                  class="mb-4"
                ></v-text-field>
                <v-switch
                  v-model="settings.sendSMS"
                  label="ارسال پیامک به مشتری"
                  color="primary"
                  hide-details
                  class="mb-4"
                ></v-switch>
                <v-text-field
                  v-model="settings.smsDays"
                  label="تعداد روز قبل از سررسید برای ارسال پیامک"
                  type="number"
                  variant="outlined"
                  density="compact"
                  hide-details
                  class="mb-4"
                ></v-text-field>
              </v-card-text>
            </v-card>
      </v-col>
      </v-row>
  </v-container>
    </v-window-item>
  </v-window>
  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    :timeout="3000"
  >
    {{ snackbar.text }}
    <template v-slot:actions>
      <v-btn
        color="white"
        variant="text"
        @click="snackbar.show = false"
      >
        بستن
      </v-btn>
    </template>
  </v-snackbar>

  <!-- دیالوگ ویرایش -->
  <v-dialog
    v-model="dialog.show"
    :max-width="dialog.editMode ? '500' : '400'"
  >
    <v-card>
      <v-card-title>{{ dialog.title }}</v-card-title>
      <v-card-text>
        <template v-if="dialog.editMode">
          <v-row>
            <v-col cols="12">
              <Hnumberinput
                v-model="dialog.installmentToEdit.amount"
                label="مبلغ قسط"
                density="compact"
                hide-details
              />
            </v-col>
            <v-col cols="12">
              <Hdatepicker
                v-model="dialog.installmentToEdit.dueDate"
                label="تاریخ سررسید"
                density="compact"
                hide-details
              />
            </v-col>
          </v-row>
        </template>
        <template v-else>
          {{ dialog.content }}
        </template>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          color="error"
          variant="text"
          @click="dialog.onCancel"
        >
          {{ dialog.cancelText }}
        </v-btn>
        <v-btn
          :color="dialog.type === 'delete' ? 'error' : 'primary'"
          @click="dialog.onConfirm"
        >
          {{ dialog.confirmText }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import Hdocsearch from '@/components/forms/Hdocsearch.vue';
import Hdatepicker from '@/components/forms/Hdatepicker.vue';
import Hnumberinput from '@/components/forms/Hnumberinput.vue';
import moment from 'moment-jalaali';
import axios from 'axios';

export default {
  name: 'GhestaMod',
  components: {
    Hdocsearch,
    Hdatepicker,
    Hnumberinput
  },
  data() {
    return {
      selectedInvoice: null,
      loading: false,
      activeTab: 'invoice',
      installments: [],
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      settings: {
        sendNotification: true,
        notificationDays: 3,
        sendSMS: false,
        smsDays: 1
      },
      installmentData: {
        firstDate: null,
        count: 12,
        interestType: 'monthly',
        interestRate: 2,
        prepayment: 0,
        latePaymentPenalty: 0,
        calculationType: 'count',
        installmentAmount: 0
      },
      interestTypes: [
        { title: 'سود ساده ماهانه', value: 'monthly' },
        { title: 'سود ساده سالانه', value: 'yearly' }
      ],
      installmentHeaders: [
        { title: 'شماره قسط', key: 'number', align: 'center' },
        { title: 'مبلغ', key: 'amount', align: 'center' },
        { title: 'تاریخ سررسید', key: 'dueDate', align: 'center' },
        { title: 'وضعیت', key: 'status', align: 'center' },
        { title: 'عملیات', key: 'actions', align: 'center', sortable: false }
      ],
      dialog: {
        show: false,
        title: '',
        content: '',
        type: 'info',
        confirmText: 'تایید',
        cancelText: 'انصراف',
        onConfirm: null,
        onCancel: null,
        editMode: false,
        installmentToEdit: null
      },
      isCalculating: false
    }
  },
  computed: {
    invoiceCode() {
      return this.selectedInvoice?.code || '';
    },
    invoiceDate() {
      return this.selectedInvoice?.date || '';
    },
    invoiceDes() {
      return this.selectedInvoice?.des || '';
    },
    invoiceAmount() {
      return this.selectedInvoice?.amount || '';
    },
    invoiceStatus() {
      return this.selectedInvoice?.status || '';
    },
    customerName() {
      return this.selectedInvoice?.personName || '';
    },
    customerNikename() {
      return this.selectedInvoice?.personNikename || '';
    },
    canCalculate() {
      return this.selectedInvoice && 
             this.selectedInvoice.status !== 'تسویه شده' &&
             this.installmentData.firstDate && 
             ((this.installmentData.calculationType === 'count' && this.installmentData.count > 0) ||
              (this.installmentData.calculationType === 'amount' && this.installmentData.installmentAmount > 0));
    },
    totalPaidAmount() {
      if (!this.selectedInvoice?.relatedDocs) return 0;
      return this.selectedInvoice.relatedDocs.reduce((sum, doc) => sum + doc.amount, 0);
    },
    remainingAmount() {
      if (!this.selectedInvoice?.amount) return 0;
      return this.selectedInvoice.amount - this.totalPaidAmount;
    },
    totalInterest() {
      if (!this.installments.length) return 0;
      const principalAmount = this.remainingAmount - (this.installmentData.prepayment || 0);
      const interestRate = Number(this.installmentData.interestRate) || 0;
      const count = this.installmentData.calculationType === 'amount' ? 
        this.installments.filter(item => item.number > 0).length : 
        Number(this.installmentData.count);

      let totalInterest;
      switch (this.installmentData.interestType) {
        case 'monthly':
          // سود کل = مبلغ اصلی * نرخ سود ماهانه * تعداد اقساط
          totalInterest = principalAmount * (interestRate / 100) * count;
          break;
        case 'yearly':
          // سود کل = مبلغ اصلی * (نرخ سود سالانه / 12) * تعداد اقساط
          totalInterest = principalAmount * (interestRate / 100 / 12) * count;
          break;
        default:
          totalInterest = 0;
      }
      return Math.round(totalInterest);
    },
    monthlyInterest() {
      if (!this.installments.length) return 0;
      const principalAmount = this.remainingAmount - (this.installmentData.prepayment || 0);
      const interestRate = Number(this.installmentData.interestRate) || 0;
      const count = this.installmentData.calculationType === 'amount' ? 
        this.installments.filter(item => item.number > 0).length : 
        Number(this.installmentData.count);

      let monthlyInterest;
      if (this.installmentData.calculationType === 'amount') {
        // در حالت محاسبه بر اساس مبلغ هر قسط
        const installmentAmount = Number(this.installmentData.installmentAmount);
        switch (this.installmentData.interestType) {
          case 'monthly':
            // سود ماهانه = مبلغ قسط - (مبلغ اصلی / تعداد اقساط)
            monthlyInterest = installmentAmount - (principalAmount / count);
            break;
          case 'yearly':
            // سود ماهانه = مبلغ قسط - (مبلغ اصلی / تعداد اقساط)
            monthlyInterest = installmentAmount - (principalAmount / count);
            break;
          default:
            monthlyInterest = 0;
        }
      } else {
        // در حالت محاسبه بر اساس تعداد اقساط
        switch (this.installmentData.interestType) {
          case 'monthly':
            monthlyInterest = principalAmount * (interestRate / 100);
            break;
          case 'yearly':
            monthlyInterest = principalAmount * (interestRate / 100 / 12);
            break;
          default:
            monthlyInterest = 0;
        }
      }
      return Math.round(monthlyInterest);
    },
    calculatedInstallments() {
      if (!this.selectedInvoice || !this.installmentData.firstDate) {
        return [];
      }

      const totalAmount = this.remainingAmount;
      const prepayment = Number(this.installmentData.prepayment) || 0;
      const remainingAmount = totalAmount - prepayment;
      const interestRate = Number(this.installmentData.interestRate) || 0;
      const interestType = this.installmentData.interestType;
      
      let count, monthlyPayment;
      
      if (this.installmentData.calculationType === 'amount') {
        monthlyPayment = Number(this.installmentData.installmentAmount);
        if (monthlyPayment <= 0) {
          this.installments = [];
          return;
        }

        // محاسبه تعداد اقساط بر اساس مبلغ هر قسط
        switch (interestType) {
          case 'monthly': {
            // برای سود ساده ماهانه
            const monthlyInterest = interestRate / 100;
            const principalPerMonth = remainingAmount / monthlyPayment;
            const interestPerMonth = monthlyInterest;
            count = Math.ceil(principalPerMonth / (1 - interestPerMonth));
            break;
          }
          case 'yearly': {
            // برای سود ساده سالانه
            const yearlyInterest = interestRate / 100;
            const monthlyInterest = yearlyInterest / 12;
            const principalPerMonth = remainingAmount / monthlyPayment;
            const interestPerMonth = monthlyInterest;
            count = Math.ceil(principalPerMonth / (1 - interestPerMonth));
            break;
          }
          default:
            count = 0;
        }

        // محدود کردن تعداد اقساط به یک عدد منطقی
        count = Math.min(Math.max(count, 1), 360); // حداکثر 30 سال
      } else {
        count = Number(this.installmentData.count) || 0;
        if (count <= 0) {
          this.installments = [];
          return;
        }
        
        switch (interestType) {
          case 'monthly': {
            const monthlyInterest = interestRate / 100;
            const totalInterest = monthlyInterest * count;
            monthlyPayment = (remainingAmount * (1 + totalInterest)) / count;
            break;
          }
          case 'yearly': {
            const yearlyInterest = interestRate / 100;
            const monthlyInterest = yearlyInterest / 12;
            const totalInterest = monthlyInterest * count;
            monthlyPayment = (remainingAmount * (1 + totalInterest)) / count;
            break;
          }
          default:
            monthlyPayment = 0;
        }
      }

      // ایجاد اقساط
      const newInstallments = [];
      let currentDate = moment(this.installmentData.firstDate, 'jYYYY/jMM/jDD').toDate();
      let remainingTotal = remainingAmount;

      for (let i = 1; i <= count; i++) {
        let installmentAmount;
        if (i === count) {
          installmentAmount = remainingTotal;
        } else {
          installmentAmount = Math.round(monthlyPayment);
          remainingTotal -= installmentAmount;
        }

        newInstallments.push({
          number: i,
          amount: installmentAmount,
          dueDate: moment(currentDate).format('jYYYY/jMM/jDD'),
          status: 'پرداخت نشده',
          latePaymentPenalty: this.installmentData.latePaymentPenalty
        });

        currentDate = moment(currentDate).add(1, 'month').toDate();
      }

      if (prepayment > 0) {
        newInstallments.unshift({
          number: 0,
          amount: prepayment,
          dueDate: moment().format('jYYYY/jMM/jDD'),
          status: 'پرداخت شده',
          latePaymentPenalty: 0
        });
      }

      this.installments = newInstallments;
    },
    dailyPenaltyAmount() {
      if (!this.installmentData.installmentAmount) {
        // اگر مبلغ هر قسط مشخص نشده، از مبلغ کل استفاده کن
        const totalAmount = this.remainingAmount;
        const count = this.installmentData.calculationType === 'amount' ? 
          this.installments.filter(item => item.number > 0).length : 
          Number(this.installmentData.count);
        
        if (count <= 0) return 0;
        
        const installmentAmount = totalAmount / count;
        return Math.round(installmentAmount * (this.installmentData.latePaymentPenalty / 100));
      }
      
      return Math.round(this.installmentData.installmentAmount * (this.installmentData.latePaymentPenalty / 100));
    }
  },
  methods: {
    showMessage(text, color = 'success') {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    },
    async saveInvoice() {
      if (!this.selectedInvoice) {
        this.showMessage('لطفا یک فاکتور انتخاب کنید', 'error');
        return;
      }

      if (this.selectedInvoice.status === 'تسویه شده') {
        this.showMessage('این فاکتور قبلاً تسویه شده است و قابل تقسیط نیست', 'error');
        return;
      }

      try {
        this.loading = true;
        
        const data = {
          hesabdariDocId: this.selectedInvoice.id,
          count: this.installmentData.count,
          profitPercent: this.installmentData.interestRate,
          profitAmount: this.totalInterest,
          profitType: this.installmentData.interestType,
          daysPay: this.installmentData.latePaymentPenalty,
          personId: this.selectedInvoice.person?.id,
          items: this.installments.map(item => ({
            date: item.dueDate,
            amount: item.amount,
            num: item.number
          }))
        };

        let response;
        if (this.$route.params.id) {
          response = await axios.post(`/api/plugins/ghesta/invoices/edit/${this.$route.params.id}`, data);
        } else {
          response = await axios.post('/api/plugins/ghesta/invoices/add', data);
        }

        if (response.data.result === 1) {
          this.showMessage('فاکتور با موفقیت ثبت شد', 'success');
          this.$router.push('/acc/plugins/ghesta/list');
        }
      } catch (error) {
        this.showMessage('خطا در ثبت فاکتور', 'error');
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    async deleteInvoice() {
      if (!this.$route.params.id) {
        this.showMessage('شناسه فاکتور یافت نشد', 'error');
        return;
      }

      try {
        this.loading = true;
        const response = await axios.delete(`/api/plugins/ghesta/invoice/${this.$route.params.id}`);
        
        if (response.data.result === 1) {
          this.showMessage('فاکتور با موفقیت حذف شد', 'success');
          this.$router.push('/acc/plugins/ghesta/list');
        }
      } catch (error) {
        this.showMessage('خطا در حذف فاکتور', 'error');
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    editInstallment(item) {
      this.dialog = {
        show: true,
        title: 'ویرایش قسط',
        type: 'edit',
        editMode: true,
        installmentToEdit: { ...item },
        confirmText: 'ذخیره',
        cancelText: 'انصراف',
        onConfirm: () => {
          const index = this.installments.findIndex(i => i.number === this.dialog.installmentToEdit.number);
          if (index !== -1) {
            // بررسی تغییرات
            const oldAmount = this.installments[index].amount;
            const newAmount = this.dialog.installmentToEdit.amount;
            
            // اگر مبلغ تغییر کرده، باید مبلغ کل را به‌روزرسانی کنیم
            if (oldAmount !== newAmount) {
              const diff = newAmount - oldAmount;
              const remainingAmount = this.remainingAmount + diff;
              
              // اگر مبلغ کل منفی شد، اجازه تغییر ندهیم
              if (remainingAmount < 0) {
                this.showMessage('مبلغ قسط نمی‌تواند بیشتر از مبلغ باقیمانده باشد', 'error');
                return;
              }
            }
            
            this.installments[index] = { ...this.dialog.installmentToEdit };
            this.showMessage('قسط با موفقیت ویرایش شد', 'success');
          }
          this.dialog.show = false;
        },
        onCancel: () => {
          this.dialog.show = false;
        }
      };
    },

    deleteInstallment(item) {
      this.dialog = {
        show: true,
        title: 'حذف قسط',
        type: 'delete',
        content: `آیا از حذف قسط شماره ${item.number} اطمینان دارید؟`,
        confirmText: 'حذف',
        cancelText: 'انصراف',
        onConfirm: () => {
          const index = this.installments.findIndex(i => i.number === item.number);
          if (index !== -1) {
            // اگر قسط پرداخت شده است، اجازه حذف ندهیم
            if (item.status === 'پرداخت شده') {
              this.showMessage('قسط پرداخت شده قابل حذف نیست', 'error');
              this.dialog.show = false;
              return;
            }
            
            // حذف قسط
            this.installments.splice(index, 1);
            
            // به‌روزرسانی شماره اقساط
            this.installments.forEach((installment, idx) => {
              installment.number = idx + 1;
            });
            
            this.showMessage('قسط با موفقیت حذف شد', 'success');
          }
          this.dialog.show = false;
        },
        onCancel: () => {
          this.dialog.show = false;
        }
      };
    },

    formatNumber(number) {
      return new Intl.NumberFormat('fa-IR').format(number);
    },
    formatDate(date) {
      if (!date) return '';
      // اگر تاریخ به صورت رشته شمسی است، همان را برگردان
      if (typeof date === 'string' && date.includes('/')) {
        return date;
      }
      // در غیر این صورت، تاریخ میلادی را به شمسی تبدیل کن
      return moment(date).format('jYYYY/jMM/jDD');
    },
    calculateInstallments() {
      if (!this.canCalculate) {
        this.showMessage('لطفا تمام اطلاعات مورد نیاز را وارد کنید', 'error');
        return;
      }

      if (this.isCalculating) {
        return;
      }
      
      try {
        this.isCalculating = true;
        this.loading = true;

        const totalAmount = this.remainingAmount;
        const prepayment = Number(this.installmentData.prepayment) || 0;
        const remainingAmount = totalAmount - prepayment;
        const interestRate = Number(this.installmentData.interestRate) || 0;
        const interestType = this.installmentData.interestType;
        
        let count, monthlyPayment;
        
        if (this.installmentData.calculationType === 'amount') {
          monthlyPayment = Number(this.installmentData.installmentAmount);
          if (monthlyPayment <= 0) {
            this.installments = [];
            this.showMessage('مبلغ هر قسط باید بزرگتر از صفر باشد', 'error');
            return;
          }

          // محاسبه تعداد اقساط بر اساس مبلغ هر قسط
          switch (interestType) {
            case 'monthly': {
              // برای سود ساده ماهانه
              const monthlyInterest = interestRate / 100;
              const principalPerMonth = remainingAmount / monthlyPayment;
              const interestPerMonth = monthlyInterest;
              count = Math.ceil(principalPerMonth / (1 - interestPerMonth));
              break;
            }
            case 'yearly': {
              // برای سود ساده سالانه
              const yearlyInterest = interestRate / 100;
              const monthlyInterest = yearlyInterest / 12;
              const principalPerMonth = remainingAmount / monthlyPayment;
              const interestPerMonth = monthlyInterest;
              count = Math.ceil(principalPerMonth / (1 - interestPerMonth));
              break;
            }
            default:
              count = 0;
          }

          // محدود کردن تعداد اقساط به یک عدد منطقی
          count = Math.min(Math.max(count, 1), 360); // حداکثر 30 سال
        } else {
          count = Number(this.installmentData.count) || 0;
          if (count <= 0) {
            this.installments = [];
            this.showMessage('تعداد اقساط باید بزرگتر از صفر باشد', 'error');
            return;
          }
          
          switch (interestType) {
            case 'monthly': {
              const monthlyInterest = interestRate / 100;
              const totalInterest = monthlyInterest * count;
              monthlyPayment = (remainingAmount * (1 + totalInterest)) / count;
              break;
            }
            case 'yearly': {
              const yearlyInterest = interestRate / 100;
              const monthlyInterest = yearlyInterest / 12;
              const totalInterest = monthlyInterest * count;
              monthlyPayment = (remainingAmount * (1 + totalInterest)) / count;
              break;
            }
            default:
              monthlyPayment = 0;
          }
        }

        // ایجاد اقساط
        const newInstallments = [];
        let currentDate = moment(this.installmentData.firstDate, 'jYYYY/jMM/jDD').toDate();
        let remainingTotal = remainingAmount;

        for (let i = 1; i <= count; i++) {
          let installmentAmount;
          if (i === count) {
            installmentAmount = remainingTotal;
          } else {
            installmentAmount = Math.round(monthlyPayment);
            remainingTotal -= installmentAmount;
          }

          newInstallments.push({
            number: i,
            amount: installmentAmount,
            dueDate: moment(currentDate).format('jYYYY/jMM/jDD'),
            status: 'پرداخت نشده',
            latePaymentPenalty: this.installmentData.latePaymentPenalty
          });

          currentDate = moment(currentDate).add(1, 'month').toDate();
        }

        if (prepayment > 0) {
          newInstallments.unshift({
            number: 0,
            amount: prepayment,
            dueDate: moment().format('jYYYY/jMM/jDD'),
            status: 'پرداخت شده',
            latePaymentPenalty: 0
          });
        }

        this.installments = newInstallments;
        this.showMessage('اقساط با موفقیت محاسبه شد', 'success');
      } catch (error) {
        console.error('خطا در محاسبه اقساط:', error);
        this.showMessage('خطا در محاسبه اقساط', 'error');
      } finally {
        this.isCalculating = false;
        this.loading = false;
      }
    },
    async loadInvoiceData() {
      try {
        this.loading = true;
        const response = await axios.get(`/api/plugins/ghesta/invoices/${this.$route.params.id}`);
        const data = response.data;
        
        // تنظیم اطلاعات فاکتور
        this.selectedInvoice = {
          id: data.id,
          code: data.id.toString(),
          date: moment.unix(data.dateSubmit).format('jYYYY/jMM/jDD'),
          dateSubmit: data.dateSubmit,
          des: 'فاکتور اقساطی',
          amount: data.profitAmount,
          status: 'در انتظار پرداخت',
          person: data.person,
          personName: data.person.name || '',
          personNikename: data.person.nikename || ''
        };
        
        // تنظیم اطلاعات اقساط
        this.installmentData = {
          count: parseInt(data.count),
          interestRate: parseFloat(data.profitPercent),
          interestType: data.profitType,
          latePaymentPenalty: parseFloat(data.daysPay),
          firstDate: data.items[0]?.date || null,
          calculationType: 'count',
          prepayment: 0
        };
        
        // تنظیم اقساط
        this.installments = data.items.map(item => ({
          number: parseInt(item.num),
          amount: parseFloat(item.amount),
          dueDate: item.date,
          status: item.hesabdariDoc ? 'پرداخت شده' : 'پرداخت نشده',
          latePaymentPenalty: parseFloat(data.daysPay)
        }));

        // محاسبه مجدد اقساط برای نمایش صحیح
        this.$nextTick(() => {
          this.calculateInstallments();
        });
        
      } catch (error) {
        this.showMessage('خطا در دریافت اطلاعات فاکتور', 'error');
        console.error(error);
      } finally {
        this.loading = false;
      }
    }
  },
  watch: {
    selectedInvoice: {
      handler(newVal) {
        if (newVal) {
          if (newVal.status === 'تسویه شده') {
            this.showMessage('این فاکتور قبلاً تسویه شده است و قابل تقسیط نیست', 'error');
            this.selectedInvoice = null;
          } else if (newVal.relatedDocs && newVal.relatedDocs.length > 0) {
            this.showMessage('این فاکتور دارای پرداختی قبلی است. مبلغ باقیمانده برای تقسیط محاسبه خواهد شد.', 'warning');
          }
          this.installments = []; // پاک کردن جدول در زمان تغییر فاکتور
        }
      },
      immediate: true
    },
    'installmentData.count': {
      handler(newVal) {
        if (newVal && this.installmentData.calculationType === 'count') {
          this.calculateInstallments();
        }
      }
    },
    'installmentData.interestRate': {
      handler(newVal, oldVal) {
        if (newVal && !oldVal) {
          // فقط در اولین بار که نرخ سود وارد می‌شود، جریمه تاخیر را محاسبه کن
          const penalty = Number(newVal) / 30;
          this.installmentData.latePaymentPenalty = Number(penalty.toFixed(2));
        }
      },
      immediate: true
    },
    'installmentData.latePaymentPenalty': {
      handler(newVal) {
        // هر زمان که جریمه تاخیر تغییر کرد، مبلغ جریمه روزانه را محاسبه کن
        if (newVal) {
          this.$forceUpdate(); // برای به‌روزرسانی computed property
        }
      }
    }
  },
  beforeDestroy() {
    if (this.calculationTimeout) {
      clearTimeout(this.calculationTimeout);
    }
  },
  mounted() {
    if (this.$route.params.id) {
      this.loadInvoiceData();
    }
  }
}
</script>

<style scoped>
.tabs-container {
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

.window-container {
  background: transparent;
}

.v-card {
  border: 1px solid rgba(0, 0, 0, 0.12);
}

:deep(.v-tab) {
  text-transform: none;
  letter-spacing: normal;
  font-weight: 500;
}

:deep(.v-data-table) {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
}

:deep(.v-data-table-header th) {
  font-weight: 600;
  background-color: rgb(var(--v-theme-surface));
}

:deep(.vpd-main) {
  position: fixed !important;
  z-index: 999999 !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  pointer-events: auto !important;
}

:deep(.vpd-wrapper) {
  position: fixed !important;
  z-index: 999999 !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  pointer-events: auto !important;
}

:deep(.vpd-container) {
  position: fixed !important;
  z-index: 999999 !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  pointer-events: auto !important;
}

:deep(.vpd-content) {
  position: fixed !important;
  z-index: 999999 !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;
  pointer-events: auto !important;
}

:deep(.vpd-overlay) {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  background: rgba(0, 0, 0, 0.5) !important;
  z-index: 999998 !important;
  pointer-events: auto !important;
}

:deep(.v-application) {
  position: relative !important;
}

:deep(.v-application--wrap) {
  position: relative !important;
}

:deep(.v-main) {
  position: relative !important;
}

:deep(.v-main__wrap) {
  position: relative !important;
}

:deep(.v-container) {
  position: relative !important;
}

:deep(.v-row) {
  position: relative !important;
}

:deep(.v-col) {
  position: relative !important;
}

:deep(.v-card) {
  position: relative !important;
}

:deep(.v-field) {
  position: relative !important;
}

:deep(.v-field__input) {
  position: relative !important;
}
</style> 