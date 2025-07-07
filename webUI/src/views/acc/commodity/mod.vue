<template>
  <v-toolbar color="toolbar" :title="$t('dialog.commodity_info')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-btn :loading="loading" @click="save()" icon="" color="green">
      <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
      <v-icon icon="mdi-content-save"></v-icon>
    </v-btn>
    <template v-slot:extension>
      <v-tabs color="primary" class="bg-light" grow v-model="tabs">
        <v-tab value="0">
          {{ $t('dialog.general') }}
        </v-tab>
        <v-tab value="1">
          {{ $t('dialog.prices') }}
        </v-tab>
        <v-tab value="2">
          {{ $t('dialog.existly') }}
        </v-tab>
        <v-tab value="3">
          {{ $t('dialog.tax') }}
        </v-tab>
      </v-tabs>
    </template>
  </v-toolbar>
  <v-row class="pa-1">
    <v-col>
      <v-tabs-window v-model="tabs">
        <v-tabs-window-item value="0">
          <v-card>
            <v-card-text>
              <div class="row py-3">
                <div class="col-sm-6 col-md-6 mb-1">
                  <div>
                    <label class="me-4 text-primary">نوع کالا یا خدمات</label>
                    <div class="form-check form-check-inline">
                      <input v-model="this.data.khadamat" class="form-check-input" type="radio" value="true">
                      <label class="form-check-label" for="inlineCheckbox1">خدمات</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input v-model="this.data.khadamat" class="form-check-input" type="radio" value="false">
                      <label class="form-check-label" for="inlineCheckbox2">کالا و اقلام فیزیکی</label>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 col-md-6 mb-1">
                  <div class="space-y-2">
                    <div class="form-check form-switch">
                      <input v-model="this.data.speedAccess" class="form-check-input" type="checkbox">
                      <label class="form-check-label">دسترسی سریع</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-6">
                  <div class="form-floating mb-4">
                    <input v-model="data.name" class="form-control" type="text">
                    <label class="form-label"><span class="text-danger">(لازم)</span> نام کالا/خدمات</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div class="form-floating mb-4">
                    <select v-model="data.unit" class="form-select">
                      <option v-for="option in units" :key="option.name" :value="option.name">
                        {{ option.name }}
                      </option>
                    </select>
                    <label class="form-label">واحد شمارش</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-12 mb-4">
                  <small class="mb-2">دسته بندی</small>
                  <select class="form-select" aria-label="دسته‌بندی" v-model="this.data.cat">
                    <option v-for="(item, index) in listCats" :value="item.id">{{ item.name }}</option>
                  </select>
                </div>
                <div class="col-sm-12 col-md-12 mb-4">
                  <v-dialog>
                    <template v-slot:activator="{ props: activatorProps }">
                      <v-btn v-bind="activatorProps" prepend-icon="mdi-wizard-hat" color="surface-variant"
                        :text="$t('dialog.barcodes_generate')" class="mb-2"></v-btn>
                    </template>

                    <template v-slot:default="{ isActive }">
                      <v-card :text="$t('dialog.barcodes_generate')">
                        <v-card-text>
                          <v-number-input :min="1" :max="400" v-model="barcode.count" :label="$t('dialog.count')"
                            prepend-inner-icon="mdi-barcode"></v-number-input>
                        </v-card-text>

                        <v-card-actions>
                          <v-spacer></v-spacer>
                          <v-btn :text="$t('dialog.generate')" color="success" variant="flat"
                            @click="isActive.value = false; generateBarcode();"></v-btn>

                          <v-btn :text="$t('dialog.close')" color="secondary" variant="flat"
                            @click="isActive.value = false"></v-btn>
                        </v-card-actions>
                      </v-card>
                    </template>
                  </v-dialog>
                  <v-textarea class="text-left" v-model="data.barcodes" :label="$t('dialog.barcodes')"
                    :placeholder="$t('dialog.barcodes_info')" prepend-inner-icon="mdi-barcode"></v-textarea>
                </div>
                <div class="col-sm-12 col-md-12">
                  <div class="form-floating mb-4">
                    <input v-model="data.des" class="form-control" type="text">
                    <label class="form-label">توضیحات</label>
                  </div>
                </div>

              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="1">
          <v-card>
            <v-card-text>
              <div class="row">
                <div class="col-sm-12 col-md-6">
                  <div class="form-floating mb-4">
                    <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="data.priceBuy" />
                    <label class="form-label">قیمت خرید</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6">
                  <div class="form-floating mb-4">
                    <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="data.priceSell" />
                    <label class="form-label">قیمت فروش</label>
                  </div>
                </div>
                <div v-if="isPluginActive('accpro')" class="col-sm-12 col-md-6" v-for="price in data.prices">
                  <div class="form-floating mb-4">
                    <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="price.priceSell" />
                    <label class="form-label">{{ price.list.label }}</label>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="2">
          <v-card>
            <v-card-text>
              <div class="col-sm-12 col-md-12">
                <b class="text-primary-dark me-3">موجودی کالا</b>
                <label class="text-muted">تنظیمات بخش موجودی کالا تنها برای نوع کالا اعمال می‌شود و برای نوع خدمات
                  نادیده
                  گرفته می‌شود.</label>
                <div class="space-y-2">
                  <div class="form-check form-switch">
                    <input v-model="data.commodityCountCheck" class="form-check-input" type="checkbox">
                    <label class="form-check-label">کنترل موجودی</label>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12 col-md-4 mt-2">
                    <div class="form-floating mb-4">
                      <input v-model="data.minOrderCount"
                        @blur="(event) => { if (this.data.minOrderCount === '' || this.data.minOrderCount === 0) { this.data.minOrderCount = 1 } }"
                        @keypress="this.$filters.onlyNumber($event)" class="form-control" type="number" min="1">
                      <label class="form-label">حداقل سفارش</label>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-4 mt-2">
                    <div class="form-floating mb-4">
                      <input v-model="data.orderPoint" @keypress="this.$filters.onlyNumber($event)" class="form-control"
                        type="number" min="1">
                      <label class="form-label">نقطه سفارش</label>
                    </div>
                  </div>
                  <div class="col-sm-12 col-md-4 mt-2">
                    <div class="form-floating mb-4">
                      <input v-model="data.dayLoading" @keypress="this.$filters.onlyNumber($event)" class="form-control"
                        type="number">
                      <label class="form-label">زمان انتظار(روز)</label>
                    </div>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="3">
          <v-card>
            <v-card-text>
              <div class="col-sm-6 col-md-6 mb-1">
                <div class="space-y-2">
                  <div class="form-check form-switch">
                    <input v-model="this.data.withoutTax" class="form-check-input" type="checkbox">
                    <label class="form-check-label">معاف از مالیات</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-4">
                <div class="form-floating mb-4">
                  <input v-model="data.taxCode" class="form-control" type="text">
                  <label class="form-label">کد مالیاتی</label>
                </div>
              </div>
                <div class="col-sm-6 col-md-4">
                  <div class="form-floating mb-4">
                    <select v-model="data.taxType" class="form-select">
                      <option value="" disabled selected>انتخاب کنید</option>
                      <!-- گزینه‌ها بعداً اضافه می‌شوند -->
                    </select>
                    <label class="form-label">نوع مالیاتی</label>
                  </div>
                </div>
                <div class="col-sm-6 col-md-4">
                  <div class="form-floating mb-4">
                    <select v-model="data.taxUnit" class="form-select">
                      <option value="" disabled selected>انتخاب کنید</option>
                      <!-- گزینه‌ها بعداً اضافه می‌شوند -->
                    </select>
                    <label class="form-label">واحد مالیاتی</label>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
      </v-tabs-window>
    </v-col>
  </v-row>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import { Money3 } from "v-money3";
