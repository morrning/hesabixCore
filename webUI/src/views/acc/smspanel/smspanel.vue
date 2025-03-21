<template>
  <v-container fluid class="pa-0">
    <v-card>
      <v-toolbar 
        color="grey-lighten-4" 
        flat 
        title="سرویس پیامک و افزایش اعتبار"
        class="text-primary-dark"
      >
      <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
      </v-toolbar>

      <v-tabs v-model="activeTab" bg-color="primary" align-tabs="center" grow>
        <v-tab value="home">
          <v-icon start>mdi-plus-circle</v-icon>
          افزایش اعتبار
        </v-tab>
        <v-tab value="profile">
          <v-icon start>mdi-cogs</v-icon>
          تنظیمات
        </v-tab>
        <v-tab value="pays">
          <v-icon start>mdi-list-status</v-icon>
          سوابق خرید اعتبار
        </v-tab>
        <v-tab value="contact">
          <v-icon start>mdi-history</v-icon>
          تاریخچه
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <v-window-item value="home" class="pa-4">
          <v-row>
            <v-col cols="12">
              <h4 class="mb-3">مبلغ اعتبار</h4>
              <v-alert type="info" variant="tonal" class="mb-4">
                به مبالغ زیر ۱۰ درصد مالیات بر ارزش افزوده اضافه می‌گردد.
              </v-alert>
              <v-radio-group v-model="smsCharge" inline>
                <v-radio label="100,000 ریال" value="100000"></v-radio>
                <v-radio label="500,000 ریال" value="500000"></v-radio>
                <v-radio label="1,000,000 ریال" value="1000000"></v-radio>
                <v-radio label="2,000,000 ریال" value="2000000"></v-radio>
              </v-radio-group>
            </v-col>
          </v-row>
          <v-btn 
            color="primary" 
            :loading="loading" 
            class="mt-4" 
            @click="pay"
          >
            <v-icon start>mdi-credit-card-outline</v-icon>
            پرداخت آنلاین
          </v-btn>
        </v-window-item>

        <v-window-item value="profile" class="pa-4">
          <h4 class="mb-3">تنظیمات سرویس پیامک</h4>
          <v-alert type="info" variant="tonal" class="mb-4">
            در نظر داشته باشید در صورت اتمام اعتبار سرویس پیامک کسب و کار شما، این تنظیمات نادیده گرفته می‌شود.
            <ul>
              <li>پیامک‌های ارسالی به شماره ثبت شده در بخش اشخاص (تلفن همراه) ارسال می‌شود.</li>
              <li>در صورت ثبت نکردن شماره تلفن در بخش اشخاص پیامک ارسال نمی شود و هزینه ای نیز از حساب شما کسر نخواهد شد.</li>
            </ul>
          </v-alert>
          <v-col cols="12" md="6">
            <v-checkbox
              v-model="settings.sendAfterSell"
              @change="saveSettings(settings)"
              label="ارسال پیامک به مشتری بعد از صدور فاکتور فروش"
            ></v-checkbox>
            <v-checkbox
              v-model="settings.sendAfterSellPayOnline"
              @change="saveSettings(settings)"
              label="ارسال پیامک به مشتری جهت پرداخت آنلاین فاکتور فروش"
              disabled
            ></v-checkbox>
            <v-divider class="my-2"></v-divider>
            <v-checkbox
              v-model="settings.sendAfterBuy"
              @change="saveSettings(settings)"
              label="ارسال پیامک به تامین کننده بعد از صدور فاکتور خرید"
              disabled
            ></v-checkbox>
            <v-checkbox
              v-model="settings.sendAfterBuyToUser"
              @change="saveSettings(settings)"
              label="ارسال پیامک به تامین کننده بعد از ثبت پرداخت فاکتور خرید"
              disabled
            ></v-checkbox>
          </v-col>
        </v-window-item>

        <v-window-item value="pays" class="pa-4">
          <v-text-field
            v-model="payssearchValue"
            prepend-inner-icon="mdi-magnify"
            placeholder="جست و جو ..."
            variant="outlined"
            class="mb-4"
            density="compact"
          ></v-text-field>
          <v-data-table
            :headers="paysheaders"
            :items="paysitems"
            :search="payssearchValue"
            :loading="loading"
            loading-text="در حال بارگذاری..."
            no-data-text="اطلاعاتی برای نمایش وجود ندارد"
          >
            <template v-slot:item.status="{ item }">
              <span :class="item.status === 0 ? 'text-danger' : 'text-success'">
                {{ item.status === 0 ? 'پرداخت نشده' : 'پرداخت شده' }}
              </span>
            </template>
          </v-data-table>
        </v-window-item>

        <v-window-item value="contact" class="pa-4">
          <v-text-field
            v-model="searchValue"
            prepend-inner-icon="mdi-magnify"
            placeholder="جست و جو ..."
            variant="outlined"
            class="mb-4"
            density="compact"
          ></v-text-field>
          <v-data-table
            :headers="headers"
            :items="items"
            :search="searchValue"
            :loading="loading"
            loading-text="در حال بارگذاری..."
            no-data-text="اطلاعاتی برای نمایش وجود ندارد"
          ></v-data-table>
        </v-window-item>
      </v-window>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import axios from "axios";

export default defineComponent({
  name: "smspanel",
  data: () => ({
    activeTab: 'home',
    settings: {
      sendAfterSell: false,
      sendAfterSellPayOnline: false,
      sendAfterBuy: false,
      sendAfterBuyToUser: false,
    },
    smsCharge: 100000,
    searchValue: '',
    loading: true,
    items: [],
    headers: [
      { title: "تاریخ", key: "date" },
      { title: "کاربر", key: "user" },
      { title: "توضیحات", key: "des" },
    ],
    payssearchValue: '',
    paysitems: [] as Array<{ dateSubmit: string; price: number; des: string; status: number }>,
    paysheaders: [
      { title: "تاریخ", key: "dateSubmit" },
      { title: "مبلغ (ریال)", key: "price" },
      { title: "توضیحات", key: "des" },
      { title: "وضعیت", key: "status" },
    ]
  }),
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/business/logs/' + localStorage.getItem('activeBid'), { type: 'sms' })
        .then((response) => {
          this.items = response.data;
          axios.post('/api/sms/load/settings')
            .then((response) => {
              this.settings = response.data;
              axios.post('/api/sms/load/pays')
                .then((response) => {
                  this.paysitems = response.data;
                  this.loading = false;
                })
            });
        });
    },
    pay() {
      this.loading = true;
      axios.post('/api/sms/charge', { price: this.smsCharge })
        .then((response) => {
          if (response.data.Success === true) {
            window.location.href = response.data.targetURL;
          }
        })
    },
    saveSettings(settings: { sendAfterSell: boolean; sendAfterSellPayOnline: boolean; sendAfterBuy: boolean; sendAfterBuyToUser: boolean; }) {
      this.loading = true;
      axios.post('/api/sms/save/settings', { settings })
        .then(() => {
          this.loading = false;
        })
    }
  },
  beforeMount() {
    this.loadData();
  }
})
</script>

<style scoped>
/* استایل‌های دلخواهت رو اینجا بذار */
</style>