<script lang="ts">
import {defineComponent} from 'vue'
import Loading from "vue-loading-overlay";
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "mod",
  components:{
    Loading
  },
  data: ()=>{return{
    isLoading: false,
    currencyConfig:{
      masked: false,
      prefix: '',
      suffix: 'ریال',
      thousands: ',',
      decimal: '.',
      precision: 0,
      disableNegative: false,
      disabled: false,
      min: 0,
      max: null,
      allowBlank: false,
      minimumNumberOfCharacters: 0,
      shouldRound: true,
      focusOnRight: true,
    },
    totalNoghre:0,
    persons:[],
    ghalams:[],
    hakaks:[],
    morsas:[],
    tarashs:[],
    year:'',
    order:{
      customer:null,
      date:'',
      dateDeliver:'',
      status:'در حال بررسی',
      place:'کارگاه مرکزی',
      des:'',
      sizeOfRing:'',
      modelOforder:'',
      tarash:null,
      ghalam:null,
      hakak:null,
      morsa:null,
      tarashPrice:0,
      morsaPrice:0,
      hakakPrice:0,
      ghalamPrice:0,
      negin:'',
      neginPrice:0,
      etcPrice:0,
      noghreAmount:0,
      feeNoghre:0,
      modelPrice:0
    }
  }},
  created() {
    this.loadData(this.$route.params.id);
  },
  methods:{
    loadData(){
      //load persons
      this.isLoading = true;
      axios.post('/api/person/list/limit')
          .then((response)=>{
            this.persons = response.data;
            this.isLoading=false;
          });
      axios.post('/api/plugin/noghre/ghalam/list')
          .then((response)=>{
            this.ghalams = response.data;
            this.isLoading=false;
          });
      axios.post('/api/plugin/noghre/morsa/list')
          .then((response)=>{
            this.morsas = response.data;
            this.isLoading=false;
          });
      axios.post('/api/plugin/noghre/hakak/list')
          .then((response)=>{
            this.hakaks = response.data;
            this.isLoading=false;
          });
      axios.post('/api/plugin/noghre/tarash/list')
          .then((response)=>{
            this.tarashs = response.data;
            this.isLoading=false;
          });
      this.isLoading = false
      //load year
      axios.post('/api/year/get').then((response)=>{
        this.year = response.data;
        this.order.date = response.data.now;
      })
    },
    save(){
      if(this.order.customer == null ||
          this.order.hakak == null ||
          this.order.tarash == null ||
          this.order.morsa == null ||
          this.order.ghalam == null ||
          this.order.dateDeliver == null ||
          this.order.date == null
      ){
        Swal.fire({
          text: 'لطفا تمام موارد ستاره دار را تکمیل نمایید.',
          icon: 'warning',
          confirmButtonText: 'قبول'
        })
      }
      else{
        //going to save data
        axios.post('/api/plugin/noghre/submit/order/' + this.$route.params.id,{
          customer: this.order.customer,
          dateDeliver: this.order.dateDeliver,
          dateSubmit: this.order.date,
          status: this.order.status,
          place: this.order.place,
          model: this.order.modelOforder,
          size: this.order.sizeOfRing,
          negin: this.order.negin,
          neginFee: this.order.neginPrice,
          tarash: this.order.tarash,
          hakak: this.order.hakak,
          morsa: this.order.morsa,
          ghalam: this.order.ghalam,
          tarashPrice: this.order.tarashPrice,
          hakakPrice: this.order.hakakPrice,
          morsaPrice: this.order.morsaPrice,
          ghalamPrice: this.order.ghalamPrice,
          etcPrice: this.order.etcPrice,
          modelPrice: this.order.modelPrice,
          des: this.order.des,
          noghreAmount: this.order.noghreAmount,
          noghreFee: this.order.noghreFee,
          id: this.$route.params.id
        }).then((response)=>{
          Swal.fire({
            text: 'سفارش ثبت شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then(() => {
            this.$router.push('/acc/plugin/noghre/order/list')
          });
        })
      }
    },
    calc(){
      this.totalNoghre = this.order.noghreAmount * this.order.noghreFee
    }
  }
})
</script>

