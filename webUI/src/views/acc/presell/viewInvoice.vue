<template>
  <v-toolbar color="toolbar" :title="`${$t('dialog.presell_invoice')} (${preinvoiceCode})`">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-tooltip text="ویرایش" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-pencil" color="primary" @click="editPreinvoice"></v-btn>
      </template>
    </v-tooltip>
    <v-tooltip text="چاپ" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-printer" color="primary" @click="modal = true"></v-btn>
      </template>
    </v-tooltip>
    <v-tooltip text="حذف" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-delete" color="danger" @click="deleteDialog = true"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container>
    <v-row>
      <v-col cols="12">
        <v-row class="mb-2">
          <v-col cols="12" sm="6">
            <v-text-field :model-value="invoiceDate" label="تاریخ پیش فاکتور" density="compact" readonly></v-text-field>
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field :model-value="customerName" label="خریدار" density="compact" readonly></v-text-field>
          </v-col>
        </v-row>
        <v-text-field :model-value="invoiceDescription" label="توضیحات پیش فاکتور" density="compact" hide-details
          class="mb-4" readonly>
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
                  <v-text-field :model-value="item.name?.name" density="compact" hide-details class="my-0"
                    style="font-size: 0.8rem;" readonly></v-text-field>
                </td>
                <td class="text-center" style="width: 100px;">
                  <v-text-field :model-value="item.count" density="compact" class="my-0" style="font-size: 0.8rem;" readonly></v-text-field>
                </td>
                <td class="text-center" style="width: 120px;">
                  <v-text-field :model-value="item.price" density="compact" class="my-0" style="font-size: 0.8rem;" readonly></v-text-field>
                </td>
                <td class="text-center" style="width: 150px;">
                  <v-text-field 
                    :model-value="item.showPercentDiscount ? `${item.discountPercent}%` : item.discountAmount"
                    density="compact" 
                    class="my-0" 
                    style="font-size: 0.8rem;"
                    readonly
                  ></v-text-field>
                </td>
                <td class="text-center font-weight-bold" style="width: 120px;">
                  {{ item.total.toLocaleString('fa-IR') }}
                </td>
              </tr>
              <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '64px' }">
                <td colspan="4">
                  <v-text-field :model-value="item.description" density="compact" hide-details placeholder="شرح" class="my-0"
                    style="font-size: 0.8rem;" readonly>
                  </v-text-field>
                </td>
                <td class="text-center" style="width: 120px;">
                </td>
              </tr>
            </template>
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
                <v-text-field :model-value="item.name?.name" density="compact" label="نام کالا" hide-details class="my-0"
                  style="font-size: 0.8rem;" readonly></v-text-field>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <div style="width: 48%;">
                  <v-text-field :model-value="item.count" density="compact" label="تعداد" hide-details class="my-0"
                    style="font-size: 0.8rem;" readonly></v-text-field>
                </div>
                <div style="width: 48%;">
                  <v-text-field :model-value="item.price" density="compact" label="قیمت" hide-details class="my-0"
                    style="font-size: 0.8rem;" readonly></v-text-field>
                </div>
              </div>
              <div class="mb-2">
                <v-text-field 
                  :model-value="item.showPercentDiscount ? `${item.discountPercent}%` : item.discountAmount"
                  density="compact" 
                  label="تخفیف" 
                  hide-details
                  class="my-0" 
                  style="font-size: 0.8rem;"
                  readonly
                ></v-text-field>
              </div>
              <div class="mb-2">
                <v-text-field :model-value="item.description" density="compact" label="شرح" hide-details class="my-0"
                  style="font-size: 0.8rem;" readonly>
                </v-text-field>
              </div>
              <div class="d-flex justify-space-between align-center">
                <span class="text-subtitle-2 font-weight-bold">جمع کل:</span>
                <span class="text-subtitle-2 font-weight-bold">{{ item.total.toLocaleString('fa-IR') }}</span>
              </div>
            </v-card-text>
          </v-card>
        </div>
        <v-card class="mt-4 pa-4" color="grey-lighten-4 border">
          <v-row>
            <v-col cols="12" sm="6">
              <v-row>
                <v-col cols="12">
                  <v-text-field :model-value="`${taxPercent}%`" label="مالیات بر ارزش افزوده" density="compact" hide-details
                    readonly></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field 
                    :model-value="showTotalPercentDiscount ? `${totalDiscountPercent}%` : totalDiscount"
                    density="compact" 
                    label="تخفیف کلی" 
                    hide-details
                    readonly
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field :model-value="shippingCost" density="compact" label="هزینه حمل" hide-details
                    readonly></v-text-field>
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
  <v-overlay :model-value="loading" class="align-center justify-center">
    <v-progress-circular color="primary" indeterminate></v-progress-circular>
  </v-overlay>
  <!-- Print Modal -->
  <PrintDialog
    v-model="modal"
    :plugins="plugins"
    @print="handlePrint"
    @cancel="modal = false"
  />
  <!-- End Print Modal -->
  <!-- Delete Dialog -->
  <v-dialog v-model="deleteDialog" max-width="500">
    <v-card class="rounded-lg">
      <v-card-title class="text-h5 pa-4 bg-red-lighten-5">
        <v-icon color="red" class="mr-2">mdi-alert-circle</v-icon>
        حذف پیش فاکتور
      </v-card-title>
      <v-card-text class="pa-4 text-body-1">
        آیا از حذف پیش فاکتور شماره {{ preinvoiceCode }} اطمینان دارید؟
        <br>
        <span class="text-red-darken-4 mt-2 d-block">این عمل قابل بازگشت نیست!</span>
      </v-card-text>
      <v-card-actions class="pa-4">
        <v-spacer></v-spacer>
        <v-btn color="grey-darken-1" variant="text" @click="deleteDialog = false">
          انصراف
        </v-btn>
        <v-btn color="red" variant="tonal" @click="confirmDelete" :loading="loading">
          حذف
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
  <!-- End Delete Dialog -->

  <!-- Snackbar -->
  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    :timeout="3000"
    location="top"
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
  <!-- End Snackbar -->
