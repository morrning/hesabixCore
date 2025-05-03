<template>
  <div>
    <v-dialog v-model="dialog" fullscreen @update:model-value="handleDialogClose">
      <v-card>
        <v-toolbar color="toolbar" :title="$t('drawer.presell_view')">
          <template v-slot:prepend>
            <v-tooltip :text="$t('dialog.back')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" @click="handleDialogClose(false)" class="d-none d-sm-flex" variant="text"
                  icon="mdi-arrow-right" />
              </template>
            </v-tooltip>
          </template>
          <v-spacer></v-spacer>
          <v-tooltip :text="$t('dialog.export_pdf')" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-file-pdf-box" color="primary" @click="modal = true"></v-btn>
            </template>
          </v-tooltip>
        </v-toolbar>

        <v-card-text>
          <v-row>
            <v-col cols="12" md="6">
              <v-card class="mb-4" :title="$t('dialog.presell_info')">
                <v-card-text>
                  <v-row>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="presellData.code" :label="$t('dialog.code')" readonly></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="presellData.date" :label="$t('dialog.date')" readonly></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="presellData.person?.nikename" :label="$t('dialog.person')" readonly>
                      </v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="presellData.des" :label="$t('dialog.des')" readonly></v-text-field>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="6">
              <v-card class="mb-4" :title="$t('dialog.financial_info')">
                <v-card-text>
                  <v-row>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="$filters.formatNumber(presellData.amount)" :label="$t('dialog.amount')"
                        readonly></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="$filters.formatNumber(presellData.totalDiscount)"
                        :label="$t('dialog.discount')" readonly></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="$filters.formatNumber(presellData.shippingCost)"
                        :label="$t('dialog.transfer_cost')" readonly></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6">
                      <v-text-field :model-value="$filters.formatNumber(presellData.totalAmount)"
                        :label="$t('dialog.total_amount')" readonly></v-text-field>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12">
              <v-card :title="$t('dialog.invoice_items')">
                <v-card-text>
                  <EasyDataTable table-class-name="customize-table" :headers="itemsHeaders" :items="presellData.items"
                    theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
                    rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد">
                    <template #item-count="{ count }">
                      {{ $filters.formatNumber(count) }}
                    </template>
                    <template #item-price="{ price }">
                      {{ $filters.formatNumber(price) }}
                    </template>
                    <template #item-discountAmount="{ discountAmount }">
                      {{ $filters.formatNumber(discountAmount) }}
                    </template>
                    <template #item-total="{ total }">
                      {{ $filters.formatNumber(total) }}
                    </template>
                  </EasyDataTable>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Print Dialog -->
    <PrintDialog
      v-model="modal"
      :plugins="plugins"
      @print="handlePrint"
      @cancel="modal = false"
    />
    <!-- End Print Dialog -->
  </div>
</template>

<script>
import axios from "axios";
import { ref, defineComponent } from "vue";
import PrintDialog from '@/components/PrintDialog.vue';

export default defineComponent({
  name: "PresellView",
  components: {
    PrintDialog
  },
  props: {
    code: {
      type: [String, Number],
      required: true
    },
    modelValue: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      dialog: this.modelValue,
      modal: false,
      presellData: {},
      plugins: {},
      itemsHeaders: [
        { text: "کد کالا", value: "itemCode" },
        { text: "نام کالا", value: "itemName" },
        { text: "تعداد", value: "count" },
        { text: "قیمت", value: "price" },
        { text: "مبلغ", value: "amount" },
        { text: "جمع", value: "total" }
      ]
    }
  },
  methods: {
    handleDialogClose(value) {
      this.dialog = value;
      this.$emit('update:modelValue', value);
      this.$emit('close');
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
      axios.post('/api/preinvoice/print/invoice', {
        'code': this.code,
        'pdf': pdf,
        'printers': cloudePrinters,
        'printOptions': printOptions
      }).then((response) => {
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      });
    },
    loadData() {
      axios.post('/api/preinvoice/get/' + this.code)
        .then((response) => {
          this.presellData = response.data;
        });
    }
  },
  created() {
    this.loadData();
    this.loadPlugins();
  }
});
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}
</style> 