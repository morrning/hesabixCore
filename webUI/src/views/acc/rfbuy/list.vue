<template>
  <!-- Print Modal -->
  <div class="modal fade" id="printModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary-light text-white">
          <h1 class="modal-title fs-5" id="printModalLabel">چاپ فاکتور</h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <p class="mb-2">برای تغییر تنظیمات پیشفرض به بخش تنظیمات چاپ مراجعه نمایید</p>
          <div class="form-floating mb-2">
            <select v-model="printOptions.paper" class="form-select">
              <option value="A4-L">A4 افقی</option>
              <option value="A4">A4 عمودی</option>
              <option value="A5-L">A5 افقی</option>
              <option value="A5">A5 عمودی</option>
            </select>
            <label>سایز کاغذ و حالت چاپ</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.bidInfo" type="checkbox">
            <label class="form-check-label">اطلاعات کسب‌وکار</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.pays" type="checkbox">
            <label class="form-check-label">نمایش پرداخت‌های فاکتور</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.note" type="checkbox">
            <label class="form-check-label">یاداشت پایین فاکتور</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.taxInfo" type="checkbox">
            <label class="form-check-label">مالیات به تفکیک اقلام</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" v-model="printOptions.discountInfo" type="checkbox">
            <label class="form-check-label">تخفیف به تفکیک اقلام</label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary mx-2" @click="printInvoice()" type="button">
            <i class="si si-printer me-1"></i>
            <span class="">چاپ</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- End Print Modal -->
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-book"></i>
        فاکتورهای برگشت از خرید
      </h3>
      <div class="block-options">
        <div class="dropdown-center block-options-item">
          <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link" data-bs-toggle="dropdown"
            id="dropdown-align-center-alt-primary" type="button">
            <i class="fa-solid fa-ellipsis"></i>
          </button>
          <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end" style="">
            <button v-for="item in types" class="dropdown-item" @click="changeLabel(item)">
              <i class="fa fa-undo text-dark pe-2"></i>
              تغییر به
              {{ item.label }}
            </button>
            <button class="dropdown-item text-danger" @click="changeLabel('clear')">
              <i class="fa fa-undo pe-2"></i>
              حذف برچسب‌ها
            </button>
            <hr class="dropdown-divider">
            <button class="dropdown-item text-danger" @click="deleteItems()">
              <i class="fa fa-trash pe-2"></i>
              حذف گروهی
            </button>
          </div>
        </div>
        <router-link to="/acc/rfbuy/mod/" type="button" class="block-options-item">
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
              <button class="btn btn-outline-success dropdown-toggle d-block d-sm-none" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-filter"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <div v-for="(item, index) in types" class="form-check">
                  <input @change="filterTable()" v-model="types[index].checked" checked="" class="form-check-input"
                    type="checkbox">
                  <label class="form-check-label">{{ item.label }}</label>
                </div>
              </ul>

            </div>
          </div>
          <div class="col-sm-12 col-md-12 border rounded mb-2 px-2 py-1 d-none d-sm-block">
            <div v-for="(item, index) in types" class="form-check form-check-inline">
              <input @change="filterTable()" v-model="types[index].checked" checked="" class="form-check-input"
                type="checkbox">
              <label class="form-check-label">{{ item.label }}</label>
            </div>
          </div>
          <EasyDataTable table-class-name="customize-table" v-model:items-selected="itemsSelected" show-index
            alternating :headers="headers" :items="items" theme-color="#1d90ff" header-text-direction="center"
            body-text-direction="center" rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
            rowsOfPageSeparatorMessage="از" :loading="loading">
            <template #item-operation="{ code, type }">
              <div class="dropdown-center">
                <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link" data-bs-toggle="dropdown"
                  id="dropdown-align-center-alt-primary" type="button">
                  <i class="fa-solid fa-ellipsis"></i>
                </button>
                <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                  style="">
                  <router-link class="dropdown-item" :to="'/acc/accounting/view/' + code">
                    <i class="fa fa-file text-success pe-2"></i>
                    سند حسابداری
                  </router-link>
                  <router-link class="dropdown-item" :to="'/acc/rfbuy/view/' + code">
                    <i class="fa fa-eye text-info pe-2"></i>
                    مشاهده فاکتور
                  </router-link>
                  <button class="dropdown-item" @click="printOptions.selectedPrintCode = code" data-bs-toggle="modal"
                    data-bs-target="#printModal">
                    <i class="fa fa-file-pdf pe-2"></i>
                    خروجی PDF
                  </button>
                  <button type="button" @click="canEditItem(code)" class="dropdown-item">
                    <i class="fa fa-edit pe-2"></i>
                    ویرایش
                  </button>
                  <button type="button" @click="deleteItem(code)" class="dropdown-item text-danger">
                    <i class="fa fa-trash pe-2"></i>
                    حذف
                  </button>
                </div>
              </div>
            </template>
            <template #item-label="{ label }">
              <span v-if="label">
                <span v-if="label.code == 'payed'" class="text-success">{{ label.label }}</span>
                <span v-if="label.code == 'returned'" class="text-danger">{{ label.label }}</span>
                <span v-if="label.code == 'accepted'" class="text-info">{{ label.label }}</span>
              </span>
            </template>
            <template #item-des="{ des }">
              {{ des.replace("فاکتور برگشت از خرید:", "") }}
            </template>
            <template #item-relatedDocsCount="{ relatedDocsCount, relatedDocsPays }">
              <span v-if="relatedDocsCount != '0'" class="text-success"><i class="fa fa-money"></i>
                {{ $filters.formatNumber(relatedDocsPays) }}
              </span>
            </template>
            <template #item-amount="{ amount }">
              <span class="text-dark">
                {{ $filters.formatNumber(amount) }}
              </span>
            </template>
            <template #item-transferCost="{ transferCost }">
              <span class="text-dark">
                {{ $filters.formatNumber(transferCost) }}
              </span>
            </template>
            <template #item-discountAll="{ discountAll }">
              <span class="text-dark">
                {{ $filters.formatNumber(discountAll) }}
              </span>
            </template>
            <template #item-person="{ person }">
              <router-link :to="'/acc/persons/card/view/' + person.code">
                {{ person.nikename }}
              </router-link>
            </template>
            <template #item-code="{ code }">
              <router-link :to="'/acc/rfbuy/view/' + code">
                {{ code }}
              </router-link>
            </template>
          </EasyDataTable>
          <div class="container-fluid p-0 mx-0 my-3">
            <a class="block block-rounded block-link-shadow border-start border-success border-3"
              href="javascript:void(0)">
              <div class="block-content block-content-full block-content-sm bg-body-light">
                <div class="row">
                  <div class="col-sm-6 com-md-6">
                    <span class="text-dark">
                      <i class="fa fa-list-dots"></i>
                      مبلغ کل:
                    </span>
                    <span class="text-primary">
                      {{ $filters.formatNumber(this.sumTotal) }}
                      {{ $filters.getActiveMoney().shortName }}
                    </span>
                  </div>

                  <div class="col-sm-6 com-md-6">
                    <span class="text-dark">
                      <i class="fa fa-list-check"></i>
                      جمع مبلغ موارد انتخابی:
                    </span>
                    <span class="text-primary">
                      {{ $filters.formatNumber(this.sumSelected) }}
                      {{ $filters.getActiveMoney().shortName }}
                    </span>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";
