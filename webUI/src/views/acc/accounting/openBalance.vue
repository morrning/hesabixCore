<template>
  <v-toolbar color="toolbar" :title="$t('drawer.open_balance')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container>
    <v-row class="pa-1">
      <v-col v-if="sums.degSum != sums.shareSum" cols="12" sm="12" md="12">
        <v-alert :title="$t('dialog.error')" :text="$t('dialog.openbalance_notvalid')" type="warning"></v-alert>
      </v-col>
      <v-col cols="12" sm="12" md="6">
        <v-card :title="$t('dialog.deg')" :subtitle="$t('dialog.deg_info')" color="success">
          <v-card-text class="px-0 pb-0">
            <v-list class="py-0">
              <v-list-item @click="sheet.banks = !sheet.banks" value="banks" :title="$t('drawer.banks')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.banks, true) }}
                </template>
              </v-list-item>
              <v-list-item @click="sheet.cashdesks = !sheet.cashdesks" value="cashdesks"
                :title="$t('drawer.cashdesks')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.cashdesks, true) }}
                </template>
              </v-list-item>
              <v-list-item @click="sheet.salarys = !sheet.salarys" value="salarys" :title="$t('drawer.salarys')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.salarys, true) }}
                </template>
              </v-list-item>
              <v-list-item @click="sheet.inventory = !sheet.inventory" value="inventory" :title="$t('drawer.inventory')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.inventory, true) }}
                </template>
              </v-list-item>
              <v-list-item :title="$t('dialog.sum')" class="bg-light">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.degSum, true) }}
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="12" md="6">
        <v-card :title="$t('dialog.shareloders_equity')" :subtitle="$t('dialog.shareloders_equity_info')" color="info">
          <v-card-text class="px-0 pb-0">
            <v-list class="py-0">
              <v-list-item @click="sheet.shareholders = !sheet.shareholders" value="shareholders"
                :title="$t('drawer.shareholders')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.shareholders, true) }}
                </template>
              </v-list-item>
              <v-list-item :title="$t('dialog.sum')" class="bg-light">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.shareSum, true) }}
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

  <v-bottom-sheet inset v-model="sheet.banks">
    <v-toolbar color="toolbar" :title="$t('drawer.banks')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.banks = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveBanks()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.banks" :key="item.info.id" cols="12" sm="6" md="4">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.name }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
  <v-bottom-sheet inset v-model="sheet.cashdesks">
    <v-toolbar color="toolbar" :title="$t('drawer.cashdesks')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.cashdesks = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveCashdesks()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.cashdesks" :key="item.info.id" cols="12" sm="6" md="4">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.name }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
  <v-bottom-sheet inset v-model="sheet.salarys">
    <v-toolbar color="toolbar" :title="$t('drawer.salarys')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.salarys = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveSalarys()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.salarys" :key="item.info.id" cols="12" sm="6" md="4">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.name }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
  <v-bottom-sheet inset v-model="sheet.shareholders">
    <v-toolbar color="toolbar" :title="$t('drawer.shareholders')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.shareholders = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveShareholders()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.shareholders" :key="item.info.id" cols="12" sm="6" md="4">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.nikename }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
  <v-dialog v-model="sheet.inventory" fullscreen>
    <v-card class="d-flex flex-column" style="height: 100vh;">
      <v-toolbar color="toolbar" :title="$t('drawer.inventory')" class="flex-grow-0">
        <template v-slot:prepend>
          <v-tooltip :text="$t('dialog.close')" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" @click="sheet.inventory = false" class="d-none d-sm-flex" variant="text"
                icon="mdi-close" />
            </template>
          </v-tooltip>
        </template>
        <v-spacer></v-spacer>
        <v-tooltip :text="$t('dialog.save')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn
              v-bind="props"
              :loading="loading"
              @click="saveInventory()"
              color="success"
              variant="text"
              icon="mdi-content-save"
            />
          </template>
        </v-tooltip>
      </v-toolbar>
      <v-table class="border d-none d-sm-table" style="width: 100%;">
          <thead>
            <tr style="background-color: #0D47A1; color: white;">
              <th class="text-center">ردیف</th>
              <th class="text-center">نام کالا</th>
              <th class="text-center">تعداد</th>
              <th class="text-center">قیمت واحد</th>
              <th class="text-center">قیمت کل</th>
              <th class="text-center">عملیات</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(item, index) in data.commodities" :key="index">
              <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '64px' }">
                <td class="text-center">{{ index + 1 }}</td>
                <td class="text-center" style="min-width: 200px;">
                  <Hcommoditysearch
                    v-model="item.info"
                    density="compact"
                    hide-details
                    class="my-0"
                    style="font-size: 0.8rem;"
                    return-object
                    @update:model-value="updateCommodityInfo(item)"
                  />
                </td>
                <td class="text-center" style="width: 100px;">
                  <Hnumberinput
                    v-model="item.count"
                    density="compact"
                    hide-details
                    class="my-0"
                    style="font-size: 0.8rem;"
                    @update:model-value="calculateTotalPrice(item)"
                  />
                </td>
                <td class="text-center" style="width: 120px;">
                  <Hnumberinput
                    v-model="item.price"
                    density="compact"
                    hide-details
                    class="my-0"
                    style="font-size: 0.8rem;"
                    @update:model-value="calculateTotalPrice(item)"
                  />
                </td>
                <td class="text-center font-weight-bold" style="width: 120px;">
                  {{ item.totalPrice.toLocaleString('fa-IR') }}
                </td>
                <td class="text-center">
                  <v-tooltip text="حذف" location="bottom">
                    <template v-slot:activator="{ props }">
                      <v-btn
                        v-bind="props"
                        icon="mdi-delete"
                        variant="text"
                        size="small"
                        color="error"
                        @click="removeInventoryItem(item)"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                </td>
              </tr>
            </template>
            <tr>
              <td colspan="6" class="text-center pa-2" style="height: 64px;">
                <v-btn color="primary" prepend-icon="mdi-plus" size="small" @click="addInventoryItem">
                  {{ $t('dialog.inventory.add_item') }}
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      <v-card-text>
        <!-- جدول موبایل -->
        <div class="d-sm-none">
          <v-card v-for="(item, index) in data.commodities" :key="index" class="mb-4" variant="outlined">
            <v-card-text>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-subtitle-2 font-weight-bold">ردیف:</span>
                <span>{{ index + 1 }}</span>
              </div>
              <div class="mb-2">
                <Hcommoditysearch
                  v-model="item.info"
                  density="compact"
                  label="نام کالا"
                  hide-details
                  class="my-0"
                  style="font-size: 0.8rem;"
                  return-object
                  @update:model-value="updateCommodityInfo(item)"
                />
              </div>
              <div class="d-flex justify-space-between mb-2">
                <div style="width: 48%;">
                  <Hnumberinput
                    v-model="item.count"
                    density="compact"
                    label="تعداد"
                    hide-details
                    class="my-0"
                    style="font-size: 0.8rem;"
                    @update:model-value="calculateTotalPrice(item)"
                  />
                </div>
                <div style="width: 48%;">
                  <Hnumberinput
                    v-model="item.price"
                    density="compact"
                    label="قیمت واحد"
                    hide-details
                    class="my-0"
                    style="font-size: 0.8rem;"
                    @update:model-value="calculateTotalPrice(item)"
                  />
                </div>
              </div>
              <div class="d-flex justify-space-between align-center">
                <span class="text-subtitle-2 font-weight-bold">قیمت کل:</span>
                <span class="text-subtitle-2 font-weight-bold">{{ item.totalPrice.toLocaleString('fa-IR') }}</span>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn icon="mdi-delete" variant="text" color="error" @click="removeInventoryItem(item)"></v-btn>
            </v-card-actions>
          </v-card>
          <v-btn color="primary" prepend-icon="mdi-plus" block class="mb-4" @click="addInventoryItem">
            {{ $t('dialog.inventory.add_item') }}
          </v-btn>
        </div>
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
</template>

