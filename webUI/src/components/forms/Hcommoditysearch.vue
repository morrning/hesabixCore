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
          class=""
          prepend-inner-icon="mdi-package-variant"
          clearable
          @click:clear="clearSelection"
          :loading="loading"
          @keydown.enter="handleEnter"
          hide-details="auto"
        >
          <template v-slot:append-inner>
            <v-icon>{{ menu ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
          </template>
        </v-text-field>
      </template>

      <v-card min-width="300" max-width="400">
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
                  <v-list-item-subtitle class="text-right">{{ item.code }}</v-list-item-subtitle>
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
              افزودن کالا/خدمت جدید
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
          <v-toolbar-title>افزودن کالا/خدمت جدید</v-toolbar-title>
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
                @click="saveCommodity"
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
          <v-tab value="details" class="flex-grow-1">جزئیات</v-tab>
          <v-tab value="pricing" class="flex-grow-1">قیمت‌گذاری</v-tab>
        </v-tabs>

        <v-card-text class="content-container">
          <v-window v-model="tabs">
            <v-window-item value="basic">
              <v-form @submit.prevent="saveCommodity">
                <v-row class="mt-4">
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="newCommodity.name"
                      label="نام کالا/خدمت *"
                      required
                      :error-messages="nameErrors"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="newCommodity.code"
                      label="کد"
                    ></v-text-field>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="newCommodity.type"
                      :items="commodityTypes"
                      label="نوع *"
                      required
                      :error-messages="typeErrors"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="newCommodity.unit"
                      :items="units"
                      label="واحد اندازه‌گیری *"
                      required
                      :error-messages="unitErrors"
                    ></v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="newCommodity.description"
                      label="توضیحات"
                      rows="3"
                    ></v-textarea>
                  </v-col>
                </v-row>
              </v-form>
            </v-window-item>

            <v-window-item value="details">
              <v-row class="mt-4">
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.brand"
                    label="برند"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.model"
                    label="مدل"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.barcode"
                    label="بارکد"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.serial"
                    label="سریال"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-switch
                    v-model="newCommodity.isService"
                    label="خدمت"
                    color="primary"
                  ></v-switch>
                </v-col>
              </v-row>
            </v-window-item>

            <v-window-item value="pricing">
              <v-row class="mt-4">
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.basePrice"
                    label="قیمت پایه"
                    type="number"
                    prefix="ریال"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.salePrice"
                    label="قیمت فروش"
                    type="number"
                    prefix="ریال"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.minStock"
                    label="حداقل موجودی"
                    type="number"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="newCommodity.maxStock"
                    label="حداکثر موجودی"
                    type="number"
                  ></v-text-field>
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

<script lang="ts">
import { defineComponent } from 'vue'
import axios from 'axios'

export default defineComponent({
  name: 'Hcommoditysearch',
  props: {
    modelValue: {
      type: [Object, Number],
      default: null
    },
    label: {
      type: String,
      default: 'کالا/خدمت'
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
      commodityTypes: ['کالا', 'خدمت'],
      units: ['عدد', 'کیلوگرم', 'گرم', 'متر', 'سانتیمتر', 'لیتر', 'متر مربع', 'متر مکعب'],
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      errorMessages: [],
      newCommodity: {
        name: '',
        code: '',
        type: '',
        unit: '',
        description: '',
        brand: '',
        model: '',
        barcode: '',
        serial: '',
        isService: false,
        basePrice: 0,
        salePrice: 0,
        minStock: 0,
        maxStock: 0
      }
    }
  },
  computed: {
    filteredItems() {
      return Array.isArray(this.items) ? this.items : []
    },
    displayValue: {
      get() {
        if (this.menu) {
          return this.searchQuery
        }
        return this.selectedItem ? this.selectedItem.name : this.searchQuery
      },
      set(value) {
        this.searchQuery = value
        if (!value) {
          this.clearSelection()
        }
      }
    },
    nameErrors() {
      if (!this.newCommodity.name) return ['نام کالا/خدمت الزامی است']
      return []
    },
    typeErrors() {
      if (!this.newCommodity.type) return ['نوع کالا/خدمت الزامی است']
      return []
    },
    unitErrors() {
      if (!this.newCommodity.unit) return ['واحد اندازه‌گیری الزامی است']
      return []
    },
    combinedRules() {
      return [
        v => !!v || 'انتخاب کالا/خدمت الزامی است',
        ...this.rules
      ]
    }
  },
  watch: {
    modelValue: {
      handler(newVal) {
        if (this.returnObject) {
          this.selectedItem = newVal
        } else {
          this.selectedItem = this.items.find(item => item.id === newVal)
        }
      },
      immediate: true
    },
    searchQuery: {
      handler(newVal) {
        this.currentPage = 1
        if (this.searchTimeout) {
          clearTimeout(this.searchTimeout)
        }
        this.searchTimeout = setTimeout(() => {
          this.fetchData()
        }, 500)
      }
    },
    showAddDialog: {
      handler(newVal) {
        if (newVal) {
          this.newCommodity.name = this.searchQuery
        }
      }
    }
  },
  methods: {
    showMessage(text: string, color = 'success') {
      this.snackbar.text = text
      this.snackbar.color = color
      this.snackbar.show = true
    },
    async fetchData() {
      this.loading = true
      try {
        const response = await axios.post('/api/commodity/list/search', {
          page: this.currentPage,
          itemsPerPage: this.itemsPerPage,
          search: this.searchQuery,
          sortBy: null
        })
                
        if (response.data && Array.isArray(response.data)) {
          this.items = response.data
          this.totalItems = response.data.length
        } else if (response.data && response.data.items) {
          this.items = response.data.items
          this.totalItems = response.data.total || response.data.items.length
        } else {
          this.items = []
          this.totalItems = 0
        }
        
        if (this.modelValue) {
          if (this.returnObject) {
            this.selectedItem = this.modelValue
          } else {
            this.selectedItem = this.items.find(item => item.id === this.modelValue)
          }
        }
      } catch (error) {
        this.showMessage('خطا در بارگذاری داده‌ها', 'error')
        this.items = []
        this.totalItems = 0
      } finally {
        this.loading = false
      }
    },
    async saveCommodity() {
      if (!this.newCommodity.name) {
        this.showMessage('نام کالا/خدمت الزامی است', 'error')
        return
      }

      if (!this.newCommodity.type) {
        this.showMessage('نوع کالا/خدمت الزامی است', 'error')
        return
      }

      if (!this.newCommodity.unit) {
        this.showMessage('واحد اندازه‌گیری الزامی است', 'error')
        return
      }

      this.saving = true
      try {
        const response = await axios.post('/api/commodity/mod/' + this.newCommodity.code, this.newCommodity)
        if (response.data.Success) {
          if (response.data.result === 1) {
            this.showMessage('کالا/خدمت با موفقیت ثبت شد')
            this.showAddDialog = false
            this.fetchData()
          } else if (response.data.result === 2) {
            this.showMessage('این کالا/خدمت قبلاً ثبت شده است', 'error')
          }
        } else {
          this.showMessage('خطا در ثبت کالا/خدمت', 'error')
        }
      } catch (error) {
        console.error('خطا در ثبت کالا/خدمت:', error)
        this.showMessage('خطا در ثبت کالا/خدمت', 'error')
      } finally {
        this.saving = false
      }
    },
    selectItem(item: any) {
      this.selectedItem = item
      this.searchQuery = item.name
      this.$emit('update:modelValue', this.returnObject ? item : item.id)
      this.menu = false
      this.errorMessages = []
    },
    clearSelection() {
      this.selectedItem = null
      this.searchQuery = ''
      this.$emit('update:modelValue', null)
      this.errorMessages = ['انتخاب کالا/خدمت الزامی است']
    },
    handleEnter() {
      if (!this.loading && this.filteredItems.length === 0) {
        this.showAddDialog = true
      }
    }
  },
  created() {
    this.fetchData()
  }
})
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

@media (max-width: 600px) {
  .content-container {
    max-height: calc(100vh - 120px);
  }
  
  .sticky-tabs {
    -webkit-overflow-scrolling: touch;
  }
}
</style>
