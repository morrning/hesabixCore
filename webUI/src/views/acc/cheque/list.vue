<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-money-check-dollar px-2"></i>
         چک‌های بانکی </h3>
      <div class="block-options">
      
      </div>
    </div>
    <div class="block-content pt-0 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="block-content p-0">
            <div class="col-sm-12 col-md-12 m-0 p-0">
              <ul class="nav nav-pills flex-column flex-sm-row border border-secondary" id="myTab" role="tablist">
                <button class="flex-sm-fill text-sm-center nav-link rounded-0 active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
                  <i class="fa fa-file-export me-2"></i>
                  چک‌های دریافتی
                </button>
                <button class="flex-sm-fill text-sm-center nav-link rounded-0" id="pays-tab" data-bs-toggle="tab" data-bs-target="#pays" type="button" role="tab" aria-controls="pays" aria-selected="false">
                  <i class="fa fa-file-import me-2"></i>
                  چک‌‌های واگذار شده
                </button>
              </ul>
              <div class="tab-content p-0" id="myTabContent">
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                  <div class="my-1">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text"><i class="fa fa-search"></i></span>
                      <input v-model="searchValueInput" class="form-control" type="text" placeholder="جست و جو ...">
                    </div>
                  </div>
                  <EasyDataTable table-class-name="customize-table"
                      multi-sort
                      show-index
                      alternating
                      :search-value="searchValueInput"
                      :headers="headersInput"
                      :items="itemsInput"
                      theme-color="#1d90ff"
                      header-text-direction="center"
                      body-text-direction="center"
                      rowsPerPageMessage="تعداد سطر"
                      emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
                      rowsOfPageSeparatorMessage="از"
                      :loading="loading"
                  >
                    <template #item-operation="{ id,locked,rejected }">
                      <passCheck v-if="!locked" :windowsState="this.passChequeWindowsState" :id="id"/>

                      <a v-if="!rejected && !locked" @click="rejectCheque(id)" class="btn btn-sm btn-link text-danger" title="برگشت چک">
                        <i class="fa fa-arrow-left px-2"></i>
                      </a>
                    </template>
                    <template #item-status="{ status }">
                      <div v-show="status=='پاس شده'" class="text-success">{{ status }}</div>
                      <div v-show="status=='پاس نشده'" class="text-dark">{{ status }}</div>
                      <div v-show="status=='برگشت خورده'" class="text-danger">{{ status }}</div>
                    </template>
                  </EasyDataTable>
                </div>
                <div class="tab-pane fade" id="pays" role="tabpanel" aria-labelledby="pays-tab">
                  <div class="my-1">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text"><i class="fa fa-search"></i></span>
                      <input v-model="searchValueOutput" class="form-control" type="text" placeholder="جست و جو ...">
                    </div>
                  </div>
                  <EasyDataTable table-class-name="customize-table"
                      multi-sort
                      show-index
                      alternating
                      :search-value="searchValueOutput"
                      :headers="headersInput"
                      :items="itemsOutput"
                      theme-color="#1d90ff"
                      header-text-direction="center"
                      body-text-direction="center"
                      rowsPerPageMessage="تعداد سطر"
                      emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
                      rowsOfPageSeparatorMessage="از"
                      :loading="loading"
                  >
                    <template #item-operation="{ id,locked,rejected }">
                      <passCheck v-if="!locked" :windowsState="this.passChequeWindowsState" :id="id"/>

                      <a v-if="!rejected && !locked" @click="rejectCheque(id)" class="btn btn-sm btn-link text-danger" title="برگشت چک">
                        <i class="fa fa-arrow-left px-2"></i>
                      </a>
                    </template>
                    <template #item-status="{ status }">
                      <div v-show="status=='پاس شده'" class="text-success">{{ status }}</div>
                      <div v-show="status=='پاس نشده'" class="text-dark">{{ status }}</div>
                      <div v-show="status=='برگشت خورده'" class="text-danger">{{ status }}</div>
                    </template>
                  </EasyDataTable>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import {ref} from "vue";
import passCheck from "../component/cheque/passCheck.vue"
export default {
  name: "list",
  components:{
    passCheck:passCheck
  },
  watch:{
    'passChequeWindowsState.submited'(newValue,oldValue) {
      this.passChequeWindowsState.submited = false;
      if(newValue){
        this.loadData();
      }
    }
  },
  data: ()=>{return {
    passChequeWindowsState:{
      submited:false
    },
    loading: ref(true),
    searchValueInput: '',
    itemsInput:[],
    itemsOutput:[],
    searchValueOutput:'',
    headersInput: [
      { text: "عملیات", value: "operation", width: "100"},
      { text: "شماره", value: "number", width: "100px" },
      { text: "کد صیاد", value: "sayadNum", width: "120px"},
      { text: "مبلغ(ریال)", value: "amount", width: "140px"},
      { text: "تاریخ", value: "datePay", width: "150px"},
      { text: "پرداخت کننده", value: "person.nikename", width: "150px"},
      { text: "بانک", value: "chequeBank", width: "150px"},
      { text: "وضعیت", value: "status", width: "150px", sortable: true},
      { text: "تاریخ وصول", value: "date", width: "150px"},
      { text: "توضیحات", value: "des", width: "150px"},
    ]
  }},
  methods: {
    loadData(){
      axios.post('/api/cheque/list')
          .then((response)=>{
            this.itemsInput = response.data.input;
            this.itemsInput.forEach((item)=>{
              item.amount = this.$filters.formatNumber(item.amount);
            });
            this.itemsOutput = response.data.output;
            this.itemsOutput.forEach((item)=>{
              item.amount = this.$filters.formatNumber(item.amount);
            });
            this.loading = false;
          });
    },
    rejectCheque(id){
      this.loading = true;
      axios.post('/api/cheque/info/' + id).then((response)=>{
            this.loading = false;
            Swal.fire({
              title: "آیا برای تغییر وضعیت چک به برگشتی مطمئن هستید؟",
              icon: "question",
              confirmButtonText: "بله",
              cancelButtonText: "خیر",
              showCancelButton: true,
              showCloseButton: true
            }).then((result)=>{
              if(result.isConfirmed){
                this.loading = true;
                axios.post('/api/cheque/reject/' + id).then((response)=>{
                    this.loading = false;
                    Swal.fire({
                      title: "وضعیت چک تغییر یافت",
                      icon: "success",
                      confirmButtonText: "بله",
                    });
                    this.loadData();
              });
              }
            });
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