<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-shopping-bag px-2"></i>
        سفارش فضای ذخیره سازی
      </h3>
      <div class="block-options">
        <button :disabled="this.loading" @click="this.submit()" type="button" class="btn btn-sm btn-primary">
          <div v-show="this.loading" class="spinner-grow spinner-grow-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <i class="fa fa-bank"></i>
          پرداخت آنلاین
        </button>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 p-1">
          <div class="alert">
            برای سفارش فضای ذخیره سازی ابتدا حجم مورد نظر را انتخاب و روی دکمه پرداخت کلیک کنید.
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <label for="customRange3" class="form-label">فضای مورد نیاز بر حسب گیگابایت:</label>
            <input :disabled="this.loading" v-model="this.space" type="range" class="form-range" min="1" max="5"
              step="1" id="customRange3">
          </div>
          <div class="col">
            <label>فضا(گیگابایت)</label>
            <input type="text" class="form-control" v-model="this.space" readonly="readonly" />
          </div>
          <div class="col">
            <label>زمان</label>
            <select :disabled="this.loading" v-model="this.month" class="form-select" aria-label="ماه">
              <option value="1" selected>یک ماه</option>
              <option value="3">سه ماه</option>
              <option value="6">شش ماه</option>
              <option value="12">یک سال</option>
            </select>
          </div>
          <div class="col">
            <label>مبلغ نهایی (ریال)</label>
            <input type="text" class="form-control text-danger" v-model="this.priceTotal" readonly="readonly" />
          </div>
        </div>
      </div>
    </div>
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