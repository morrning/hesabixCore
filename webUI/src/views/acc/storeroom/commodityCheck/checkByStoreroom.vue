<script lang="ts">
import {defineComponent,ref} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "checkByStoreroom",
  data:()=>{return{
    storerooms:[],
    storeroom:null,
    searchValue: '',
    loading : ref(true),
    items:[],
    orgItems:[],
    headers: [
      { text: "کد", value: "commodity.code" },
      { text: "دسته بندی", value: "commodity.cat.name", sortable: true},
      { text: "نام", value: "commodity.name", sortable: true},
      { text: "واحد", value: "commodity.unit.name", sortable: true},
      { text: "ورودی", value: "input", sortable: true},
      { text: "خروجی", value: "output", sortable: true},
      { text: "موجودی انبار", value: "existCount"},
      { text: "نقطه سفارش", value: "commodity.orderPoint"},
      { text: "وضعیت", value: "operation"},
    ]
  }},
  watch:{
    'searchValue'(newValue, oldValue) {
      this.searchTable();
    }
  },
  methods: {
    loadData(){
      this.loading = true;
      axios.post('/api/storeroom/list')
          .then((response)=>{
            this.storerooms = response.data.data;
            this.storerooms.forEach((element)=>{
              element.name = element.name + ' انباردار : ' + element.manager
            });
            this.loading = false;
          });
    },
    loadStoreItems(){
      this.loading = true;
      axios.post('/api/storeroom/commodity/list/' + this.storeroom.id)
          .then((response)=>{
            this.items = response.data;
            this.orgItems = response.data;
            this.loading = false;
          });
    },
    searchTable() {
      this.loading = true;
      let calcItems = [];
      let isAll = false;
      if(this.searchValue == ''){isAll = true;}
      if (isAll) {
        this.items = this.orgItems;
      }
      else {
        this.orgItems.forEach((item) => {
          let addItem = false;
          if(item.commodity.name.includes(this.searchValue)){
            addItem = true
          }
          else if(item.commodity.cat.name.includes(this.searchValue)){
            addItem = true
          }
          else if(item.commodity.unit.name.includes(this.searchValue)){
            addItem = true
          }
          if(addItem){
            calcItems.push(item);
          }
        });
        this.items = calcItems;
      }
      this.loading = false;
    },
  },
  beforeMount() {
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
        <i class="mx-2 fa fa-boxes-stacked"></i>
        موجودی کالا </h3>
      <div class="block-options">
        <button class="btn btn-sm btn-primary mx-2" onclick="document.getElementById('hide-on-print').classList.add('d-none');Dashmix.helpers('dm-print');" type="button">
          <i class="si si-printer me-1"></i>
          <span class="d-none d-sm-inline-block">چاپ</span>
        </button>
      </div>
    </div>
    <div class="block-content pt-1 pb-3 dm-print">
      <div class="row">
        <div  class="col-sm-12 col-md-12 px-0 mb-2">
          <div class="form-control">
            <label class="form-label">
              انبار :
              <span class="text-muted">برای مشاهده موجودی ابتدا انبار را انتخاب نمایید.</span>
            </label>
            <v-cob
                dir="rtl"
                :options="storerooms"
                label="name"
                v-model="storeroom"
                @option:selected="loadStoreItems()"
            >
              <template #no-options="{ search, searching, loading }">
                نتیجه‌ای یافت نشد!
              </template>
            </v-cob>
          </div>
        </div>
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="mb-1">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
            </div>
          </div>
          <EasyDataTable table-class-name="customize-table"
              multi-sort
              show-index
              alternating
              :headers="headers"
              :items="items"
              theme-color="#1d90ff"
              header-text-direction="center"
              body-text-direction="center"
              rowsPerPageMessage="تعداد سطر"
              emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از"
              :loading="loading"

          >
            <template #item-existCount="{ existCount }">
              <b>{{Math.abs(existCount)}}</b>
              <span v-if="parseInt(existCount) < 0" class="text-danger"> (منفی) </span>
            </template>
            <template #item-operation="{ existCount, commodity}">
              <span v-if="parseInt(existCount) < parseInt(commodity.orderPoint)" class="text-danger"> نیاز به شارژ انبار </span>
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>