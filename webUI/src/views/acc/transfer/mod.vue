<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        انتقال
      </h3>
      <div class="block-options">
        <archive-upload v-if="this.$route.params.id != ''" :docid="this.$route.params.id" doctype="transfer"
          cat="transfer"></archive-upload>
        <button @click="save()" type="button" class="btn btn-sm btn-alt-primary"><i class="fa fa-save"></i> ثبت</button>
      </div>
    </div>
    <div class="block-content py-3 vl-parent">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false" />
      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <div class="form-control">
              <label class="form-label">تاریخ:</label>
              <date-picker class="" v-model="this.date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
                :min="this.year.start" :max="this.year.end" />
            </div>
          </div>
          <div class="col-sm-12 col-md-6">
            <div class="form-floating mb-4">
              <input v-model="this.des" class="form-control" type="text">
              <label class="form-label">توضیحات</label>
            </div>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <h3>از:</h3>
            <div class="btn-group d-flex" role="group" aria-label="Basic radio toggle button group">
              <input :checked="this.sideOne.content == 'bank'" @change="this.changeFrom('bank')" type="radio"
                class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
              <label class="btn btn-outline-primary" for="btnradio1">بانک</label>

              <input :checked="this.sideOne.content == 'cashdesk'" @change="this.changeFrom('cashdesk')" type="radio"
                v-model="this.sideOne.content" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
              <label class="btn btn-outline-primary" for="btnradio2">صندوق</label>

              <input :checked="this.sideOne.content == 'salary'" @change="this.changeFrom('salary')" type="radio"
                class="btn-check" name="btnradio" id="btnradio3" autocomplete="off">
              <label class="btn btn-outline-primary" for="btnradio3">تنخواه</label>
            </div>
            <div class="row mt-2">
              <div class="col-12">
                <div v-if="this.sideOne.content == 'bank'" class="">
                  <label class="form-label">بانک</label>
                  <div class="form-floating mb-2">
                    <v-cob dir="rtl" :options="banks" label="name" v-model="this.sideOne.bank">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                      <template #option="option">
                        <div class="row">
                          <div class="col-12">
                            <i class="fa fa-bank"></i>
                            {{ option.name }}
                          </div>
                          <div class="col-12">
                            موجودی:
                            {{ $filters.formatNumber(option.balance) }}
                            <span class="text-danger" v-if="option.balance < 0">بدهکار</span>
                            <span class="text-success" v-if="option.balance > 0">بستانکار</span>
                          </div>
                        </div>
                      </template>
                    </v-cob>

                  </div>
                </div>
                <div v-if="this.sideOne.content == 'cashdesk'" class="">
                  <label class="form-label">صندوق</label>
                  <div class="form-floating mb-2">
                    <v-cob dir="rtl" :options="cashdesks" label="name" v-model="this.sideOne.cashdesk">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                      <template #option="option">
                        <div class="row">
                          <div class="col-12">
                            <i class="fa fa-bank"></i>
                            {{ option.name }}
                          </div>
                          <div class="col-12">
                            موجودی:
                            {{ $filters.formatNumber(option.balance) }}
                            <span class="text-danger" v-if="option.balance < 0">بدهکار</span>
                            <span class="text-success" v-if="option.balance > 0">بستانکار</span>
                          </div>
                        </div>
                      </template>
                    </v-cob>
                  </div>
                </div>
                <div v-if="this.sideOne.content == 'salary'" class="">
                  <label class="form-label">تنخواه گردان</label>
                  <div class="form-floating mb-2">
                    <v-cob dir="rtl" :options="salarys" label="name" v-model="this.sideOne.salary">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                      <template #option="option">
                        <div class="row">
                          <div class="col-12">
                            <i class="fa fa-bank"></i>
                            {{ option.name }}
                          </div>
                          <div class="col-12">
                            موجودی:
                            {{ $filters.formatNumber(option.balance) }}
                            <span class="text-danger" v-if="option.balance < 0">بدهکار</span>
                            <span class="text-success" v-if="option.balance > 0">بستانکار</span>
                          </div>
                        </div>
                      </template>
                    </v-cob>
                  </div>
                </div>
                <div class="form-floating mb-2">
                  <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="this.sideOne.bs" />
                  <label class="form-label">مبلغ</label>
                </div>
                <div class="form-floating mb-2">
                  <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="this.sideOne.tax" />
                  <label class="form-label">کارمزد خدمات بانکی</label>
                </div>
                <div class="form-floating mb-2">
                  <input v-model="this.sideOne.reference" class="form-control" type="text">
                  <label class="form-label">ارجاع</label>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6">
            <h3>به:</h3>
            <div class="btn-group d-flex" role="group" aria-label="Basic radio toggle button group">
              <input v-model="this.sideTwo.content" :checked="this.sideTwo.content == 'bank'"
                @change="this.changeDes('bank')" type="radio" class="btn-check" name="btnradio1" id="btnradio4"
                autocomplete="off" checked>
              <label class="btn btn-outline-warning" for="btnradio4">بانک</label>

              <input v-model="this.sideTwo.content" :checked="this.sideTwo.content == 'cashdesk'"
                @change="this.changeDes('cashdesk')" type="radio" class="btn-check" name="btnradio1" id="btnradio5"
                autocomplete="off">
              <label class="btn btn-outline-warning" for="btnradio5">صندوق</label>

              <input v-model="this.sideTwo.content" :checked="this.sideTwo.content == 'salary'"
                @change="this.changeDes('salary')" type="radio" class="btn-check" name="btnradio1" id="btnradio6"
                autocomplete="off">
              <label class="btn btn-outline-warning" for="btnradio6">تنخواه</label>
            </div>
            <div class="row mt-2">
              <div class="col-12">
                <div v-if="this.sideTwo.content == 'bank'" class="">
                  <label class="form-label">بانک</label>
                  <div class="form-floating mb-2">
                    <v-cob dir="rtl" :options="banks" label="name" v-model="this.sideTwo.bank">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                      <template #option="option">
                        <div class="row">
                          <div class="col-12">
                            <i class="fa fa-bank"></i>
                            {{ option.name }}
                          </div>
                          <div class="col-12">
                            موجودی:
                            {{ $filters.formatNumber(option.balance) }}
                            <span class="text-danger" v-if="option.balance < 0">بدهکار</span>
                            <span class="text-success" v-if="option.balance > 0">بستانکار</span>
                          </div>
                        </div>
                      </template>
                    </v-cob>
                  </div>
                </div>
                <div v-if="this.sideTwo.content == 'cashdesk'" class="">
                  <label class="form-label">صندوق</label>
                  <div class="form-floating mb-2">
                    <v-cob dir="rtl" :options="cashdesks" label="name" v-model="this.sideTwo.cashdesk">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                      <template #option="option">
                        <div class="row">
                          <div class="col-12">
                            <i class="fa fa-bank"></i>
                            {{ option.name }}
                          </div>
                          <div class="col-12">
                            موجودی:
                            {{ $filters.formatNumber(option.balance) }}
                            <span class="text-danger" v-if="option.balance < 0">بدهکار</span>
                            <span class="text-success" v-if="option.balance > 0">بستانکار</span>
                          </div>
                        </div>
                      </template>
                    </v-cob>
                  </div>
                </div>
                <div v-if="this.sideTwo.content == 'salary'" class="">
                  <label class="form-label">تنخواه گردان</label>
                  <div class="form-floating mb-2">
                    <v-cob dir="rtl" :options="salarys" label="name" v-model="this.sideTwo.salary">
                      <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                      </template>
                      <template #option="option">
                        <div class="row">
                          <div class="col-12">
                            <i class="fa fa-bank"></i>
                            {{ option.name }}
                          </div>
                          <div class="col-12">
                            موجودی:
                            {{ $filters.formatNumber(option.balance) }}
                            <span class="text-danger" v-if="option.balance < 0">بدهکار</span>
                            <span class="text-success" v-if="option.balance > 0">بستانکار</span>
                          </div>
                        </div>
                      </template>
                    </v-cob>
                  </div>
                </div>
                <div class="form-floating mb-2">
                  <money3 readonly="readonly" v-bind="currencyConfig" min=0 class="form-control"
                    v-model="this.sideTwo.bd" />
                  <label class="form-label">مبلغ</label>
                </div>
                <div class="form-floating mb-2">
                  <money3 readonly="readonly" v-bind="currencyConfig" min=0 class="form-control"
                    v-model="this.sideTwo.tax" />
                  <label class="form-label">کارمزد خدمات بانکی</label>
                </div>
                <div class="form-floating mb-2">
                  <input v-model="this.sideTwo.reference" class="form-control" type="text">
                  <label class="form-label">ارجاع</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Loading from "vue-loading-overlay";
