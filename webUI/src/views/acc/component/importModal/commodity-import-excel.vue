<script lang="ts">
import axios from 'axios';
import Swal from 'sweetalert2';
import {defineComponent} from 'vue'
import {ref} from 'vue'
export default defineComponent({
  name: "commodity-import-excel",
  props:{
    windowsState:Object
  },
  data:()=>{return{
    loading:ref(false),
    file:null
  }},
  methods:{
    addFile(e) {
      this.file = e.target.files[0];
    },
    submit(){
      if(this.file === null){
        Swal.fire({
          text: 'فایل انتخاب نشده است',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else{
        //send excel file to server
        let formData = new FormData();
        formData.append('file', this.file);
        axios.post( '/api/commodity/import/excel',formData,
        {
          headers: {
              'Content-Type': 'multipart/form-data'
          }
        }).then(()=>{
          Swal.fire({
            text: 'فایل با موفقیت ثبت شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then((resp)=>{
            this.$refs.Close.click();
            this.$props.windowsState.submited = true;
          });
        })
        .catch(()=>{
          Swal.fire({
            text: 'متاسفانه خطایی به وجود آمد.',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
        });
      }
    }
  }
})
</script>

<template>
  <v-tooltip :text="$t('dialog.import_excel')" location="bottom">
    <template v-slot:activator="{ props }">
      <v-btn v-bind="props" icon="mdi-table-arrow-left" color="primary" data-bs-toggle="modal"
        data-bs-target="#importexcel">
      </v-btn>
    </template>
  </v-tooltip>
  <!-- Modal -->
  <div class="modal modal-lg fade" id="importexcel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="importexcelLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary-light text-white">
          <h1 class="modal-title fs-5" id="importexcelLabel">وارد کردن از اکسل</h1>
          <div class="block-options">
            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
            <ul>
                <li>برای وارد کردن لیست کالا و خدمات در اکسل ابتدا فایل نمونه را دریافت نمایید سپس مطابق الگو اطلاعات را تکمیل کنید در مرحله بعدی با انتخاب فایل نسبت به ورود از لیست اقدام کنید</li>
                <li>
                    <a :href="this.$filters.getApiUrl() + '/imports/commodities-import.xlsx'" target="_blank">دریافت فایل نمونه</a>
                </li>
            </ul>
            <form @submit.prevent="submit()">
              <div class="mb-3">
                  <label for="formFileSm" class="form-label">انتخاب فایل</label>
                  <input @change="addFile" class="custom-file-input form-control" id="formFileSm" type="file" accept="application/vnd.ms-excel,application/vnd.ms-excel.sheet.macroEnabled.12,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
              </div>
              <div class="mb-3">
                  <button type="submit" class="btn btn-primary">
                      وارد کردن
                  </button>
              </div>
            </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
  
</style>