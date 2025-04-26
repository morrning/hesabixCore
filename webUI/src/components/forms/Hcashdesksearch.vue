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
          :label="label || 'جستجوی صندوق'"
          class="my-0"
          prepend-inner-icon="mdi-cash-register"
          clearable
          @click:clear="clearSelection"
          :loading="loading"
          @keydown.enter="handleEnter"
          hide-details
          density="compact"
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
                  class="mb-1"
                >
                  <v-list-item-title class="text-right">{{ item.name }}</v-list-item-title>
                  <v-list-item-subtitle class="text-right">{{ item.balance }}</v-list-item-subtitle>
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
              افزودن صندوق جدید
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

    <v-dialog v-model="showAddDialog" :fullscreen="$vuetify.display.mobile" max-width="600">
      <v-card>
        <v-toolbar color="primary" density="compact" class="sticky-toolbar">
          <v-toolbar-title>افزودن صندوق جدید</v-toolbar-title>
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
                @click="saveCashdesk"
                :loading="saving"
              ></v-btn>
            </template>
          </v-tooltip>
        </v-toolbar>

        <v-card-text class="content-container">
          <v-form @submit.prevent="saveCashdesk">
            <v-row class="mt-4">
              <v-col cols="12">
                <v-text-field
                  v-model="newCashdesk.name"
                  label="نام صندوق *"
                  required
                  :error-messages="nameErrors"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="newCashdesk.des"
                  label="توضیحات"
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
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
  name: 'Hcashdesksearch',
  props: {
    modelValue: {
      type: [Object, Number],
      default: null
    },
    label: {
      type: String,
      default: 'صندوق'
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
      saving: false,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      errorMessages: [],
      newCashdesk: {
        name: '',
        des: '',
        code: 0
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
        return this.selectedItem ? this.selectedItem.name : this.searchQuery;
      },
      set(value) {
        this.searchQuery = value;
        if (!value) {
          this.clearSelection();
        }
      }
    },
    nameErrors() {
      if (!this.newCashdesk.name) return ['نام صندوق الزامی است'];
      return [];
    },
    combinedRules() {
      return [
        v => !!v || 'انتخاب صندوق الزامی است',
        ...this.rules
      ]
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (newVal) {
          if (this.returnObject) {
            this.selectedItem = newVal;
            this.searchQuery = newVal.name;
          } else {
            this.fetchCashDeskById(newVal);
          }
        } else {
          this.selectedItem = null;
          this.searchQuery = '';
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
          this.newCashdesk.name = this.searchQuery;
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
        const response = await axios.post('/api/cashdesk/search', {
          page: this.currentPage,
          itemsPerPage: this.itemsPerPage,
          search: this.searchQuery
        });
                
        if (response.data && response.data.items) {
          this.items = response.data.items;
          this.totalItems = response.data.total;
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
        console.error('خطا در بارگذاری داده‌ها:', error);
        this.showMessage('خطا در بارگذاری داده‌ها', 'error');
        this.items = [];
        this.totalItems = 0;
      } finally {
        this.loading = false;
      }
    },
    async saveCashdesk() {
      if (!this.newCashdesk.name) {
        this.showMessage('نام صندوق الزامی است', 'error');
        return;
      }

      this.saving = true;
      try {
        const response = await axios.post('/api/cashdesk/mod/' + (this.newCashdesk.code || 0), {
          name: this.newCashdesk.name,
          des: this.newCashdesk.des
        });

        if (response.data.result === 1) {
          this.showMessage('صندوق با موفقیت ثبت شد');
          this.showAddDialog = false;
          this.fetchData();
        } else if (response.data.result === 2) {
          this.showMessage('این صندوق قبلاً ثبت شده است', 'error');
        } else if (response.data.result === 3) {
          this.showMessage('نام صندوق نمی‌تواند خالی باشد', 'error');
        } else {
          this.showMessage('خطا در ثبت صندوق', 'error');
        }
      } catch (error) {
        console.error('خطا در ثبت صندوق:', error);
        this.showMessage('خطا در ثبت صندوق', 'error');
      } finally {
        this.saving = false;
      }
    },
    selectItem(item) {
      this.selectedItem = item;
      this.searchQuery = item.name;
      this.$emit('update:modelValue', this.returnObject ? item : item.id);
      this.menu = false;
      this.errorMessages = [];
    },
    clearSelection() {
      this.selectedItem = null;
      this.searchQuery = '';
      this.$emit('update:modelValue', null);
      this.errorMessages = ['انتخاب صندوق الزامی است'];
    },
    handleEnter() {
      if (!this.loading && this.filteredItems.length === 0) {
        this.showAddDialog = true;
      }
    },
    async fetchCashDeskById(id) {
      try {
        const response = await axios.get(`/api/componenets/cashdesk/get/${id}`);
        if (response.data && response.data.item) {
          this.selectedItem = response.data.item;
          this.searchQuery = response.data.item.name;
        }
      } catch (error) {
        console.error('خطا در دریافت صندوق:', error);
      }
    }
  },
  created() {
    this.fetchData();
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

:deep(.v-menu__content) {
  position: fixed !important;
  z-index: 9999 !important;
  transform-origin: center top !important;
}

:deep(.v-overlay__content) {
  position: fixed !important;
}

@media (max-width: 600px) {
  .content-container {
    max-height: calc(100vh - 120px);
  }
}
</style> 