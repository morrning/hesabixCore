<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-list-dots px-2"></i>
        لیست سفارشات فضای ابری
      </h3>
      <div class="block-options">

      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 p-1">
          <div class="my-2">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
            </div>
          </div>
          <EasyDataTable
              :headers="headers"
              :items="items"
              alternating
              :search-value="searchValue"
              theme-color="#1d90ff"
              table-class-name="customize-table"
              header-text-direction="center"
              body-text-direction="center"
              rowsPerPageMessage="تعداد سطر"
              emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از"
              :loading="loading"
          >
            <template #item-status="{ status }">
              <span v-if="status == 100" class="text-success"><i class="fa fa-check me-2"></i>موفق</span>
              <span v-else class="text-danger"><i class="fa fa-info me-2"></i>پرداخت نشده</span>
            </template>
            <template #item-price="{ price }">
              <span class="">{{ $filters.formatNumber(price)}}</span>
            </template>
            <template #item-cardPan="{ cardPan }">
              <span style="direction:ltr" class="">{{cardPan}}</span>
            </template>
            <template #item-gatePay="{ gatePay }">
                  <span class="text-warning" v-if="gatePay == 'zarinpal'">
                    <img src="/img/icons/zarinpal.png" class="img-avatar img-avatar16" />
                    زرین پال
                  </span>
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import {ref} from "vue";

export default {
  name: "orders_list",
  data: ()=>{return {
    searchValue: '',
    loading: ref(true),
    items:[],
    headers: [
      { text: "تاریخ", value: "dateSubmit" },
      { text: "وضعیت", value: "status"},
      { text: "مبلغ (ریال)", value: "price"},
      { text: "توضیحات", value: "des"},
      { text: "شماره کارت", value: "cardPan"},
      { text: "شماره پیگیری", value: "refID"},
      { text: "درگاه پرداخت", value: "gatePay"},
    ],
  }},
  methods: {
    loadData(){
      this.loading = true;
      axios.post('/api/archive/orders/list')
          .then((response)=>{
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

</style>