<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';

export default defineComponent({
  name: 'PrintOptions',
  props: {
    invoiceId: { type: String, required: true },
  },
  data: () => ({
    dialog: false,
    loading: false,
    printOptions: {
      pays: true,
      note: true,
      bidInfo: true,
      taxInfo: true,
      discountInfo: true,
      paper: 'A4-L',
    },
  }),
  methods: {
    printInvoice(pdf = true, cloudPrinters = true) {
      this.loading = true;
      axios
        .post('/api/sell/print/invoice', {
          code: this.$props.invoiceId,
          pdf,
          printers: cloudPrinters,
          printOptions: this.printOptions,
        })
        .then((response) => {
          window.open(`${this.$API_URL}/front/print/${response.data.id}`, '_blank', 'noreferrer');
          this.dialog = false;
        })
        .catch((error) => {
          console.error('خطا در چاپ:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    // متد برای جلوگیری از بسته شدن دیالوگ با کلیک خارج
    preventClose() {
      // هیچ عملی انجام نمی‌شود تا دیالوگ بسته نشود
    },
  },
});
</script>

<template>
  <v-btn icon color="primary" class="ml-2" @click="dialog = true">
    <v-icon>mdi-printer</v-icon>
    <v-tooltip activator="parent" location="bottom">چاپ فاکتور</v-tooltip>
  </v-btn>

  <v-dialog
    v-model="dialog"
    max-width="500"
    persistent
    @click:outside="preventClose"
  >
    <v-card>
      <v-toolbar color="grey-lighten-4" flat>
        <v-toolbar-title>
          <v-icon color="primary" left>mdi-printer</v-icon>
          چاپ فاکتور
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn
          icon
          color="green"
          :loading="loading"
          @click="printInvoice"
          class="mx-1"
        >
          <v-icon>mdi-printer</v-icon>
          <v-tooltip activator="parent" location="bottom">چاپ فاکتور</v-tooltip>
        </v-btn>
        <v-btn icon @click="dialog = false" :disabled="loading">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text class="pt-4">
        <p class="mb-2">برای تغییر تنظیمات پیش‌فرض به بخش تنظیمات چاپ مراجعه نمایید</p>
        <v-select
          v-model="printOptions.paper"
          :items="[
            { title: 'A4 افقی', value: 'A4-L' },
            { title: 'A4 عمودی', value: 'A4' },
            { title: 'A5 افقی', value: 'A5-L' },
            { title: 'A5 عمودی', value: 'A5' },
          ]"
          label="سایز کاغذ و حالت چاپ"
          variant="outlined"
        ></v-select>
        <v-switch
          v-model="printOptions.bidInfo"
          label="اطلاعات کسب‌وکار"
          class="my-1"
          hide-details
        ></v-switch>
        <v-switch
          v-model="printOptions.pays"
          label="نمایش پرداخت‌های فاکتور"
          class="my-1"
          hide-details
        ></v-switch>
        <v-switch
          v-model="printOptions.note"
          label="یادداشت پایین فاکتور"
          class="my-1"
          hide-details
        ></v-switch>
        <v-switch
          v-model="printOptions.taxInfo"
          label="مالیات به تفکیک اقلام"
          class="my-1"
          hide-details
        ></v-switch>
        <v-switch
          v-model="printOptions.discountInfo"
          label="تخفیف به تفکیک اقلام"
          class="my-1"
          hide-details
        ></v-switch>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>
/* استایل‌های اضافی در صورت نیاز */
</style>