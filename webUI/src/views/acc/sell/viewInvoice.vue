<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";
import rec from "../component/rec.vue";
import recList from "../component/recList.vue";
import ArchiveUpload from "../component/archive/archiveUpload.vue";
import type { Header, Item } from "vue3-easy-data-table";
import { getApiUrl } from "@/hesabixConfig";
import notes from '../component/notes.vue';
export default defineComponent({
  name: "viewInvoice",
  components: {
    ArchiveUpload,
    rec: rec,
    recList: recList,
    notes
  },
  watch: {
    'PayWindowsState.submited'(newValue, oldValue) {
      this.PayWindowsState.submited = false;
      if (newValue) {
        this.loadData();
        this.recModal.hide()
      }
    },
    'recListWindowsState.submited'(newValue, oldValue) {
      this.recListWindowsState.submited = false;
      if (newValue) {
        this.loadData();
      }
    }
  },
  data: () => {
    return {
      printOptions: {
        pays: true,
        note: true,
        bidInfo: true,
        taxInfo: true,
        discountInfo: true,
        paper: 'A4-L'
      },
      notes: {
        count: 0
      },
      PayWindowsState: {
        submited: false
      },
      recListWindowsState: {
        submited: false
      },
      recModal: {},
      recListModal: {},
      loading: ref(true),
      shortlink_url: '',
      copy_label: 'کپی',
      send_message_label: 'ارسال',
      bid: {
        legal_name: '',
        shortlinks: false,
      },
      item: {
        doc: {
          id: 0,
          date: null,
          code: null,
          des: '',
          amount: 0,
          profit: 0,
          shortLink: null,
        },
        relatedDocs: [],
        rows: []
      },
      person: {
        nikename: null,
        mobile: '',
        tel: '',
        addres: '',
        postalcode: '',
      },
      commoditys: [],
      totalRec: 0,
      totalDiscount: 0,
      totalTax: 0,
      transferCost: 0,
      discountAll: 0,
      mobileHeaders: [
        { text: "کالا", value: "commodity.name" },
        { text: "تعداد", value: "count" },
        { text: "مبلغ کل", value: "sumTotal" },
      ]
    }
  },
  setup() {

  },
  methods: {
    copyToCliboard() {
      navigator.clipboard.writeText(this.shortlink_url);
      this.copy_label = 'کپی شد !';
    },
    loadData() {
      this.loading = true;
      this.commoditys = [];
      axios.post('/api/accounting/doc/get', { 'code': this.$route.params.id }).then((response) => {
        this.item = response.data;
        if (this.item.doc.shortLink != null) {
          this.shortlink_url = getApiUrl() + '/sl/sell/' + localStorage.getItem("activeBid") + '/' + this.item.doc.shortLink;
        }
        else {
          this.shortlink_url = getApiUrl() + '/sl/sell/' + localStorage.getItem("activeBid") + '/' + this.item.doc.id;
        }
        response.data.relatedDocs.forEach((rdoc: any) => {
          this.totalRec += parseInt(rdoc.amount)
        });
      });

      axios.get('/api/sell/get/info/' + this.$route.params.id).then((response) => {
        this.person = response.data.person;
        this.discountAll = response.data.discountAll;
        this.transferCost = response.data.transferCost;
        this.item.doc.profit = response.data.profit;
        response.data.rows.forEach((item: any) => {
          if (item.commodity != null) {
            this.totalTax += parseInt(item.tax);
            this.totalDiscount += parseInt(item.discount);
            this.commoditys.push({
              commodity: item.commodity,
              count: item.commodity_count,
              price: parseInt((parseInt(item.bs) - parseInt(item.tax) + parseInt(item.discount)) / parseFloat(item.commodity_count)),
              bs: item.bs,
              bd: item.bd,
              id: item.commodity.id,
              des: item.des,
              discount: item.discount,
              tax: item.tax,
              sumWithoutTax: item.bs - item.tax,
              sumTotal: item.bs,
              table: 53
            });
          }

        });
      });
      axios.post('/api/business/get/info/' + localStorage.getItem('activeBid')).then((response) => {
        this.bid = response.data;
        this.loading = false;
      });
      axios.get("/api/printers/options/info").then((response) => {
        this.loading = false;
        this.printOptions = response.data.sell;
      })
    },
    sendSMS() {
      this.loading = true;
      const regex = new RegExp("^(\\+98|0)?9\\d{9}$");
      if (!regex.test(this.person.mobile)) {
        Swal.fire({
          text: 'شماره موبایل وارد شده نا معتبر است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        this.loading = false;
      }
      else {
        this.send_message_label = 'در حال ارسال...';
        axios.post('/api/sms/send/sell-invoice/' + this.item.doc.id + '/' + this.person.mobile).then((response) => {
          if (response.data.result == 2) {
            Swal.fire({
              text: 'اعتبار سرویس پیامک کافی نیست.',
              icon: 'error',
              confirmButtonText: 'قبول'
            });
            this.send_message_label = 'ارسال';
          }
          else if (response.data.result == 1) {
            Swal.fire({
              text: 'پیامک اطلاع رسانی ارسال شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            });
            this.send_message_label = 'ارسال شد!'
          }
          this.loading = false;
        })
      }


    },
    printInvoice(pdf = true, cloudePrinters = true) {
      axios.post('/api/sell/print/invoice', {
        'code': this.$route.params.id,
        'pdf': pdf,
        'printers': cloudePrinters,
        'printOptions': this.printOptions
      }).then((response) => {
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      });
    }
  },
  mounted() {
    this.loadData();
    this.recModal = new bootstrap.Modal(document.getElementById('rec-modal'))
    this.recListModal = new bootstrap.Modal(document.getElementById('rec-list-modal'))
  }
})
</script>

<template>
  <!-- Print Modal -->
  <div class="modal fade" id="printModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary-light text-white">
          <h1 class="modal-title fs-5" id="printModalLabel">چاپ فاکتور</h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <p class="mb-2">برای تغییر تنظیمات پیشفرض به بخش تنظیمات چاپ مراجعه نمایید</p>
          <div class="form-floating mb-2">
            <select v-model="printOptions.paper" class="form-select">
              <option value="A4-L">A4 افقی</option>
              <option value="A4">A4 عمودی</option>
              <option value="A5-L">A5 افقی</option>
              <option value="A5">A5 عمودی</option>
            </select>
            <label>سایز کاغذ و حالت چاپ</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.bidInfo" type="checkbox">
            <label class="form-check-label">اطلاعات کسب‌وکار</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.pays" type="checkbox">
            <label class="form-check-label">نمایش پرداخت‌های فاکتور</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.note" type="checkbox">
            <label class="form-check-label">یاداشت پایین فاکتور</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.taxInfo" type="checkbox">
            <label class="form-check-label">مالیات به تفکیک اقلام</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.discountInfo" type="checkbox">
            <label class="form-check-label">تخفیف به تفکیک اقلام</label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary mx-2" @click="printInvoice()" type="button">
            <i class="si si-printer me-1"></i>
            <span class="">چاپ</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- End Print Modal -->

  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fas fa-file-invoice-dollar"></i>
        مشاهده فاکتور
      </h3>
      <div class="block-options">
        <archive-upload v-if="item.doc.id != 0" :docid="item.doc.id" doctype="sell" cat="sell"></archive-upload>
        <button type="button" class="btn btn-sm btn-warning text-light me-2" data-bs-toggle="modal"
          data-bs-target="#notesModal">
          <span class="badge text-bg-dark me-2">{{ notes.count }}</span>
          <i class="fa-regular fa-note-sticky me-1"></i>
          <span class="d-none d-sm-inline-block">یاداشت‌‌ها</span>
        </button>
        <notes :stat="notes" :code="$route.params.id" typeNote="sell" />
        <!-- Button trigger modal -->
        <button v-show="parseInt(item.doc.amount) > parseInt(totalRec)" type="button" class="btn btn-sm btn-danger"
          @click="recModal.show()">
          <i class="fas fa-money-bill-1-wave"></i>
          <span class="d-none d-sm-inline-block">ثبت دریافت</span>
        </button>
        <!-- Modal -->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="rec-modal" tabindex="-1"
          aria-labelledby="exampleModalLabel1" aria-hidden="true">
          <rec ref="submitPay" :windowsState="PayWindowsState" :person="person.id" :original-doc="item.doc.code"
            :total-amount="parseInt(item.doc.amount) - parseInt(totalRec)">
          </rec>
        </div>
        <button type="button" class="btn btn-sm btn-info ms-2" @click="recListModal.show()">
          <i class="fas fa-arrow-alt-circle-down"></i>
          <span class="d-none d-sm-inline-block">دریافت‌ها</span>
        </button>
        <!-- Modal -->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="rec-list-modal" tabindex="-1"
          aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel1">
                  <i class="fas fa-arrow-alt-circle-down ms-2"></i>
                  دریافت‌ها
                </h1>
                <div class="block-options">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
              </div>
              <div class="modal-body">
                <rec-list ref="recListRef" :windowsState="recListWindowsState" :items="item.relatedDocs"></rec-list>
              </div>
              <div class="modal-footer">
                <button type="button" ref="btnCloseModalRec" class="btn btn-secondary btn-close-modal"
                  data-bs-dismiss="modal">بازگشت</button>
              </div>
            </div>
          </div>
        </div>
        <!-- Button trigger modal -->
        <button v-show="bid.shortlinks" type="button" class="btn btn-sm btn-success ms-2" data-bs-toggle="modal"
          data-bs-target="#exampleModal">
          <i class="fas fa-share-nodes"></i>
          <span class="d-none d-sm-inline-block">اشتراک گذاری</span>
        </button>
        <!-- Modal -->
        <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="exampleModal" tabindex="-1"
          aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">
                  <i class="fas fa-share-nodes"></i>
                  اشتراک گذاری
                </h1>
                <div class="block-options">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
              </div>
              <div class="modal-body">
                <div class="container">
                  <div class="row">
                    <div class="input-group mb-2">
                      <div class="input-group-text">
                        <i class="fa fa-paperclip me-2"></i>
                        پیوند فاکتور
                      </div>
                      <input :readonly="true" type="text" class="form-control" v-model="shortlink_url">
                      <button class="btn btn-outline-success" type="button" @click="copyToCliboard()">{{ copy_label
                      }}</button>
                    </div>
                    <div class="input-group">
                      <div class="input-group-text">
                        <i class="fa fa-message me-2"></i>
                        ارسال پیامک
                      </div>
                      <input type="text" class="form-control" v-model="person.mobile">
                      <button :disabled="loading" class="btn btn-outline-success" type="button" @click="sendSMS()">{{
                        send_message_label }}</button>
                    </div>
                  </div>
                  <div class="mt-3">
                    <i class="fas fa-share-nodes me-3"></i>
                    <label>اشتراک گذاری در شبکه‌های اجتماعی</label>
                  </div>
                  <div class="mt-2">
                    <a target="_blank" :href="'tg://msg?text=' + shortlink_url">
                      <img src="/img/icons/telegram.png" class="m-3" style="max-width: 30px;" />
                    </a>
                    <a target="_blank" :href="'et://msg_url?url=' + shortlink_url">
                      <img src="/img/icons/eitaa.jpeg" class="m-3" style="max-width: 30px;" />
                    </a>
                    <a target="_blank" :href="'https://ble.ir/share/url?url=' + shortlink_url">
                      <img src="/img/icons/bale-logo.png" class="m-3" style="max-width: 30px;" />
                    </a>
                    <a target="_blank" :href="'https://ble.ir/share/url?url=' + shortlink_url">
                      <img src="/img/icons/robika.png" class="m-3" style="max-width: 30px;" />
                    </a>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بازگشت</button>
              </div>
            </div>
          </div>
        </div>
        <!-- print trigger modal -->
        <button type="button" class="btn btn-sm btn-primary mx-2" data-bs-toggle="modal" data-bs-target="#printModal">
          <i class="si si-printer me-1"></i>
          <span class="d-none d-sm-inline-block">چاپ فاکتور</span>
        </button>
      </div>
    </div>
    <div class="block-content py-2 mt-2">
      <div class="row">
        <div class="col-12">
          <div class="row">
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">شماره</span>
                <input type="text" :readonly="true" v-model="item.doc.code" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">تاریخ</span>
                <input type="text" :readonly="true" v-model="item.doc.date" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">شرح</span>
                <input type="text" :readonly="true" v-model="item.doc.des" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
          </div>
          <b class="ps-2">خریدار</b>
          <div class="row">
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">نام</span>
                <input type="text" :readonly="true" v-model="person.nikename" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">موبایل</span>
                <input type="text" :readonly="true" v-model="person.mobile" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">تلفن</span>
                <input type="text" :readonly="true" v-model="person.tel" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">کد پستی</span>
                <input type="text" :readonly="true" v-model="person.postalcode" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-12 col-md-9">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">آدرس</span>
                <input type="text" :readonly="true" v-model="person.address" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
          </div>
          <b class="ps-2">اقلام</b>
          <EasyDataTable table-class-name="customize-table" :headers="mobileHeaders" :items="commoditys" show-index
            alternating theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
            rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از"
            :loading="loading">
            <template #item-sumTotal="{ sumTotal }">
              {{ $filters.formatNumber(sumTotal) }}
            </template>
            <template #item-count="{ count, commodity }">
              {{ count }} {{ commodity.unit }}
            </template>
            <template #expand="{ tax, discount, des, price }">
              <div class="p-1 m-0">
                <ul class="list-group">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    قیمت واحد
                    <span class="badge text-bg-primary rounded-pill">
                      {{ $filters.formatNumber(price) }}
                    </span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    تخفیف
                    <span class="badge text-bg-primary rounded-pill">
                      {{ $filters.formatNumber(discount) }}
                    </span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    مالیات
                    <span class="badge text-bg-primary rounded-pill">
                      {{ $filters.formatNumber(tax) }}
                    </span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    شرح:
                    {{ des }}
                  </li>
                </ul>
              </div>
            </template>
          </EasyDataTable>
          <div class="row pt-2">
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">جمع کل</span>
                <input type="text" :readonly="true" :value="$filters.formatNumber(item.doc.amount)"
                  class="form-control" aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">وضعیت</span>
                <input v-if="parseInt(item.doc.amount) <= parseInt(totalRec)" type="text" :readonly="true"
                  value="تسویه شده" class="form-control text-success" aria-describedby="inputGroup-sizing-sm">
                <input v-else type="text" :readonly="true" value="تسویه نشده" class="form-control text-danger"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div v-if="parseInt(item.doc.profit) >= 0" class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">سود فاکتور</span>
                <input type="text" :readonly="true" :value="$filters.formatNumber(Math.abs(item.doc.profit))"
                  class="form-control text-success">
              </div>
              <div v-else class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">زیان فاکتور</span>
                <input type="text" :readonly="true" :value="$filters.formatNumber(Math.abs(item.doc.profit))"
                  class="form-control text-danger">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">مالیات</span>
                <input type="text" :readonly="true" :value="$filters.formatNumber(totalTax)" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">تخفیف</span>
                <input type="text" :readonly="true" :value="$filters.formatNumber(discountAll)" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="input-group input-group-sm mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">هزینه حمل و نقل</span>
                <input type="text" :readonly="true" :value="$filters.formatNumber(transferCost)" class="form-control"
                  aria-describedby="inputGroup-sizing-sm">
              </div>
            </div>
          </div>
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">دریافت‌ها</h3>
              <div class="block-options">
              </div>
            </div>
            <div class="block-content p-0">
              <table v-if="item.relatedDocs.length != 0"
                class="table border-0 table-borderless table-striped table-vcenter fs-sm">
                <thead class="bg-primary text-light">
                  <tr class="text-center">
                    <th>مشاهده</th>
                    <th>شماره</th>
                    <th>تاریخ</th>
                    <th>مبلغ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="rd in item.relatedDocs" class="text-center">
                    <td>
                      <router-link :to="'/acc/accounting/view/' + rd.code">
                        <span class="text-success fa fa-eye"></span>
                      </router-link>
                    </td>
                    <td class="fw-semibold" style="width: 100px;">
                      {{ rd.code }}
                    </td>
                    <td class="fw-semibold">
                      {{ rd.date }}
                    </td>
                    <td class="fw-semibold">
                      {{ $filters.formatNumber(rd.amount) }}
                    </td>
                  </tr>
                  <tr v-if="item.relatedDocs.length == 0" class="text-center">
                    <td colspan="4">
                      سند پرداختی تاکنون ثبت نشده است.
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="text-center py-2 text-danger" v-else>
                <span>تاکنون سند دریافتی ثبت نشده است</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>
table {
  font-size: small;
  border: 1px solid gray;
}

.table-header {
  background-color: lightgray;
}

.c-print {
  background-color: white;
}

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