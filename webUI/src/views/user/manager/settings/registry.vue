<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';

export default defineComponent({
  name: 'registry',
  data: () => ({
    loading: true,
    tab: null,
    settings: {
      canRegister: true,
      canFreeAccounting: false,
      smsPrice: '0',
      cloudPricePerGb: '0',
      unlimitedPrice: '0',
      accountingDocPrice: '0',
      giftCredit: '0',
      unlimitedDuration: [] as string[],
      smsAlertEnabled: false,
      smsAlertMobile: '',
      autoUpdateTicketsEnabled: false,
      autoUpdateTicketsInterval: 24,
      sponsorMessage: '',
      footerLeft: '',
      footerRight: '',
      appName: '',
      appUrl: '',
      appSlogan: '',
      verifyMobileViaSms: false,
      ftpEnabled: false,
      ftpHost: '',
      ftpPort: '21',
      ftpUsername: '',
      ftpPassword: '',
      ftpPath: '',
    },
    dialogVisible: false,
    dialogMessage: '',
    dialogLoading: false,
  }),
  methods: {
    async loadData() {
      this.loading = true;
      try {
        const settingsResponse = await axios.post('/api/admin/registry/settings/load');
        if (settingsResponse.data.result === 1) {
          const data = settingsResponse.data.data;
          this.settings = {
            ...this.settings,
            ...data,
            smsPrice: this.formatNumber(data.smsPrice),
            cloudPricePerGb: this.formatNumber(data.cloudPricePerGb),
            unlimitedPrice: this.formatNumber(data.unlimitedPrice),
            accountingDocPrice: this.formatNumber(data.accountingDocPrice),
            giftCredit: this.formatNumber(data.giftCredit),
            appUrl: data.appUrl || '',
            appSlogan: data.appSlogan || '',
            verifyMobileViaSms: data.verifyMobileViaSms || false,
          };
          this.checkFreeAccounting();
        }

        const cronResponse = await axios.get('/api/admin/cron/check-autoupdate-tickets');
        this.settings.autoUpdateTicketsEnabled = cronResponse.data.enabled;
        this.settings.autoUpdateTicketsInterval = cronResponse.data.interval || 24;
      } catch (error) {
        console.error('Error loading data:', error);
      } finally {
        this.loading = false;
      }
    },
    async submit() {
      if (!this.validateForm()) return;

      this.dialogLoading = true;
      this.dialogVisible = true;
      this.dialogMessage = 'در حال ذخیره تنظیمات...';
      
      const submitData = {
        ...this.settings,
        smsPrice: this.parseNumber(this.settings.smsPrice),
        cloudPricePerGb: this.parseNumber(this.settings.cloudPricePerGb),
        unlimitedPrice: this.parseNumber(this.settings.unlimitedPrice),
        accountingDocPrice: this.parseNumber(this.settings.accountingDocPrice),
        giftCredit: this.parseNumber(this.settings.giftCredit),
        appUrl: this.settings.appUrl,
        appSlogan: this.settings.appSlogan,
        verifyMobileViaSms: this.settings.verifyMobileViaSms,
      };

      try {
        const settingsResp = await axios.post('/api/admin/registry/settings/save', submitData);
        let messages = [settingsResp.data.result === 1 ? 'تنظیمات با موفقیت ذخیره شد' : 'خطا در ذخیره تنظیمات'];

        if (this.settings.autoUpdateTicketsEnabled) {
          const cronResp = await axios.post('/api/admin/cron/add-autoupdate-tickets', {
            interval: this.settings.autoUpdateTicketsInterval,
          });
          messages.push(cronResp.data.message);
        } else {
          const removeResp = await axios.post('/api/admin/cron/remove-autoupdate-tickets');
          messages.push(removeResp.data.message);
        }
        
        this.dialogMessage = messages.join('\n');
      } catch (error) {
        this.dialogMessage = error.response?.data?.message || 'خطایی رخ داد';
      } finally {
        this.dialogLoading = false;
        this.loading = false;
      }
    },
    validateForm() {
      if (!this.settings.canFreeAccounting) {
        const checks = [
          { value: this.parseNumber(this.settings.accountingDocPrice), message: 'قیمت هر سند حسابداری باید بزرگ‌تر از صفر باشد' },
          { value: this.parseNumber(this.settings.unlimitedPrice), message: 'قیمت هر ماه استفاده نامحدود باید بزرگ‌تر از صفر باشد' },
          { value: this.settings.unlimitedDuration.length, message: 'حداقل یکی از مدت زمان‌های بسته نامحدود باید انتخاب شود' }
        ];

        const error = checks.find(check => check.value <= 0);
        if (error) {
          this.showErrorDialog(error.message);
          return false;
        }
      }

      // اعتبارسنجی تنظیمات FTP
      if (this.settings.ftpEnabled) {
        const ftpChecks = [
          { value: this.settings.ftpHost, message: 'آدرس سرور FTP الزامی است' },
          { value: this.settings.ftpPort, message: 'پورت FTP الزامی است' },
          { value: this.settings.ftpUsername, message: 'نام کاربری FTP الزامی است' },
          { value: this.settings.ftpPassword, message: 'رمز عبور FTP الزامی است' },
          { value: this.settings.ftpPath, message: 'مسیر ذخیره‌سازی FTP الزامی است' }
        ];

        const ftpError = ftpChecks.find(check => !check.value);
        if (ftpError) {
          this.showErrorDialog(ftpError.message);
          return false;
        }

        // اعتبارسنجی پورت
        const port = parseInt(this.settings.ftpPort);
        if (isNaN(port) || port < 1 || port > 65535) {
          this.showErrorDialog('پورت FTP باید عددی بین 1 تا 65535 باشد');
          return false;
        }

        // اعتبارسنجی آدرس سرور
        const hostRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/;
        if (!hostRegex.test(this.settings.ftpHost)) {
          this.showErrorDialog('آدرس سرور FTP نامعتبر است');
          return false;
        }
      }

      return true;
    },
    async runTicketUpdate() {
      this.dialogVisible = true;
      this.dialogLoading = true;
      this.dialogMessage = 'در حال بررسی تیکت‌ها...';

      try {
        const response = await axios.post('/api/admin/cron/run-autoupdate-tickets');
        this.dialogMessage = response.data.message;
      } catch (error) {
        this.dialogMessage = error.response?.data?.message || 'خطایی در بررسی تیکت‌ها رخ داد';
      } finally {
        this.dialogLoading = false;
      }
    },
    showErrorDialog(message: string) {
      this.dialogVisible = true;
      this.dialogMessage = message;
      this.dialogLoading = false;
    },
    formatNumber(value: number | string): string {
      const cleanValue = value.toString().replace(/[^0-9]/g, '');
      return cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',') || '0';
    },
    parseNumber(value: string): number {
      return Number(value.replace(/,/g, '')) || 0;
    },
    handleInput(field: keyof typeof this.settings, value: string) {
      this.settings[field] = this.formatNumber(value);
    },
    checkFreeAccounting() {
      if (this.settings.canFreeAccounting) {
        this.settings.accountingDocPrice = '0';
        this.settings.unlimitedPrice = '0';
        this.settings.unlimitedDuration = [];
      }
    },
    validateInterval() {
      if (this.settings.autoUpdateTicketsInterval < 1) {
        this.settings.autoUpdateTicketsInterval = 1;
      }
    },
    async testFtpConnection() {
      if (!this.validateFtpFields()) {
        return;
      }

      this.dialogVisible = true;
      this.dialogLoading = true;
      this.dialogMessage = 'در حال تست اتصال به سرور FTP...';

      try {
        const response = await axios.post('/api/admin/registry/settings/test-ftp', {
          host: this.settings.ftpHost,
          port: this.settings.ftpPort,
          username: this.settings.ftpUsername,
          password: this.settings.ftpPassword,
          path: this.settings.ftpPath
        });

        this.dialogMessage = response.data.success 
          ? 'اتصال به سرور FTP با موفقیت برقرار شد'
          : 'خطا در اتصال به سرور FTP: ' + response.data.message;
      } catch (error: any) {
        this.dialogMessage = error.response?.data?.message || 'خطایی در تست اتصال رخ داد';
      } finally {
        this.dialogLoading = false;
      }
    },
    validateFtpFields() {
      if (!this.settings.ftpEnabled) {
        this.showErrorDialog('لطفا ابتدا اتصال FTP را فعال کنید');
        return false;
      }

      const ftpChecks = [
        { value: this.settings.ftpHost, message: 'آدرس سرور FTP الزامی است' },
        { value: this.settings.ftpPort, message: 'پورت FTP الزامی است' },
        { value: this.settings.ftpUsername, message: 'نام کاربری FTP الزامی است' },
        { value: this.settings.ftpPassword, message: 'رمز عبور FTP الزامی است' },
        { value: this.settings.ftpPath, message: 'مسیر ذخیره‌سازی FTP الزامی است' }
      ];

      const ftpError = ftpChecks.find(check => !check.value);
      if (ftpError) {
        this.showErrorDialog(ftpError.message);
        return false;
      }

      const port = parseInt(this.settings.ftpPort);
      if (isNaN(port) || port < 1 || port > 65535) {
        this.showErrorDialog('پورت FTP باید عددی بین 1 تا 65535 باشد');
        return false;
      }

      const hostRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/;
      if (!hostRegex.test(this.settings.ftpHost)) {
        this.showErrorDialog('آدرس سرور FTP نامعتبر است');
        return false;
      }

      return true;
    },
  },
  watch: {
    'settings.canFreeAccounting'(newValue) {
      this.checkFreeAccounting();
    },
  },
  beforeMount() {
    this.loadData();
  },
});
</script>

