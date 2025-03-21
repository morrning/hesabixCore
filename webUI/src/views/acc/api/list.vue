<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-plug-circle-plus"></i>
        دسترسی توسعه دهندگان</h3>
      <div class="block-options">
        <div class="block-options-item">
          <button @click="submitNew()" class="btn btn-sm btn-success">ایجاد رابط جدید</button>
        </div>
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
            <template #item-operation="{ token }">
              <span  class="text-danger px-1" @click="deleteItem(token)">
                <i class="fa fa-trash"></i>
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
  name: "list",
  data: ()=>{return {
    searchValue: '',
    loading: ref(true),
    items:[],
    headers: [
      { text: "توکن دسترسی", value: "token" , sortable: true},
      { text: "مهر زمان انقضا", value: "dateExpire"},
      { text: "عملیات", value: "operation"},
    ]
  }},
  methods: {
    loadData(){
      this.loading = true;
      axios.post('/api/business/api/list')
          .then((response)=>{
            this.items = response.data;
            this.loading = false;
          })
    },
    submitNew(){
      this.loading = true;
      axios.post('/api/business/api/new')
          .then((response)=>{
            this.items.push(response.data);
            this.loading = false;
            Swal.fire({
              text:'توکن ایجاد شد. رابط توکن: ' + response.data.token,
              confirmButtonText: 'قبول',
            })
          })
    },
    deleteItem(token){
      Swal.fire({
        text: 'آیا برای این مورد مطمئن هستید؟ دسترسی برنامه‌هایی که از این رابط استفاده می‌کنند قطع خواهد شد.',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon:'warning'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/business/api/remove/' + token).then((response)=>{
            if(response.data.result == 1){
              let index = 0;
              for(let z=0; z<this.items.length; z++){
                index ++;
                if(this.items[z]['token'] == token){
                  this.items.splice(index -1 ,1);
                }
              }
              Swal.fire({
                text: 'توکن با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
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