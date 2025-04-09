<template>
  <div class="d-flex flex-column">
    <v-toolbar title="سفارش فضای ذخیره سازی" color="toolbar">
      <template v-slot:prepend>
        <v-btn icon @click="$router.back()">
          <v-icon>mdi-arrow-right</v-icon>
        </v-btn>
      </template>
      <v-spacer></v-spacer>
      <v-btn
        :loading="loading"
        :disabled="loading"
        @click="submit()"
        color="success"
        prepend-icon="mdi-credit-card-check"
        variant="elevated"
        class="px-4"
      >
        <template v-slot:prepend>
          <v-icon size="20" class="ml-2"></v-icon>
        </template>
        پرداخت آنلاین
        <v-tooltip activator="parent" location="bottom">
          پرداخت آنلاین از طریق درگاه بانکی
        </v-tooltip>
      </v-btn>
    </v-toolbar>

    <v-container>
      <v-alert
        type="info"
        class="mb-4"
      >
        برای سفارش فضای ذخیره سازی ابتدا حجم مورد نظر را انتخاب و روی دکمه پرداخت کلیک کنید.
      </v-alert>

      <v-row>
        <v-col cols="12">
          <v-label>فضای مورد نیاز بر حسب گیگابایت:</v-label>
          <v-slider
            v-model="space"
            :disabled="loading"
            min="1"
            max="5"
            step="1"
            thumb-label
            class="mt-2"
          ></v-slider>
        </v-col>

        <v-col cols="12" sm="4">
          <v-text-field
            v-model="space"
            label="فضا (گیگابایت)"
            readonly
            variant="outlined"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="4">
          <v-select
            v-model="month"
            :disabled="loading"
            :items="[
              { title: 'یک ماه', value: 1 },
              { title: 'سه ماه', value: 3 },
              { title: 'شش ماه', value: 6 },
              { title: 'یک سال', value: 12 }
            ]"
            label="زمان"
            variant="outlined"
          ></v-select>
        </v-col>

        <v-col cols="12" sm="4">
          <v-text-field
            v-model="priceTotal"
            label="مبلغ نهایی (ریال)"
            readonly
            variant="outlined"
            color="error"
          ></v-text-field>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "order_new",
  watch: {
    space() {
      this.calc();
    },
    month() {
      this.calc();
    },
  },
  data: () => {
    return {
      searchValue: '',
      loading: ref(true),
      space: 1,
      priceBase: 0,
      month: 1,
      priceTotal: 0
    }
  },
  methods: {
    calc() {
      this.priceTotal = this.$filters.formatNumber(parseInt(this.space) * parseInt(this.priceBase) * parseInt(this.month)) + 'ریال';
    },
    loadData(cat) {
      axios.post('/api/archive/order/settings')
        .then((response) => {
          this.priceBase = response.data.priceBase;
          this.loading = false;
          this.calc();
        });
    },
    submit() {
      this.loading = true;
      axios.post('/api/archive/order/submit', {
        space: this.space,
        month: this.month
      }).then((response) => {
        if (response.data.Success == true) {
          window.location.href = response.data.targetURL;
        }
      })
    }
  },
  mounted() {
    this.loadData();
  }
}
</script>

<style scoped></style>