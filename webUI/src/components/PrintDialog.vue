<template>
  <v-dialog v-model="dialog" width="auto">
    <v-card>
      <v-toolbar color="primary">
        <v-toolbar-title class="text-white">
          <v-icon icon="mdi-file-pdf-box" class="ml-2"></v-icon>
          {{ $t('dialog.export_pdf') }}
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-tooltip :text="$t('dialog.print')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-printer" color="white" @click="handlePrint"></v-btn>
          </template>
        </v-tooltip>
        <v-tooltip :text="$t('dialog.cancel')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" icon="mdi-close" color="white" @click="handleCancel"></v-btn>
          </template>
        </v-tooltip>
      </v-toolbar>
      <v-card-text>
        <v-select class="mb-2" v-model="printOptions.paper" :items="paperSizes" :label="$t('dialog.paper_size')">
        </v-select>
        <v-row>
          <v-col cols="12" sm="6">
            <v-tooltip :text="$t('dialog.bid_info_label')" location="right">
              <template v-slot:activator="{ props }">
                <v-switch v-bind="props" inset v-model="printOptions.bidInfo" color="primary" :label="$t('dialog.bid_info_label')" hide-details density="compact"></v-switch>
              </template>
            </v-tooltip>
            <v-tooltip :text="$t('dialog.invoice_pays')" location="right">
              <template v-slot:activator="{ props }">
                <v-switch v-bind="props" inset v-model="printOptions.pays" color="primary" :label="$t('dialog.invoice_pays')" hide-details density="compact"></v-switch>
              </template>
            </v-tooltip>
            <v-tooltip :text="$t('dialog.invoice_footer_note')" location="right">
              <template v-slot:activator="{ props }">
                <v-switch v-bind="props" inset v-model="printOptions.note" color="primary" :label="$t('dialog.invoice_footer_note')" hide-details density="compact"></v-switch>
              </template>
            </v-tooltip>
          </v-col>
          <v-col cols="12" sm="6">
            <v-tooltip :text="$t('dialog.tax_dexpo')" location="right">
              <template v-slot:activator="{ props }">
                <v-switch v-bind="props" inset v-model="printOptions.taxInfo" color="primary" :label="$t('dialog.tax_dexpo')" hide-details density="compact"></v-switch>
              </template>
            </v-tooltip>
            <v-tooltip :text="$t('dialog.discount_dexpo')" location="right">
              <template v-slot:activator="{ props }">
                <v-switch v-bind="props" inset v-model="printOptions.discountInfo" color="primary" :label="$t('dialog.discount_dexpo')" hide-details density="compact"></v-switch>
              </template>
            </v-tooltip>
            <v-tooltip :text="$t('dialog.business_stamp')" location="right">
              <template v-slot:activator="{ props }">
                <v-switch v-if="isPluginActive('accpro')" v-bind="props" inset v-model="printOptions.businessStamp" color="primary" :label="$t('dialog.business_stamp')" hide-details density="compact"></v-switch>
              </template>
            </v-tooltip>
            <v-tooltip :text="$t('dialog.invoice_index')" location="right">
              <template v-slot:activator="{ props }">
                <v-switch v-if="isPluginActive('accpro')" v-bind="props" inset v-model="printOptions.invoiceIndex" color="primary" :label="$t('dialog.invoice_index')" hide-details density="compact"></v-switch>
              </template>
            </v-tooltip>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { ref, defineComponent } from 'vue';

export default defineComponent({
  name: 'PrintDialog',
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    plugins: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['update:modelValue', 'print', 'cancel'],
  setup(props, { emit }) {
    const dialog = ref(props.modelValue);
    const printOptions = ref({
      note: true,
      bidInfo: true,
      taxInfo: true,
      discountInfo: true,
      pays: false,
      paper: 'A4-L',
      businessStamp: false,
      invoiceIndex: false
    });

    const paperSizes = [
      { title: 'A4 عمودی', value: 'A4' },
      { title: 'A4 افقی', value: 'A4-L' },
      { title: 'A5 عمودی', value: 'A5' },
      { title: 'A5 افقی', value: 'A5-L' }
    ];

    const isPluginActive = (pluginCode) => {
      return props.plugins && props.plugins[pluginCode];
    };

    const handlePrint = () => {
      emit('print', printOptions.value);
      dialog.value = false;
    };

    const handleCancel = () => {
      emit('cancel');
      dialog.value = false;
    };

    return {
      dialog,
      printOptions,
      paperSizes,
      isPluginActive,
      handlePrint,
      handleCancel
    };
  },
  watch: {
    modelValue(newVal) {
      this.dialog = newVal;
    },
    dialog(newVal) {
      this.$emit('update:modelValue', newVal);
    }
  }
});
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}
.v-dialog {
  transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style> 