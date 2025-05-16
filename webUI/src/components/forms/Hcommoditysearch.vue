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
          prepend-inner-icon="mdi-package-variant"
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
          <template v-slot:prepend>
            <v-tooltip bottom>
              <template v-slot:activator="{ props }">
                <v-icon v-bind="props" color="danger" @click.stop="openScanner">mdi-barcode-scan</v-icon>

              </template>
              <span>اسکن بارکد</span>
            </v-tooltip>
          </template>
        </v-text-field>
      </template>

      <v-card min-width="300" max-width="500" class="search-card">
        <v-card-text class="pa-2">
          <template v-if="!loading">
            <v-list density="compact" class="list-container">
              <template v-if="filteredItems.length > 0">
                <v-list-item
                  v-for="item in filteredItems"
                  :key="item.id"
                  @click="selectItem(item)"
                  class="search-item"
                >
                  <div class="d-flex flex-column w-100">
                    <div class="d-flex justify-space-between align-center mb-1">
                      <span class="text-grey-darken-1 text-caption">{{ item.code }}</span>
                      <span class="text-warning text-caption">{{ formatNumber(item.count) }} عدد</span>
                    </div>
                    <div class="text-subtitle-1 font-weight-medium mb-1">{{ item.name }}</div>
                    <div class="d-flex justify-space-between align-center">
                      <span class="text-success text-caption">خرید: {{ formatPrice(item.priceBuy) }} ریال</span>
                      <span class="text-primary text-caption">فروش: {{ formatPrice(item.priceSell) }} ریال</span>
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
        <v-toolbar color="toolbar" :title="$t('dialog.commodity_info')">
          <template v-slot:prepend>
            <v-btn icon @click="showAddDialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </template>
          <v-spacer></v-spacer>
          <v-btn :loading="saving" @click="saveCommodity" icon color="green">
            <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
            <v-icon icon="mdi-content-save"></v-icon>
          </v-btn>
          <template v-slot:extension>
            <v-tabs color="primary" class="bg-light" grow v-model="tabs">
              <v-tab value="0">{{ $t('dialog.general') }}</v-tab>
              <v-tab value="1">{{ $t('dialog.prices') }}</v-tab>
              <v-tab value="2">{{ $t('dialog.existly') }}</v-tab>
              <v-tab value="3">{{ $t('dialog.tax') }}</v-tab>
            </v-tabs>
          </template>
        </v-toolbar>

        <v-card-text>
          <v-tabs-window v-model="tabs">
            <v-tabs-window-item value="0">
              <v-card>
                <v-card-text>
                  <div class="row py-3">
                    <div class="col-sm-6 col-md-6 mb-1">
                      <div>
                        <label class="me-4 text-primary">نوع کالا یا خدمات</label>
                        <div class="form-check form-check-inline">
                          <input v-model="newCommodity.khadamat" class="form-check-input" type="radio" value="true">
                          <label class="form-check-label">خدمات</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input v-model="newCommodity.khadamat" class="form-check-input" type="radio" value="false">
                          <label class="form-check-label">کالا و اقلام فیزیکی</label>
                        </div>
                      </div>
                    </div>
                    <div class="col-sm-6 col-md-6 mb-1">
                      <div class="space-y-2">
                        <div class="form-check form-switch">
                          <input v-model="newCommodity.speedAccess" class="form-check-input" type="checkbox">
                          <label class="form-check-label">دسترسی سریع</label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-sm-12 col-md-6">
                      <div class="form-floating mb-4">
                        <input v-model="newCommodity.name" class="form-control" type="text">
                        <label class="form-label"><span class="text-danger">(لازم)</span> نام کالا/خدمات</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6">
                      <div class="form-floating mb-4">
                        <select v-model="newCommodity.unit" class="form-select">
                          <option v-for="option in units" :key="option.name" :value="option.name">
                            {{ option.name }}
                          </option>
                        </select>
                        <label class="form-label">واحد شمارش</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-12 mb-4">
                      <small class="mb-2">دسته بندی</small>
                      <select class="form-select" aria-label="دسته‌بندی" v-model="newCommodity.cat">
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
                      <v-textarea class="text-left" v-model="newCommodity.barcodes" :label="$t('dialog.barcodes')"
                        :placeholder="$t('dialog.barcodes_info')" prepend-inner-icon="mdi-barcode"></v-textarea>
                    </div>
                    <div class="col-sm-12 col-md-12">
                      <div class="form-floating mb-4">
                        <input v-model="newCommodity.des" class="form-control" type="text">
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
                        <Hnumberinput
                          v-model="newCommodity.priceBuy"
                          :min="0"
                          suffix="ریال"
                        />
                        <label class="form-label">قیمت خرید</label>
                      </div>
                    </div>
                    <div class="col-sm-12 col-md-6">
                      <div class="form-floating mb-4">
                        <Hnumberinput
                          v-model="newCommodity.priceSell"
                          :min="0"
                          suffix="ریال"
                        />
                        <label class="form-label">قیمت فروش</label>
                      </div>
                    </div>
                    <div v-if="isPluginActive('accpro')" class="col-sm-12 col-md-6" v-for="price in newCommodity.prices">
                      <div class="form-floating mb-4">
                        <Hnumberinput
                          v-model="price.priceSell"
                          :min="0"
                          class="form-control"
                          prefix="ریال"
                        />
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
                    <label class="text-muted">تنظیمات بخش موجودی کالا تنها برای نوع کالا اعمال می‌شود و برای نوع خدمات نادیده گرفته می‌شود.</label>
                    <div class="space-y-2">
                      <div class="form-check form-switch">
                        <input v-model="newCommodity.commodityCountCheck" class="form-check-input" type="checkbox">
                        <label class="form-check-label">کنترل موجودی</label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-12 col-md-4 mt-2">
                        <div class="form-floating mb-4">
                          <input v-model="newCommodity.minOrderCount"
                            @blur="(event) => { if (newCommodity.minOrderCount === '' || newCommodity.minOrderCount === 0) { newCommodity.minOrderCount = 1 } }"
                            @keypress="onlyNumber($event)" class="form-control" type="number" min="1">
                          <label class="form-label">حداقل سفارش</label>
                        </div>
                      </div>
                      <div class="col-sm-12 col-md-4 mt-2">
                        <div class="form-floating mb-4">
                          <input v-model="newCommodity.orderPoint" @keypress="onlyNumber($event)" class="form-control"
                            type="number" min="1">
                          <label class="form-label">نقطه سفارش</label>
                        </div>
                      </div>
                      <div class="col-sm-12 col-md-4 mt-2">
                        <div class="form-floating mb-4">
                          <input v-model="newCommodity.dayLoading" @keypress="onlyNumber($event)" class="form-control"
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
                        <input v-model="newCommodity.withoutTax" class="form-check-input" type="checkbox">
                        <label class="form-check-label">معاف از مالیات</label>
                      </div>
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-tabs-window-item>
          </v-tabs-window>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="showBarcodeScanner" max-width="500" persistent>
      <v-card>
        <v-toolbar color="primary" dark>
          <v-toolbar-title>اسکن بارکد</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click="closeScanner">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text class="pa-0">
          <div class="scanner-container">
            <div class="scanner-overlay">
              <div class="scanner-line"></div>
              <div class="scanner-corner top-left"></div>
              <div class="scanner-corner top-right"></div>
              <div class="scanner-corner bottom-left"></div>
              <div class="scanner-corner bottom-right"></div>
            </div>
            <qrcode-stream
              :camera="camera"
              :torch="isFlashOn"
              @detect="onDetect"
              @init="onInit"
              :track="paintOutline"
              :formats="supportedFormats"
            />
          </div>

          <div class="scanner-controls pa-4">
            <v-row no-gutters>
              <v-col cols="4" class="text-center">
                <v-btn
                  block
                  :color="isFlashOn ? 'primary' : 'grey'"
                  variant="tonal"
                  @click="toggleFlash"
                  class="mb-2"
                >
                  <v-icon start>mdi-flash</v-icon>
                  فلش
                </v-btn>
              </v-col>
              <v-col cols="4" class="text-center">
                <v-btn
                  block
                  :color="isAutoFocus ? 'primary' : 'grey'"
                  variant="tonal"
                  @click="toggleAutoFocus"
                  class="mb-2"
                >
                  <v-icon start>mdi-focus</v-icon>
                  فوکوس خودکار
                </v-btn>
              </v-col>
              <v-col cols="4" class="text-center">
                <v-btn
                  block
                  :color="cameraSettings.isBackCamera ? 'primary' : 'grey'"
                  variant="tonal"
                  @click="toggleCamera"
                  class="mb-2"
                  :disabled="availableCameras.length < 2"
                >
                  <v-icon start>mdi-camera-flip</v-icon>
                  {{ cameraSettings.isBackCamera ? 'عقب' : 'جلو' }}
                </v-btn>
              </v-col>
            </v-row>
          </div>
        </v-card-text>

        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="tonal"
            @click="closeScanner"
          >
            بستن
          </v-btn>
        </v-card-actions>
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
import { QrcodeStream, QrcodeCapture } from 'vue3-qrcode-reader'
import Hnumberinput from '@/components/forms/Hnumberinput.vue'