export default {
 name: "list",
 data: () => {
   return {
     printOptions: {
       pays: true,
       note: true,
       bidInfo: true,
       taxInfo: true,
       discountInfo: true,
       selectedPrintCode: 0,
       paper:  'A4-L'
     },
     sumSelected: 0,
     sumTotal: 0,
     itemsSelected: [],
     searchValue: '',
     types: [],
     loading: ref(true),
     items: [],
     orgItems: [],
     headers: [
       { text: "عملیات", value: "operation" },
       { text: "فاکتور", value: "code", sortable: true },
       { text: "تاریخ", value: "date", sortable: true },
       { text: "خریدار", value: "person", sortable: true },
       { text: "تخفیف", value: "discountAll", sortable: true },
       { text: "حمل و نقل", value: "transferCost", sortable: true },
       { text: "مبلغ", value: "amount", sortable: true },
       { text: "پرداختی", value: "relatedDocsCount", sortable: true },
       { text: "برچسب", value: "label", width: 100 },
       { text: "شرح", value: "des", sortable: true },
     ]
   }
 },
 methods: {
   changeLabel(label) {
     if (this.itemsSelected.length == 0) {
       Swal.fire({
         text: 'هیچ موردی انتخاب نشده است.',
         icon: 'warning',
         confirmButtonText: 'قبول'
       });
     }
     else {
       this.loading = true;
       axios.post('/api/rfbuy/label/change', {
         'items': this.itemsSelected,
         'label': label
       }
       ).then((response) => {
         this.loading == false;
         if (response.data.code == 0) {
           Swal.fire({
             text: 'فاکتور‌ها با موفقیت ویرایش شد.',
             icon: 'success',
             confirmButtonText: 'قبول'
           });
           this.itemsSelected = [];
         }
         else if (response.data.result == 2) {
           Swal.fire({
             text: response.data.message,
             icon: 'warning',
             confirmButtonText: 'قبول'
           });
         }
         this.loadData();
       })
     }
   },
   filterTable() {
     this.loading = true;
     let calcItems = [];
     let isAll = true;
     let selectedTypes = [];
     this.types.forEach((item) => {
       if (item.checked == true) {
         isAll = false;
         selectedTypes.push(item);
       }
     });
     if (isAll) {
       this.items = this.orgItems;
     }
     else {
       this.orgItems.forEach((item) => {
         selectedTypes.forEach((st) => {
           if (item.label) {
             if (st.code == item.label.code) {
               let existBefore = false;
               calcItems.forEach((ri) => {
                 if (item.label.code == ri.code) {
                   existBefore = true;
                 }
               })
               if (existBefore == false) {
                 calcItems.push(item);
               }
             }
           }

         });
       });
       this.items = calcItems;
     }

     this.loading = false;
   },
   loadData() {
     axios.post("/api/printers/options/info").then((response) => {
       this.printOptions = response.data.rfbuy;
     });

     axios.post('/api/invoice/types', {
       type: 'rfbuy'
     }).then((response) => {
       this.types = response.data;
     });

     axios.post('/api/rfbuy/docs/search', {
       type: 'rfbuy'
     })
       .then((response) => {
         this.items = response.data;
         this.orgItems = response.data;
         this.items.forEach((item) => {
           this.sumTotal += parseInt(item.amount);
         })
         this.loading = false;
       })
   },
   canEditItem(code) {
     this.loading = true;
     axios.post('/api/rfbuy/edit/can/' + code)
       .then((response) => {
         this.loading = false;
         if (response.data.result == false) {
           Swal.fire({
             text: 'این فاکتور به دلیل وجود اسناد پرداخت یا حواله های انبار مرتبط با آن قابل ویرایش نیست',
             confirmButtonText: 'قبول',
             icon: 'error'
           });
         }
         else {
           this.$router.push('/acc/rfbuy/mod/' + code);
         }
       });
   },
   deleteItems() {
     if (this.itemsSelected.length == 0) {
       Swal.fire({
         text: 'هیچ موردی انتخاب نشده است.',
         icon: 'warning',
         confirmButtonText: 'قبول'
       });
     }
     else {
       Swal.fire({
         text: 'آیا برای حذف این مورد مطمئن هستید؟ تمامی اسناد پرداخت و حواله های انبار همراه فاکتور نیز حذف خواهند شد.',
         showCancelButton: true,
         confirmButtonText: 'بله',
         cancelButtonText: `خیر`,
         icon: 'warning'
       }).then((result) => {
         /* Read more about isConfirmed, isDenied below */
         if (result.isConfirmed) {
           this.loading = true;
           axios.post('/api/accounting/remove/group', {
             'items': this.itemsSelected
           }
           ).then((response) => {
             this.loading = false;
             if (response.data.result == 1) {
               this.loadData();
               Swal.fire({
                 text: 'فاکتور ها با موفقیت حذف شد.',
                 icon: 'success',
                 confirmButtonText: 'قبول'
               });
             }
             else if (response.data.result == 2) {
               Swal.fire({
                 text: response.data.message,
                 icon: 'warning',
                 confirmButtonText: 'قبول'
               });
             }
           })
         }
       })
     }
   },
   printInvoice(pdf = true,cloudePrinters=true) {
     this.loading = true;
     axios.post('/api/rfbuy/print/invoice', { 
       'code': this.printOptions.selectedPrintCode,
       'pdf': pdf,
       'printers':cloudePrinters,
       'printOptions': this.printOptions
      }).then((response) => {
       this.loading = false;
       window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
     });
   },
   deleteItem(code) {
     Swal.fire({
       text: 'آیا برای حذف این مورد مطمئن هستید؟ تمامی اسناد پرداخت و حواله های انبار همراه فاکتور نیز حذف خواهند شد.',
       showCancelButton: true,
       confirmButtonText: 'بله',
       cancelButtonText: `خیر`,
       icon: 'warning'
     }).then((result) => {
       /* Read more about isConfirmed, isDenied below */
       if (result.isConfirmed) {
         axios.post('/api/accounting/remove', {
           'code': code
         }
         ).then((response) => {
           if (response.data.result == 1) {
             let index = 0;
             for (let z = 0; z < this.items.length; z++) {
               index++;
               if (this.items[z]['code'] == code) {
                 this.items.splice(index - 1, 1);
               }
             }
             Swal.fire({
               text: 'فاکتور برگشت از خرید با موفقیت حذف شد.',
               icon: 'success',
               confirmButtonText: 'قبول'
             });
           }
           else if (response.data.result == 2) {
             Swal.fire({
               text: response.data.message,
               icon: 'warning',
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
 },
 watch: {
   itemsSelected: {
     handler: function (val, oldVal) {
       this.sumSelected = 0;
       this.itemsSelected.forEach((item) => {
         if (typeof item.amount.valueOf() === "string") {
           this.sumSelected += parseInt(item.amount.replaceAll(",", ""))
         }
         else{
           this.sumSelected += item.amount;
         }
       });
     },
     deep: true
   },
   searchValue: {
     handler: function (val, oldVal) {
       if (this.searchValue == '') {
         this.items = this.orgItems;
       }
       else {
         let temp = [];
         this.orgItems.forEach((item) => {
           if (item.person.nikename.includes(this.searchValue)) {
             temp.push(item)
           }
           else if (item.date.includes(this.searchValue)) {
             temp.push(item)
           }
           else if (item.amount.toString().includes(this.searchValue)) {
             temp.push(item)
           }
           else if (item.des.includes(this.searchValue)) {
             temp.push(item)
           }
           else if (item.code.includes(this.searchValue)) {
             temp.push(item)
           }
           else if (item.label) {
             if (item.label.label.includes(this.searchValue)) {
               temp.push(item)
             }
           }
         });
         this.items = temp;
       }
     },
     deep: false
   }
 }
}
</script>

<style scoped></style>