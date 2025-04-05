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
              <v-row>
                <v-col cols="12">
                  <v-row dense>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.sell.bidInfo" label="اطلاعات کسب‌وکار" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.sell.pays" label="نمایش پرداخت‌های فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.sell.note" label="یادداشت پایین فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.sell.taxInfo" label="مالیات به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.sell.discountInfo" label="تخفیف به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" md="6">
                  <v-textarea v-model="settings.sell.noteString" label="یادداشت پایین فاکتور" rows="4"
                    placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد"></v-textarea>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.sell.paper" :items="paperOptions" label="سایز کاغذ و حالت چاپ"></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="1">
          <v-card>
            <v-card-text>
              <v-row>
                <v-col cols="12">
                  <v-row dense>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.buy.bidInfo" label="اطلاعات کسب‌وکار" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.buy.pays" label="نمایش پرداخت‌های فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.buy.note" label="یادداشت پایین فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.buy.taxInfo" label="مالیات به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.buy.discountInfo" label="تخفیف به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" md="6">
                  <v-textarea v-model="settings.buy.noteString" label="یادداشت پایین فاکتور" rows="4"
                    placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد"></v-textarea>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.buy.paper" :items="paperOptions" label="سایز کاغذ و حالت چاپ"></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="2">
          <v-card>
            <v-card-text>
              <v-row>
                <v-col cols="12">
                  <v-row dense>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfbuy.bidInfo" label="اطلاعات کسب‌وکار" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfbuy.pays" label="نمایش پرداخت‌های فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfbuy.note" label="یادداشت پایین فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfbuy.taxInfo" label="مالیات به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfbuy.discountInfo" label="تخفیف به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" md="6">
                  <v-textarea v-model="settings.rfbuy.noteString" label="یادداشت پایین فاکتور" rows="4"
                    placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد"></v-textarea>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.rfbuy.paper" :items="paperOptions" label="سایز کاغذ و حالت چاپ"></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="3">
          <v-card>
            <v-card-text>
              <v-row>
                <v-col cols="12">
                  <v-row dense>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfsell.bidInfo" label="اطلاعات کسب‌وکار" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfsell.pays" label="نمایش پرداخت‌های فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfsell.note" label="یادداشت پایین فاکتور" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfsell.taxInfo" label="مالیات به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.rfsell.discountInfo" label="تخفیف به تفکیک اقلام" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12" md="6">
                  <v-textarea v-model="settings.rfsell.noteString" label="یادداشت پایین فاکتور" rows="4"
                    placeholder="این نوشته در پایین فاکتور‌ها چاپ خواهد شد"></v-textarea>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.rfsell.paper" :items="paperOptions" label="سایز کاغذ و حالت چاپ"></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="4">
          <v-card>
            <v-card-text>
              <v-row>
                <v-col cols="12">
                  <v-row dense>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.fastsell.invoice" :label="$t('dialog.invoice')" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.fastsell.cashdeskTicket" :label="$t('dialog.cashdeskTicket')" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6" md="4" lg="3">
                      <v-switch v-model="settings.fastsell.pdf" :label="$t('dialog.export_pdf')" color="primary" hide-details density="compact"></v-switch>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="5">
          <v-card>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <CustomEditor title="یادداشت پایین قبض تعمیرات" v-model="settings.repservice.noteString" />
                </v-col>
                <v-col cols="12" md="6">
                  <v-select v-model="settings.repservice.paper" :items="paperOptions" label="سایز کاغذ و حالت چاپ"></v-select>
                </v-col>
              </v-row>
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
import { ref, onMounted } from "vue";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import CustomEditor from '@/components/Editor.vue'

export default {
  name: "bussiness",
  components: {
    Loading,
    CustomEditor
  },
  data: () => ({
    loading: ref(false),
    tabs: 0,
    plugins: [],
    paperOptions: [
      { title: 'A4 افقی', value: 'A4-L' },
      { title: 'A4 عمودی', value: 'A4' },
      { title: 'A5 افقی', value: 'A5-L' },
      { title: 'A5 عمودی', value: 'A5' }
    ],
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
  }),
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

<style scoped>

</style>