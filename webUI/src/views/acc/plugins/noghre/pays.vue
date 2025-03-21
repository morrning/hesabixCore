<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "pays",
  created() {
    this.loadData();
  },
  mounted() {

  },
  data: () => {
    return {
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
      transfer: {
        content: 'cashdesk',
        bank: undefined,
        cashdesk: undefined,
        salary: undefined,
        amount: 0,
        reference: '',
        table: 5,
        id: '',
        des: 'پرداخت بابت منسوجات نقره'
      },
      banks: [],
      cashdesks: [],
      salarys: [],
      order: {
        doc: []
      },
      orderPays: 0,
      customer: [],
      searchValue: '',
      loading: ref(true),
      items: [],
      headers: [
        { text: "تاریخ", value: "date", sortable: true },
        { text: "مبلغ(ریال)", value: "amount", sortable: true },
        { text: "نوع پرداخت", value: "type", sortable: true },
        { text: "مرجع پرداخت", value: "ref", sortable: true },
        { text: "توضیحات", value: "des", sortable: true },
        { text: "عملیات", value: "operation" },
      ]
    }
  },
  watch: {
    'transfer.bank': function () {
      this.transfer.id = this.transfer.bank.id;
    },
    'transfer.salary': function () {
      this.transfer.id = this.transfer.salary.id;
    },
    'transfer.cashdesk': function () {
      this.transfer.id = this.transfer.cashdesk.id;
    },
  },
  methods: {
    removePay(code) {
      Swal.fire({
        text: 'آیا برای حذف این سوابق پرداخت مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/plugin/noghre/order/pays/remove/' + code).then((response) => {
            if (response.data.result == 1) {
              this.loadPays();
              Swal.fire({
                text: 'پرداخت با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    },
    changeDes(content) {
      this.transfer.content = content;
      if (content == ' bank') { this.transfer.table = 5; }
      else if (content == ' salary') { this.transfer.table = 122; }
      else if (content == ' cashdesk') { this.transfer.table = 121; }
    },
    loadData() {
      //get order info
      axios.post('/api/plugin/noghre/order/info/' + this.$route.params.id).then((response) => {
        this.order = response.data;
        this.customer = response.data.customer;
        this.loading = false;
      });

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

      this.loadPays();
    },
    loadPays() {
      this.loading = true;
      axios.post('/api/plugin/noghre/order/pays/list/' + this.$route.params.id).then((response) => {
        this.items = response.data;
        this.orderPays = 0;
        this.items.forEach((item) => {
          this.orderPays += parseInt(item.amount);
        });

        this.loading = false;
      });
    },
    balace() {
      this.transfer.amount = this.order.doc.amount - this.orderPays;
    },
    insertPay() {
      if (this.transfer.amount == 0) {
        Swal.fire({
          text: 'مبلغ وارد نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else if (
        (this.transfer.content == 'bank' && this.transfer.bank == undefined) ||
        (this.transfer.content == 'salary' && this.transfer.salary == undefined) ||
        (this.transfer.content == 'cashdesk' && this.transfer.cashdesk == undefined)
      ) {
        Swal.fire({
          text: 'حساب دریافت کننده انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else {
        let PushData = {
          date: this.date,
          des: this.transfer.des,
          type: 'plugNoghrePay',
          refData: this.order.id,
          plugin: 'plugNoghrePay',
          update: '',
          rows: [
            {
              bs: 0,
              bd: this.transfer.amount,
              type: this.transfer.content,
              bank: this.transfer.bank,
              salary: this.transfer.salary,
              cashdesk: this.transfer.cashdesk,
              table: this.transfer.table,
              id: this.transfer.id,
              des: this.transfer.des,
              referral: this.transfer.reference,
              plugin: 'plugNoghrePay',
              refData: this.order.doc.code
            },
            {
              bd: 0,
              bs: this.transfer.amount,
              type: 'person',
              table: 3,
              id: this.customer.id,
              des: this.transfer.des,
              referral: this.transfer.reference
            }
          ]
        };
        axios.post('/api/accounting/insert', PushData).then((response) => {
          if (response.data.result == '1') {
            Swal.fire({
              text: ' پرداخت با موفقیت ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then((res) => {
              document.getElementById('modalCloseBtn').click();
              this.loadPays();
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
    }
  }
})
</script>

<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="mx-2 fa fa-money-bill-wave"></i>
        پرداخت‌های سفارش
      </h3>
      <div class="block-options">
        <!-- Button trigger modal -->
        <button v-show="(this.orderPays != this.order.doc.amount) && this.loading === false" type="button"
          class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#theModal">
          افزودن پرداخت
        </button>
        <!-- Modal -->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="theModal" tabindex="-1"
          aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">افزودن پرداخت</h1>
                <div class="block-options">
                  <button type="button" id="modalCloseBtn" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
                </div>
              </div>
              <div class="modal-body">
                <div class="container">
                  <div class="row">
                    <div class="col-sm-12 col-md-12">
                      <h3>واریز به:</h3>
                      <div class="btn-group d-flex" role="group" aria-label="Basic radio toggle button group">
                        <input @change="this.changeDes('cashdesk')" type="radio" class="btn-check" name="btnradio1"
                          id="btnradio5" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="btnradio5">صندوق</label>

                        <input @change="this.changeDes('bank')" type="radio" class="btn-check" name="btnradio1"
                          id="btnradio4" autocomplete="off">
                        <label class="btn btn-outline-primary" for="btnradio4">بانک</label>

                        <input @change="this.changeDes('salary')" type="radio" class="btn-check" name="btnradio1"
                          id="btnradio6" autocomplete="off">
                        <label class="btn btn-outline-primary" for="btnradio6">تنخواه</label>
                      </div>
                      <div class="row mt-2">
                        <div class="col-12">
                          <div v-if="this.transfer.content == 'bank'" class="">
                            <label class="form-label">بانک</label>
                            <div class="form-floating mb-2">
                              <v-cob dir="rtl" :options="banks" label="name" v-model="this.transfer.bank">
                                <template #no-options="{ search, searching, loading }">
                                  نتیجه‌ای یافت نشد!
                                </template>
                              </v-cob>
                            </div>
                          </div>
                          <div v-if="this.transfer.content == 'cashdesk'" class="">
                            <label class="form-label">صندوق</label>
                            <div class="form-floating mb-2">
                              <v-cob dir="rtl" :options="cashdesks" label="name" v-model="this.transfer.cashdesk">
                                <template #no-options="{ search, searching, loading }">
                                  نتیجه‌ای یافت نشد!
                                </template>
                              </v-cob>
                            </div>
                          </div>
                          <div v-if="this.transfer.content == 'salary'" class="">
                            <label class="form-label">تنخواه گردان</label>
                            <div class="form-floating mb-2">
                              <v-cob dir="rtl" :options="salarys" label="name" v-model="this.transfer.salary">
                                <template #no-options="{ search, searching, loading }">
                                  نتیجه‌ای یافت نشد!
                                </template>
                              </v-cob>
                            </div>
                          </div>
                          <div class="form-floating mb-2">
                            <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="this.transfer.amount" />
                            <label class="form-label">مبلغ</label>
                            <button @click="balace()" class="btn btn-primary mt-1">تسویه کامل</button>
                          </div>
                          <div class="form-floating mb-2">
                            <input v-model="this.transfer.reference" class="form-control" type="text">
                            <label class="form-label">ارجاع</label>
                          </div>
                          <div class="form-floating mb-2">
                            <input v-model="this.transfer.des" class="form-control" type="text">
                            <label class="form-label">توضیحات</label>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button @click="insertPay()" type="button" class="btn btn-primary">ثبت</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="row  mx-0 my-1 py-2 border border-secondary rounded-2 bg-success-light">
          <div class="col-sm-12 col-md-3">
            <p>مشتری:{{ this.customer.nikename }}</p>
          </div>
          <div class="col-sm-12 col-md-3">
            <p>مبلغ سفارش:{{ $filters.formatNumber(this.order.doc.amount) }}ریال</p>
          </div>
          <div class="col-sm-12 col-md-3">
            <p>مبلغ پرداختی:{{ $filters.formatNumber(this.orderPays) }}ریال</p>
          </div>
          <div class="col-sm-12 col-md-3">
            <p v-show="this.orderPays == this.order.doc.amount">وضعیت:
              <span class="text-success">تسویه شده</span>
            </p>
            <p v-show="this.orderPays == 0">وضعیت:
              <span class="text-danger">بدون پرداختی</span>
            </p>
            <p v-show="this.orderPays != 0 && this.orderPays != this.order.doc.amount">
              <span class="text-warning">
                {{ $filters.formatNumber(this.order.doc.amount - this.orderPays) }} ریال بدهکار
              </span>
            </p>
          </div>
        </div>
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="mb-1">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
            </div>
          </div>
          <EasyDataTable table-class-name="customize-table" multi-sort show-index alternating
            :search-value="searchValue" :headers="headers" :items="items" theme-color="#1d90ff"
            header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
            emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
            <template #item-operation="{ code, id }">

              <router-link :to="'/acc/accounting/view/' + code">
                <i class="fa fa-book px-2"></i>
              </router-link>
              <a @click.prevent="removePay(code)">
                <i class="fa fa-trash px-2 text-danger"></i>
              </a>
            </template>
            <template #item-amount="{ amount }">
              {{ $filters.formatNumber(amount) }}
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped></style>