<template>
    <div class="block block-content-full ">
      <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
        <h3 class="block-title text-primary-dark">
          <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
            <i class="fa fw-bold fa-arrow-right"></i>
          </button>
          <i class="fa fa-folder-tree px-2"></i>
          آرشیو فایل‌ها</h3>
        <div class="block-options">
         
        </div>
      </div>
      <div class="block-content pt-1 pb-3">
        <div class="row">
          <div class="col-sm-12 col-md-3 p-1">
            <div class="list-group">
              <button class="list-group-item list-group-item-action active" aria-current="true">
                <i class="fa fa-folder-tree"></i>
                دسته بندی فایل‌ها
              </button>
              <button type="button" @click="this.loadData('all')" class="list-group-item list-group-item-action">همه فایل‌ها</button>
              <button type="button" @click="this.loadData('accounting')" class="list-group-item list-group-item-action">اسناد حسابداری</button>
              <button type="button" @click="this.loadData('persons')" class="list-group-item list-group-item-action">اشخاص</button>
              <button type="button" @click="this.loadData('commodity')" class="list-group-item list-group-item-action">کالا</button>
              <button type="button" @click="this.loadData('bank')" class="list-group-item list-group-item-action">بانک</button>
              <button type="button" @click="this.loadData('cashdesk')" class="list-group-item list-group-item-action">صندوق</button>
              <button type="button" @click="this.loadData('salary')" class="list-group-item list-group-item-action">تنخواه</button>
              <button type="button" @click="this.loadData('storeroom')" class="list-group-item list-group-item-action">انبار</button>
              <button type="button" @click="this.loadData('transfer')" class="list-group-item list-group-item-action">انتقال</button>
              <button type="button" @click="this.loadData('persons_recive')" class="list-group-item list-group-item-action">دریافت</button>
              <button type="button" @click="this.loadData('persns_send')" class="list-group-item list-group-item-action">پرداخت</button>
              <button type="button" @click="this.loadData('income')" class="list-group-item list-group-item-action">درآمد</button>
              <button type="button" @click="this.loadData('cost')" class="list-group-item list-group-item-action">هزینه</button>
              <button type="button" @click="this.loadData('buy')" class="list-group-item list-group-item-action">خرید</button>
              <button type="button" @click="this.loadData('sell')" class="list-group-item list-group-item-action">فروش</button>
              <button type="button" @click="this.loadData('onlinestore')" class="list-group-item list-group-item-action">فروشگاه آنلاین</button>
            </div>
          </div>
          <div class="col-sm-12 col-md-9 p-0">
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
              <template #item-operation="{ id, filename,fileType}">
                <a class="btn btn-link" href="/" @click.prevent="downloadFile(id,filename,fileType)">
                  <i class="fa fa-download"></i>
                </a>
                <button @click="deleteItem(id)" class="btn ms-2 btn-link text-danger">
                  <i class="fa fa-trash"></i>
                </button>
              </template>
              <template #item-cat="{ cat }">
                <span v-if="cat == 'accounting'">اسناد حسابداری</span>
                <span v-if="cat == 'persons'">اشخاص</span>
                <span v-if="cat == 'commodity'">کالا</span>    
                <span v-if="cat == 'bank'">بانک</span> 
                <span v-if="cat == 'cashdesk'">صندوق</span> 
                <span v-if="cat == 'salary'">تنخواه گردان</span> 
                <span v-if="cat == 'transfer'">انتقال</span> 
                <span v-if="cat == 'storeroom'">انبار</span> 
                <span v-if="cat == 'persons_recive'">دریافت</span> 
                <span v-if="cat == 'persns_send'">پرداخت</span>
                <span v-if="cat == 'income'">درآمد</span> 
                <span v-if="cat == 'cost'">هزینه</span> 
                <span v-if="cat == 'buy'">خرید</span> 
                <span v-if="cat == 'sell'">فروش</span> 
                <span v-if="cat == 'onlinestore'">فروشگاه آنلاین</span>              
              </template>
              <template #item-filePublic="{ filePublic }">
                <i v-if="filePublic" class="text-success fa fa-check"></i>  
                <i v-else class="text-danger fa fa-close"></i>                           
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
      cat:'all',
      items:[],
      headers: [
        { text: "عملیات", value: "operation", width: "130"},
        { text: "نام فایل", value: "filename", width: "100" },
        { text: "نوع فایل", value: "fileType", width: "120"},
        { text: "حجم (مگابایت)", value: "filesize", width: "100" },
        { text: "تاریخ ایجاد", value: "dateSubmit", width: "140"},
        { text: "ایجاد کننده", value: "submitter", width: "140"},
        { text: "دسترسی عمومی", value: "filePublic", width: "120"},
        { text: "دسته بندی", value: "cat", width: "120"},
    
      ]
    }},
    methods: {
      loadData(cat) {
        axios.post('/api/archive/list/' + cat)
            .then((response) => {
              this.items = response.data;
              this.loading = false;
            })
      },
      deleteItem(id) {
        Swal.fire({
          text: 'آیا برای حذف فایل مطمئن هستید؟',
          showCancelButton: true,
          confirmButtonText: 'بله',
          cancelButtonText: `خیر`,
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            axios.post('api/archive/file/remove/' + id).then((response) => {
              if (response.data.result == 1) {
                this.loadData(this.cat);
                Swal.fire({
                  text: 'فایل با موفقیت حذف شد.',
                  icon: 'success',
                  confirmButtonText: 'قبول'
                });
              }
            })
          }
        })
      },
      downloadFile(id,filename,fileType) {
        axios.post(this.$filters.getApiUrl() + '/api/archive/file/get/' + id, {responseType: "arraybuffer"})
            .then(response => {
              const blob = new Blob([response.data], {type: fileType});
              const link = document.createElement('a');
              link.href = URL.createObjectURL(blob);
              link.download = filename;
              link.click();
              URL.revokeObjectURL(link.href);
            });
      },
    },
    beforeMount() {
      this.loadData(this.cat);
    }
  }
  </script>
  
  <style scoped>
  
  </style>