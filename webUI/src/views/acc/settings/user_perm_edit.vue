<template>
  <div>
    <v-toolbar color="toolbar" title="تنظیمات دسترسی">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
              icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
    </v-toolbar>
    <v-container>
      <v-card class="mb-4" elevation="2">
        <v-card-text>
          <v-alert
            type="info"
            variant="tonal"
            class="mb-4 rounded-lg custom-alert"
            border="start"
            border-color="primary"
            elevation="2"
          >
            <template v-slot:prepend>
              <v-avatar color="primary" class="me-3">
                <v-icon color="white">mdi-shield-account</v-icon>
              </v-avatar>
            </template>
            <div class="d-flex flex-column">
              <div class="text-h6 font-weight-bold mb-3 text-indigo-darken-2">
                تنظیم دسترسی‌های کاربر
              </div>
              <v-card variant="outlined" class="mb-3 pa-3 custom-card">
                <div class="d-flex align-center mb-2">
                  <v-icon color="indigo-darken-2" class="me-2">mdi-account-circle</v-icon>
                  <span class="text-body-1 text-grey-darken-1">نام کاربر:</span>
                  <strong class="text-body-1 me-2 text-indigo-darken-2">{{ info.user }}</strong>
                </div>
                <div class="d-flex align-center">
                  <v-icon color="indigo-darken-2" class="me-2">mdi-email-outline</v-icon>
                  <span class="text-body-1 text-grey-darken-1">پست الکترونیکی:</span>
                  <strong class="text-body-1 text-indigo-darken-2">{{ info.email }}</strong>
                </div>
              </v-card>
              <v-alert
                type="info"
                variant="tonal"
                class="mt-2 custom-warning"
                density="compact"
                border="start"
                border-color="indigo-darken-2"
              >
                <template v-slot:prepend>
                  <v-icon color="indigo-darken-2">mdi-shield-alert</v-icon>
                </template>
                <div class="text-body-2 text-indigo-darken-2">
                  لطفاً در تنظیم دسترسی‌ها دقت کنید. دسترسی‌های نامناسب می‌تواند امنیت سیستم را به خطر بیندازد.
                </div>
              </v-alert>
            </div>
          </v-alert>

          <v-row>
            <v-col cols="12" md="4">
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.persons"
                        label="اشخاص"
                        @change="savePerms('persons')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.persons"
                        :disabled="loadingSwitches.persons"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.getpay"
                        label="دریافت و پرداخت"
                        @change="savePerms('getpay')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.getpay"
                        :disabled="loadingSwitches.getpay"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.commodity"
                        label="کالا و خدمات"
                        @change="savePerms('commodity')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.commodity"
                        :disabled="loadingSwitches.commodity"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.bank"
                        label="حساب‌های بانکی"
                        @change="savePerms('bank')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.bank"
                        :disabled="loadingSwitches.bank"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.salary"
                        label="تنخواه گردان‌ها"
                        @change="savePerms('salary')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.salary"
                        :disabled="loadingSwitches.salary"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.bankTransfer"
                        label="انتقال بین بانکی"
                        @change="savePerms('bankTransfer')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.bankTransfer"
                        :disabled="loadingSwitches.bankTransfer"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.archiveUpload"
                        label="افزودن فایل به آرشیو"
                        @change="savePerms('archiveUpload')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.archiveUpload"
                        :disabled="loadingSwitches.archiveUpload"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.archiveView"
                        label="مشاهده فایل های آرشیو"
                        @change="savePerms('archiveView')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.archiveView"
                        :disabled="loadingSwitches.archiveView"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.buy"
                        label="فاکتورهای خرید"
                        @change="savePerms('buy')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.buy"
                        :disabled="loadingSwitches.buy"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.sell"
                        label="فاکتورهای فروش"
                        @change="savePerms('sell')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.sell"
                        :disabled="loadingSwitches.sell"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.cost"
                        label="هزینه‌ها"
                        @change="savePerms('cost')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.cost"
                        :disabled="loadingSwitches.cost"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.income"
                        label="درآمدها"
                        @change="savePerms('income')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.income"
                        :disabled="loadingSwitches.income"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.report"
                        label="گزارشات"
                        @change="savePerms('report')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.report"
                        :disabled="loadingSwitches.report"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.cashdesk"
                        label="صندوق‌ها"
                        @change="savePerms('cashdesk')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.cashdesk"
                        :disabled="loadingSwitches.cashdesk"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.shareholder"
                        label="سهامداران"
                        @change="savePerms('shareholder')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.shareholder"
                        :disabled="loadingSwitches.shareholder"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.archiveMod"
                        label="ویرایش فایل های آرشیو"
                        @change="savePerms('archiveMod')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.archiveMod"
                        :disabled="loadingSwitches.archiveMod"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.cheque"
                        label="مدیریت چک‌ها"
                        @change="savePerms('cheque')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.cheque"
                        :disabled="loadingSwitches.cheque"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.accounting"
                        label="اسناد حسابداری"
                        @change="savePerms('accounting')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.accounting"
                        :disabled="loadingSwitches.accounting"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.settings"
                        label="تنظیمات کسب و کار"
                        @change="savePerms('settings')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.settings"
                        :disabled="loadingSwitches.settings"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.log"
                        label="تاریخچه کسب‌و‌کار"
                        @change="savePerms('log')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.log"
                        :disabled="loadingSwitches.log"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.permission"
                        label="تغییر سطوح دسترسی"
                        @change="savePerms('permission')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.permission"
                        :disabled="loadingSwitches.permission"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.store"
                        label="انبارداری"
                        @change="savePerms('store')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.store"
                        :disabled="loadingSwitches.store"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.wallet"
                        label="کیف پول"
                        @change="savePerms('wallet')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.wallet"
                        :disabled="loadingSwitches.wallet"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.archiveDelete"
                        label="حذف فایل های آرشیو"
                        @change="savePerms('archiveDelete')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.archiveDelete"
                        :disabled="loadingSwitches.archiveDelete"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-row v-if="isPluginActive('accpro')" class="mt-4">
            <v-col cols="12">
              <v-card-title class="text-h6 font-weight-bold mb-4">بسته حسابداری پیشرفته</v-card-title>
            </v-col>
            <v-col cols="12" md="4">
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugAccproRfbuy"
                        label="فاکتورهای برگشت از خرید"
                        @change="savePerms('plugAccproRfbuy')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugAccproRfbuy"
                        :disabled="loadingSwitches.plugAccproRfbuy"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugAccproCloseYear"
                        label="بستن سال مالی"
                        @change="savePerms('plugAccproCloseYear')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugAccproCloseYear"
                        :disabled="loadingSwitches.plugAccproCloseYear"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugAccproRfsell"
                        label="فاکتورهای برگشت از فروش"
                        @change="savePerms('plugAccproRfsell')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugAccproRfsell"
                        :disabled="loadingSwitches.plugAccproRfsell"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugAccproPresell"
                        label="پیش فاکتور فروش"
                        @change="savePerms('plugAccproPresell')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugAccproPresell"
                        :disabled="loadingSwitches.plugAccproPresell"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" md="4">
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugAccproAccounting"
                        label="صدور و ویرایش اسناد حسابداری"
                        @change="savePerms('plugAccproAccounting')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugAccproAccounting"
                        :disabled="loadingSwitches.plugAccproAccounting"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-row v-if="isPluginActive('repservice')" class="mt-4">
            <v-col cols="12">
              <v-card-title class="text-h6 font-weight-bold mb-4">افزونه تعمیرکاران</v-card-title>
            </v-col>
            <v-col cols="12" md="4">
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugRepservice"
                        label="ثبت و ویرایش"
                        @change="savePerms('plugRepservice')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugRepservice"
                        :disabled="loadingSwitches.plugRepservice"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-row class="mt-4">
            <v-col v-if="isPluginActive('noghre')" cols="12" md="4">
              <v-card-title class="text-h6 font-weight-bold mb-4">افزونه کارگاه نقره سازی</v-card-title>
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugNoghreAdmin"
                        label="مدیر"
                        @change="savePerms('plugNoghreAdmin')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugNoghreAdmin"
                        :disabled="loadingSwitches.plugNoghreAdmin"
                      ></v-switch>
                    </v-list-item>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugNoghreSell"
                        label="صندوق / فروش"
                        @change="savePerms('plugNoghreSell')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugNoghreSell"
                        :disabled="loadingSwitches.plugNoghreSell"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col v-if="isPluginActive('cc')" cols="12" md="4">
              <v-card-title class="text-h6 font-weight-bold mb-4">افزونه باشگاه مشتریان</v-card-title>
              <v-card variant="outlined" class="h-100">
                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-switch
                        v-model="info.plugCCAdmin"
                        label="مدیر"
                        @change="savePerms('plugCCAdmin')"
                        hide-details
                        color="success"
                        density="comfortable"
                        :loading="loadingSwitches.plugCCAdmin"
                        :disabled="loadingSwitches.plugCCAdmin"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-container>
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="3000"
    >
      {{ snackbar.text }}
      <template v-slot:actions>
        <v-btn
          color="white"
          variant="text"
          @click="snackbar.show = false"
        >
          بستن
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "user_perm_edit",
  data: () => ({
    info: {},
    plugins: {},
    snackbar: {
      show: false,
      text: '',
      color: 'success'
    },
    loadingSwitches: {}
  }),
  methods: {
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
    getData(id) {
      // مقداردهی اولیه همه فیلدها با false
      const defaultPermissions = {
        persons: false,
        getpay: false,
        commodity: false,
        bank: false,
        salary: false,
        bankTransfer: false,
        archiveUpload: false,
        archiveView: false,
        buy: false,
        sell: false,
        cost: false,
        income: false,
        report: false,
        cashdesk: false,
        shareholder: false,
        archiveMod: false,
        cheque: false,
        accounting: false,
        settings: false,
        log: false,
        permission: false,
        store: false,
        wallet: false,
        archiveDelete: false,
        plugAccproRfbuy: false,
        plugAccproCloseYear: false,
        plugAccproRfsell: false,
        plugAccproPresell: false,
        plugAccproAccounting: false,
        plugRepservice: false,
        plugNoghreAdmin: false,
        plugNoghreSell: false,
        plugCCAdmin: false
      };

      axios.post('/api/business/get/user/permissions',
        {
          'bid': localStorage.getItem('activeBid'),
          'email': id
        }
      ).then((response) => {
        // ترکیب مقادیر پیش‌فرض با مقادیر دریافتی از سرور
        this.info = {
          ...defaultPermissions,
          ...response.data,
          bid: localStorage.getItem('activeBid')
        };
      });
      //get active plugins
      axios.post('/api/plugin/get/actives',).then((response) => {
        this.plugins = response.data;
      });
    },
    async savePerms() {
      try {
        await axios.post('/api/business/save/user/permissions', this.info);
        this.snackbar = {
          show: true,
          text: 'دسترسی‌ها با موفقیت ذخیره شد',
          color: 'success'
        };
      } catch (error) {
        console.error('Error saving permissions:', error);
        this.snackbar = {
          show: true,
          text: 'خطا در ذخیره دسترسی‌ها',
          color: 'error'
        };
      }
    }
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.getData(to.params.email);
    })
  },
}
</script>

<style scoped>
.v-list-item {
  min-height: 48px;
}
.v-card {
  transition: all 0.3s ease;
}
.v-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.v-switch {
  margin-right: 16px;
}
.custom-alert {
  background: linear-gradient(to right, #f8f9fa, #e9ecef);
}
.custom-card {
  background: linear-gradient(to right, #ffffff, #f8f9fa);
  border-color: #dee2e6;
}
.custom-warning {
  background: linear-gradient(to right, #e8eaf6, #c5cae9);
}
.text-indigo-darken-2 {
  color: #3949ab !important;
}
.v-switch.v-input--disabled {
  opacity: 0.7;
}
</style>