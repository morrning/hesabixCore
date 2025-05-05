<template>
  <v-toolbar color="grey-lighten-3" title="لیست سفارشات فضای ابری" density="compact" class="px-2">
    <template v-slot:prepend>
      <v-btn icon @click="$router.back()">
        <v-icon>mdi-arrow-right</v-icon>
      </v-btn>
    </template>
  </v-toolbar>
  <v-row>
    <v-col cols="12">
      <v-text-field v-model="searchValue" prepend-inner-icon="mdi-magnify" label="جست و جو ..." variant="outlined"
        density="compact" hide-details :rounded="false" class=""></v-text-field>

      <v-data-table :headers="headers" :items="items" :search="searchValue" :loading="loading"
        loading-text="در حال بارگذاری..." no-data-text="اطلاعاتی برای نمایش وجود ندارد" items-per-page-text="تعداد سطر"
        :items-per-page-options="[10, 25, 50, 100]" class="elevation-1" :header-props="{ class: 'custom-header' }">
        <template v-slot:item.status="{ item }">
          <v-chip :color="item.status === 100 ? 'success' : 'error'" size="small">
            <v-icon start>
              {{ item.status === 100 ? 'mdi-check' : 'mdi-information' }}
            </v-icon>
            {{ item.status === 100 ? 'موفق' : 'پرداخت نشده' }}
          </v-chip>
        </template>

        <template v-slot:item.price="{ item }">
          {{ $filters.formatNumber(item.price) }}
        </template>

        <template v-slot:item.cardPan="{ item }">
          <span style="direction: ltr">{{ item.cardPan }}</span>
        </template>

        <template v-slot:item.gatePay="{ item }">
          <v-chip v-if="item.gatePay === 'zarinpal'" color="warning" size="small">
            <v-avatar start>
              <v-img src="/img/icons/zarinpal.png" width="16" height="16"></v-img>
            </v-avatar>
            زرین پال
          </v-chip>
          <v-chip v-else color="error" size="small">
            سایر
          </v-chip>
        </template>
      </v-data-table>
    </v-col>
  </v-row>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "orders_list",
  data: () => ({
    searchValue: '',
    loading: ref(true),
    items: [],
    headers: [
      { title: "تاریخ", key: "dateSubmit", align: "center" },
      { title: "وضعیت", key: "status", align: "center" },
      { title: "مبلغ (ریال)", key: "price", align: "center" },
      { title: "توضیحات", key: "des", align: "center" },
      { title: "شماره کارت", key: "cardPan", align: "center" },
      { title: "شماره پیگیری", key: "refID", align: "center" },
      { title: "درگاه پرداخت", key: "gatePay", align: "center" },
    ],
  }),
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/archive/orders/list')
        .then((response) => {
          this.items = response.data;
          this.loading = false;
        });
    }
  },
  mounted() {
    this.loadData();
  }
}
</script>

<style scoped>
.v-toolbar-title {
  font-size: 1.1rem;
}
</style>