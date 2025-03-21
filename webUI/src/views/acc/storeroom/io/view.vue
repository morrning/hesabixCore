<script lang="ts">
import {defineComponent, ref} from 'vue'
import axios from "axios";
import Swal from "sweetalert2";
export default defineComponent({
  name: "viewInvoice",
  components:{

  },
  watch:{

  },
  data:()=>{return{
    loading:ref(false),
    bid:{
      legal_name:'',
    },
    item:{
      ticket:{
        id:0,
        date:null,
        code:null,
        des:'',
        storeroom:{
          manager:''
        }
      },
      rows:[],
      person:{
        nikename: null,
        mobile:'',
        address:'',
        tel:'',
        codeeqtesadi:'',
        keshvar:'',
        ostan:'',
        shahr: ''
      },
    },
    headers: [
      { text: "کالا", value: "commodity" },
      { text: "تعداد", value: "count" },
      { text: "مورد نیاز", value: "hesabdariCount" },
      { text: "باقی‌مانده", value: "remain" },
    ]

  }
  },
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/storeroom/tickets/info/' + this.$route.params.id).then((response) => {
        this.item.ticket = response.data.ticket;
        this.item.person = response.data.person;
        this.item.transferType = response.data.transferType;
        this.item.rows = response.data.commodities;
        this.loading = false;
      });
      axios.post('/api/business/get/info/' + localStorage.getItem('activeBid')).then((response) => {
        this.bid = response.data;
      });
    },
    printInvoice() {
      axios.post('/api/storeroom/print/ticket', {
        'code': this.$route.params.id,
        'type': this.item.ticket.type
      }).then((response) => {
        this.printID = response.data.id;
        window.open(this.$API_URL + '/front/print/' + this.printID, '_blank', 'noreferrer');
      });
    }
  },
  mounted() {
    this.loadData();
  }
})
</script>

<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fas fa-file-invoice-dollar"></i>
        مشاهده و چاپ حواله انبار
      </h3>
      <div class="block-options">
        <button class="btn btn-sm btn-primary mx-2" @click="printInvoice()" type="button">
          <i class="si si-printer me-1"></i>
          <span class="d-none d-sm-inline-block">چاپ</span>
        </button>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <b class="ps-2">اطلاعات حواله انبار</b>
      <div class="row">
        <div class="col-sm-6 col-md-2">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">شماره</span>
            <input type="text" readonly="readonly" v-model="item.ticket.code" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
        <div class="col-sm-6 col-md-2">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">نوع</span>
            <input type="text" readonly="readonly" v-model="item.ticket.typeString" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
        <div class="col-sm-6 col-md-2">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">تاریخ</span>
            <input type="text" readonly="readonly" v-model="item.ticket.date" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
        <div class="col-sm-12 col-md-6">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">شرح</span>
            <input type="text" readonly="readonly" v-model="item.ticket.des" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
      </div>
      <b class="ps-2">طرف حساب</b>
      <div class="row">
        <div class="col-sm-6 col-md-4">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">نام</span>
            <input type="text" readonly="readonly" v-model="item.person.nikename" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
        <div class="col-sm-6 col-md-4">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">موبایل</span>
            <input type="text" readonly="readonly" v-model="item.person.mobile" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
        <div class="col-sm-6 col-md-4">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">تلفن</span>
            <input type="text" readonly="readonly" v-model="item.person.tel" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
        <div class="col-sm-6 col-md-3">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">کد پستی</span>
            <input type="text" readonly="readonly" v-model="item.person.postalcode" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
        <div class="col-sm-12 col-md-9">
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">آدرس</span>
            <input type="text" readonly="readonly" v-model="item.person.address" class="form-control"
              aria-describedby="inputGroup-sizing-sm">
          </div>
        </div>
      </div>
      <b class="ps-2">اقلام</b>
      <EasyDataTable table-class-name="customize-table" :headers="headers" :items="item.rows" show-index alternating
        theme-color="#1d90ff" header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
        emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
        <template #item-count="{ count, commodity }">
          {{ count }} {{ commodity.unit.name }}
        </template>
        <template #item-commodity="{ commodity }">
          {{ commodity.code }} {{ commodity.name }}
        </template>
        <template #expand="{ des, referal }">
          <div class="p-1 m-0 text-start">
            شرح
            :
            {{ des }}
            <br />
            ارجاع:
            {{ referal }}
          </div>
        </template>
      </EasyDataTable>
    </div>
  </div>

</template>

<style scoped>
table{
  font-size: small;
  border: 1px solid gray;
}
.table-header{
  background-color: lightgray;
}
.c-print{
  background-color: white;
}

@media print
{
  @page {
    margin-top: 0;
    margin-bottom: 0;
  }
  body  {
    padding-top: 72px;
    padding-bottom: 72px ;
  }
}
</style>