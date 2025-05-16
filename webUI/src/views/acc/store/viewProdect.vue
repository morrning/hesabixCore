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
      icon: '',
    },
  }),
  created() {
    axios.post('/api/plugin/get/info/' + this.$route.params.id)
      .then((response) => {
        const data = response.data;
        data.id = Number(data.id);
        data.price = Number(data.price);
        this.item = data;
        this.loading = false;
      })
      .catch((error) => {
        this.loading = false;
        Swal.fire({
          text: 'خطا در دریافت اطلاعات افزونه!',
          icon: 'error',
          confirmButtonText: 'باشه',
        });
        console.error(error);
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
  <v-toolbar title="جزئیات خرید افزونه" flat color="toolbar">
    <template v-slot:prepend>
      <v-btn icon @click="$router.back()" class="me-2 d-none d-md-flex" variant="text">
        <v-icon>mdi-arrow-right</v-icon>
      </v-btn>
    </template>
  </v-toolbar>

  <v-container class="d-flex justify-center align-center" style="min-height: 80vh;">
    <v-row justify="center" align="center">
      <v-col cols="12" md="7" lg="5">
        <div v-if="loading" class="d-flex flex-column align-center justify-center py-16">
          <v-progress-circular indeterminate color="primary" size="48" />
          <div class="mt-4 text-body-2 text-muted">در حال بارگذاری اطلاعات...</div>
        </div>
        <v-card v-else elevation="6" class="pa-7 rounded-xl pay-card clean-card">
          <v-img
            :src="item.icon ? '/u/img/plugins/' + item.icon : '/images/plugin-default.png'"
            height="200"
            class="rounded-t-lg plugin-image mb-5"
            cover
            alt="عکس افزونه"
          />
          <v-card-text class="text-center mt-3">
            <h2 class="text-h5 font-weight-bold mb-4 main-title">{{ item.name }}</h2>
            <div class="mb-4">
              <span class="label">مدت اعتبار:</span>
              <span class="value">{{ item.timelabel }}</span>
            </div>
            <v-divider class="my-4" />
            <div class="mb-3">
              <span class="label">قیمت افزونه:</span>
              <span class="value price">{{ formattedPrice }} تومان</span>
            </div>
            <div class="mb-2">
              <span class="label">مبلغ قابل پرداخت (با مالیات و کارمزد):</span>
              <span class="value total">{{ totalPrice }} تومان</span>
            </div>
          </v-card-text>
          <v-card-actions class="justify-center mt-8 mb-2 pay-btn-wrapper">
            <v-btn
              color="success"
              size="x-large"
              class="rounded-3 px-12 pay-btn"
              @click="insert"
              prepend-icon="mdi-credit-card-outline"
              elevation="1"
            >
              پرداخت آنلاین
            </v-btn>
            <v-btn
              variant="outlined"
              color="grey-darken-2"
              size="large"
              class="cancel-btn rounded-3"
              @click="$router.back()"
              prepend-icon="mdi-close-circle-outline"
            >
              انصراف
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.text-h5 {
  font-size: 1.4rem;
}
.pay-card.clean-card {
  background: #fff;
  border: 1px solid #f1f1f1;
  box-shadow: 0 4px 24px 0 rgba(60, 72, 88, 0.08);
  min-height: 420px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.main-title {
  color: #222;
  letter-spacing: -0.5px;
}
.label {
  color: #888;
  font-size: 1rem;
  margin-left: 0.5rem;
}
.value {
  color: #222;
  font-size: 1.08rem;
  font-weight: 500;
}
.price {
  color: #1976d2;
  font-weight: bold;
  margin-right: 0.5rem;
}
.total {
  color: #388e3c;
  font-weight: bold;
  margin-right: 0.5rem;
}
.pay-btn-wrapper {
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  margin-top: 2rem;
  margin-bottom: 0.5rem;
}
.pay-btn {
  background: #43a047 !important;
  color: #fff !important;
  font-size: 1.15rem;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(67, 160, 71, 0.13);
  transition: transform 0.15s, box-shadow 0.15s;
}
.pay-btn:hover {
  transform: scale(1.04);
  box-shadow: 0 6px 24px rgba(67, 160, 71, 0.18);
}
.cancel-btn {
  border: 1px solid #bdbdbd !important;
  color: #757575 !important;
  background: #fff !important;
  margin-right: 1rem;
}
.cancel-btn:hover {
  color: #ff9800 !important;
  border-color: #ff9800 !important;
}
@media (max-width: 600px) {
  .pay-card.clean-card {
    min-height: 340px;
    padding: 1.5rem !important;
  }
  .main-title {
    font-size: 1.1rem;
  }
  .pay-btn-wrapper {
    bottom: 12px;
  }
}
.plugin-image {
  display: block;
  margin-bottom: 1.5rem;
  border-radius: 18px;
  box-shadow: 0 2px 12px rgba(60,72,88,0.10);
  background: #f8f8f8;
  object-fit: cover;
}
</style>