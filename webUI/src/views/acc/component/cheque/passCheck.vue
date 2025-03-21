<script lang="ts">
import axios from 'axios';
import {defineComponent,ref} from 'vue'
import Swal from "sweetalert2";

export default defineComponent({
  name: "passCheck",
  props:{
    id: Number,
    windowsState: Object,
  },
  data:()=>{return {
    loading:ref(true),
    banks : [],
    modal:'',
    bankSelected: null,
    des:'',
    passDate:'',
    year:{
      start:'',
      end:'',
      now:''
    }
  }},
  methods:{
    loadData(){
      axios.post('/api/bank/list').then((response) => {
          this.banks = response.data;
          this.isLoading = false;
        });

        //load year
      axios.post('/api/year/get').then((response)=>{
        this.year = response.data;
        this.passDate = response.data.now;
      });
      this.loading = false;
    },
    save(){
      if(this.bankSelected == '' || this.bankSelected == null){
        Swal.fire({
            text:'بانک انتخاب نشده است',
            icon: 'success',
            confirmButtonText: 'قبول'
          })
      }
      else{
       this.loading = true;
       axios.post('/api/cheque/pass/' + this.$props.id,{
        bank:this.bankSelected,
        date:this.passDate,
        des:this.des
      }).then((response) => {
          Swal.fire({
            text:'ثبت وصول چک با موفقیت ثبت شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then((result)=>{
            this.submitedDoc = response.data.doc;
            let btnClose = document.getElementById('pass-check-btn-close');
            btnClose.click();
            this.$props.windowsState.submited = true;
            this.loading = false;
          });
        }); 
      }
      
    }
  },
  beforeMount(){
    this.loadData();
  }
})
</script>

<template>
  <!-- Button trigger modal -->
  <a type="button" class="btn btn-sm btn-link text-primary" title="پاس کردن چک" data-bs-toggle="modal" data-bs-target="#modal-pass-check" >
    <i class="fa fa-money-bill-trend-up pe-1"></i>
  </a>

  <!-- Modal -->
  <div class="modal fade" id="modal-pass-check" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary-light text-white">
          <h1 class="modal-title fs-5" id="staticBackdropLabel">پاس کردن چک</h1>
          <div class="block-options">
            <button type="button" id="pass-check-btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-12 col-md-12 mb-2">
              <div class="form-control">
                <label class="form-label">تاریخ</label>
                <date-picker
                    class=""
                    v-model="passDate"
                    format="jYYYY/jMM/jDD"
                    display-format="jYYYY/jMM/jDD"
                    :min="year.start"
                    :max="year.end"
                  
                />
              </div>
            </div>
            <div class="col-sm-12 col-md-12 mb-2">
              <div class="form-control">
                <label class="form-label">بانک</label>
                <v-cob
                        dir="rtl"
                        :options="banks"
                        label="name"
                        v-model="bankSelected"
                  >
                    <template #no-options="{ search, searching, loading }">
                        نتیجه‌ای یافت نشد!
                    </template>
                </v-cob>
              </div>
            </div>
            <div class="col-sm-12 col-md-12 mb-2">
              <div class="form-control">
                <label class="form-label">توضیحات</label>
                <input type="text" class="form-control" v-model="des" />
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer float-end align-end">
              <button :disabled="loading" type="button" class="btn btn-success" @click="save()">
                <i class="fa fa-save pe-2" />
                ثبت
              </button>
            </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>