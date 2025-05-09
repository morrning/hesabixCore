<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default defineComponent({
  name: 'ViewProduct',
  data: () => ({
    loading: true,
    item: {
      id: '',
      name: '',
      price: 0,
      timelabel: 0,
    },
  }),
  created() {
    axios.post('/api/plugin/get/info/' + this.$route.params.id).then((response) => {
      this.item = response.data;
      this.loading = false;
    });
  },
  methods: {
    insert() {
      axios
        .post('/api/plugin/insert/' + this.item.id)
        .then((response) => {
          if (response.data.Success === true) {
            window.location.href = response.data.targetURL;
          }
        })
        .catch((error) => {
          Swal.fire({
            text: 'متاسفانه مشکلی در پردازش درخواست پیش آمد. لطفا مجددا درخواست خود را تکرار نمایید.',
            icon: 'error',
            confirmButtonText: 'قبول',
          });
        });
    },
  },
  computed: {
    formattedPrice() {
      return new Intl.NumberFormat('en-US').format(this.item.price);
    },
    totalPrice() {
      return new Intl.NumberFormat('en-US').format((this.item.price * 111) / 100);
    },
  },
});
</script>

<template>
  <!-- هدر -->
  <v-toolbar color="toolbar" title="جزئیات خرید افزونه">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
  </v-toolbar>

  <!-- محتوای اصلی -->
  <v-card :loading="loading" elevation="2" class="pa-4 ma-2" color="lighten-5">
    <v-card-title class="text-center">
      <v-icon size="x-large" color="grey">mdi-toy-brick-outline</v-icon>
    </v-card-title>
    <v-card-text class="text-center">
      <!-- نام افزونه -->
      <h3 class="text-h5 text-dark mt-3">{{ item.name }}</h3>

      <!-- مدت اعتبار -->
      <p class="text-body-1 text-muted mb-3">
        مدت اعتبار افزونه:
        <span class="text-primary">{{ item.timelabel }}</span>
      </p>

      <!-- قیمت اصلی -->
      <v-chip color="primary" class="mb-3">
        {{ formattedPrice }} تومان
      </v-chip>

      <!-- مبلغ قابل پرداخت -->
      <p class="text-body-1 font-weight-bold text-muted">
        مبلغ قابل پرداخت (با احتساب مالیات بر ارزش افزوده و کارمزد درگاه واسط):
      </p>
      <h3 class="text-success">{{ totalPrice }} تومان</h3>
    </v-card-text>

    <!-- دکمه پرداخت -->
    <v-card-actions class="justify-center">
      <v-btn variant="tonal" block color="primary" @click="insert">
        پرداخت آنلاین از طریق زرین‌پال
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<style scoped></style>