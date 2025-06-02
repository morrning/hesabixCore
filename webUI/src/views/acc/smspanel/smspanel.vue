<template>
  <v-container fluid class="pa-0">
    <v-card>
      <v-toolbar color="grey-lighten-4" flat title="سرویس پیامک و افزایش اعتبار" class="text-primary-dark">
        <template v-slot:prepend>
          <v-tooltip :text="$t('dialog.back')" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
                icon="mdi-arrow-right" />
            </template>
          </v-tooltip>
        </template>
      </v-toolbar>

      <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
        {{ snackbar.text }}
        <template v-slot:actions>
          <v-btn variant="text" @click="snackbar.show = false">
            بستن
          </v-btn>
        </template>
      </v-snackbar>

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
              <v-alert type="info" variant="tonal" class="mb-4" border="start" color="primary">
                <template v-slot:prepend>
                  <v-icon icon="mdi-information-outline" color="primary" size="24"></v-icon>
                </template>
                <div class="text-body-1">
                  <div class="d-flex align-center mb-2">
                    <v-icon icon="mdi-currency-usd" size="18" color="primary" class="ml-1"></v-icon>
                    <span class="font-weight-medium">اطلاعات مالی:</span>
                  </div>
                  <ul class="mb-0 ps-4">
                    <li class="mb-2">به مبالغ انتخاب شده ۱۰ درصد مالیات بر ارزش افزوده اضافه می‌گردد.</li>
                    <li class="mb-2">اعتبار خریداری شده بلافاصله به حساب شما اضافه خواهد شد.</li>
                    <li>این اعتبار صرفاً برای استفاده از سرویس پیامک کوتاه قابل استفاده است و برای سایر خدمات قابل استفاده نمی‌باشد.</li>
                  </ul>
                </div>
              </v-alert>

              <v-row>
                <v-col cols="12" sm="6" md="3" v-for="(amount, index) in chargeAmounts" :key="index">
                  <v-card :class="['charge-card', { 'selected': smsCharge === amount.value }]"
                    @click="smsCharge = amount.value" :elevation="smsCharge === amount.value ? 4 : 1" class="h-100"
                    :color="smsCharge === amount.value ? 'primary' : 'surface'" variant="elevated">
                    <v-card-text class="text-center">
                      <div class="text-h6 mb-2" :class="{ 'text-white': smsCharge === amount.value }">{{ amount.label }}
                      </div>
                      <div class="text-subtitle-1"
                        :class="{ 'text-white': smsCharge === amount.value, 'text-medium-emphasis': smsCharge !== amount.value }">
                        {{ formatPrice(amount.value) }} تومان
                      </div>
                      <div class="text-caption mt-2"
                        :class="{ 'text-white': smsCharge === amount.value, 'text-medium-emphasis': smsCharge !== amount.value }">
                        با احتساب مالیات: {{ formatPrice(amount.value * 1.1) }} تومان
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
          <v-row class="mt-6">
            <v-col cols="12" class="text-center">
              <v-btn color="primary" :loading="loading" size="large" :disabled="!smsCharge" @click="pay" class="px-8">
                <v-icon start>mdi-credit-card-outline</v-icon>
                پرداخت آنلاین
              </v-btn>
            </v-col>
          </v-row>
        </v-window-item>

        <v-window-item value="profile" class="pa-4">
          <h4 class="mb-3">تنظیمات سرویس پیامک</h4>
          <v-alert type="info" variant="tonal" class="mb-4">
            در نظر داشته باشید در صورت اتمام اعتبار سرویس پیامک کسب و کار شما، این تنظیمات نادیده گرفته می‌شود.
            <ul>
              <li>پیامک‌های ارسالی به شماره ثبت شده در بخش اشخاص (تلفن همراه) ارسال می‌شود.</li>
              <li>در صورت ثبت نکردن شماره تلفن در بخش اشخاص پیامک ارسال نمی شود و هزینه ای نیز از حساب شما کسر نخواهد
                شد.</li>
            </ul>
          </v-alert>
          <v-col cols="12" md="6">
            <v-checkbox v-model="settings.sendAfterSell" @change="saveSettings(settings)"
              label="ارسال پیامک به مشتری بعد از صدور فاکتور فروش"></v-checkbox>
            <v-checkbox v-model="settings.sendAfterSellPayOnline" @change="saveSettings(settings)"
              label="ارسال پیامک به مشتری جهت پرداخت آنلاین فاکتور فروش" disabled></v-checkbox>
            <v-divider class="my-2"></v-divider>
            <v-checkbox v-model="settings.sendAfterBuy" @change="saveSettings(settings)"
              label="ارسال پیامک به تامین کننده بعد از صدور فاکتور خرید" disabled></v-checkbox>
            <v-checkbox v-model="settings.sendAfterBuyToUser" @change="saveSettings(settings)"
              label="ارسال پیامک به تامین کننده بعد از ثبت پرداخت فاکتور خرید" disabled></v-checkbox>
          </v-col>
        </v-window-item>

        <v-window-item value="pays" class="pa-4">
          <v-text-field v-model="payssearchValue" prepend-inner-icon="mdi-magnify" placeholder="جست و جو ..."
            variant="outlined" class="mb-4" density="compact"></v-text-field>
          <v-data-table :headers="paysheaders" :items="paysitems" :search="payssearchValue" :loading="loading"
            :header-props="{ class: 'custom-header' }" loading-text="در حال بارگذاری..."
            no-data-text="اطلاعاتی برای نمایش وجود ندارد">
            <template v-slot:item.status="{ item }">
              <span :class="item.status === 0 ? 'text-danger' : 'text-success'">
                {{ item.status === 0 ? 'پرداخت نشده' : 'پرداخت شده' }}
              </span>
            </template>
          </v-data-table>
        </v-window-item>

        <v-window-item value="contact" class="pa-4">
          <v-text-field v-model="searchValue" prepend-inner-icon="mdi-magnify" placeholder="جست و جو ..."
            variant="outlined" class="mb-4" density="compact"></v-text-field>
          <v-data-table :headers="headers" :items="items" :header-props="{ class: 'custom-header' }"
            :search="searchValue" :loading="loading" loading-text="در حال بارگذاری..."
            no-data-text="اطلاعاتی برای نمایش وجود ندارد"></v-data-table>
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
    snackbar: {
      show: false,
      text: '',
      color: 'error'
    },
    smsCharge: 100000,
    chargeAmounts: [
      { label: '۵۰ هزار تومان', value: 50000 },
      { label: '۱۰۰ هزار تومان', value: 100000 },
      { label: '۲۰۰ هزار تومان', value: 200000 },
      { label: '۵۰۰ هزار تومان', value: 500000 }
    ],
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
      { title: "مبلغ (تومان)", key: "price" },
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
      const amountInRial = this.smsCharge * 10; // تبدیل تومان به ریال
      axios.post('/api/sms/charge', { price: amountInRial })
        .then((response) => {
          if (response.data.Success === true) {
            window.location.href = response.data.targetURL;
          } else {
            this.snackbar.text = response.data.message || 'خطا در ایجاد درخواست پرداخت';
            this.snackbar.color = 'error';
            this.snackbar.show = true;
          }
        })
        .catch((error) => {
          this.snackbar.text = 'خطا در ارتباط با سرور';
          this.snackbar.color = 'error';
          this.snackbar.show = true;
          console.error('Error:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    saveSettings(settings: { sendAfterSell: boolean; sendAfterSellPayOnline: boolean; sendAfterBuy: boolean; sendAfterBuyToUser: boolean; }) {
      this.loading = true;
      axios.post('/api/sms/save/settings', { settings })
        .then(() => {
          this.loading = false;
        })
    },
    formatPrice(price: number): string {
      return new Intl.NumberFormat('fa-IR').format(price);
    },
  },
  beforeMount() {
    this.loadData();
  }
})
</script>