<script>
import axios from "axios";
import Loading from 'vue-loading-overlay';
import Hcommoditysearch from '@/components/forms/Hcommoditysearch.vue';
import Hnumberinput from '@/components/forms/Hnumberinput.vue';

export default {
  name: "table",
  components: {
    Loading,
    Hcommoditysearch,
    Hnumberinput
  },
  data: () => {
    return {
      loading: false,
      inventoryHeaders: [
        { title: 'ردیف', key: 'row', sortable: false },
        { title: 'کالا', key: 'name', sortable: false },
        { title: 'تعداد', key: 'count', sortable: false },
        { title: 'قیمت واحد', key: 'price', sortable: false },
        { title: 'قیمت کل', key: 'totalPrice', sortable: false },
        { title: 'عملیات', key: 'actions', sortable: false },
      ],
      currencyConfig: {
        masked: false,
        prefix: '',
        suffix: '',
        thousands: ',',
        decimal: '.',
        precision: 0,
        disableNegative: true,
        disabled: false,
        min: 0,
        max: null,
        allowBlank: false,
        minimumNumberOfCharacters: 1,
        shouldRound: false,
        focusOnRight: true,
      },
      sheet: {
        banks: false,
        cashdesks: false,
        salarys: false,
        shareholders: false,
        inventory: false,
      },
      data: {
        banks: [],
        cashdesks: [],
        salarys: [],
        shareholders: [],
        commodities: [],
      },
      sums: {
        banks: 0,
        cashdesks: 0,
        salarys: 0,
        shareholders: 0,
        inventory: 0,
        degSum: 0,
        shareSum: 0,
      },
      snackbar: {
        show: false,
        text: '',
        color: 'error'
      }
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      this.sums.banks = 0;
      this.sums.cashdesks = 0;
      this.sums.salarys = 0;
      this.sums.shareholders = 0;
      this.sums.inventory = 0;

      axios.post('/api/openbalance/get').then((Response) => {
        if (Response.data && Response.data.data) {
        this.data = Response.data.data;
          // اطمینان از وجود آرایه commodities
          if (!this.data.commodities) {
            this.data.commodities = [];
          }
          
          // محاسبه مجموع موجودی کالا
          if (this.data.commodities) {
            this.data.commodities.forEach(item => {
              if (item.totalPrice) {
                this.sums.inventory += parseFloat(item.totalPrice);
              }
            });
          }
          
          this.sums.degSum = parseFloat(this.sums.banks) + parseFloat(this.sums.cashdesks) + parseFloat(this.sums.salarys) + parseFloat(this.sums.inventory);
          this.sums.shareSum = parseFloat(this.sums.shareholders);
        }
      }).catch(error => {
        console.error('Error loading data:', error);
        this.snackbar.text = 'خطا در بارگذاری داده‌ها';
        this.snackbar.color = 'error';
        this.snackbar.show = true;
      });
    },
    saveBanks() {
      this.loading = true;
      axios.post('/api/openbalance/save/banks', this.data.banks).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.banks = false;
      })
    },
    saveCashdesks() {
      this.loading = true;
      axios.post('/api/openbalance/save/cashdesks', this.data.cashdesks).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.cashdesks = false;
      })
    },
    saveSalarys() {
      this.loading = true;
      axios.post('/api/openbalance/save/salarys', this.data.salarys).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.salarys = false;
      })
    },
    saveShareholders() {
      this.loading = true;
      axios.post('/api/openbalance/save/shareholders', this.data.shareholders).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.shareholders = false;
      })
    },
    calculateTotalPrice(item) {
      item.totalPrice = parseFloat(item.count || 0) * parseFloat(item.price || 0);
    },
    addInventoryItem() {
      if (!this.data.commodities) {
        this.data.commodities = [];
      }
      this.data.commodities.push({
        info: null,
        count: 0,
        price: 0,
        totalPrice: 0
      });
    },
    updateCommodityInfo(item) {
      if (item.info) {
        // بررسی تکراری بودن کالا
        const duplicateItem = this.data.commodities.find(commodity => 
          commodity !== item && // بررسی نکنیم که خود آیتم فعلی باشد
          commodity.info && // مطمئن شویم که info خالی نیست
          commodity.info.id === item.info.id // مقایسه id کالاها
        );

        if (duplicateItem) {
          // نمایش پیام خطا
          this.snackbar.text = 'این کالا قبلاً در لیست انتخاب شده است';
          this.snackbar.color = 'error';
          this.snackbar.show = true;
          
          // پاک کردن انتخاب
          item.info = null;
          item.price = 0;
          item.count = 0;
          this.calculateTotalPrice(item);
          return;
        }

        // اگر تکراری نبود، قیمت را به‌روز کن
        item.price = item.info.basePrice || 0;
        this.calculateTotalPrice(item);
      }
    },
    removeInventoryItem(item) {
      const index = this.data.commodities.indexOf(item);
      if (index > -1) {
        this.data.commodities.splice(index, 1);
      }
    },
    saveInventory() {
      if (!this.validateInventory()) {
        return;
      }
      this.loading = true;
      // محاسبه مجدد قیمت کل برای همه اقلام
      this.data.commodities.forEach(item => {
        this.calculateTotalPrice(item);
      });

      // تبدیل داده‌ها به فرمت مورد نیاز بک‌اند
      const commoditiesData = this.data.commodities.map(item => ({
        info: {
          id: item.info.id,
          code: item.info.code,
          name: item.info.name
        },
        count: parseFloat(item.count),
        price: parseFloat(item.price),
        totalPrice: parseFloat(item.totalPrice)
      }));

      axios.post('/api/openbalance/save/commodities', commoditiesData).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.inventory = false;
        this.snackbar.text = this.$t('dialog.inventory.save_success');
        this.snackbar.color = 'success';
        this.snackbar.show = true;
      }).catch(error => {
        this.loading = false;
        this.snackbar.text = this.$t('dialog.inventory.save_error');
        this.snackbar.color = 'error';
        this.snackbar.show = true;
        console.error('Error saving inventory:', error);
      });
    },
    validateInventory() {
      // بررسی تکمیل اطلاعات هر کالا
      for (const item of this.data.commodities) {
        // بررسی انتخاب کالا
        if (!item.info) {
          this.snackbar.text = 'لطفاً کالا را برای تمام سطرها انتخاب کنید';
          this.snackbar.color = 'error';
          this.snackbar.show = true;
          return false;
        }

        // بررسی تعداد
        if (!item.count || parseFloat(item.count) <= 0) {
          this.snackbar.text = 'تعداد کالا باید بزرگتر از صفر باشد';
          this.snackbar.color = 'error';
          this.snackbar.show = true;
          return false;
        }

        // بررسی قیمت
        if (!item.price || parseFloat(item.price) <= 0) {
          this.snackbar.text = 'قیمت کالا باید بزرگتر از صفر باشد';
          this.snackbar.color = 'error';
          this.snackbar.show = true;
          return false;
        }
      }

      // بررسی تکراری نبودن کالاها
      const commodityIds = new Set();
      for (const item of this.data.commodities) {
        if (commodityIds.has(item.info.id)) {
          this.snackbar.text = 'کالای تکراری در لیست وجود دارد';
          this.snackbar.color = 'error';
          this.snackbar.show = true;
          return false;
        }
        commodityIds.add(item.info.id);
      }

      return true;
    },
  }
}
</script>

<style scoped></style>