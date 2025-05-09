<script lang="ts">
import {defineComponent} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "list",
  data:()=>{return {
    business:{},
    customer:{},
    order:{}
  }},
  methods:{
    loadData(){
      axios.post('/api/plugin/noghre/order/info/' + this.$route.params.id).then((response) => {
        this.order = response.data;
        this.business = response.data.bid;
        this.customer = response.data.customer;
      });
    }
  },
  created() {
    this.loadData()
  }
})
</script>

<template>
  <div class="row">
    <div class="col-sm-12 col-md-12 m-0 p-0">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">
            <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
              <i class="fa fw-bold fa-arrow-right"></i>
            </button>
            شماره سفارش: {{this.order.doc.code}}
          </h3>
          <div class="block-options">
            <!-- Print Page functionality is initialized dmPrint() -->
            <button class="btn-block-option" onclick="Dashmix.helpers('dm-print');" type="button">
              <i class="si si-printer me-1"></i> چاپ فاکتور </button>
          </div>
        </div>
        <div class="block-content">
          <div class="container">
            <!-- Invoice Info -->
            <div class="row">
              <!-- Company Info -->
              <div class="col-6">
                <p class="h3">{{business.type}} {{business.name}}</p>
                <address>
                  آدرس:
                  {{business.ostan}} {{business.shahrestan}} {{business.address}} تلفن:{{business.tel}}
                </address>
                <address>
                  کد پستی:{{business.postalcode}}
                </address>
              </div>
              <!-- END Company Info -->

              <!-- Client Info -->
              <div class="col-6 text-end">
                <p class="h3">مشتری: {{customer.nikename}}</p>
                <address>
                  آدرس: {{this.customer.address}}
                  تلفن: {{this.customer.tel}}
                  موبایل: {{this.customer.mobile}}
                </address>
              </div>
              <!-- END Client Info -->
            </div>
            <!-- END Invoice Info -->
            <div class="row">
              <div class="col-sm-6 col-md-3">
                تاریخ سفارش:
                <span class="text-primary-darker">{{this.order.doc.date}}</span>
              </div>
              <div class="col-sm-6 col-md-3">
                تاریخ تحویل:
                <span class="text-primary-darker">{{this.order.deliveryDate}}</span>
              </div>
              <div class="col-sm-6 col-md-3">
                مدل سفارش:
                <span class="text-primary-darker">{{this.order.ringModel}}</span>
              </div>
              <div class="col-sm-6 col-md-3">
                سایز:
                <span class="text-primary-darker">{{this.order.ringSize}}</span>
              </div>
            </div>
            <!-- Table -->
            <div class="table-responsive push">
              <table class="table table-bordered">
                <thead class="bg-body">
                <tr>
                  <th class="text-center" style="width: 60px;">ردیف</th>
                  <th>محصول</th>
                  <th class="text-end" style="width: 120px;">مبلغ({{ $filters.getActiveMoney().shortName }})</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td class="text-center">1</td>
                  <td>
                    <p class="fw-semibold mb-1">
                      هزینه
                      {{this.order.noghreAmount}}
                      گرم نقره با فی فروش
                      {{$filters.formatNumber(this.order.noghreFee)}}
                    </p>
                  </td>
                  <td class="text-center">
                    {{$filters.formatNumber(this.order.noghreFee * this.order.noghreAmount)}}
                  </td>
                </tr>
                <tr>
                  <td class="text-center">2</td>
                  <td>
                    <p class="fw-semibold mb-1">
                       هزینه نگین
                      {{this.order.negin}}
                    </p>
                  </td>
                  <td class="text-center">
                    {{$filters.formatNumber(this.order.neginFee )}}
                  </td>
                </tr>
                <tr>
                  <td class="text-center">3</td>
                  <td>
                    <p class="fw-semibold mb-1">
                      هزینه تراش (تراشکار:
                      {{this.order.tarash.nikename}}
                      )
                    </p>
                  </td>
                  <td class="text-center">
                    {{$filters.formatNumber(this.order.tarashRow.bs )}}
                  </td>
                </tr>
                <tr>
                  <td class="text-center">4</td>
                  <td>
                    <p class="fw-semibold mb-1">
                      هزینه حک (حکاک:
                      {{this.order.hakak.nikename}}
                      )
                    </p>
                  </td>
                  <td class="text-center">
                    {{$filters.formatNumber(this.order.hakakPrice.bs )}}
                  </td>
                </tr>
                <tr>
                  <td class="text-center">5</td>
                  <td>
                    <p class="fw-semibold mb-1">
                      هزینه قلم زنی (قلم زن:
                      {{this.order.hakak.nikename}}
                      )
                    </p>
                  </td>
                  <td class="text-center">
                    {{$filters.formatNumber(this.order.ghalamRow.bs )}}
                  </td>
                </tr>
                <tr>
                  <td class="text-center">6</td>
                  <td>
                    <p class="fw-semibold mb-1">
                      هزینه مرصع کاری ( مرصع کار:
                      {{this.order.hakak.nikename}}
                      )
                    </p>
                  </td>
                  <td class="text-center">
                    {{$filters.formatNumber(this.order.morsaRow.bs )}}
                  </td>
                </tr>
                <tr>
                  <td class="text-center">7</td>
                  <td>
                    <p class="fw-semibold mb-1">
                      سایز هزینه‌ها
                    </p>
                  </td>
                  <td class="text-center">
                    {{$filters.formatNumber(this.order.etcPriceRow.bs )}}
                  </td>
                </tr>
                <tr>
                  <td class="fw-bold text-uppercase text-end bg-body-light" colspan="1">مبلغ کل :</td>
                  <td class="fw-bold text-end bg-body-light" colspan="2">{{ $filters.formatNumber(this.order.doc.amount) }}  {{ $filters.getActiveMoney().shortName }}</td>
                </tr>
                </tbody>
              </table>
            </div>
            <!-- END Table -->
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>