<style scoped>
.charge-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
}

.charge-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px 0 rgba(0, 0, 0, 0.1);
}

.charge-card.selected {
  border-color: transparent;
  background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
  color: white;
  box-shadow: 0 8px 25px 0 rgba(33, 150, 243, 0.3);
}

.charge-card .v-card-text {
  padding: 1.5rem;
}

.charge-card:not(.selected):hover {
  border-color: #2196F3;
  background: linear-gradient(135deg, #ffffff 0%, #E3F2FD 100%);
}

/* اضافه کردن انیمیشن برای تغییر رنگ */
.charge-card {
  position: relative;
  overflow: hidden;
}

.charge-card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.1) 0%, rgba(25, 118, 210, 0.1) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.charge-card:hover::after {
  opacity: 1;
}

.charge-card.selected::after {
  opacity: 0;
}

.info-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.info-card .v-card-text {
  background: #ffffff;
  border-radius: 8px;
}

.v-alert {
  background-color: rgba(var(--v-theme-primary), 0.05) !important;
  border-left: 4px solid rgb(var(--v-theme-primary)) !important;
}

.v-alert ul {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.v-alert ul li {
  position: relative;
  padding-right: 1.5rem;
  line-height: 1.6;
}

.v-alert ul li::before {
  content: "•";
  color: rgb(var(--v-theme-primary));
  position: absolute;
  right: 0;
  font-size: 1.2em;
}

.custom-amount-input :deep(.v-field__input) {
  color: white !important;
  caret-color: white !important;
}

.custom-amount-input :deep(.v-field__input::placeholder) {
  color: rgba(255, 255, 255, 0.7) !important;
}

.custom-amount-input :deep(.v-field__outline) {
  border-color: rgba(255, 255, 255, 0.7) !important;
}
</style>