<script lang="ts">
import { defineComponent } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "rec",
  props: {
    totalAmount: Number,
    originalDoc: String,
    person: [String, Number],
    windowsState: Object,
  },
  data: () => {
    return {
      submitedDoc: {},
      des: '',
      items: [],
      date: '',
      year: {},
      listBanks: [],
      listSalarys: [],
      listCashdesks: [],
      listChequesForPay: [],
      totalPays: 0,
      currencyConfig: {
        masked: false,
        prefix: '',
        suffix: 'ریال',
        thousands: ',',
        decimal: '.',
        precision: 0,
        disableNegative: false,
        disabled: false,
        min: 0,
        max: null,
        allowBlank: false,
        minimumNumberOfCharacters: 0,
        shouldRound: true,
        focusOnRight: true,
      },
    }
  },
  methods: {
    fillWithTotal(pay) {
      pay.bs = this.$props.totalAmount;
    },
    addItem(type) {
      let obj = {};
      let canAdd = true;
      if (type == 'bank') {
        if (this.listBanks.length == 0) {
          Swal.fire({
            text: 'ابتدا یک حساب بانکی ایجاد کنید.در حال حاضر هیچ بانکی تعریف نشده است.',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
          canAdd = false;
        }
        else {
          obj = {
            id: '',
            type: 'bank',
            bank: {},
            cashdesk: {},
            salary: {},
            cheque: {},
            chequeforpay: {},
            bs: 0,
            bd: 0,
            des: '',
            table: 5,
            referral: ''
          }
        }
      }
      else if (type == 'cashdesk') {
        if (this.listCashdesks.length == 0) {
          Swal.fire({
            text: 'ابتدا یک صندوق ایجاد کنید.در حال حاضر هیچ صندوقی تعریف نشده است.',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
          canAdd = false;
        }
        obj = {
          id: '',
          type: 'cashdesk',
          bank: {},
          cashdesk: {},
          salary: {},
          cheque: {},
          chequeforpay: {},
          bs: 0,
          bd: 0,
          des: '',
          table: 121,
          referral: ''
        }
      }
      else if (type == 'chequeForPay') {
        if (this.listChequesForPay.length == 0) {
          Swal.fire({
            text: 'هیچ چک قابل واگذاری یافت نشد.',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
          canAdd = false;
        }
        else {
          obj = {
            id: '',
            type: 'chequeforpay',
            bank: {},
            cashdesk: {},
            salary: {},
            cheque: {},
            chequeforpay: {},
            bs: 0,
            bd: 0,
            des: '',
            table: 125,
            referral: ''
          }
        }

      }
      else if (type == 'salary') {
        if (this.listSalarys.length == 0) {
          Swal.fire({
            text: 'ابتدا یک تخواه گردان ایجاد کنید.در حال حاضر هیچ تنخواه گردانی تعریف نشده است.',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
          canAdd = false;
        }
        obj = {
          id: '',
          type: 'salary',
          bank: {},
          cashdesk: {},
          salary: {},
          cheque: {},
          chequeforpay: {},
          bs: 0,
          bd: 0,
          des: '',
          table: 122,
          referral: ''
        }
      }
      if (canAdd) {
        this.items.push(obj);
      }
    },
    deleteItem(key) {
      this.items.splice(key, 1);
      this.calc();
    },
    loadData() {
      //get list of banks
      axios.post('/api/bank/list').then((response) => {
        this.listBanks = response.data;
      })
      //get list of salarys
      axios.post('/api/salary/list').then((response) => {
        this.listSalarys = response.data;
      })
      //get list of cashdesks
      axios.post('/api/cashdesk/list').then((response) => {
        this.listCashdesks = response.data;
      })
      //get list of cheques for pay
      axios.post('/api/cheque/list/forpay').then((response) => {
        this.listChequesForPay = response.data;
      });
      //load year
      axios.post('/api/year/get').then((response) => {
        this.year = response.data;
        this.date = response.data.now;
      })
    },
    calc() {
      this.totalPays = 0;
      this.items.forEach((value) => {
        this.totalPays += parseInt(value.bs);
      })
    },
    async submit() {
      let errors = [];
      if (this.$props.totalAmount < this.totalPays) {
        errors.push('مبالغ وارد شده بیشتر از مبلغ فاکتور است.');
      }
      this.items.forEach((element, index) => {
        if (element.bs == 0) {
          errors.push('مبلغ صفر در ردیف ' + (index + 1) + ' نا معتبر است.');
        }
        //check type selected
        if (element.type == 'bank') {
          if (element.bank == null || Object.keys(element.bank).length == 0) {
            errors.push('بانک در ردیف  ' + (index + 1) + ' انتخاب نشده است.');
          }
        }
        else if (element.type == 'salary') {
          if (element.salary == null || Object.keys(element.salary).length == 0) {
            errors.push('تنخواه گردان در ردیف  ' + (index + 1) + ' انتخاب نشده است.');
          }
        }
        else if (element.type == 'cashdesk') {
          if (element.cashdesk == null || Object.keys(element.cashdesk).length == 0) {
            errors.push('صندوق در ردیف  ' + (index + 1) + ' انتخاب نشده است.');
          }
        }
      })
      if (this.items.length == 0) {
        Swal.fire({
          text: 'هیچ پرداختی ثبت نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else if (errors.length != 0) {
        //show errors
        let errorHtml = '<ul class="justify-content-center">';
        errors.forEach((element) => {
          errorHtml += '<div class="">' + element + '</div>';
        });
        errorHtml += '</ul>';
        Swal.fire({
          html: errorHtml,
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else {
        let rows = [...this.items];
        rows.forEach((element) => {
          if (element.type == 'bank') {
            element.id = element.bank.id;
          }
          else if (element.type == 'salary') {
            element.id = element.salary.id;
          }
          else if (element.type == 'cashdesk') {
            element.id = element.cashdesk.id;
          }
          if (element.des == '') {
            element.des = 'پرداخت وجه فاکتور شماره ' + this.$props.originalDoc
          }
        });
        rows.push({
          id: this.$props.person,
          type: 'person',
          bd: this.totalPays,
          bs: 0,
          table: 3,
          des: 'پرداخت وجه فاکتور شماره ' + this.$filters.formatNumber(this.$props.originalDoc)
        });
        if (this.des == '') {
          this.des = 'پرداخت وجه فاکتور شماره ' + this.$filters.formatNumber(this.originalDoc);
        }
        axios.post('/api/accounting/insert', {
          date: this.date,
          des: this.des,
          type: 'buy_send',
          update: null,
          rows: rows,
          related: this.$props.originalDoc
        }).then((response) => {
          if (response.data.result == '1') {
            Swal.fire({
              text: 'سند با موفقیت ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then((result) => {
              this.submitedDoc = response.data.doc;
              this.$props.windowsState.submited = true;
            });
          }
          else if (response.data.result == '4') {
            Swal.fire({
              text: response.data.msg,
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          }
        })
      }
    }
  },
  mounted() {
    this.loadData();
  }
})
</script>

<template>
  <div class="row mb-3">
    <div class="col-sm-12 col-md-5 mb-2">
      <date-picker class="form-control" v-model="date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
        :min="year.start" :max="year.end" />
    </div>
    <div class="col-sm-12 col-md-7 mb-2">
      <input type="text" class="form-control" v-model="des" placeholder="شرح">
    </div>
  </div>
  <div class="row">
    <div class="col-12 text-center">
      <div class="input-group text-center justify-content-center">
        <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown"
          aria-expanded="false">
          افزودن پرداخت
        </button>
        <ul class="dropdown-menu">
          <li><button type="button" @click="addItem('bank')" class="dropdown-item"><i class="fa fa-dot-circle"></i> حساب
              بانکی</button></li>
          <li><button type="button" @click="addItem('cashdesk')" class="dropdown-item"><i class="fa fa-dot-circle"></i>
              صندوق</button></li>
          <li><button type="button" @click="addItem('salary')" class="dropdown-item"><i class="fa fa-dot-circle"></i>
              تنخواه گردان</button></li>
          <li><button disabled="disabled" type="button" @click="addItem('cheque')" class="dropdown-item"><i
                class="fa fa-dot-circle"></i>چک</button></li>
          <li><button disabled="disabled" type="button" @click="addItem('chequeForPay')" class="dropdown-item"><i
                class="fa fa-dot-circle"></i>خرج چک</button></li>
        </ul>
        <span class="input-group-text">
          مجموع:
          {{ $filters.formatNumber(parseInt(this.totalPays)) }}
          {{ $filters.getActiveMoney().shortName }}
        </span>
        <span class="input-group-text">
          باقی مانده:
          {{ $filters.formatNumber(parseInt(this.$props.totalAmount) - parseInt(this.totalPays)) }}
          {{ $filters.getActiveMoney().shortName }}
        </span>
        <button @click="submit()" class="btn btn-primary" type="button">
          <i class="fa fa-save me-2"></i>
          ثبت
        </button>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <p class="mb-1">پرداخت‌ها:</p>
        <div v-show="items.length === 0" class="alert alert-info mx-3"><i class="fa fa-info"></i>
          پرداختی افزوده نشده است!
        </div>
      </div>
    </div>
    <div v-for="(pay, key) in items" class="col-12 ps-2">
      <i class="card my-1">
        <div class="card-body p-1">
          <div class="row">
            <div class="col-2 text-center">
              <h6 class="mb-1 text-start"><span class="badge bg-primary-light">{{ key + 1 }}</span></h6>
              <img v-show="pay.type == 'bank'" src="/img/icons/bank.jpg" class="img-fluid" />
              <img v-show="pay.type == 'cashdesk'" src="/img/icons/cashdesk.jpg" class="img-fluid" />
              <img v-show="pay.type == 'salary'" src="/img/icons/salary.jpg" class="img-fluid" />
              <img v-show="pay.type == 'cheque'" src="/img/icons/check.jpg" class="img-fluid" />
              <img v-show="pay.type == 'chequeforpay'" src="/img/icons/check.jpg" class="img-fluid" />
              <button @click="deleteItem(key)" type="button" class="btn text-danger mt-2">
                <i class="fa fa-trash"></i>
              </button>
            </div>
            <div class="col-10 my-2 ps-0">
              <div class="row">
                <div class="col-sm-12 col-md-6">
                  <div v-show="pay.type == 'bank'" class="">
                    <label class="form-label">بانک</label>
                    <v-cob dir="rtl" :options="listBanks" label="name" v-model="pay.bank">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                    </v-cob>
                  </div>

                  <div v-show="pay.type == 'chequeforpay'" class="">
                    <label class="form-label">چک‌های قابل واگذاری</label>
                    <v-cob dir="rtl" :options="listChequesForPay" label="label" v-model="pay.chequeforpay">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                    </v-cob>
                  </div>

                  <div v-show="pay.type == 'cashdesk'" class="">
                    <label class="form-label">صندوق</label>
                    <v-cob dir="rtl" :options="listCashdesks" label="name" v-model="pay.cashdesk">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                    </v-cob>
                  </div>
                  <div v-show="pay.type == 'salary'" class="">
                    <label class="form-label">تنخواه گردان</label>
                    <v-cob dir="rtl" :options="listSalarys" label="name" v-model="pay.salary">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                    </v-cob>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div class="mb-1">
                    <div class="block-options">
                      <label class="form-label">مبلغ</label>
                      <button @click="fillWithTotal(pay)"
                        class="btn btn-sm btn-link block-options-item float-end me-2">کل
                        فاکتور</button>
                    </div>
                    <money3 @change="calc()" class="form-control" v-model="pay.bs" v-bind="currencyConfig"></money3>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div class="mb-1">
                    <label class="form-label">ارجاع</label>
                    <input type="text" v-model="pay.referral" class="form-control">
                  </div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div class="mb-1">
                    <label class="form-label">شرح</label>
                    <input class="form-control" v-model="pay.des">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </i>
    </div>
  </div>
</template>

<style scoped></style>