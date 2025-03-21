<script lang="ts">
import {defineComponent, ref} from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "list",
  data: ()=>{return {
    searchValue: '',
    loading : ref(true),
    items:[],
    headers: [
      { text: "کد", value: "code" },
      { text: "وضعیت", value: "status", sortable: true},
      { text: "مشتری", value: "customer", sortable: true},
      { text: "تاریخ تحویل", value: "dateDeliver"},
      { text: "مدل", value: "model"},
      { text: "سایز", value: "size"},
      { text: "نگین", value: "negin"},
      { text: "هزینه نگین", value: "neginFee"},
      { text: "عملیات", value: "operation"},
    ]
  }},
  methods: {
    loadData(){
      axios.post('/api/plugin/noghre/orders/list')
          .then((response)=>{
            this.items = response.data;
            this.loading = false;
          })
    },
    deleteItem(code){
      Swal.fire({
        text: 'آیا برای حذف این مورد مطمئن هستید؟ در نظر داشته باشید همراه با حذف سفارش کلیه اسناد پرداخت مشتری نیز حذف خواهند شد.',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/plugin/noghre/remove/order/' + code).then((response)=>{
            if(response.data.result == 'ok'){
              let index = 0;
              for(let z=0; z<this.items.length; z++){
                index ++;
                if(this.items[z]['code'] == code){
                  this.items.splice(index -1 ,1);
                }
              }
              Swal.fire({
                text: 'سفارش با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    }
  },
  created() {
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
        <i class="mx-2 fa fa-list"></i>
        سفارشات </h3>
      <div class="block-options">
        <router-link to="/acc/plugin/noghre/order/mod/0" class="block-options-item">
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
            <template #item-operation="{ code ,id }">
              <router-link :to="'/acc/plugin/noghre/employees/mod/' + code">
                <i class="fa fa-edit px-2"></i>
              </router-link>
              <router-link :to="'/acc/accounting/view/' + code">
                <i class="fa fa-book px-2"></i>
              </router-link>
              <a @click.prevent="deleteItem(code)">
                <i class="fa fa-trash px-2 text-danger"></i>
              </a>
              <router-link :to="'/acc/plugin/noghre/order/view/' + id">
                <i class="fa fa-file px-2 text-success"></i>
              </router-link>
              <router-link :to="'/acc/plugin/noghre/pays/view/' + id">
                <i class="fa fa-money-bill-1-wave px-2 text-warning"></i>
              </router-link>
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>