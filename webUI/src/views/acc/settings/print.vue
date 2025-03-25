<template>
  <v-toolbar color="toolbar" :title="$t('drawer.print_settings')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-btn :loading="loading" @click="submit()" icon="" color="green">
      <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
      <v-icon icon="mdi-content-save"></v-icon>
    </v-btn>
    <template v-slot:extension>
      <v-tabs color="primary" class="bg-light" grow v-model="tabs">
        <v-tab value="0">
          {{ $t('drawer.sell') }}
        </v-tab>
        <v-tab value="1">
          {{ $t('drawer.buy') }}
        </v-tab>
        <v-tab v-if="isPluginActive('accpro')" value="2">
          {{ $t('drawer.rfbuy_invoices') }}
        </v-tab>
        <v-tab v-if="isPluginActive('accpro')" value="3">
          {{ $t('drawer.rfsell_invoices') }}
        </v-tab>
        <v-tab value="4">
          {{ $t('drawer.fast_sell') }}
        </v-tab>
        <v-tab v-if="isPluginActive('repservice')" value="5">
          {{ $t('drawer.repservice') }}
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
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.sell.bidInfo" type="checkbox">
                    <label class="form-check-label">اطلاعات کسب‌وکار</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.sell.pays" type="checkbox">
                    <label class="form-check-label">نمایش پرداخت‌های فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.sell.note" type="checkbox">
                    <label class="form-check-label">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.sell.taxInfo" type="checkbox">
                    <label class="form-check-label">مالیات به تفکیک اقلام</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.sell.discountInfo" type="checkbox">
                    <label class="form-check-label">تخفیف به تفکیک اقلام</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <textarea v-model="settings.sell.noteString" class="form-control"
                      placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد" style="height: 100px"></textarea>
                    <label for="floatingTextarea2">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <select v-model="settings.sell.paper" class="form-select">
                      <option value="A4-L">A4 افقی</option>
                      <option value="A4">A4 عمودی</option>
                      <option value="A5-L">A5 افقی</option>
                      <option value="A5">A5 عمودی</option>
                    </select>
                    <label>سایز کاغذ و حالت چاپ</label>
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
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.buy.bidInfo" type="checkbox">
                    <label class="form-check-label">اطلاعات کسب‌وکار</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.buy.pays" type="checkbox">
                    <label class="form-check-label">نمایش پرداخت‌های فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.buy.note" type="checkbox">
                    <label class="form-check-label">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.buy.taxInfo" type="checkbox">
                    <label class="form-check-label">مالیات به تفکیک اقلام</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.buy.discountInfo" type="checkbox">
                    <label class="form-check-label">تخفیف به تفکیک اقلام</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <textarea v-model="settings.buy.noteString" class="form-control"
                      placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد" style="height: 100px"></textarea>
                    <label for="floatingTextarea2">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <select v-model="settings.buy.paper" class="form-select">
                      <option value="A4-L">A4 افقی</option>
                      <option value="A4">A4 عمودی</option>
                      <option value="A5-L">A5 افقی</option>
                      <option value="A5">A5 عمودی</option>
                    </select>
                    <label>سایز کاغذ و حالت چاپ</label>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="2">
          <v-card>
            <v-card-text>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfbuy.bidInfo" type="checkbox">
                    <label class="form-check-label">اطلاعات کسب‌وکار</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfbuy.pays" type="checkbox">
                    <label class="form-check-label">نمایش پرداخت‌های فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfbuy.note" type="checkbox">
                    <label class="form-check-label">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfbuy.taxInfo" type="checkbox">
                    <label class="form-check-label">مالیات به تفکیک اقلام</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfbuy.discountInfo" type="checkbox">
                    <label class="form-check-label">تخفیف به تفکیک اقلام</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <textarea v-model="settings.rfbuy.noteString" class="form-control"
                      placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد" style="height: 100px"></textarea>
                    <label for="floatingTextarea2">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <select v-model="settings.rfbuy.paper" class="form-select">
                      <option value="A4-L">A4 افقی</option>
                      <option value="A4">A4 عمودی</option>
                      <option value="A5-L">A5 افقی</option>
                      <option value="A5">A5 عمودی</option>
                    </select>
                    <label>سایز کاغذ و حالت چاپ</label>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="3">
          <v-card>
            <v-card-text>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfsell.bidInfo" type="checkbox">
                    <label class="form-check-label">اطلاعات کسب‌وکار</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfsell.pays" type="checkbox">
                    <label class="form-check-label">نمایش پرداخت‌های فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfsell.note" type="checkbox">
                    <label class="form-check-label">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfsell.taxInfo" type="checkbox">
                    <label class="form-check-label">مالیات به تفکیک اقلام</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.rfsell.discountInfo" type="checkbox">
                    <label class="form-check-label">تخفیف به تفکیک اقلام</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <textarea v-model="settings.rfsell.noteString" class="form-control"
                      placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد" style="height: 100px"></textarea>
                    <label for="floatingTextarea2">یادداشت پایین فاکتور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <select v-model="settings.rfsell.paper" class="form-select">
                      <option value="A4-L">A4 افقی</option>
                      <option value="A4">A4 عمودی</option>
                      <option value="A5-L">A5 افقی</option>
                      <option value="A5">A5 عمودی</option>
                    </select>
                    <label>سایز کاغذ و حالت چاپ</label>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="4">
          <v-card>
            <v-card-text>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.fastsell.invoice" type="checkbox">
                    <label class="form-check-label">{{ $t('dialog.invoice') }}</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.fastsell.cashdeskTicket" type="checkbox">
                    <label class="form-check-label">{{ $t('dialog.cashdeskTicket') }}</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-check form-switch">
                    <input class="form-check-input" v-model="settings.fastsell.pdf" type="checkbox">
                    <label class="form-check-label">{{ $t('dialog.export_pdf') }}</label>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="5">
          <v-card>
            <v-card-text>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <textarea v-model="settings.repservice.noteString" class="form-control"
                      placeholder="این نوشته در پایین قبض چاپ خواهد شد" style="height: 100px"></textarea>
                    <label for="floatingTextarea2">قوانین تعمیرگاه</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <select v-model="settings.repservice.paper" class="form-select">
                      <option value="A4-L">A4 افقی</option>
                      <option value="A4">A4 عمودی</option>
                      <option value="A5-L">A5 افقی</option>
                      <option value="A5">A5 عمودی</option>
                    </select>
                    <label>سایز کاغذ و حالت چاپ</label>
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
import { ref } from "vue";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
export default {
  name: "bussiness",
  components: {
    Loading
  },
  data: () => {
    return {
      loading: ref(false),
      tabs: 0,
      plugins: [],
      settings: {
        sell: {
          pays: true,
          note: true,
          noteString: '',
          bidInfo: true,
          taxInfo: true,
          discountInfo: true,
          paper: 'A4-L',
        },
        buy: {
          pays: true,
          note: true,
          noteString: '',
          bidInfo: true,
          taxInfo: true,
          discountInfo: true,
          paper: 'A4-L',
        },
        rfbuy: {
          pays: true,
          note: true,
          noteString: '',
          bidInfo: true,
          taxInfo: true,
          discountInfo: true,
          paper: 'A4-L',
        },
        rfsell: {
          pays: true,
          note: true,
          noteString: '',
          bidInfo: true,
          taxInfo: true,
          discountInfo: true,
          paper: 'A4-L',
        },
        repservice: {
          noteString: '',
          paper: 'A4-L',
        },
        fastsell: {
          invoice: true,
          cashdeskTicket: true,
          pdf: true
        }
      }
    }
  },
  methods: {
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
    submit() {
      this.loading = true;
      axios.post('/api/printers/options/save', this.settings).then((response) => {
        if (response.data.code == 0) {
          Swal.fire({
            text: 'با موفقیت ثبت شد.',
            icon: 'success',
            confirmButtonText: 'قبول',
          })
        }
        this.loading = false;
      })
    }
  },
  async beforeMount() {
    this.loading = true;
    axios.post("/api/printers/options/info").then((response) => {
      this.loading = false;
      this.settings = response.data;
      if (this.settings.repservice.paper == null || this.settings.repservice.paper == '') {
        this.settings.repservice.paper = 'A5-L';
      }
    });
    //get active plugins
    axios.post('/api/plugin/get/actives',).then((response) => {
      this.plugins = response.data;
    });
  }
}
</script>

<style scoped></style>