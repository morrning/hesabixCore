<script lang="ts">
import { defineComponent } from 'vue'
import { ref } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import quickAddCommodity from "../component/commodity/quickAddCommodity.vue";
import quickView from "../component/person/quickView.vue";
import quickAdd from "../component/person/quickAdd.vue";
import { useDebounceFn } from "@vueuse/core"

export default defineComponent({
  name: "fastMod",
  components: {
    quickAddCommodity,
    quickView,
    quickAdd,
  },
  data() {
    const self = this;
    return {
      barcodeSearch: '',
      onInput: useDebounceFn(() => {
        if (self.barcodeSearch != '') {
          self.loading = true;
          axios.post('/api/commodity/list/search/barcode', { barcode: self.barcodeSearch }).then((response) => {
            self.loading = false;
            if (response.data.Success == true) {
              self.addFastItem(response.data.data);
            }
            else {
              Swal.fire({
                text: self.$t('dialog.commodity_not_found'),
                icon: 'error',
                confirmButtonText: 'قبول'
              })
            }
            self.barcodeSearch = '';
          })
        }
      }, 500),
      tabs: 0,
      canSubmitRecpDoc: true,
      canPdf: true,
      canPrint: true,
      canPrintCashdeskRecp: false,
      update: 0,
      commodity: [],
      selectedCommodity: null,
      tempID: '',
      year: {},
      data: {
        des: '',
        date: '',
        items: []
      },
      itemsSelected: [],
      unitConfig: {
        masked: false,
        prefix: '',
        suffix: '',
        thousands: ',',
        decimal: '.',
        precision: 0,
        disableNegative: true,
        disabled: false,
        allowBlank: false,
        shouldRound: false,
        focusOnRight: true,
      },
      units: [],
      persons: [],
      person: {
        nikename: ''
      },
      cashdesks: [],
      cashdesk: null,
      commoditySpeedAccess: [],
      loading: true,
      sumSelected: 0,
      sumTotal: 0,
      headers: [
        { text: "کالا", value: "commodity.name" },
        { text: "تعداد", value: "count", width: "100" },
        { text: "واحد", value: "commodity.unit" },
        { text: "مبلغ واحد(ریال)", value: "commodity.priceSell" },
        { text: "مبلغ کل(ریال)", value: "bs" },
        { text: "عملیات", value: "operation" },
      ]
    }
  },
  methods: {
    searchPerson(query: any, loading: any) {
      loading(true);
      axios.post('/api/person/list/search', { search: query }).then((response) => {
        this.persons = response.data;
        loading(false);
      });
    },
    searchCommodity(query, loading) {
      loading(true);
      axios.post('/api/commodity/list/search', { search: query }).then((response) => {
        this.commodity = response.data;
        loading(false);
        if (document.querySelector(".cobcom div div input") != null) {
          const cob = document.querySelector(".cobcom div div input");
          cob.focus();
        }
      });
    },
    calcInvoice() {
      this.sumTotal = 0;
      this.data.items.forEach((item) => {
        this.sumTotal += parseFloat(item.bs);
        item.bs = item.count * item.commodity.priceSell
      });
    },
    newPage(withMessage = true) {
      if (withMessage == true) {
        if (this.data.items.length != 0) {
          Swal.fire({
            text: 'آیا فاکتور کنونی را نادیده می گیرید؟',
            showCancelButton: true,
            confirmButtonText: 'بله',
            cancelButtonText: `خیر`,
          }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
              this.loadData();
              this.data.items = [];
            }
          });
        }
      }
      else {
        this.loadData();
        this.data.items = [];
      }
    },
    getCount(commodity) {
      let res = 0;
      this.data.items.forEach((item) => {
        if (item.commodity.code === commodity.code) {
          res = item.count;
        }
      });
      return res;
    },
    addFastItem(commodity) {
      let addNew = true;
      this.data.items.forEach((item) => {
        if (item.commodity.code == commodity.code) {
          item.count++;
          item.bs += commodity.priceSell;
          addNew = false;
        }
      });
      if (addNew) {
        var it = {
          commodity: commodity,
          id: 0,
          bs: parseInt(commodity.priceSell),
          bd: 0,
          count: 1,
          price: parseInt(commodity.priceSell),
          type: 'commodity',
          des: '',
          table: 53,
          unitFormat: JSON.parse(JSON.stringify(this.unitConfig)),
          arrayIndex: this.data.items.length
        }
        it.unitFormat.precision = commodity.unitData.floatNumber;
        this.data.items.push(it);
      }

    },
    loadData() {
      //load year
      axios.post('/api/year/get').then((response) => {
        this.year = response.data;
        this.data.date = response.data.now;
      })
      //load persons
      axios.post('/api/person/list/limit', { speedAccess: true }).then((response) => {
        this.persons = response.data;
        if (response.data.length != 0) {
          this.person = response.data[0];
        }
      });
      //get cashdesks
      axios.post('/api/cashdesk/list', { speedAccess: true }).then((response) => {
        this.cashdesks = response.data;
        if (response.data.length != 0) {
          this.cashdesk = response.data[0];
        }
      });
      //load commodities
      axios.post('/api/commodity/list', { speedAccess: true }).then((response) => {
        this.commoditySpeedAccess = response.data;
        this.commodity = response.data;
      });
      //load commodity units
      axios.post('/api/commodity/units').then((response) => {
        this.units = response.data;
      });
      axios.post("/api/printers/options/info").then((response) => {
        this.loading = false;
        this.canPdf = response.data.fastsell.pdf;
        this.canPrintCashdeskRecp = response.data.fastsell.cashdeskTicket;
        this.canPrint = response.data.fastsell.invoice;
      });
    },
    save() {
      if (this.data.items.length === 0) {
        Swal.fire({
          text: 'فاکتور فاقد کالا می‌باشد.',
          icon: 'error',
          confirmButtonText: 'بازگشت',
        });
      }
      else if (this.person == null || this.person == undefined) {
        Swal.fire({
          text: 'مشتری انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'بازگشت',
        });
      }
      else if (this.cashdesk == null || this.cashdesk == undefined) {
        Swal.fire({
          text: 'صندوق انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'بازگشت',
        });
      }
      else {
        let canAdd = true;
        this.data.items.forEach((item) => {
          if (item.bs == 0) {
            canAdd = false;
          }
        });
        if (canAdd) {
          this.loading = true;
          let outItems = [
            ...this.data.items
          ];
          //save data
          let bd = 0;
          this.data.items.forEach((item) => {
            bd = bd + parseInt(item.bs);
          })
          outItems.push({
            bs: 0,
            bd: bd,
            type: 'person',
            id: this.person.id,
            des: 'فروش کالا به مشتری',
            table: 3
          });
          axios.post('/api/accounting/insert', {
            type: 'sell',
            date: this.data.date,
            des: this.data.des,
            rows: outItems,
            update: ''
          }).then((response) => {
            this.loading = false;
            if (response.data.result == '1') {
              this.update = response.data.doc.code;
              if (this.canPrint || this.canPrintCashdeskRecp) {
                axios.post('/api/sell/print/invoice', {
                  code: this.update,
                  pdf: this.canPdf,
                  posPrint: this.canPrint,
                  posPrintRecp: this.canPrintCashdeskRecp,
                  printers: this.canPdf
                }).then((response) => {
                  if (this.canPdf) {
                    this.printID = response.data.id;
                    window.open(this.$API_URL + '/front/print/' + this.printID, '_blank', 'noreferrer');
                  }
                })
              }
              if (this.canSubmitRecpDoc) {
                outItems = [];
                outItems.push({
                  bs: bd,
                  bd: 0,
                  type: 'person',
                  id: this.person.id,
                  des: 'دریافت وجه فاکتور',
                  table: 3
                });
                outItems.push({
                  bs: 0,
                  bd: bd,
                  type: 'cashdesk',
                  id: this.cashdesk.id,
                  des: 'دریافت وجه فاکتور',
                  table: 121
                });
                this.tempID = response.data.doc.code;
                axios.post('/api/accounting/insert', {
                  type: 'sell_receive',
                  date: this.data.date,
                  des: 'دریافت وجه فاکتور',
                  rows: outItems,
                  update: '',
                  related: response.data.doc.code
                }).then((response) => {
                  if (response.data.result == '4') {
                    Swal.fire({
                      text: response.data.msg,
                      icon: 'error',
                      confirmButtonText: 'قبول'
                    });
                  }
                });
              }
              Swal.fire({
                text: 'فاکتور ثبت شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              }).then(() => {
                this.newPage(false);
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
        else {
          Swal.fire({
            text: 'قیمت یکی از اقلام صفر است',
            icon: 'error',
            confirmButtonText: 'قبول'
          })
        }
      }
    },
    deleteItem(code) {
      if (this.data.items.length == 1) {
        this.data.items = [];
      }
      else {
        let index = 0;
        for (let z = 0; z < this.data.items.length; z++) {
          index++;
          if (this.data.items[z]['commodity']['code'] == code) {
            this.data.items.splice(index - 1, 1);
          }
        }
        for (let z = 0; z < this.data.items.length; z++) {
          this.data.items[z].arrayIndex = z;
        }
      }

    },
  },
  mounted() {
    this.loadData();
  },
  watch: {
    itemsSelected: {
      handler: function (val, oldVal) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((item) => {
          this.sumSelected += parseFloat(item.bs);
        })
      },
      deep: true
    },
    'data.items': {
      handler: function (val, oldVal) {
        this.calcInvoice();
      },
      deep: true
    },
    'selectedCommodity': {
      handler: function (val, oldVal) {
        if (val != null) {
          let canAdd = true;
          this.commoditySpeedAccess.forEach((item) => {
            if (item.id == val.id) {
              canAdd = false;
            }
          })
          if (canAdd) {
            this.commoditySpeedAccess.push(val);
          }
          this.addFastItem(val);
          this.selectedCommodity = null;
        }
      },
      deep: true
    },
  }
})
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('drawer.fast_sell')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-btn :loading="loading" @click="newPage()" icon="" color="danger">
      <v-tooltip activator="parent" :text="$t('dialog.new')" location="bottom" />
      <v-icon icon="mdi-invoice-text-plus-outline"></v-icon>
    </v-btn>
    <v-btn :loading="loading" @click="save()" icon="" color="green">
      <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
      <v-icon icon="mdi-content-save"></v-icon>
    </v-btn>
    <template v-slot:extension>
      <v-tabs color="primary" class="bg-light" grow v-model="tabs">
        <v-tab value="0">
          {{ $t('dialog.faktor_info') }}
        </v-tab>
        <v-tab value="1">
          {{ $t('dialog.details_faktor') }}
        </v-tab>
        <v-tab value="2">
          {{ $t('dialog.print_settings') }}
        </v-tab>
      </v-tabs>
    </template>
  </v-toolbar>
  <v-tabs-window v-model="tabs">
    <v-tabs-window-item value="0">
      <v-card>
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="12" md="3" class="pe-0 ps-0">
              <div class="card">
                <div class="card-header">
                  <i class="fa fa-boxes me-2"></i>
                  کالا ها و خدمات
                  <div class="block-options float-end">
                    <quickAddCommodity></quickAddCommodity>
                  </div>
                </div>
                <div class="card-body p-0">
                  <v-text-field :loading="loading" v-model="barcodeSearch" @input="onInput" clearable
                    :label="$t('dialog.barcode')" color="primary" class="pa-2" density="comfortable" variant="outlined">
                    <template v-slot:append-inner="{ props }">
                      <v-icon color="danger" icon="mdi-barcode-scan"></v-icon>
                    </template>
                  </v-text-field>
                  <v-cob dir="rtl" @search="searchCommodity" :filterable="false" :options="commodity" label="name"
                    v-model="selectedCommodity" class="rounded-0 m-1 cobcom" :placeholder="$t('dialog.search')">
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>

                    <template v-slot:option="option">
                      <v-row class="my-0 py-0">
                        <v-col class="ma-0 py-0" cols="12">{{ option.name }}</v-col>
                        <v-col class="ma-0 py-0" cols="12">{{ $t('dialog.each') }} {{ option.unit }}:{{
                          $filters.formatNumber(option.priceSell) }}</v-col>
                      </v-row>
                    </template>
                  </v-cob>
                  <v-list lines="one">
                    <v-list-item @click="addFastItem(cm)" v-for="cm in commoditySpeedAccess" :title="cm.name" :subtitle="$t('dialog.each') + cm.unit + ':' +
                      $filters.formatNumber(cm.priceSell)">
                      <template v-slot:append="option">
                        <span class="badge text-bg-primary rounded-pill">
                          {{ getCount(cm) + ' ' + cm.unit }}
                        </span>
                      </template>
                    </v-list-item>
                  </v-list>
                </div>
              </div>
              <div class="card mt-2">
                <div class="card-header">
                  <i class="fa fa-user me-2"></i>
                  مشتری
                  <div class="block-options float-end">
                    <quickView :code="this.person.code"></quickView>
                    <quickAdd :code="this.person.code"></quickAdd>
                  </div>
                </div>
                <div class="card-body p-1">
                  <v-cob :filterable="false" dir="rtl" @search="searchPerson" :options="persons" label="nikename"
                    v-model="person">
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                  </v-cob>
                </div>
              </div>
              <div class="card mt-2">
                <div class="card-header">
                  <i class="fa fa-shopping-cart me-2"></i>
                  صندوق
                </div>
                <div class="card-body p-1">
                  <v-cob dir="rtl" :options="cashdesks" label="name" v-model="cashdesk">
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                  </v-cob>
                </div>
              </div>
            </v-col>
            <v-col cols="12" sm="12" md="9">
              <EasyDataTable table-class-name="customize-table" show-index :headers="headers"
                v-model:items-selected="itemsSelected" :items="data.items" theme-color="#1d90ff"
                header-text-direction="center" border-cell body-text-direction="center" rowsPerPageMessage="تعداد سطر"
                emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
                <template #item-commodity.priceSell="{ commodity, arrayIndex }">
                  <money3 v-model="data.items[arrayIndex].commodity.priceSell" v-bind="unitConfig"
                    class="form-control form-control-sm border-0 text-center" />
                </template>
                <template #item-commodity.name="{ commodity }">
                  <span>{{ $filters.formatNumber(commodity.code) + ' - ' + commodity.name }}</span>
                </template>
                <template #item-bs="{ bs }">
                  <span>{{ $filters.formatNumber(bs) }}</span>
                </template>
                <template #item-count="{ unitFormat, arrayIndex }">
                  <money3 v-model="data.items[arrayIndex].count" v-bind="unitFormat"
                    class="form-control form-control-sm border-0 text-center" />
                </template>
                <template #item-operation="{ commodity }">
                  <span class="text-danger px-1" @click="deleteItem(commodity.code)">
                    <i class="fa fa-trash"></i>
                  </span>
                </template>
              </EasyDataTable>
              <div class="container-fluid p-0 mx-0 mt-2">
                <a class="block block-rounded block-link-shadow border-start border-success border-3"
                  href="javascript:void(0)">
                  <div class="block-content block-content-full block-content-sm bg-body-light">
                    <div class="row">
                      <div class="col-sm-12 col-md-6">
                        <span class="text-dark">
                          <i class="fa fa-list-check"></i>
                          جمع مبلغ موارد انتخابی:
                        </span>
                        <span class="text-primary">
                          {{ $filters.formatNumber(this.sumSelected) }}
                          {{ $filters.getActiveMoney().shortName }}
                        </span>
                      </div>
                      <div class="col-sm-12 col-md-6">
                        <span class="text-dark">
                          <i class="fa fa-list-dots"></i>
                          جمع کل:
                        </span>
                        <span class="text-primary">
                          {{ $filters.formatNumber(this.sumTotal) }}
                          {{ $filters.getActiveMoney().shortName }}
                        </span>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-tabs-window-item>
    <v-tabs-window-item value="1">
      <v-card>
        <v-card-text>
          <div class="row">
            <div class="col-sm-12 col-md-6 mb-2">
              <div class="">
                <label class="form-label">تاریخ:</label>
                <date-picker class="" v-model="data.date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
                  :min="year.start" :max="year.end" />
              </div>
            </div>
            <div class="col-sm-12 col-md-6 mb-2">
              <div class="">
                <label class="form-label">شرح:</label>
                <input class="form-control form-control-sm" v-model="data.des" type="text">
              </div>
            </div>
            <div class="col-sm-12 col-md-6 mb-2">
              <span class="form-check form-switch  form-check-inline">
                <input :disabled="this.loading" v-model="canSubmitRecpDoc" class="form-check-input" type="checkbox">
                <label class="form-check-label">
                  ثبت خودکار سند دریافت وجه فاکتور
                </label>
              </span>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </v-tabs-window-item>
    <v-tabs-window-item value="2">
      <v-card>
        <v-card-text>
          <div class="row">
            <div class="col-sm-12 col-md-4">
              <span class="form-check form-switch  form-check-inline">
                <input :disabled="this.loading" v-model="canPrint" class="form-check-input" type="checkbox">
                <label class="form-check-label">
                  <i class="fa-solid fa-cloud me-1"></i>
                  صورت حساب
                </label>
              </span>
            </div>
            <div class="col-sm-12 col-md-4">
              <span class="form-check form-switch  form-check-inline">
                <input :disabled="this.loading" v-model="canPrintCashdeskRecp" class="form-check-input" type="checkbox">
                <label class="form-check-label">
                  <i class="fa-solid fa-cloud me-1"></i>
                  قبض صندوق
                </label>
              </span>
            </div>
            <div class="col-sm-12 col-md-4">
              <span class="form-check form-switch  form-check-inline">
                <input :disabled="this.loading" v-model="canPdf" class="form-check-input" type="checkbox">
                <label class="form-check-label">
                  <i class="fa-regular fa-file-pdf me-1"></i>
                  خروجی PDF
                </label>
              </span>
            </div>
          </div>
        </v-card-text>
      </v-card>
    </v-tabs-window-item>
  </v-tabs-window>
</template>

<style scoped></style>