<template>
  <v-container fluid class="pa-0">
    <v-toolbar color="toolbar">
      <v-toolbar-title>{{ $t('pages.manager.system_settings_registry') }}</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn 
        icon="mdi-content-save" 
        color="primary" 
        :loading="loading" 
        @click="submit"
      ></v-btn>
    </v-toolbar>

    <v-tabs v-model="tab" grow>
      <v-tab value="general">تنظیمات عمومی</v-tab>
      <v-tab value="pricing">قیمت‌گذاری</v-tab>
      <v-tab value="automation">اتوماسیون</v-tab>
      <v-tab value="ftp">تنظیمات FTP</v-tab>
    </v-tabs>

    <v-card :loading="loading ? 'red' : undefined" :disabled="loading">
      <v-window v-model="tab">
        <v-window-item value="general">
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="12" md="4">
                <v-switch
                  v-model="settings.canRegister"
                  label="فعال بودن ثبت‌نام کاربران جدید"
                  color="primary"
                ></v-switch>
              </v-col>
              <v-col cols="12" sm="12" md="4">
                <v-switch
                  v-model="settings.verifyMobileViaSms"
                  label="تایید شماره موبایل از طریق پیامک"
                  color="primary"
                ></v-switch>
              </v-col>
              <v-col cols="12" sm="12" md="4">
                <v-switch
                  v-model="settings.canFreeAccounting"
                  label="فعال بودن ثبت رایگان سند حسابداری"
                  color="primary"
                ></v-switch>
              </v-col>
              <v-row>
              <v-col cols="12" sm="12" md="4">
                <v-text-field
                  v-model="settings.appName"
                  label="اسم برنامه"
                  hint="این نام در بخش‌های مختلف نرم‌افزار مانند هدر و نمایش فاکتور برای مشتریان و ... نمایش داده می‌شود"
                  persistent-hint
                  type="text"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="4">
                <v-text-field
                  v-model="settings.appUrl"
                  label="آدرس برنامه"
                  hint="آدرس URL برنامه"
                  persistent-hint
                  type="url"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="4">
                <v-text-field
                  v-model="settings.giftCredit"
                  label="اعتبار هدیه به کسب‌و‌کار جدید (ریال)"
                  type="text"
                  @input="handleInput('giftCredit', $event.target.value)"
                  prefix="₽"
                ></v-text-field>
              </v-col>
            </v-row>
              <v-col cols="12" sm="12" md="12">
                <v-text-field
                  v-model="settings.appSlogan"
                  label="شعار برنامه"
                  hint="این شعار در بخش‌های مختلف نرم‌افزار نمایش داده می‌شود"
                  persistent-hint
                  type="text"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="12">
                <v-textarea
                  v-model="settings.sponsorMessage"
                  label="پیام اسپانسر (نمایش در صفحه ورود به کسب و کار)"
                  hint="این پیام در صفحه لودینگ ورود به کسب و کار نمایش داده می‌شود"
                  persistent-hint
                  rows="4"
                  class="mt-4"
                ></v-textarea>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="settings.footerLeft"
                  label="پاورقی سمت چپ اسناد چاپی"
                  type="text"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="settings.footerRight"
                  label="پاورقی سمت راست اسناد چاپی"
                  type="text"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
        </v-window-item>

        <v-window-item value="pricing">
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="settings.smsPrice"
                  label="قیمت هر پیامک (ریال)"
                  type="text"
                  @input="handleInput('smsPrice', $event.target.value)"
                  prefix="₽"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="settings.cloudPricePerGb"
                  label="قیمت هر گیگ/ماه فضای ابری (ریال)"
                  type="text"
                  @input="handleInput('cloudPricePerGb', $event.target.value)"
                  prefix="₽"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="settings.accountingDocPrice"
                  label="قیمت ثبت هر سند حسابداری (ریال)"
                  type="text"
                  @input="handleInput('accountingDocPrice', $event.target.value)"
                  prefix="₽"
                  :disabled="settings.canFreeAccounting"
                ></v-text-field>
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="settings.unlimitedPrice"
                  label="قیمت هر ماه استفاده نامحدود (ریال)"
                  type="text"
                  @input="handleInput('unlimitedPrice', $event.target.value)"
                  prefix="₽"
                  :disabled="settings.canFreeAccounting"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-card flat :disabled="settings.canFreeAccounting">
                  <v-card-title>مدت زمان بسته‌های نامحدود</v-card-title>
                  <v-card-text>
                    <v-row>
                      <v-col v-for="month in ['1', '3', '6', '12']" :key="month" cols="12" sm="3">
                        <v-checkbox
                          v-model="settings.unlimitedDuration"
                          :label="`${month} ماه`"
                          :value="month"
                          :disabled="settings.canFreeAccounting"
                        ></v-checkbox>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-window-item>

        <v-window-item value="automation">
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="12" md="6">
                <v-card variant="outlined" class="pa-4 mb-4">
                  <v-row>
                    <v-col cols="12" sm="4">
                      <v-switch
                        v-model="settings.smsAlertEnabled"
                        label="هشدار پیامکی"
                        color="primary"
                        density="comfortable"
                      ></v-switch>
                    </v-col>
                    <v-col cols="12" sm="8">
                      <v-text-field
                        v-model="settings.smsAlertMobile"
                        label="شماره موبایل پشتیبانی"
                        :disabled="!settings.smsAlertEnabled"
                        type="tel"
                        placeholder="09180000000"
                        density="comfortable"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-card>
              </v-col>

              <v-col cols="12" sm="12" md="6">
                <v-card variant="outlined" class="pa-4 mb-4">
                  <v-row>
                    <v-col cols="12" sm="6">
                      <v-switch
                        v-model="settings.autoUpdateTicketsEnabled"
                        label="به‌روزرسانی خودکار تیکت‌ها"
                        color="primary"
                        density="comfortable"
                      ></v-switch>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model.number="settings.autoUpdateTicketsInterval"
                        label="فاصله زمانی (ساعت)"
                        type="number"
                        min="1"
                        @input="validateInterval"
                        :disabled="!settings.autoUpdateTicketsEnabled"
                        density="comfortable"
                      ></v-text-field>
                    </v-col>
                  </v-row>
                </v-card>
              </v-col>

              <v-col cols="12" class="text-center">
                <v-btn
                  @click="runTicketUpdate()"
                  color="secondary"
                  prepend-icon="mdi-refresh"
                  :loading="dialogLoading"
                  class="mt-4"
                >
                  بررسی فوری تیکت‌ها
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-window-item>

        <v-window-item value="ftp">
          <v-card-text>
            <v-row>
              <v-col cols="12">
                <v-card variant="outlined" class="pa-4 mb-4">
                  <v-row>
                    <v-col cols="12" sm="4">
                      <v-switch
                        v-model="settings.ftpEnabled"
                        label="فعال‌سازی اتصال FTP"
                        color="primary"
                        density="comfortable"
                      ></v-switch>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="settings.ftpHost"
                        label="آدرس سرور FTP"
                        :disabled="!settings.ftpEnabled"
                        type="text"
                        placeholder="ftp.example.com"
                        density="comfortable"
                        :rules="[
                          v => !settings.ftpEnabled || !!v || 'آدرس سرور FTP الزامی است',
                          v => !settings.ftpEnabled || /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/.test(v) || 'آدرس سرور FTP نامعتبر است'
                        ]"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="settings.ftpPort"
                        label="پورت"
                        :disabled="!settings.ftpEnabled"
                        type="text"
                        placeholder="21"
                        density="comfortable"
                        :rules="[
                          v => !settings.ftpEnabled || !!v || 'پورت FTP الزامی است',
                          v => !settings.ftpEnabled || (!isNaN(parseInt(v)) && parseInt(v) >= 1 && parseInt(v) <= 65535) || 'پورت FTP باید عددی بین 1 تا 65535 باشد'
                        ]"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="settings.ftpUsername"
                        label="نام کاربری"
                        :disabled="!settings.ftpEnabled"
                        type="text"
                        density="comfortable"
                        :rules="[v => !settings.ftpEnabled || !!v || 'نام کاربری FTP الزامی است']"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field
                        v-model="settings.ftpPassword"
                        label="رمز عبور"
                        :disabled="!settings.ftpEnabled"
                        type="password"
                        density="comfortable"
                        :rules="[v => !settings.ftpEnabled || !!v || 'رمز عبور FTP الزامی است']"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model="settings.ftpPath"
                        label="مسیر ذخیره‌سازی"
                        :disabled="!settings.ftpEnabled"
                        type="text"
                        placeholder="/public_html/backup"
                        density="comfortable"
                        :rules="[
                          v => !settings.ftpEnabled || !!v || 'مسیر ذخیره‌سازی FTP الزامی است',
                          v => !settings.ftpEnabled || v.startsWith('/') || 'مسیر باید با / شروع شود'
                        ]"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" class="text-center">
                      <v-btn
                        @click="testFtpConnection"
                        color="secondary"
                        prepend-icon="mdi-connection"
                        :loading="dialogLoading"
                        :disabled="!settings.ftpEnabled"
                        class="mt-4"
                      >
                        تست اتصال
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-window-item>
      </v-window>
    </v-card>

    <v-dialog v-model="dialogVisible" max-width="500px">
      <v-card>
        <v-card-title>نتیجه عملیات</v-card-title>
        <v-card-text>
          <p style="white-space: pre-line">{{ dialogMessage }}</p>
          <v-progress-circular v-if="dialogLoading" indeterminate color="primary"></v-progress-circular>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" text @click="dialogVisible = false" :disabled="dialogLoading">
            بستن
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<style scoped>
.v-text-field {
  margin-bottom: 16px;
}

.v-switch {
  margin-top: 8px;
}

@media (max-width: 600px) {
  .v-card {
    margin-bottom: 16px;
  }
}
</style>