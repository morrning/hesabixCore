<script lang="ts">
import {defineComponent} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "view",
  data: ()=>{return{
    walletInfo:{
      deposit:0,
      transactions:0,
      turnover:0
    },
    logSearchValue: '',
    logItems:[],
    logHeaders: [
      { text: "تاریخ", value: "date" },
      { text: "توضیحات", value: "des"},
      { text: "بخش", value: "part"},
      { text: "آی پی آدرس", value: "ipaddress"},
    ],
    walletSearchValue: '',
    walletItems:[],
    walletHeaders: [
      { text: "تاریخ", value: "dateSubmit" },
      { text: "وضعیت", value: "status"},
      { text: "مبلغ", value: "amount"},
      { text: "توضیحات", value: "des"},
      { text: "شماره کارت", value: "cardPan"},
      { text: "شماره پیگیری", value: "refID"},
      { text: "درگاه پرداخت", value: "gatePay"},
    ],
    walletPaysSearchValue: '',
    walletPaysItems:[],
    walletPaysHeaders: [
      { text: "تاریخ", value: "dateSubmit" },
      { text: "مبلغ", value: "amount"},
      { text: "توضیحات", value: "des"},
      { text: "شماره شبا", value: "shaba"},
      { text: "شماره پیگیری", value: "refID"},
      { text: "بانک مبدا", value: "bank"},
    ]
  }},
  methods:{
    loadData(){
      this.loading = true;
      axios.post('/api/wallet/info').then((response)=>{
        this.walletInfo = response.data
      });
      axios.post('/api/business/logs/' + localStorage.getItem('activeBid'),{type:'wallet'})
          .then((response)=>{
            this.logItems = response.data;
            this.loading=false;
          });
      axios.post('/api/wallet/transactions/income')
          .then((response)=>{
            this.walletItems = response.data;
            this.loading=false;
          })
      axios.post('/api/wallet/transactions/pay')
          .then((response)=>{
            this.walletPaysItems = response.data;
            this.loading=false;
          })
    }
  },
  mounted() {
    this.loadData();
  }
})
</script>

<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-wallet px-2"></i>
        کیف پول </h3>
      <div class="block-options">

      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12">
          <div class="block block-rounded mb-0">
            <div class="block-content text-center">
              <div class="py-2">
                <div class="mb-3">
                  <img alt="" class="img-avatar img-avatar96" src="/img/icons/wallet.png">
                </div>
                <h1 class="fs-lg mb-0">کیف پول </h1>
                <p class="text-muted">
                  <i class="fa fa-award text-warning me-1"></i> سریعترین راه دریافت آنلاین مبلغ  فاکتور از مشتری </p>
              </div>
            </div>
            <div class="block-content bg-body-light text-center">
              <div class="row items-push text-uppercase">
                <div class="col-6 col-md-4">
                  <div class="fw-semibold text-dark mb-1">تراکنش‌های موفق</div>
                  <a class="link-fx fs-3" href="javascript:void(0)">{{ $filters.formatNumber(this.walletInfo.transactions)}}</a>
                </div>
                <div class="col-6 col-md-4">
                  <div class="fw-semibold text-dark mb-1">موجودی</div>
                  <a class="link-fx fs-3" href="javascript:void(0)">{{ $filters.formatNumber(this.walletInfo.deposit)}}  ریال</a>
                </div>
                <div class="col-6 col-md-4">
                  <div class="fw-semibold text-dark mb-1">گردش مالی</div>
                  <a class="link-fx fs-3" href="javascript:void(0)">{{ $filters.formatNumber(this.walletInfo.turnover)}}  ریال</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <ul class="nav nav-pills flex-column flex-sm-row border border-secondary" id="myTab" role="tablist">
            <button class="flex-sm-fill text-sm-center nav-link rounded-0" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">
              <i class="fa fa-cogs me-2"></i>
              سوابق تسویه پایا/ساتنا
            </button>
            <button class="flex-sm-fill text-sm-center nav-link rounded-0" id="pays-tab" data-bs-toggle="tab" data-bs-target="#pays" type="button" role="tab" aria-controls="pays" aria-selected="false">
              <i class="fa fa-list-dots me-2"></i>
              سوابق تراکنش‌ها
            </button>
            <button class="flex-sm-fill text-sm-center nav-link rounded-0" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
              <i class="fa fa-history me-2"></i>
              تاریخچه
            </button>
          </ul>
          <div class="tab-content px-3" id="myTabContent">
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
              <div class="my-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="fa fa-search"></i></span>
                  <input v-model="walletPaysSearchValue" class="form-control" type="text" placeholder="جست و جو ...">
                </div>
              </div>
              <EasyDataTable table-class-name="customize-table"
                  :headers="walletPaysHeaders"
                  :items="walletPaysItems"
                  alternating
                  :search-value="walletPaysSearchValue"
                  theme-color="#1d90ff"
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
                <template #item-amount="{ amount }">
                  <span class="">{{ $filters.formatNumber(amount)}}</span>
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
            <div class="tab-pane fade" id="pays" role="tabpanel" aria-labelledby="pays-tab">
              <div class="my-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="fa fa-search"></i></span>
                  <input v-model="walletSearchValue" class="form-control" type="text" placeholder="جست و جو ...">
                </div>
              </div>
              <EasyDataTable table-class-name="customize-table"
                  :headers="walletHeaders"
                  :items="walletItems"
                  alternating
                  :search-value="walletSearchValue"
                  theme-color="#1d90ff"
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
                <template #item-amount="{ amount }">
                  <span class="">{{ $filters.formatNumber(amount)}}</span>
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
            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
              <div class="my-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="fa fa-search"></i></span>
                  <input v-model="logSearchValue" class="form-control" type="text" placeholder="جست و جو ...">
                </div>
              </div>
              <EasyDataTable table-class-name="customize-table"
                  :headers="logHeaders"
                  :items="logItems"
                  alternating
                  :search-value="logSearchValue"
                  theme-color="#1d90ff"
                  header-text-direction="center"
                  body-text-direction="center"
                  rowsPerPageMessage="تعداد سطر"
                  emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
                  rowsOfPageSeparatorMessage="از"
                  :loading="loading"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>