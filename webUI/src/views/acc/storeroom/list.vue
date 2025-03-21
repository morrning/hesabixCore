<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="mx-2 fa fa-boxes-stacked"></i>
        انبارها </h3>
      <div class="block-options">
        <router-link to="/acc/storeroom/mod/" class="btn btn-sm btn-primary ms-1">
          <span class="fa fa-plus fw-bolder"></span>
        </router-link>
      </div>
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
            <template #item-operation="{ id }">
              <router-link :to="'/acc/storeroom/mod/' + id">
                <i class="fa fa-edit px-2"></i>
              </router-link>
            </template>
            <template #item-active="{ active }">
              <label class="text-primary" v-if="active">فعال</label>
              <label class="text-danger" v-else>غیرفعال</label>
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
    printID:'',
    searchValue: '',
    loading : ref(true),
    items:[],
    headers: [
      { text: "کد", value: "id" },
      { text: "نام انبار", value: "name", sortable: true},
      { text: "انباردار", value: "manager", sortable: true},
      { text: "تلفن", value: "tel", sortable: true},
      { text: "آدرس", value: "adr"},
      { text: "وضعیت", value: "active"},
      { text: "عملیات", value: "operation"},
    ]
  }},
  methods: {
    loadData(){
      axios.post('/api/storeroom/list/all')
          .then((response)=>{
            this.items = response.data.data;
            this.loading = false;
          })
    },
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<style scoped>

</style>