<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light" >
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-basket-shopping"></i>
        سفارش
      </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content pb-3">
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-12 col-md-4">
            <label class="form-label required">مشتری</label>
            <div class="mb-2">
              <v-cob
                  dir="rtl"
                  :options="this.persons"
                  label="nikename"
                  v-model="this.order.customer"
              >
                <template #no-options="{ search, searching, loading }">
                  نتیجه‌ای یافت نشد!
                </template>
              </v-cob>
            </div>
          </div>
          <div class="col-sm-12 col-md-4 mb-2">
            <label class=" required">تاریخ سفارش</label>
            <date-picker
                class=""
                v-model="order.date"
                format="jYYYY/jMM/jDD"
                display-format="jYYYY/jMM/jDD"
                :min="year.start"
                :max="year.end"
                
            />
          </div>
          <div class="col-sm-12 col-md-4 mb-2">
            <label class=" required">تاریخ تحویل</label>
            <date-picker
                class=""
                v-model="order.dateDeliver"
                format="jYYYY/jMM/jDD"
                display-format="jYYYY/jMM/jDD"
                :min="year.start"
                :max="year.end"
                
            />
          </div>
          <div class="col-sm-12 col-md-3 mb-2">
            <label>وضعیت سفارش</label>
            <select v-model="order.status" class="form-select">
              <option value="در حال بررسی" selected>در حال بررسی</option>
              <option value="در دست حکاک">در دست حکاک</option>
              <option value="در دست قلم زن">در دست قلم زن</option>
              <option value="در دست تراشکار">در دست تراشکار</option>
              <option value="در دست مرصع کار">در دست مرصع کار</option>
              <option value="تحویل داده شده">تحویل داده شده</option>
            </select>
          </div>
          <div class="col-sm-12 col-md-3 mb-2">
            <label class=" required">محل ساخت</label>
            <input type="text" v-model="order.place" class="form-control">
          </div>
          <div class="col-sm-12 col-md-3 mb-2">
            <label class=" required">مدل سفارش</label>
            <input type="text" v-model="order.modelOforder" class="form-control">
          </div>
          <div class="col-sm-12 col-md-3 mb-2">
            <label class=" required">سایز</label>
            <input type="text" v-model="order.sizeOfRing" class="form-control">
          </div>
          <div class="col-sm-12 col-md-12 mb-2 border rounded-2">
            <div class="row my-2">
              <div class="col-sm-12 col-md-4">
                <label>نقره مصرفی</label>
                <div class="">
                  <input @change="calc()" v-model="order.noghreAmount" class="form-control" type="number" min="0.1">
                </div>
              </div>
              <div class="col-sm-12 col-md-4">
                <label>فی هر گرم نقره ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 @change="calc()" class="form-control" v-model="order.noghreFee" v-bind="currencyConfig"></money3>
                </div>
              </div>
              <div class="col-sm-12 col-md-4">
                <label>مبلغ کل نقره مصرفی ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 readonly="readonly" class="form-control" v-model="totalNoghre" v-bind="currencyConfig"></money3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 mb-2 border rounded-2">
            <div class="row my-2">
              <div class="col-sm-12 col-md-6">
                <label class=" required">نوع نگین</label>
                <div class="">
                  <input v-model="order.noghre" class="form-control" type="text" min="0.1">
                </div>
              </div>
              <div class="col-sm-12 col-md-6">
                <label class="required">هزینه نگین ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 class="form-control" v-model="order.neginPrice" v-bind="currencyConfig"></money3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 mb-2 border rounded-2">
            <div class="row my-2">
              <div class="col-sm-12 col-md-6">
                <label class=" required">تراشکار</label>
                <div class="mb-2">
                  <v-cob
                      dir="rtl"
                      :options="this.tarashs"
                      label="nikename"
                      v-model="this.order.tarash"
                  >
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                  </v-cob>
                </div>
              </div>
              <div class="col-sm-12 col-md-6">
                <label class=" required">هزینه تراشکار ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 class="form-control" v-model="order.tarashPrice" v-bind="currencyConfig"></money3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 mb-2 border rounded-2">
            <div class="row my-2">
              <div class="col-sm-12 col-md-6">
                <label class=" required">حکاک</label>
                <div class="mb-2">
                  <v-cob
                      dir="rtl"
                      :options="this.hakaks"
                      label="nikename"
                      v-model="this.order.hakak"
                  >
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                  </v-cob>
                </div>
              </div>
              <div class="col-sm-12 col-md-6">
                <label class=" required">هزینه حکاکی ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 class="form-control" v-model="order.hakakPrice" v-bind="currencyConfig"></money3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 mb-2 border rounded-2">
            <div class="row my-2">
              <div class="col-sm-12 col-md-6">
                <label class=" required">قلم زن</label>
                <div class="mb-2">
                  <v-cob
                      dir="rtl"
                      :options="this.ghalams"
                      label="nikename"
                      v-model="this.order.ghalam"
                  >
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                  </v-cob>
                </div>
              </div>
              <div class="col-sm-12 col-md-6">
                <label class=" required">هزینه قلم زن ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 class="form-control" v-model="order.ghalamPrice" v-bind="currencyConfig"></money3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 mb-2 border rounded-2">
            <div class="row my-2">
              <div class="col-sm-12 col-md-6">
                <label class=" required">مرصع کار</label>
                <div class="mb-2">
                  <v-cob
                      dir="rtl"
                      :options="this.morsas"
                      label="nikename"
                      v-model="this.order.morsa"
                  >
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                  </v-cob>
                </div>
              </div>
              <div class="col-sm-12 col-md-6">
                <label class=" required">هزینه مرصع کار ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 class="form-control" v-model="order.morsaPrice" v-bind="currencyConfig"></money3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 mb-2 border rounded-2">
            <div class="row my-2">
              <div class="col-sm-12 col-md-6">
                <label class="required">هزینه اجرای مدل ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 class="form-control" v-model="order.modelPrice" v-bind="currencyConfig"></money3>
                </div>
              </div>
              <div class="col-sm-12 col-md-6">
                <label>سایر هزینه‌ها ({{ $filters.getActiveMoney().shortName }})</label>
                <div class="">
                  <money3 class="form-control" v-model="order.etcPrice" v-bind="currencyConfig"></money3>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-12 col-md-12 mb-2">
            <label>توضیحات سفارش</label>
            <textarea v-model="order.des" class="form-select"></textarea>
          </div>
        </div>
        <button @click="save()" type="button" class="btn btn-alt-primary">ثبت</button>
      </div>
    </div>
  </div>

</template>

<style scoped>
.required:before {
  content:"*";
  color:red;
}
</style>