export default defineComponent({
  name: 'Hcommoditysearch',
  components: {
    QrcodeStream,
    QrcodeCapture,
    Hnumberinput
  },
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
        prices: []
      },
      showBarcodeScanner: false,
      camera: 'environment',
      isFlashOn: false,
      isAutoFocus: true,
      supportedFormats: [
        'QR_CODE',
        'EAN_13',
        'EAN_8',
        'UPC_A',
        'UPC_E',
        'CODE_39',
        'CODE_128',
        'CODABAR',
        'ITF',
        'PDF_417',
        'AZTEC',
        'DATA_MATRIX'
      ],
      tabs: 0,
      plugins: [],
      barcode: {
        count: 1,
      },
      priceList: [],
      listCats: [],
      currencyConfig: {
        prefix: 'ریال',
        suffix: '',
        thousands: ',',
        decimal: '.',
        precision: 0,
        disableNegative: true,
        disabled: false,
        min: 0,
        max: null,
        allowBlank: false,
        minimumNumberOfCharacters: 0,
        shouldRound: true,
        focusOnRight: false,
      },
      cameraSettings: {
        deviceId: 'environment',
        isFlashOn: false,
        isAutoFocus: true,
        isBackCamera: true
      },
      availableCameras: [],
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
        } else if (value !== this.selectedItem?.name) {
          this.menu = true
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
        if (newVal) {
          if (this.returnObject) {
            this.selectedItem = newVal;
          } else {
            this.selectedItem = this.items.find(item => item.id === newVal) || { id: newVal, name: 'در حال بارگذاری...' };
          }
        } else {
          this.selectedItem = null;
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
          if (newVal && newVal !== this.selectedItem?.name) {
            this.menu = true
          }
        }, 500)
      }
    },
    menu: {
      handler(newVal) {
        if (!newVal) {
          this.searchQuery = this.selectedItem?.name || ''
        }
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
            if (!this.selectedItem) {
              await this.fetchSingleCommodity(this.modelValue)
            }
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
    async fetchSingleCommodity(id) {
      try {
        const response = await axios.get(`/api/commodity/${id}`)
        if (response.data && response.data.id) {
          this.items.push(response.data)
          this.selectedItem = response.data
          this.searchQuery = response.data.name
          this.$emit('update:modelValue', this.returnObject ? response.data : response.data.id)
        }
      } catch (error) {
        this.showMessage('خطا در بارگذاری کالا', 'error')
      }
    },
    async setValue(commodity) {
      if (commodity) {
        this.selectedItem = commodity
        this.searchQuery = commodity.name
        this.$emit('update:modelValue', this.returnObject ? commodity : commodity.id)
      }
    },
    async saveCommodity() {
      if (!this.newCommodity.name) {
        this.showMessage('نام کالا/خدمت الزامی است', 'error')
        return
      }

      this.saving = true;
      try {
        const response = await axios.post('/api/commodity/mod/' + this.newCommodity.code, this.newCommodity);
        if (response.data.result === 2) {
          this.showMessage('این کالا/خدمت قبلاً ثبت شده است', 'error');
        } else {
          this.showMessage('کالا/خدمت با موفقیت ثبت شد', 'success');
          this.showAddDialog = false;
          this.fetchData();
        }
      } catch (error) {
        console.error('خطا در ثبت کالا/خدمت:', error);
        this.showMessage('خطا در ثبت کالا/خدمت', 'error');
      } finally {
        this.saving = false;
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
      this.menu = false
    },
    handleEnter() {
      if (!this.loading && this.filteredItems.length === 0) {
        this.showAddDialog = true
      }
    },
    async openScanner() {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        this.showMessage('مرورگر شما از دوربین پشتیبانی نمی‌کند. لطفاً از مرورگرهای Chrome، Firefox یا Safari استفاده کنید.', 'error');
        return;
      }

      try {
        // ابتدا دسترسی به دوربین را درخواست می‌کنیم
        await navigator.mediaDevices.getUserMedia({ video: true });
        
        // سپس لیست دوربین‌ها را دریافت می‌کنیم
        await this.loadAvailableCameras();
        
        if (this.availableCameras.length === 0) {
          this.showMessage('هیچ دوربینی در دستگاه شما یافت نشد', 'error');
          return;
        }

        // همیشه سعی می‌کنیم دوربین عقب را پیدا کنیم
        const backCamera = this.availableCameras.find(device => 
          device.label.toLowerCase().includes('back') || 
          device.label.toLowerCase().includes('rear') ||
          device.label.toLowerCase().includes('main') ||
          device.label.toLowerCase().includes('primary')
        );
        
        if (backCamera) {
          this.camera = backCamera.deviceId;
          this.cameraSettings.deviceId = backCamera.deviceId;
          this.cameraSettings.isBackCamera = true;
        } else if (this.availableCameras.length > 0) {
          // اگر دوربین عقب پیدا نشد، از اولین دوربین استفاده می‌کنیم
          this.camera = this.availableCameras[0].deviceId;
          this.cameraSettings.deviceId = this.availableCameras[0].deviceId;
          this.cameraSettings.isBackCamera = false;
        }

        this.showBarcodeScanner = true;
      } catch (error) {
        console.error('خطا در دسترسی به دوربین:', error);
        this.showMessage('خطا در دسترسی به دوربین. لطفاً مطمئن شوید که به دوربین دسترسی داده‌اید.', 'error');
      }
    },
    closeScanner() {
      this.showBarcodeScanner = false;
      this.saveCameraSettings();
    },
    onInit(promise) {
      promise
        .then(() => {
          this.showMessage('اسکنر آماده است', 'success');
        })
        .catch(error => {
          console.error('خطا در راه‌اندازی اسکنر:', error);
          // بررسی نوع خطا و نمایش پیام مناسب
          if (error.name === 'NotAllowedError') {
            this.showMessage('دسترسی به دوربین رد شد. لطفاً دسترسی را مجدداً بررسی کنید.', 'error');
          } else if (error.name === 'NotFoundError') {
            this.showMessage('دوربین مورد نظر یافت نشد.', 'error');
          } else if (error.name === 'NotReadableError') {
            this.showMessage('دوربین در حال استفاده توسط برنامه دیگری است.', 'error');
          } else {
            this.showMessage('خطا در راه‌اندازی اسکنر. لطفاً دوباره تلاش کنید.', 'error');
          }
          this.closeScanner();
        });
    },
    onDetect(promisedQrCode) {
      promisedQrCode
        .then(result => {
          if (!result || !result.length) {
            return;
          }

          const barcode = result[0].rawValue;
          if (!barcode) {
            return;
          }

          // حذف کاراکترهای غیرمجاز از بارکد
          const cleanBarcode = barcode.replace(/[^0-9A-Za-z]/g, '');
          
          // بررسی اعتبار بارکد
          if (cleanBarcode.length < 8) {
            this.showMessage('بارکد نامعتبر است', 'error');
            return;
          }

          this.showMessage('بارکد با موفقیت اسکن شد', 'success');
          this.handleBarcodeResult(cleanBarcode);
        })
        .catch(error => {
          console.error('خطا در اسکن:', error);
          this.showMessage('خطا در اسکن بارکد. لطفاً دوباره تلاش کنید.', 'error');
        });
    },
    handleBarcodeResult(barcode: string) {
      if (!barcode) return;
      
      this.searchQuery = barcode;
      this.closeScanner();
      this.fetchData().then(() => {
        this.menu = true;
      });
    },
    paintOutline(location, ctx) {
      if (!location) return;

      const canvas = ctx.canvas;
      if (canvas) {
        canvas.willReadFrequently = true;
      }

      const { topLeftCorner, topRightCorner, bottomLeftCorner, bottomRightCorner } = location;
      
      // رسم خطوط راهنما
      ctx.strokeStyle = '#00ff00';
      ctx.lineWidth = 4;
      
      ctx.beginPath();
      ctx.moveTo(topLeftCorner.x, topLeftCorner.y);
      ctx.lineTo(topRightCorner.x, topRightCorner.y);
      ctx.lineTo(bottomRightCorner.x, bottomRightCorner.y);
      ctx.lineTo(bottomLeftCorner.x, bottomLeftCorner.y);
      ctx.lineTo(topLeftCorner.x, topLeftCorner.y);
      ctx.stroke();

      // نمایش نوع کد
      ctx.fillStyle = '#00ff00';
      ctx.font = '16px Arial';
      ctx.fillText(location.format || 'کد شناسایی شده', topLeftCorner.x, topLeftCorner.y - 10);
    },
    toggleFlash() {
      this.isFlashOn = !this.isFlashOn;
      this.saveCameraSettings();
    },
    toggleAutoFocus() {
      this.isAutoFocus = !this.isAutoFocus;
      this.camera = this.isAutoFocus ? 'auto' : 'environment';
      this.saveCameraSettings();
    },
    generateBarcode() {
      for (let index = 0; index < this.barcode.count; index++) {
        let x = Math.random() * 1000000000000000000;
        this.newCommodity.barcodes = this.newCommodity.barcodes + ';' + x
      }
    },
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
    onlyNumber(event) {
      const keyCode = event.keyCode;
      if ((keyCode < 48 || keyCode > 57) && keyCode !== 8) {
        event.preventDefault();
      }
    },
    async loadData() {
      //get active plugins
      const pluginsResponse = await axios.post('/api/plugin/get/actives');
      this.plugins = pluginsResponse.data;

      const unitsResponse = await axios.post('/api/commodity/units');
      this.units = unitsResponse.data;

      const priceListResponse = await axios.post('/api/commodity/pricelist/list');
      if (priceListResponse.data.length === 0) {
        this.newCommodity.prices = [];
      } else {
        this.priceList = priceListResponse.data;
        this.priceList.forEach((item) => {
          this.newCommodity.prices.push({
            id: 0,
            priceBuy: 0,
            priceSell: 0,
            list: item
          });
        });
      }

      const catsResponse = await axios.post('/api/commodity/cat/get/line');
      this.listCats = catsResponse.data;
      if (!this.newCommodity.code) {
        this.newCommodity.cat = catsResponse.data[1];
      }
    },
    formatPrice(price: number): string {
      return new Intl.NumberFormat('fa-IR').format(price || 0);
    },
    formatNumber(num: number): string {
      return new Intl.NumberFormat('fa-IR').format(num || 0);
    },
    stopScanning() {
      if (this.showBarcodeScanner) {
        this.closeScanner();
      }
    },
    loadCameraSettings() {
      try {
        const savedSettings = localStorage.getItem('cameraSettings');
        if (savedSettings) {
          this.cameraSettings = JSON.parse(savedSettings);
          this.camera = this.cameraSettings.deviceId;
          this.isFlashOn = this.cameraSettings.isFlashOn;
          this.isAutoFocus = this.cameraSettings.isAutoFocus;
        }
      } catch (error) {
        console.error('خطا در بارگذاری تنظیمات دوربین:', error);
      }
    },
    saveCameraSettings() {
      try {
        this.cameraSettings = {
          deviceId: this.camera,
          isFlashOn: this.isFlashOn,
          isAutoFocus: this.isAutoFocus,
        };
        localStorage.setItem('cameraSettings', JSON.stringify(this.cameraSettings));
      } catch (error) {
        console.error('خطا در ذخیره تنظیمات دوربین:', error);
      }
    },
    async loadAvailableCameras() {
      try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        this.availableCameras = devices.filter(device => device.kind === 'videoinput');
        
        // همیشه سعی می‌کنیم دوربین عقب را پیدا کنیم
        const backCamera = this.availableCameras.find(device => 
          device.label.toLowerCase().includes('back') || 
          device.label.toLowerCase().includes('rear') ||
          device.label.toLowerCase().includes('main') ||
          device.label.toLowerCase().includes('primary')
        );
        
        if (backCamera) {
          this.camera = backCamera.deviceId;
          this.cameraSettings.deviceId = backCamera.deviceId;
          this.cameraSettings.isBackCamera = true;
        } else if (this.availableCameras.length > 0) {
          this.camera = this.availableCameras[0].deviceId;
          this.cameraSettings.deviceId = this.availableCameras[0].deviceId;
          this.cameraSettings.isBackCamera = false;
        }
      } catch (error) {
        console.error('خطا در دریافت لیست دوربین‌ها:', error);
        throw error;
      }
    },
    async toggleCamera() {
      if (this.availableCameras.length < 2) {
        this.showMessage('تنها یک دوربین در دسترس است', 'warning');
        return;
      }

      try {
        // پیدا کردن دوربین فعلی
        const currentIndex = this.availableCameras.findIndex(cam => cam.deviceId === this.camera);
        // انتخاب دوربین بعدی
        const nextIndex = (currentIndex + 1) % this.availableCameras.length;
        const nextCamera = this.availableCameras[nextIndex];

        this.camera = nextCamera.deviceId;
        this.cameraSettings.deviceId = nextCamera.deviceId;
        this.cameraSettings.isBackCamera = 
          nextCamera.label.toLowerCase().includes('back') || 
          nextCamera.label.toLowerCase().includes('rear') ||
          nextCamera.label.toLowerCase().includes('main') ||
          nextCamera.label.toLowerCase().includes('primary');
        
        this.saveCameraSettings();
        this.showMessage(`دوربین ${this.cameraSettings.isBackCamera ? 'عقب' : 'جلو'} فعال شد`, 'success');
      } catch (error) {
        console.error('خطا در تعویض دوربین:', error);
        this.showMessage('خطا در تعویض دوربین', 'error');
      }
    },
  },
  created() {
    this.fetchData()
    this.loadData()
    this.loadCameraSettings()
  },
  beforeUnmount() {
    this.stopScanning()
  }
})
</script>

