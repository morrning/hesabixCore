<template>
  <v-toolbar color="toolbar" :title="$t('dialog.presell_invoice')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-tooltip text="ثبت فاکتور" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-content-save" color="success" @click="savePreinvoice" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
    <v-tooltip v-if="$route.params.id" text="حذف فاکتور" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-delete" color="error" @click="deletePreinvoice" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container>
    <v-row>
      <v-col cols="12">
        <v-row class="mb-2">
          <v-col cols="12" sm="6">
            <Hdatepicker v-model="invoiceDate" label="تاریخ پیش فاکتور" density="compact"></Hdatepicker>
          </v-col>
          <v-col cols="12" sm="6">
            <Hpersonsearch v-model="customer" label="خریدار" :rules="[v => !!v || 'خریدار الزامی است']" required></Hpersonsearch>
          </v-col>
        </v-row>
        <v-text-field v-model="invoiceDescription" label="توضیحات پیش فاکتور" density="compact" hide-details
          class="mb-4">
          <template v-slot:prepend-inner>
            <mostdes v-model="invoiceDescription" :submitData="{id: null, des: invoiceDescription}" type="sell" label=""></mostdes>
          </template>
        </v-text-field>
        <v-table class="border rounded d-none d-sm-table" style="width: 100%;">
          <thead>
            <tr style="background-color: #0D47A1; color: white;">
              <th class="text-center">نام کالا</th>
              <th class="text-center">تعداد</th>
              <th class="text-center">قیمت</th>
              <th class="text-center">تخفیف</th>
              <th class="text-center" style="width: 150px;">جمع کل</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(item, index) in items" :key="index">
              <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '64px' }">
                <td class="text-center" style="min-width: 200px;">
                  <Hcommoditysearch v-model="item.name" density="compact" hide-details class="my-0"
                    style="font-size: 0.8rem;" return-object></Hcommoditysearch>
                </td>
                <td class="text-center" style="width: 100px;">
                  <Hnumberinput v-model="item.count" density="compact" @update:modelValue="recalculateTotals"
                    class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                </td>
                <td class="text-center" style="width: 120px;">
                  <Hnumberinput v-model="item.price" density="compact" @update:modelValue="recalculateTotals"
                    class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                </td>
                <td class="text-center" style="width: 150px;">
                  <div class="d-flex align-center">
                    <Hnumberinput 
                      v-if="item.showPercentDiscount"
                      v-model="item.discountPercent" 
                      density="compact" 
                      suffix="%" 
                      @update:modelValue="recalculateTotals"
                      class="my-0" 
                      style="font-size: 0.8rem;"
                    >
                      <template v-slot:prepend>
                        <v-tooltip text="تخفیف درصدی" location="bottom">
                          <template v-slot:activator="{ props }">
                            <v-checkbox
                              v-bind="props"
                              v-model="item.showPercentDiscount"
                              hide-details
                              density="compact"
                              color="primary"
                              class="mt-0"
                              @update:modelValue="handleDiscountTypeChange(item)"
                            ></v-checkbox>
                          </template>
                        </v-tooltip>
                      </template>
                    </Hnumberinput>
                    <Hnumberinput 
                      v-else
                      v-model="item.discountAmount" 
                      density="compact" 
                      @update:modelValue="recalculateTotals"
                      class="my-0" 
                      style="font-size: 0.8rem;"
                    >
                      <template v-slot:prepend>
                        <v-tooltip text="تخفیف مبلغی" location="bottom">
                          <template v-slot:activator="{ props }">
                            <v-checkbox
                              v-bind="props"
                              v-model="item.showPercentDiscount"
                              hide-details
                              density="compact"
                              color="primary"
                              class="mt-0"
                              @update:modelValue="handleDiscountTypeChange(item)"
                            ></v-checkbox>
                          </template>
                        </v-tooltip>
                      </template>
                    </Hnumberinput>
                  </div>
                </td>
                <td class="text-center font-weight-bold" style="width: 120px;">
                  {{ item.total.toLocaleString('fa-IR') }}
                </td>
              </tr>
              <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '64px' }">
                <td colspan="4">
                  <v-text-field v-model="item.description" density="compact" hide-details placeholder="شرح" class="my-0"
                    style="font-size: 0.8rem;">
                    <template v-slot:prepend-inner>
                      <mostdes v-model="item.description" :submitData="{id: null, des: item.description}" type="sell" label=""></mostdes>
                    </template>
                  </v-text-field>
                </td>
                <td class="text-center" style="width: 120px;">
                  <v-tooltip text="حذف" location="bottom">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-delete" variant="text" size="small" color="error"
                        @click="removeItem(index)"></v-btn>
                    </template>
                  </v-tooltip>
                </td>
              </tr>
            </template>
            <tr>
              <td colspan="5" class="text-center pa-2" style="height: 64px;">
                <v-btn color="primary" prepend-icon="mdi-plus" size="small" @click="addItem">افزودن سطر جدید</v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>

        <!-- جدول موبایل -->
        <div class="d-sm-none">
          <v-card v-for="(item, index) in items" :key="index" class="mb-4" variant="outlined">
            <v-card-text>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-subtitle-2 font-weight-bold">ردیف:</span>
                <span>{{ index + 1 }}</span>
              </div>
              <div class="mb-2">
                <Hcommoditysearch v-model="item.name" density="compact" label="نام کالا" hide-details class="my-0"
                  style="font-size: 0.8rem;" return-object></Hcommoditysearch>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <div style="width: 48%;">
                  <Hnumberinput v-model="item.count" density="compact" label="تعداد" hide-details class="my-0"
                    style="font-size: 0.8rem;" @update:modelValue="recalculateTotals"></Hnumberinput>
                </div>
                <div style="width: 48%;">
                  <Hnumberinput v-model="item.price" density="compact" label="قیمت" hide-details class="my-0"
                    style="font-size: 0.8rem;" @update:modelValue="recalculateTotals"></Hnumberinput>
                </div>
              </div>
              <div class="mb-2">
                <div class="d-flex align-center">
                  <Hnumberinput 
                    v-if="item.showPercentDiscount"
                    v-model="item.discountPercent" 
                    density="compact" 
                    label="تخفیف" 
                    suffix="%" 
                    hide-details
                    @update:modelValue="recalculateTotals"
                    class="my-0" 
                    style="font-size: 0.8rem;"
                  >
                    <template v-slot:prepend>
                      <v-tooltip text="تخفیف درصدی" location="bottom">
                        <template v-slot:activator="{ props }">
                          <v-checkbox
                            v-bind="props"
                            v-model="item.showPercentDiscount"
                            hide-details
                            density="compact"
                            color="primary"
                            class="mt-0"
                            @update:modelValue="handleDiscountTypeChange(item)"
                          ></v-checkbox>
                        </template>
                      </v-tooltip>
                    </template>
                  </Hnumberinput>
                  <Hnumberinput 
                    v-else
                    v-model="item.discountAmount" 
                    density="compact" 
                    label="تخفیف" 
                    hide-details
                    @update:modelValue="recalculateTotals"
                    class="my-0" 
                    style="font-size: 0.8rem;"
                  >
                    <template v-slot:prepend>
                      <v-tooltip text="تخفیف مبلغی" location="bottom">
                        <template v-slot:activator="{ props }">
                          <v-checkbox
                            v-bind="props"
                            v-model="item.showPercentDiscount"
                            hide-details
                            density="compact"
                            color="primary"
                            class="mt-0"
                            @update:modelValue="handleDiscountTypeChange(item)"
                          ></v-checkbox>
                        </template>
                      </v-tooltip>
                    </template>
                  </Hnumberinput>
                </div>
              </div>
              <div class="mb-2">
                <v-text-field v-model="item.description" density="compact" label="شرح" hide-details class="my-0"
                  style="font-size: 0.8rem;">
                  <template v-slot:prepend-inner>
                    <mostdes v-model="item.description" :submitData="{id: null, des: item.description}" type="sell" label=""></mostdes>
                  </template>
                </v-text-field>
              </div>
              <div class="d-flex justify-space-between align-center">
                <span class="text-subtitle-2 font-weight-bold">جمع کل:</span>
                <span class="text-subtitle-2 font-weight-bold">{{ item.total.toLocaleString('fa-IR') }}</span>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn icon="mdi-delete" variant="text" color="error" @click="removeItem(index)"></v-btn>
            </v-card-actions>
          </v-card>
          <v-btn color="primary" prepend-icon="mdi-plus" block class="mb-4" @click="addItem">افزودن کالای جدید</v-btn>
        </div>
        <v-card class="mt-4 pa-4" color="grey-lighten-4 border">
          <v-row>
            <v-col cols="12" sm="6">
              <v-row>
                <v-col cols="12">
                  <Hnumberinput v-model="taxPercent" label="مالیات بر ارزش افزوده" density="compact" hide-details
                    suffix="%" @update:modelValue="recalculateTotals"></Hnumberinput>
                </v-col>
                <v-col cols="12">
                  <div class="d-flex align-center">
                    <Hnumberinput 
                      v-if="showTotalPercentDiscount"
                      v-model="totalDiscountPercent" 
                      density="compact" 
                      label="تخفیف کلی" 
                      hide-details
                      suffix="%" 
                      @update:modelValue="recalculateTotals"
                    >
                      <template v-slot:prepend>
                        <v-tooltip text="تخفیف درصدی" location="bottom">
                          <template v-slot:activator="{ props }">
                            <v-checkbox
                              v-bind="props"
                              v-model="showTotalPercentDiscount"
                              hide-details
                              density="compact"
                              color="primary"
                              class="mt-0"
                            ></v-checkbox>
                          </template>
                        </v-tooltip>
                      </template>
                    </Hnumberinput>
                    <Hnumberinput 
                      v-else
                      v-model="totalDiscount" 
                      density="compact" 
                      label="تخفیف کلی" 
                      hide-details
                      @update:modelValue="recalculateTotals"
                    >
                      <template v-slot:prepend>
                        <v-tooltip text="تخفیف مبلغی" location="bottom">
                          <template v-slot:activator="{ props }">
                            <v-checkbox
                              v-bind="props"
                              v-model="showTotalPercentDiscount"
                              hide-details
                              density="compact"
                              color="primary"
                              class="mt-0"
                            ></v-checkbox>
                          </template>
                        </v-tooltip>
                      </template>
                    </Hnumberinput>
                  </div>
                </v-col>
                <v-col cols="12">
                  <Hnumberinput v-model="shippingCost" density="compact" label="هزینه حمل" hide-details
                    @update:modelValue="recalculateTotals"></Hnumberinput>
                </v-col>
              </v-row>
            </v-col>
            <v-col cols="12" sm="6">
              <v-card class="pa-4" color="white">
                <div class="d-flex align-center justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">جمع کل فاکتور:</span>
                  <span class="text-subtitle-2 font-weight-bold">{{ totalInvoice.toLocaleString('fa-IR') }}</span>
                </div>
                <div class="d-flex align-center justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">تخفیف کلی:</span>
                  <span class="text-subtitle-2 font-weight-bold">{{ (showTotalPercentDiscount ? Math.round((totalInvoice * totalDiscountPercent) / 100) : totalDiscount).toLocaleString('fa-IR') }}</span>
                </div>
                <div class="d-flex align-center justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">هزینه حمل:</span>
                  <span class="text-subtitle-2 font-weight-bold">{{ shippingCost.toLocaleString('fa-IR') }}</span>
                </div>
                <div class="d-flex align-center justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">جمع کل با تخفیف و حمل:</span>
                  <span class="text-subtitle-2 font-weight-bold">{{ (totalInvoice - (showTotalPercentDiscount ? Math.round((totalInvoice * totalDiscountPercent) / 100) : totalDiscount) + shippingCost).toLocaleString('fa-IR') }}</span>
                </div>
                <div class="d-flex align-center justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">مبلغ مالیات:</span>
                  <span class="text-subtitle-2 font-weight-bold">{{ taxAmount.toLocaleString('fa-IR') }}</span>
                </div>
                <div class="d-flex align-center justify-space-between mb-2">
                  <span class="text-subtitle-2 font-weight-bold">جمع کل نهایی:</span>
                  <span class="text-subtitle-2 font-weight-bold">{{ finalTotal.toLocaleString('fa-IR') }}</span>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <v-snackbar v-model="showError" color="error" timeout="3000">
    مبلغ تخفیف نمی‌تواند از قیمت پایه بیشتر باشد
  </v-snackbar>
  <v-snackbar v-model="showDiscountError" color="error" timeout="3000">
    مبلغ تخفیف کلی نمی‌تواند از جمع کل فاکتور بیشتر باشد
  </v-snackbar>
  <v-snackbar v-model="showError" color="error" timeout="3000">
    {{ error }}
  </v-snackbar>
  <v-snackbar v-model="showSuccess" color="success" timeout="3000">
    {{ successMessage }}
  </v-snackbar>
  <v-snackbar v-model="showValidationErrors" color="error" timeout="3000">
    <ul class="mb-0">
      <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
    </ul>
  </v-snackbar>
  <v-overlay :model-value="loading" class="align-center justify-center">
    <v-progress-circular color="primary" indeterminate></v-progress-circular>
  </v-overlay>

  <!-- دیالوگ تأیید حذف -->
  <v-dialog v-model="deleteDialog" max-width="400">
    <v-card>
      <v-card-title class="text-h5">
        حذف پیش فاکتور
      </v-card-title>
      <v-card-text>
        آیا مطمئن هستید که می‌خواهید این پیش فاکتور را حذف کنید؟
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="grey-darken-1" variant="text" @click="deleteDialog = false">
          انصراف
        </v-btn>
        <v-btn color="error" variant="text" @click="confirmDelete" :loading="loading">
          حذف
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from "axios";
import Hnumberinput from "@/components/forms/Hnumberinput.vue";
import Hdatepicker from "@/components/forms/Hdatepicker.vue";
import Hpersonsearch from "@/components/forms/Hpersonsearch.vue";
import mostdes from "../component/mostdes.vue";
import Hcommoditysearch from "@/components/forms/Hcommoditysearch.vue";
import { ref } from 'vue';

