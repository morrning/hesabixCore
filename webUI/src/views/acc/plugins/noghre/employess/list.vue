<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <i class="mx-2 fa fa-list"></i>
        کارکنان کارگاه </h3>
      <div class="block-options">
        <router-link to="/acc/plugin/noghre/employees/mod" class="block-options-item">
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
            <template #item-operation="{ code }">
              <router-link :to="'/acc/plugin/noghre/employees/mod/' + code">
                <i class="fa fa-edit px-2"></i>
              </router-link>
            </template>
            <template #item-plugNoghreMorsa="{ plugNoghreMorsa }">
              <i v-show="plugNoghreMorsa" class="fa fa-check text-danger"></i>
            </template>
            <template #item-plugNoghreHakak="{ plugNoghreHakak }">
              <i v-show="plugNoghreHakak" class="fa fa-check text-danger"></i>
            </template>
            <template #item-plugNoghreTarash="{ plugNoghreTarash }">
              <i v-show="plugNoghreTarash" class="fa fa-check text-danger"></i>
            </template>
            <template #item-plugNoghreGhalam="{ plugNoghreGhalam }">
              <i v-show="plugNoghreGhalam" class="fa fa-check text-danger"></i>
            </template>
            <template #item-nikename="{ nikename,code }">
              <router-link :to="'/acc/persons/card/view/' + code">
                {{ nikename }}
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
    loading : ref(true),
    items:[],
    headers: [
      { text: "کد", value: "code" },
      { text: "نام مستعار", value: "nikename", sortable: true},
      { text: "نام و نام خانوادگی", value: "name", sortable: true},
      { text: "مرصع‌کار", value: "plugNoghreMorsa"},
      { text: "حکاک", value: "plugNoghreHakak"},
      { text: "قلم زن", value: "plugNoghreGhalam"},
      { text: "تراشکار", value: "plugNoghreTarash"},
      { text: "تلفن همراه", value: "mobile"},
      { text: "عملیات", value: "operation"},
    ]
  }},
  methods: {
    loadData(){
      axios.post('/api/plugin/noghre/employess/list')
          .then((response)=>{
            this.items = response.data;
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