<style scoped>
.list-container {
  max-height: 400px;
  overflow-y: auto;
}

.search-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-item {
  border-radius: 4px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  padding: 0px 0px;
  margin-bottom: 4px;
}

.search-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
  border-color: rgba(var(--v-theme-primary), 0.1);
}

.v-toolbar {
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
}

.scanner-container {
  width: 100%;
  height: 300px;
  background: #000;
  position: relative;
  overflow: hidden;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.scanner-container video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.scanner-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
  pointer-events: none;
}

.scanner-line {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 2px;
  background: #00ff00;
  animation: scan 2s linear infinite;
}

.scanner-corner {
  position: absolute;
  width: 20px;
  height: 20px;
  border-color: #00ff00;
  border-style: solid;
  border-width: 0;
}

.scanner-corner.top-left {
  top: 20%;
  left: 20%;
  border-top-width: 4px;
  border-left-width: 4px;
}

.scanner-corner.top-right {
  top: 20%;
  right: 20%;
  border-top-width: 4px;
  border-right-width: 4px;
}

.scanner-corner.bottom-left {
  bottom: 20%;
  left: 20%;
  border-bottom-width: 4px;
  border-left-width: 4px;
}

.scanner-corner.bottom-right {
  bottom: 20%;
  right: 20%;
  border-bottom-width: 4px;
  border-right-width: 4px;
}

@keyframes scan {
  0% {
    transform: translateY(-50px);
  }
  50% {
    transform: translateY(50px);
  }
  100% {
    transform: translateY(-50px);
  }
}

.scanner-controls {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}

.scanner-controls .v-btn {
  text-transform: none;
  letter-spacing: 0;
  font-size: 0.875rem;
}

.scanner-controls .v-icon {
  margin-right: 4px;
}
</style>