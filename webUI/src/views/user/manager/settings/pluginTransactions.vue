<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "pluginTransactions",
  data: () => {
    return {
      loading: false,
      search: '',
      transactions: [],
      headers: [
        { title: 'نام افزونه', key: 'des', align: 'right' },
        { title: 'نام کسب و کار', key: 'businessName', align: 'right' },
        { title: 'نام خریدار', key: 'submitterName', align: 'right' },
        { title: 'قیمت', key: 'price', align: 'right' },
        { title: 'تاریخ خرید', key: 'dateSubmit', align: 'right' },
        { title: 'تاریخ انقضا', key: 'dateExpire', align: 'right' },
        { title: 'وضعیت', key: 'status', align: 'right' },
        { title: 'شماره کارت', key: 'cardPan', align: 'right' },
        { title: 'کد پیگیری', key: 'refID', align: 'right' },
      ],
    }
  },
  methods: {
    async loadTransactions() {
      this.loading = true;
      try {
        const response = await axios.post('/api/admin/plugins/transactions');
        this.transactions = response.data;
      } catch (error) {
        Swal.fire({
          title: 'خطا',
          text: 'در دریافت اطلاعات تراکنش‌ها مشکلی پیش آمده است',
          icon: 'error',
          confirmButtonText: 'باشه'
        });
      }
      this.loading = false;
    },
    getStatusText(status) {
      switch (parseInt(status)) {
        case 0:
          return 'در انتظار پرداخت';
        case 100:
          return 'پرداخت موفق';
        case 101:
          return 'پرداخت ناموفق';
        case 102:
          return 'لغو شده';
        default:
          return 'نامشخص';
      }
    },
    getStatusColor(status) {
      switch (parseInt(status)) {
        case 0:
          return 'warning';
        case 100:
          return 'success';
        case 101:
          return 'error';
        case 102:
          return 'grey';
        default:
          return 'error';
      }
    },
    getStatusIcon(status) {
      switch (parseInt(status)) {
        case 0:
          return 'mdi-clock-outline';
        case 100:
          return 'mdi-check-circle';
        case 101:
          return 'mdi-close-circle';
        case 102:
          return 'mdi-cancel';
        default:
          return 'mdi-help-circle';
      }
    }
  },
  mounted() {
    this.loadTransactions();
  }
}
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('dialog.plugins')">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-4">
    <v-card>
      <v-card-text>
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="جستجو"
          single-line
          hide-details
          class="mb-4"
          density="compact"
        ></v-text-field>
      </v-card-text>
      <v-data-table
        :headers="headers"
        :items="transactions"
        :loading="loading"
        :search="search"
        class="elevation-1"
      >
        <template v-slot:item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            :prepend-icon="getStatusIcon(item.status)"
          >
            {{ getStatusText(item.status) }}
          </v-chip>
        </template>
        <template v-slot:item.price="{ item }">
          {{ item.price.toLocaleString() }} ریال
        </template>
        <template v-slot:no-data>
          <v-alert
            type="info"
            text="هیچ تراکنشی یافت نشد"
            class="ma-4"
          ></v-alert>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<style scoped>
.v-data-table {
  direction: rtl;
}
</style>