import Treeselect from 'vue3-treeselect'
// import the styles
import 'vue3-treeselect/dist/vue3-treeselect.css'

export default {
  name: "mod",
  components: {
    Treeselect: Treeselect,
    Loading,
    Money3
  },
  data: () => {
    return {
      tabs: 0,
      loading: false,
      plugins: [],
      units: '',
      barcode: {
        count: 1,
      },
      priceList: [],
      data: {
        name: '',
        priceSell: 0,
        priceBuy: 0,
        des: '',
        unit: 'عدد',
        code: 0,
        khadamat: false,
        cat: null,
        orderPoint: 0,
        commodityCountCheck: false,
        minOrderCount: 1,
        dayLoading: 0,
        speedAccess: false,
        withoutTax: false,
        barcodes: '',
        prices: [],
        taxCode: '',
        taxType: '',
        taxUnit: ''
      },
      listCats: [],
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
    }
  },
  mounted() {
    this.loadData(this.$route.params.id);
  },
  beforeRouteUpdate(to, from) {
    this.loadData(to.params.id);
  },
  methods: {
    generateBarcode(){
      for (let index = 0; index < this.barcode.count; index++) {
        let x = Math.random() * 1000000000000000000;
        this.data.barcodes = this.data.barcodes + ';' + x
      }
    },
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
    loadData(id = '') {
      this.loading = true;
      //get active plugins
      axios.post('/api/plugin/get/actives',).then((response) => {
        this.plugins = response.data;
      });

      axios.post('/api/commodity/units').then((response) => {
        this.units = response.data;
      });

      if (id == '') {
        axios.post('/api/commodity/pricelist/list').then((response) => {
          if (response.data.length == 0) {
            this.data.prices = [];
          }
          else {
            this.priceList = response.data;
            this.priceList.forEach((item) => {
              this.data.prices.push({
                id: 0,
                priceBuy: 0,
                priceSell: 0,
                list: item
              });
            });
          }

        });
      }

      axios.post('/api/commodity/cat/get/line').then((response) => {
        this.listCats = response.data;
        if (!this.$route.params.id) {
          this.data.cat = response.data[1]
        }
      });
      if (id != '') {
        //load info
        this.loading = true;
        axios.post('/api/commodity/info/' + id).then((response) => {
          this.data = response.data;
          if (this.data.prices.length == 0) {
            this.data.prices = this.priceList;
          }
        });
      }
      this.loading = false;
    },
    save() {
      if (this.data.name.length === 0)
        Swal.fire({
          text: 'نام کالا یا خدمات الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      else {
        this.loading = true;
        axios.post('/api/commodity/mod/' + this.data.code, this.data).then((response) => {
          this.loading = false;
          if (response.data.result == 2) {
            Swal.fire({
              text: 'قبلا ثبت شده است.',
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          } else {
            Swal.fire({
              text: 'مشخصات کالا و خدمات ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then(() => {
              this.$router.push('/acc/commodity/list')
            });
          }
        })
      }

    }
  }
}
</script>

<style scoped></style>