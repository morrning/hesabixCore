<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-circle-dot px-2"></i>
        سهامداران </h3>
      <div class="block-options">
       
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row mb-3 align-items-end border border-secondary rounded-2 p-2">
        <div class="col-sm-12 col-md-4 my-2">
          <label class="form-label">
            <i class="fa fa-person me-2"></i>
            سهامدار
          </label>
          <v-cob dir="rtl" class="" :options="persons" label="nikename" v-model="shareholder.person">
            <template #no-options="{ search, searching, loading }">
              نتیجه‌ای یافت نشد!
            </template>
          </v-cob>
        </div>
        <div class="col-sm-12 col-md-4 my-2">
          <label class="form-label">
            <i class="fa fa-file me-2"></i>
            تعداد سهام
          </label>
          <input type="number" @keypress="this.$filters.onlyNumber($event)" class="form-control" min="1" v-model="this.shareholder.percent">
        </div>
        <div class="col-sm-12 col-md-4 my-2">
          <button :disabled="this.loading" @click="submit()" type="button" class="btn btn-primary">
            <div v-show="this.loading" class="spinner-grow spinner-grow-sm me-2" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <i class="fa fa-save me-2"></i>
            افزودن سهامدار
          </button>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
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
            <template #item-percent="{ percent }">
              <span>{{ $filters.formatNumber(percent)}}</span>
            </template>
            <template #item-operation="{ id }">
              <button @click="this.deleteItem(id)" type="button" class="btn btn-link">
                <i class="fa fa-trash px-2 text-danger"></i>
              </button>
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
      { text: "شخص", value: "person.nikename", width: "120px"},
      { text: "تعداد سهام", value: "percent", width: "140px"},
      { text: "عملیات", value: "operation", width: "130"},
    ],
    persons:[],
    shareholder:{
      percent:1,
      person:null
    }
  }},
  methods: {
    loadData(){
      this.loading = true;
      axios.post('/api/shareholders/list')
          .then((response)=>{
            this.items = response.data;
            this.loading = false;
          });
      //load persons
      axios.post('/api/person/list/limit').then((response)=>{
        this.persons = response.data;
      });
    },
    submit(){
      if(this.shareholder.person == null){
        Swal.fire({
          text: 'سهامدار انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else if(this.shareholder.percent == ''){
        Swal.fire({
          text: 'تعداد سهام وارد نشده است .',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else{
        //going to save data
        axios.post('/api/shareholders/insert',{
          person:this.shareholder.person.id,
          count:this.shareholder.percent
        }).then((response)=>{
          Swal.fire({
            text: 'سهامدار با موفقیت افزوده شد .',
            icon: 'success',
            confirmButtonText: 'قبول'
          });
          this.shareholder.percent = 1;
          this.shareholder.person = null;
          this.loadData();
        });
      }
    },
    deleteItem(id){
      Swal.fire({
        text: 'آیا برای حذف این سهامدار مطمئن هستید؟',
        icon: 'warning',
        confirmButtonText: 'قبول',
        showCancelButton:true,
        cancelButtonText:'انصراف'
      }).then((result)=>{
        if (result.isConfirmed) {
          this.loading = true;
          axios.post('/api/shareholders/remove/' + id)
              .then((response)=>{
                this.loading = false;
                Swal.fire({
                  text: 'سهامدار حذف شد.',
                  icon: 'success',
                  confirmButtonText: 'قبول'
                }).then((result)=>{
                  for(let z=0; z<this.items.length; z++){
                    if(this.items[z]['id'] == id){
                      this.items.splice(z ,1);
                    }
                  }
                });
              });
        }
      });
    }
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<style scoped>

</style>