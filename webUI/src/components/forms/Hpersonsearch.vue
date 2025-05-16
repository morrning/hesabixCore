<template>
  <div>
    <v-menu v-model="menu" :close-on-content-click="false">
      <template v-slot:activator="{ props }">
        <v-text-field
          v-bind="props"
          v-model="displayValue"
          variant="outlined"
          :error-messages="errorMessages"
          :rules="combinedRules"
          :label="label"
          class="my-0"
          prepend-inner-icon="mdi-account"
          clearable
          @click:clear="clearSelection"
          :loading="loading"
          @keydown.enter="handleEnter"
          hide-details
          density="comfortable"
          style="font-size: 0.7rem;"
        >
          <template v-slot:append-inner>
            <v-icon>{{ menu ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
          </template>
        </v-text-field>
      </template>

      <v-card min-width="300" max-width="400" class="search-card">
        <v-card-text class="pa-2">
          <template v-if="!loading">
            <v-list density="compact" class="list-container">
              <template v-if="filteredItems.length > 0">
                <v-list-item
                  v-for="item in filteredItems"
                  :key="item.id"
                  @click="selectItem(item)"
                  class="mb-2 search-result-item"
                  :class="{ 'selected-item': selectedItem?.id === item.id }"
                >
                  <div class="d-flex flex-column w-100">
                    <div class="d-flex align-center justify-space-between mb-1">
                      <div class="d-flex align-center">
                        <v-icon size="small" color="primary" class="mr-1">mdi-cellphone</v-icon>
                        <span class="text-caption text-primary-dark">{{ item.mobile || 'بدون موبایل' }}</span>
                      </div>
                      <span class="text-caption text-grey-darken-1">{{ item.code }}</span>
                    </div>
                    <div class="d-flex align-center justify-space-between">
                      <span class="text-body-2 font-weight-medium text-primary-dark">{{ item.nikename }}</span>
                      <v-chip
                        size="small"
                        :color="getBalanceColor(item.balance)"
                        class="balance-chip"
                        :class="getBalanceTextColor(item.balance)"
                      >
                        {{ formatBalance(item.balance) }}
                      </v-chip>
                    </div>
                  </div>
                </v-list-item>
              </template>
              <template v-else>
                <v-list-item>
                  <v-list-item-title class="text-center text-grey">
                    نتیجه‌ای یافت نشد
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-list>
            <v-btn
              v-if="filteredItems.length === 0"
              block
              color="primary"
              class="mt-2"
              @click="showAddDialog = true"
            >
              افزودن کاربر جدید
            </v-btn>
          </template>
          <v-progress-circular
            v-else
            indeterminate
            color="primary"
            class="d-flex mx-auto my-4"
          ></v-progress-circular>
        </v-card-text>
      </v-card>
    </v-menu>

    <v-dialog v-model="showAddDialog" :fullscreen="$vuetify.display.mobile" max-width="800">
      <v-card>
        <v-toolbar color="primary" density="compact" class="sticky-toolbar">
          <v-toolbar-title>افزودن کاربر جدید</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-tooltip text="بستن">
            <template v-slot:activator="{ props }">
              <v-btn
                icon="mdi-close"
                v-bind="props"
                @click="showAddDialog = false"
              ></v-btn>
            </template>
          </v-tooltip>
          <v-tooltip text="ذخیره">
            <template v-slot:activator="{ props }">
              <v-btn
                icon="mdi-content-save"
                v-bind="props"
                @click="savePerson"
                :loading="saving"
              ></v-btn>
            </template>
          </v-tooltip>
        </v-toolbar>

        <v-tabs
          v-model="tabs"
          color="primary"
          show-arrows
          class="sticky-tabs"
        >
          <v-tab value="basic" class="flex-grow-1">اطلاعات پایه</v-tab>
          <v-tab value="contact" class="flex-grow-1">اطلاعات تماس</v-tab>
          <v-tab value="address" class="flex-grow-1">آدرس</v-tab>
          <v-tab value="bank" class="flex-grow-1">حساب‌های بانکی</v-tab>
        </v-tabs>

        <v-card-text class="content-container">
          <v-window v-model="tabs">
            <v-window-item value="basic">
              <v-form @submit.prevent="savePerson">
                <v-row class="mt-4">
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="newPerson.nikename"
                      label="نام مستعار *"
                      required
                      :error-messages="nikenameErrors"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="newPerson.name"
                      label="نام و نام خانوادگی"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="newPerson.company"
                      label="شرکت"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="newPerson.des"
                      label="توضیحات"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="newPerson.speedAccess"
                      label="دسترسی سریع"
                      color="primary"
                    ></v-switch>
                  </v-col>
                  <v-col cols="12">
                    <v-card variant="outlined" class="pa-2">
                      <v-card-title class="text-subtitle-1">نوع مشتری</v-card-title>
                      <v-card-text class="pa-0">
                        <v-row dense>
                          <v-col v-for="(type, index) in personTypes" :key="type.code" cols="12" sm="6" md="4">
                            <v-checkbox
                              v-model="newPerson.types[index].checked"
                              :label="type.label"
                              color="primary"
                              density="compact"
                              hide-details
                            ></v-checkbox>
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </v-form>
            </v-window-item>

            <v-window-item value="contact">
              <v-row class="mt-4">
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.mobile"
                    label="موبایل"
                    :error-messages="mobileErrors"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.mobile2"
                    label="موبایل دوم"
                    :error-messages="mobile2Errors"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.tel"
                    label="تلفن"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.fax"
                    label="فکس"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.email"
                    label="ایمیل"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.website"
                    label="وب سایت"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-window-item>

            <v-window-item value="address">
              <v-row class="mt-4">
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.keshvar"
                    label="کشور"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.ostan"
                    label="استان"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.shahr"
                    label="شهر"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newPerson.postalcode"
                    label="کد پستی"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="newPerson.address"
                    label="آدرس"
                    rows="3"
                  ></v-textarea>
                </v-col>
              </v-row>
            </v-window-item>

            <v-window-item value="bank">
              <v-row class="mt-4">
                <v-col cols="12">
                  <v-btn
                    color="primary"
                    @click="addNewBankAccount"
                    prepend-icon="mdi-plus"
                  >
                    افزودن حساب بانکی
                  </v-btn>
                </v-col>
                <v-col cols="12" v-for="(account, index) in newPerson.accounts" :key="index">
                  <v-card variant="outlined" class="mb-4">
                    <v-card-title class="d-flex justify-space-between align-center">
                      <span>حساب بانکی {{ index + 1 }}</span>
                      <v-btn
                        icon="mdi-delete"
                        variant="text"
                        color="error"
                        @click="removeBankAccount(index)"
                      ></v-btn>
                    </v-card-title>
                    <v-card-text>
                      <v-row>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="account.bank"
                            label="نام بانک *"
                            required
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="account.accountNum"
                            label="شماره حساب"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="account.cardNum"
                            label="شماره کارت"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="account.shabaNum"
                            label="شماره شبا"
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </v-window-item>
          </v-window>
        </v-card-text>
      </v-card>
    </v-dialog>

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
import axios from 'axios';

export default {
  name: 'Hpersonsearch',
  props: {
    modelValue: {
      type: [Object, Number],
      default: null
    },
    label: {
      type: String,
      default: 'شخص'
    },
    returnObject: {
      type: Boolean,
      default: false
    },
    rules: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      selectedItem: null,
      items: [],
      loading: false,
      menu: false,
      searchQuery: '',
      totalItems: 0,
      currentPage: 1,
      itemsPerPage: 10,
      searchTimeout: null,
      showAddDialog: false,
      tabs: 'basic',
      saving: false,
      personTypes: [],
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      errorMessages: [],
      newPerson: {
        nikename: '',
        name: '',
        des: '',
        tel: '',
        mobile: '',
        mobile2: '',
        address: '',
        company: '',
        shenasemeli: '',
        codeeghtesadi: '',
        sabt: '',
        keshvar: '',
        ostan: '',
        shahr: '',
        postalcode: '',
        email: '',
        website: '',
        fax: '',
        code: 0,
        types: [],
        accounts: [],
        speedAccess: false
      }
    };
  },
  computed: {
    filteredItems() {
      return Array.isArray(this.items) ? this.items : [];
    },
    displayValue: {
      get() {
        if (this.menu) {
          return this.searchQuery;
        }
        return this.selectedItem ? this.selectedItem.nikename : this.searchQuery;
      },
      set(value) {
        this.searchQuery = value;
        if (!value) {
          this.clearSelection();
        }
      }
    },
    nikenameErrors() {
      if (!this.newPerson.nikename) return ['نام مستعار الزامی است'];
      return [];
    },
    mobileErrors() {
      if (this.newPerson.mobile && !/^09\d{9}$/.test(this.newPerson.mobile)) {
        return ['شماره موبایل باید با 09 شروع شود و 11 رقم باشد'];
      }
      return [];
    },
    mobile2Errors() {
      if (this.newPerson.mobile2 && !/^09\d{9}$/.test(this.newPerson.mobile2)) {
        return ['شماره موبایل دوم باید با 09 شروع شود و 11 رقم باشد'];
      }
      return [];
    },
    combinedRules() {
      return [
        v => !!v || 'انتخاب شخص الزامی است',
        ...this.rules
      ]
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (this.returnObject) {
          this.selectedItem = newVal;
        } else {
          this.selectedItem = this.items.find(item => item.id === newVal);
        }
      },
      immediate: true
    },
    searchQuery: {
      handler(newVal) {
        this.currentPage = 1;
        if (this.searchTimeout) {
          clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = setTimeout(() => {
          this.fetchData();
        }, 500);
      }
    },
    showAddDialog: {
      handler(newVal) {
        if (newVal) {
          this.newPerson.nikename = this.searchQuery;
        }
      }
    },
    'newPerson.mobile': {
      handler(newVal) {
        if (newVal && !newVal.startsWith('09')) {
          this.newPerson.mobile = '09' + newVal.replace(/^09/, '');
        }
      }
    },
    'newPerson.mobile2': {
      handler(newVal) {
        if (newVal && !newVal.startsWith('09')) {
          this.newPerson.mobile2 = '09' + newVal.replace(/^09/, '');
        }
      }
    }
  },
  methods: {
    showMessage(text, color = 'success') {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    },
    async fetchData() {
      this.loading = true;
      try {
        const response = await axios.post('/api/person/list', {
          page: this.currentPage,
          itemsPerPage: this.itemsPerPage,
          search: this.searchQuery,
          types: null,
          transactionFilters: null,
          sortBy: null
        });
                
        if (response.data && Array.isArray(response.data)) {
          this.items = response.data;
          this.totalItems = response.data.length;
        } else if (response.data && response.data.items) {
          this.items = response.data.items;
          this.totalItems = response.data.total || response.data.items.length;
        } else {
          this.items = [];
          this.totalItems = 0;
        }
        
        
        if (this.modelValue) {
          if (this.returnObject) {
            this.selectedItem = this.modelValue;
          } else {
            this.selectedItem = this.items.find(item => item.id === this.modelValue);
          }
        }
      } catch (error) {
        this.showMessage('خطا در بارگذاری داده‌ها', 'error');
        this.items = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async loadPersonTypes() {
      try {
        const response = await axios.post('/api/person/types/get');
        this.personTypes = response.data;
        this.newPerson.types = response.data.map(type => ({
          ...type,
          checked: false
        }));
      } catch (error) {
        console.error('خطا در دریافت انواع شخص:', error);
        this.showMessage('خطا در بارگذاری انواع شخص', 'error');
      }
    },
    addNewBankAccount() {
      this.newPerson.accounts.push({
        bank: '',
        accountNum: '',
        cardNum: '',
        shabaNum: ''
      });
    },
    removeBankAccount(index) {
      this.newPerson.accounts.splice(index, 1);
    },
    async savePerson() {
      if (!this.newPerson.nikename) {
        this.showMessage('نام مستعار الزامی است', 'error');
        return;
      }

      if (this.newPerson.mobile && !/^09\d{9}$/.test(this.newPerson.mobile)) {
        this.showMessage('شماره موبایل باید با 09 شروع شود و 11 رقم باشد', 'error');
        return;
      }

      if (this.newPerson.mobile2 && !/^09\d{9}$/.test(this.newPerson.mobile2)) {
        this.showMessage('شماره موبایل دوم باید با 09 شروع شود و 11 رقم باشد', 'error');
        return;
      }

      for (const account of this.newPerson.accounts) {
        if (!account.bank) {
          this.showMessage('نام بانک برای حساب‌های بانکی الزامی است', 'error');
          return;
        }
      }

      this.saving = true;
      try {
        const response = await axios.post('/api/person/mod/' + this.newPerson.code, this.newPerson);
        if (response.data.Success) {
          if (response.data.result === 1) {
            this.showMessage('شخص با موفقیت ثبت شد');
            this.showAddDialog = false;
            this.fetchData();
          } else if (response.data.result === 2) {
            this.showMessage('این شخص قبلاً ثبت شده است', 'error');
          }
        } else {
          this.showMessage('خطا در ثبت شخص', 'error');
        }
      } catch (error) {
        console.error('خطا در ثبت شخص:', error);
        this.showMessage('خطا در ثبت شخص', 'error');
      } finally {
        this.saving = false;
      }
    },
    selectItem(item) {
      this.selectedItem = item;
      this.searchQuery = item.nikename;
      this.$emit('update:modelValue', this.returnObject ? item : item.id);
      this.menu = false;
      this.errorMessages = [];
    },
    clearSelection() {
      this.selectedItem = null;
      this.searchQuery = '';
      this.$emit('update:modelValue', null);
      this.errorMessages = ['انتخاب شخص الزامی است'];
    },
    handleEnter() {
      if (!this.loading && this.filteredItems.length === 0) {
        this.showAddDialog = true;
      }
    },
    getBalanceColor(balance) {
      if (!balance) return 'grey-lighten-3';
      return balance > 0 ? 'green-lighten-5' : balance < 0 ? 'red-lighten-5' : 'grey-lighten-3';
    },
    getBalanceTextColor(balance) {
      if (!balance) return 'text-grey-darken-2';
      return balance > 0 ? 'text-green-darken-2' : balance < 0 ? 'text-red-darken-2' : 'text-grey-darken-2';
    },
    formatBalance(balance) {
      if (!balance) return 'بدون تراز';
      return balance > 0 ? `بستانکار: ${this.formatNumber(balance)}` : 
             balance < 0 ? `بدهکار: ${this.formatNumber(Math.abs(balance))}` : 
             'بدون تراز';
    },
    formatNumber(number) {
      return new Intl.NumberFormat('fa-IR').format(number);
    }
  },
  created() {
    this.fetchData();
    this.loadPersonTypes();
  }
};
</script>

<style scoped>
.list-container {
  max-height: 300px;
  overflow-y: auto;
}

.content-container {
  max-height: 500px;
  overflow-y: auto;
}

.sticky-toolbar {
  position: sticky;
  top: 0;
  z-index: 1;
}

.sticky-tabs {
  position: sticky;
  top: 48px;
  z-index: 1;
  overflow-x: auto;
  white-space: nowrap;
}

:deep(.v-menu__content) {
  position: fixed !important;
  z-index: 9999 !important;
  transform-origin: center top !important;
}

:deep(.v-overlay__content) {
  position: fixed !important;
}

:deep(.v-window-item[value="contact"] .v-text-field input) {
  text-align: left !important;
  direction: ltr !important;
}

:deep(.v-window-item[value="contact"] .v-text-field .v-field__input) {
  text-align: left !important;
  direction: ltr !important;
}

:deep(.v-window-item[value="contact"] .v-text-field .v-label) {
  text-align: right !important;
}

@media (max-width: 600px) {
  .content-container {
    max-height: calc(100vh - 120px);
  }
  
  .sticky-tabs {
    -webkit-overflow-scrolling: touch;
  }
}

.search-result-item {
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 1px solid rgba(var(--v-theme-outline), 0.1);
  background-color: rgb(var(--v-theme-surface));
  margin: 4px 0;
}

.search-result-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
  border-color: rgba(var(--v-theme-primary), 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  transform: translateY(-1px);
}

.selected-item {
  background-color: rgba(var(--v-theme-primary), 0.08);
  border-color: rgba(var(--v-theme-primary), 0.5);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.balance-chip {
  min-width: 110px;
  justify-content: center;
  font-weight: 500;
  letter-spacing: 0.3px;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

:deep(.v-list-item__content) {
  padding: 12px;
}

:deep(.v-list-item) {
  min-height: 72px;
}

:deep(.v-chip) {
  border: 1px solid rgba(0, 0, 0, 0.08);
}

:deep(.text-green-darken-2) {
  color: #1b5e20 !important;
}

:deep(.text-red-darken-2) {
  color: #b71c1c !important;
}

:deep(.text-grey-darken-2) {
  color: #424242 !important;
}

:deep(.text-primary-dark) {
  color: rgb(var(--v-theme-primary-dark)) !important;
}

:deep(.v-list) {
  padding: 8px;
}

:deep(.v-card) {
  border-radius: 16px;
  overflow: hidden;
}

:deep(.v-card-text) {
  padding: 16px;
}
</style>
