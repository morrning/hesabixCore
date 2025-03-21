<script>
import {defineComponent, ref} from 'vue'
import axios from "axios";

export default defineComponent({
  name: "plugin-my",
  data: ()=>{return{
    searchValue: '',
    loading : ref(true),
    items:[],
    headers: [
      { text: "افزونه", value: "des"},
      { text: "تاریخ ایجاد", value: "dateSubmit", sortable: true},
      { text: "تاریخ اعتبار", value: "dateExpire", sortable: true},
      { text: "وضعیت", value: "status", sortable: true},
      { text: "قیمت (تومان)", value: "price", sortable: true},
    ]
  }},
  methods:{
    loadData(){
      axios.post('/api/plugin/get/paids').then((response)=>{
        this.items = response.data;
        this.loading = false;
      })
    }
  },
  created() {
    this.loadData()
  }
})
</script>

<template>
  <div class="block block-content-full">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light" >
      <h3 class="block-title text-primary-dark">
        <i class="fa fa-cog"></i>
        تاریخچه پرداخت‌ها
      </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content pb-3">
      <div class="row">
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

              :search-value="searchValue"
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

            <template #item-status="{ status }">
              <span v-show="status == 100" class="text-success">پرداخت شده</span>
              <span v-show="status != 100" class="text-danger">پرداخت نشده</span>
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>

</template>

<style scoped>

</style>