</template>

<script>
import axios from "axios";
import { ref, computed } from 'vue';
import PrintDialog from '@/components/PrintDialog.vue';

export default {
  name: "viewInvoice",
  components: {
    PrintDialog
  },
  setup() {
    const items = ref([]);
    const totalInvoice = ref(0);
    const invoiceDescription = ref('');
    const invoiceDate = ref(null);
    const customer = ref({});
    const taxPercent = ref(0);
    const totalDiscount = ref(0);
    const totalDiscountPercent = ref(0);
    const shippingCost = ref(0);
    const finalTotal = ref(0);
    const totalWithoutTax = ref(0);
    const taxAmount = ref(0);
    const loading = ref(false);
    const showPercentDiscount = ref(true);
    const showTotalPercentDiscount = ref(true);
    const preinvoiceCode = ref('');
    const modal = ref(false);
    const deleteDialog = ref(false);
    const plugins = ref({});
    const snackbar = ref({
      show: false,
      text: '',
      color: 'success'
    });

    const customerName = computed(() => {
      return customer.value?.nikename || customer.value?.name || 'نامشخص';
    });

    return {
      items,
      totalInvoice,
      invoiceDescription,
      invoiceDate,
      customer,
      customerName,
      taxPercent,
      totalDiscount,
      totalDiscountPercent,
      shippingCost,
      finalTotal,
      totalWithoutTax,
      taxAmount,
      loading,
      showPercentDiscount,
      showTotalPercentDiscount,
      preinvoiceCode,
      modal,
      deleteDialog,
      plugins,
      snackbar
    };
  },
  mounted() {
    const id = this.$route.params.id;
    if (id) {
      this.loadPreinvoice(id);
      this.loadPlugins();
    }
  },
  methods: {
    async loadPreinvoice(id) {
      try {
        this.loading = true;
        const response = await axios.get(`/api/preinvoice/get/${id}`);
        const data = response.data;

        this.invoiceDate = data.date || 'نامشخص';
        this.customer = data.person || {};
        this.invoiceDescription = data.des || '';
        this.taxPercent = Number(data.taxPercent) || 0;
        this.totalDiscount = Number(data.totalDiscount) || 0;
        this.totalDiscountPercent = Number(data.totalDiscountPercent) || 0;
        this.shippingCost = Number(data.shippingCost) || 0;
        this.showPercentDiscount = data.showPercentDiscount ?? true;
        this.showTotalPercentDiscount = data.showTotalPercentDiscount ?? true;
        this.preinvoiceCode = data.code || '';

        this.items = (data.items || []).map(item => ({
          name: {
            id: item.commodity?.id || 0,
            name: item.commodity?.name || 'نامشخص',
            code: item.commodity?.code || ''
          },
          count: Number(item.count) || 0,
          price: Number(item.price) || 0,
          discountPercent: Number(item.discountPercent) || 0,
          discountAmount: Number(item.discountAmount) || 0,
          total: 0,
          description: item.description || '',
          showPercentDiscount: item.showPercentDiscount ?? true
        }));

        this.recalculateTotals();
      } catch (error) {
        console.error('خطا در بارگذاری پیش‌فاکتور:', error);
        alert('خطایی در بارگذاری پیش‌فاکتور رخ داد. لطفاً دوباره تلاش کنید.');
      } finally {
        this.loading = false;
      }
    },
    editPreinvoice() {
      if (this.preinvoiceCode) {
        this.$router.push(`/acc/presell/mod/${this.preinvoiceCode}`);
      }
    },
    recalculateTotals() {
      let total = 0;
      this.items.forEach(item => {
        const count = Number(item.count) || 0;
        const price = Number(item.price) || 0;
        const basePrice = count * price;
        let totalDiscount = 0;

        if (item.showPercentDiscount) {
          const discountPercent = Number(item.discountPercent) || 0;
          totalDiscount = Math.round((basePrice * discountPercent) / 100);
        } else {
          totalDiscount = Number(item.discountAmount) || 0;
        }

        const itemTotal = basePrice - totalDiscount;
        item.total = itemTotal;
        total += itemTotal;
      });

      this.totalInvoice = total;
      this.taxAmount = Math.round((total * (Number(this.taxPercent) || 0)) / 100);
      this.totalWithoutTax = total;

      let calculatedTotalDiscount = 0;
      if (this.showTotalPercentDiscount) {
        calculatedTotalDiscount = Math.round((total * (Number(this.totalDiscountPercent) || 0)) / 100);
      } else {
        calculatedTotalDiscount = Number(this.totalDiscount) || 0;
      }

      this.finalTotal = total + this.taxAmount - calculatedTotalDiscount + (Number(this.shippingCost) || 0);
    },
    async loadPlugins() {
      try {
        const response = await axios.post('/api/plugin/get/actives');
        this.plugins = response.data;
      } catch (error) {
        console.error('خطا در بارگذاری افزونه‌ها:', error);
      }
    },
    handlePrint(printOptions) {
      this.printInvoice(true, true, printOptions);
    },
    printInvoice(pdf = true, cloudePrinters = true, printOptions = null) {
      this.loading = true;
      axios.post('/api/preinvoice/print/invoice', {
        'code': this.preinvoiceCode,
        'pdf': pdf,
        'printers': cloudePrinters,
        'printOptions': printOptions
      }).then((response) => {
        this.loading = false;
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      });
    },
    confirmDelete() {
      this.deleteDialog = false;
      this.loading = true;
      axios.post('/api/preinvoice/delete/' + this.preinvoiceCode).then((response) => {
        this.loading = false;
        if (response.data.message) {
          this.snackbar.text = response.data.message;
          this.snackbar.color = 'success';
          this.snackbar.show = true;
          setTimeout(() => {
            this.$router.push('/acc/presell/list');
          }, 1500);
        } else {
          this.snackbar.text = 'خطایی در حذف پیش‌فاکتور رخ داد. لطفاً دوباره تلاش کنید.';
          this.snackbar.color = 'error';
          this.snackbar.show = true;
        }
      }).catch(error => {
        this.loading = false;
        console.error('خطا در حذف پیش‌فاکتور:', error);
        this.snackbar.text = 'خطایی در حذف پیش‌فاکتور رخ داد. لطفاً دوباره تلاش کنید.';
        this.snackbar.color = 'error';
        this.snackbar.show = true;
      });
    }
  }
}
</script>

<style scoped>
/* اگر استایل خاصی دارید، اینجا اضافه کنید */
</style>