import Swal from "sweetalert2";
import archiveUpload from "../component/archive/archiveUpload.vue";

export default {
  name: "mod",
  components: {
    Loading,
    archiveUpload
  },
  watch: {
    'sideOne.bs': function () {
      this.sideTwo.bd = this.sideOne.bs;
    },
    'sideOne.tax': function () {
      this.sideTwo.tax = this.sideOne.tax;
    },
    'sideOne.bank': function () {
      this.sideOne.id = this.sideOne.bank.id;
    },
    'sideOne.salary': function () {
      this.sideOne.id = this.sideOne.salary.id;
    },
    'sideOne.cashdesk': function () {
      this.sideOne.id = this.sideOne.cashdesk.id;
    },
    'sideTwo.bank': function () {
      this.sideTwo.id = this.sideTwo.bank.id;
    },
    'sideTwo.salary': function () {
      this.sideTwo.id = this.sideTwo.salary.id;
    },
    'sideTwo.cashdesk': function () {
      this.sideTwo.id = this.sideTwo.cashdesk.id;
    },
  },
  data: () => {
    return {
      isLoading: false,
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
        focusOnRight: false,
      },
      year: {},
      date: '',
      des: '',
      sideOne: {
        content: 'bank',
        bank: undefined,
        cashdesk: undefined,
        salary: undefined,
        bs: 0,
        bd: 0,
        tax: 0,
        reference: '',
        table: 5,
        id: '',
        des: 'انتقال بین حساب‌های بانکی،صندوق،تنخواه گردان'
      },
      sideTwo: {
        content: 'bank',
        bank: undefined,
        cashdesk: undefined,
        salary: undefined,
        bs: 0,
        bd: 0,
        tax: 0,
        reference: '',
        table: 5,
        id: '',
        des: 'انتقال بین حساب‌های بانکی،صندوق،تنخواه گردان'
      },
      banks: [],
      cashdesks: [],
      salarys: []
    }
  },
  methods: {
    loadData() {
      axios.post('/api/bank/list').then((response) => {
        this.banks = response.data;
      });
      axios.post('/api/cashdesk/list').then((response) => {
        this.cashdesks = response.data;
      });
      axios.post('/api/salary/list').then((response) => {
        this.salarys = response.data;
      });
      //load year
      axios.post('/api/year/get').then((response) => {
        this.year = response.data;
        this.date = response.data.now;
      });
      if (this.$route.params.id != '') {
        axios.post('/api/accounting/doc/get', {
          code: this.$route.params.id
        }).then((response) => {
          let taxAmount = 0;
          response.data.rows.forEach((item, key) => {
            if (item.refCode == '108') {
              //item is tax
              taxAmount = item.bd;
              response.data.rows[key].id = 'ignore';
            }
          });

          response.data.rows.forEach((item, key) => {
            if (item.bs == taxAmount && item.bs != 0) {
              response.data.rows[key].id = 'ignore';
            }
          });
          response.data.rows.forEach((item, key) => {

            if (item.bs != 0 && item.tableCode == 5 && item.id != 'ignore') {
              let opt = {
                content: '',
                bank: undefined,
                cashdesk: undefined,
                salary: undefined,
                bs: item.bs,
                bd: item.bd,
                tax: taxAmount,
                reference: '',
                table: 5,
                id: '',
                des: item.des
              };
              if (item.bank != undefined) {
                opt.bank = item.bank;
                opt.content = 'bank';
              }
              else if (item.cashdesk != undefined) {
                opt.cashdesk = item.cashdesk;
                opt.content = 'cashdesk';
              }
              else if (item.salary != undefined) {
                opt.salary = item.salary;
                opt.content = 'salary';
              }
              this.sideOne = opt
            }
            if (parseInt(item.bd) != 0 && parseInt(item.tableCode) == 5 && item.id != 'ignore') {
              let opt = {
                content: '',
                bank: undefined,
                cashdesk: undefined,
                salary: undefined,
                bs: item.bs,
                bd: item.bd,
                tax: taxAmount,
                reference: '',
                table: 5,
                id: '',
                des: item.des
              };
              if (item.bank != undefined) {
                opt.bank = item.bank;
                opt.content = 'bank';
              }
              else if (item.cashdesk != undefined) {
                opt.cashdesk = item.cashdesk;
                opt.content = 'cashdesk';
              }
              else if (item.salary != undefined) {
                opt.salary = item.salary;
                opt.content = 'salary';
              }
              this.sideTwo = opt;
            }
          });
        });
      }
    },
    save() {
      if (this.sideOne.bs == 0) {
        Swal.fire({
          text: 'مبلغ انتقال وارد نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else if (
        (this.sideOne.content == 'bank' && this.sideOne.bank == undefined) ||
        (this.sideOne.content == 'salary' && this.sideOne.salary == undefined) ||
        (this.sideOne.content == 'cashdesk' && this.sideOne.cashdesk == undefined)
      ) {
        Swal.fire({
          text: 'انتقال دهنده انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else if (
        (this.sideTwo.content == 'bank' && this.sideTwo.bank == undefined) ||
        (this.sideTwo.content == 'salary' && this.sideTwo.salary == undefined) ||
        (this.sideTwo.content == 'cashdesk' && this.sideTwo.cashdesk == undefined)
      ) {
        Swal.fire({
          text: 'انتقال گیرنده انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else {
        let PushData = {
          date: this.date,
          des: this.des,
          type: 'transfer',
          update: this.$route.params.id,
          rows: [
            {
              bs: this.sideOne.bs,
              bd: 0,
              type: this.sideOne.content,
              bank: this.sideOne.bank,
              salary: this.sideOne.salary,
              cashdesk: this.sideOne.cashdesk,
              table: this.sideOne.table,
              id: this.sideOne.id,
              des: this.sideOne.des,
              referral: this.sideOne.reference
            },
            {
              bd: this.sideTwo.bd,
              bs: 0,
              type: this.sideTwo.content,
              bank: this.sideTwo.bank,
              salary: this.sideTwo.salary,
              cashdesk: this.sideTwo.cashdesk,
              table: this.sideTwo.table,
              id: this.sideTwo.id,
              des: this.sideTwo.des,
              referral: this.sideTwo.reference
            }
          ]
        };
        if (this.sideOne.tax != 0) {
          PushData.rows.push({
            bd: this.sideOne.tax,
            bs: 0,
            type: 'calc',
            table: 108,
            des: 'کارمزد هزینه‌های بانکی'
          });
          PushData.rows.push({
            bs: this.sideOne.tax,
            bd: 0,
            type: this.sideOne.content,
            bank: this.sideOne.bank,
            salary: this.sideOne.salary,
            cashdesk: this.sideOne.cashdesk,
            table: this.sideOne.table,
            id: this.sideOne.id,
            des: 'کارمزد هزینه‌های بانکی'
          })
        }
        axios.post('/api/accounting/insert', PushData).then((response) => {
          if (response.data.result == '1') {
            Swal.fire({
              text: 'سند انتقال با موفقیت ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then((res) => {
              this.$router.push('/acc/transfer/list');
            });
          }
          else if (response.data.result == '4') {
            Swal.fire({
              text: response.data.msg,
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          }
        });
      }

    },
    changeDes(content) {
      this.sideTwo.content = content;
      if (content == ' bank') { this.sideTwo.table = 5; }
      else if (content == ' salary') { this.sideTwo.table = 122; }
      else if (content == ' cashdesk') { this.sideTwo.table = 121; }
    },
    changeFrom(content) {
      this.sideOne.content = content;
      if (content == ' bank') { this.sideOne.table = 5; }
      else if (content == ' salary') { this.sideOne.table = 122; }
      else if (content == ' cashdesk') { this.sideOne.table = 121; }
    },
  },
  mounted() {
    this.loadData();
  }
}
</script>

<style scoped></style>