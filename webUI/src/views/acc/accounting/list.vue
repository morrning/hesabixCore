<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-book-open-reader"></i>
        اسناد حسابداری</h3>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="mb-1">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
            </div>
          </div>
          <EasyDataTable table-class-name="customize-table"
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
              :loading = "loading"
          >
            <template #item-state="{ type }">
              <i v-if="type != 'accounting'" class="fa fa-lock text-danger"></i>
              <i v-else class="fa fa-lock-open text-success"></i>
            </template>
            <template #item-operation="{ code,type }">
              <router-link class="btn btn-sm btn-link text-success" :to="'/acc/accounting/view/' + code">
                <i class="fa fa-eye px-1"></i>
              </router-link>
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
  name: "list",
  data: ()=>{return {
    searchValue: '',
    loading: ref(true),
    items:[],
    headers: [
      { text: "وضعیت", value: "state" , sortable: true},
      { text: "عملیات", value: "operation"},
      { text: "کد", value: "code" , sortable: true},
      { text: "تاریخ", value: "date", sortable: true},
      { text: "شرح", value: "des", sortable: true},
      { text: "مبلغ", value: "amount", sortable: true},
      { text: "ثبت کننده", value: "submitter", sortable: true},
    ]
  }},
  methods: {
    loadData(){
      axios.post('/api/accounting/search',{
        type: 'all'
      })
          .then((response)=>{
            this.items = response.data;
            this.items.forEach((item)=>{
              item.amount = this.$filters.formatNumber(item.amount)
            })
            this.loading = false;
          })
    }
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<style scoped>

</style>