export default {
  name: "mod",
  components: {
    Hnumberinput,
    Hdatepicker,
    Hpersonsearch,
    mostdes,
    Hcommoditysearch
  },
  setup() {
    const items = ref([]);
    const totalInvoice = ref(0);
    const showError = ref(false);
    const showDiscountError = ref(false);
    const showPercentDiscount = ref(true);
    const showTotalPercentDiscount = ref(true);
    const invoiceDescription = ref('');
    const invoiceDate = ref(null);
    const customer = ref(null);
    const taxPercent = ref(0);
    const totalDiscount = ref(0);
    const totalDiscountPercent = ref(0);
    const shippingCost = ref(0);
    const finalTotal = ref(0);
    const totalWithoutTax = ref(0);
    const taxAmount = ref(0);
    const loading = ref(false);
    const error = ref(null);
    const validationErrors = ref([]);
    const deleteDialog = ref(false);
    const showSuccess = ref(false);
    const successMessage = ref('');
    const showValidationErrors = ref(false);

    return {
      items,
      totalInvoice,
      showError,
      showDiscountError,
      showPercentDiscount,
      showTotalPercentDiscount,
      invoiceDescription,
      invoiceDate,
      customer,
      taxPercent,
      totalDiscount,
      totalDiscountPercent,
      shippingCost,
      finalTotal,
      totalWithoutTax,
      taxAmount,
      loading,
      error,
      validationErrors,
      deleteDialog,
      showSuccess,
      successMessage,
      showValidationErrors
    };
  },
  watch: {
    showTotalPercentDiscount(newVal) {
      if (newVal) {
        this.totalDiscount = 0;
      } else {
        this.totalDiscountPercent = 0;
      }
      this.recalculateTotals();
    }
  },
  mounted() {
    const id = this.$route.params.id;
    if (id) {
      this.loadPreinvoice(id);
    } else {
      this.addItem();
    }
  },
  methods: {
    validateForm() {
      this.validationErrors = [];
      this.showValidationErrors = false;

      if (!this.customer) {
        this.validationErrors.push('لطفا خریدار را انتخاب کنید');
      }

      if (!this.invoiceDate) {
        this.validationErrors.push('لطفا تاریخ را انتخاب کنید');
      }

      if (this.items.length === 0) {
        this.validationErrors.push('لطفا حداقل یک کالا اضافه کنید');
      } else {
        this.items.forEach((item, index) => {
          if (!item.name) {
            this.validationErrors.push(`لطفا کالای ردیف ${index + 1} را انتخاب کنید`);
          }
          if (!item.count || item.count <= 0) {
            this.validationErrors.push(`لطفا تعداد کالای ردیف ${index + 1} را وارد کنید`);
          }
          if (!item.price || item.price <= 0) {
            this.validationErrors.push(`لطفا قیمت کالای ردیف ${index + 1} را وارد کنید`);
          }
        });
      }

      if (this.validationErrors.length > 0) {
        this.showValidationErrors = true;
      }

      return this.validationErrors.length === 0;
    },
    async loadPreinvoice(id) {
      try {
        this.loading = true;
        const response = await axios.get(`/api/preinvoice/get/${id}`);
        const data = response.data;
        
        this.invoiceDate = data.date;
        this.customer = data.person.id;
        this.invoiceDescription = data.des;
        this.taxPercent = data.taxPercent;
        this.totalDiscount = data.totalDiscount;
        this.totalDiscountPercent = data.totalDiscountPercent;
        this.shippingCost = data.shippingCost;
        this.showPercentDiscount = data.showPercentDiscount ?? true;
        this.showTotalPercentDiscount = data.showTotalPercentDiscount ?? true;
        
        this.items = data.items.map(item => ({
          name: {
            id: item.commodity.id,
            name: item.commodity.name || '',
            code: item.commodity.code || ''
          },
          count: item.count,
          price: item.price,
          discountPercent: item.discountPercent || 0,
          discountAmount: item.discountAmount || 0,
          total: 0,
          description: item.description || '',
          showPercentDiscount: item.showPercentDiscount ?? true
        }));
        
        this.recalculateTotals();
      } catch (error) {
        this.error = 'خطا در بارگذاری پیش فاکتور';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async savePreinvoice() {
      if (!this.validateForm()) {
        return;
      }

      try {
        this.loading = true;
        const data = {
          date: this.invoiceDate,
          person: { id: this.customer },
          des: this.invoiceDescription,
          amount: this.totalInvoice,
          taxPercent: this.taxPercent,
          totalDiscount: this.totalDiscount,
          totalDiscountPercent: this.totalDiscountPercent,
          shippingCost: this.shippingCost,
          showPercentDiscount: this.showPercentDiscount,
          showTotalPercentDiscount: this.showTotalPercentDiscount,
          items: this.items.map(item => ({
            commodity: { id: item.name.id },
            count: item.count,
            price: item.price,
            discountPercent: item.discountPercent,
            discountAmount: item.discountAmount,
            description: item.description,
            showPercentDiscount: item.showPercentDiscount
          }))
        };

        if (this.$route.params.id) {
          data.id = this.$route.params.id;
        }

        const response = await axios.post('/api/preinvoice/save', data);
        this.successMessage = this.$route.params.id ? 'پیش‌فاکتور با موفقیت ویرایش شد' : 'پیش‌فاکتور با موفقیت ثبت شد';
        this.showSuccess = true;
        
        setTimeout(() => {
          this.$router.push('/acc/presell/list');
        }, 1500);
      } catch (error) {
        this.error = 'خطا در ذخیره پیش فاکتور';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async deletePreinvoice() {
      this.deleteDialog = true;
    },
    async confirmDelete() {
      try {
        this.loading = true;
        await axios.delete(`/api/preinvoice/delete/${this.$route.params.id}`);
        this.$router.push('/acc/presell/list');
      } catch (error) {
        this.error = 'خطا در حذف پیش فاکتور';
        console.error(error);
      } finally {
        this.loading = false;
        this.deleteDialog = false;
      }
    },
    addItem() {
      this.items.push({
        name: null,
        count: 0,
        price: 0,
        discountPercent: 0,
        discountAmount: 0,
        total: 0,
        description: '',
        showPercentDiscount: this.showPercentDiscount
      });
      this.recalculateTotals();
    },
    removeItem(index) {
      this.items.splice(index, 1);
      this.recalculateTotals();
    },
    recalculateTotals() {
      let total = 0;
      this.items.forEach(item => {
        const basePrice = (item.count || 0) * (item.price || 0);
        let totalDiscount = 0;
        
        if (item.showPercentDiscount) {
          totalDiscount = Math.round((basePrice * (item.discountPercent || 0)) / 100);
        } else {
          totalDiscount = item.discountAmount || 0;
        }
        
        if (totalDiscount > basePrice) {
          item.discountPercent = 0;
          item.discountAmount = 0;
          this.showError = true;
        }
        
        const itemTotal = basePrice - totalDiscount;
        item.total = itemTotal;
        total += itemTotal;
      });
      this.totalInvoice = total;

      this.taxAmount = Math.round((total * this.taxPercent) / 100);
      this.totalWithoutTax = total;
      
      let calculatedTotalDiscount = 0;
      if (this.showTotalPercentDiscount) {
        calculatedTotalDiscount = Math.round((total * this.totalDiscountPercent) / 100);
      } else {
        calculatedTotalDiscount = this.totalDiscount;
      }
      
      if (calculatedTotalDiscount > total) {
        this.totalDiscountPercent = 0;
        this.totalDiscount = 0;
        this.showDiscountError = true;
      }
      
      this.finalTotal = total + this.taxAmount - calculatedTotalDiscount + this.shippingCost;
    },
    handleDiscountTypeChange(item) {
      if (item.showPercentDiscount) {
        item.discountAmount = 0;
      } else {
        item.discountPercent = 0;
      }
      this.recalculateTotals();
    }
  }
}
</script>

<style scoped>
/* اگر استایل خاصی دارید، اینجا اضافه کنید */
</style>