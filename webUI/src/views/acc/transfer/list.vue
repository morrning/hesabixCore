<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-book"></i>
        لیست انتقال‌ها
      </h3>
      <div class="block-options">
        <router-link title="افزودن سند انتقال جدید" to="/acc/transfer/mod/" class="block-options-item">
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
          <EasyDataTable table-class-name="customize-table" show-index alternating :search-value="searchValue" :headers="headers" :items="items"
            theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
            rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از"
            :loading="loading">
            <template #item-operation="{ code, type }">
              <div class="dropdown-center">
                <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link"
                  data-bs-toggle="dropdown" id="dropdown-align-center-alt-primary" type="button">
                  <i class="fa-solid fa-ellipsis"></i>
                </button>
                <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                  style="">
                  <router-link class="dropdown-item" :to="'/acc/accounting/view/' + code">
                    <i class="fa fa-file text-success pe-2"></i>
                    سند حسابداری
                  </router-link>
                  <!-- Button trigger modal -->
                  <router-link :to="'/acc/transfer/mod/' + code" class="dropdown-item">
                    <i class="fa fa-eye text-primary pe-2"></i>
                    مشاهده
                  </router-link>
                  <router-link :to="'/acc/transfer/mod/' + code" class="dropdown-item">
                    <i class="fa fa-edit pe-2"></i>
                    ویرایش
                  </router-link>
                  <button type="button" @click="deleteItem(code)" class="dropdown-item text-danger">
                    <i class="fa fa-trash pe-2"></i>
                    حذف
                  </button>
                </div>
              </div>
            </template>
            <template #item-fromType="{ fromType, fromObject }">
              <label v-if="fromType == 'bank'">حساب بانکی: {{ fromObject }}</label>
              <label v-if="fromType == 'salary'">تنخواه گردان: {{ fromObject }}</label>
              <label v-if="fromType == 'cashDesk'">صندوق: {{ fromObject }}</label>
            </template>
            <template #item-toType="{ toType, toObject }">
              <label v-if="toType == 'bank'">حساب بانکی: {{ toObject }}</label>
              <label v-if="toType == 'salary'">تنخواه گردان: {{ toObject }}</label>
              <label v-if="toType == 'cashDesk'">صندوق: {{ toObject }}</label>
            </template>
            <template #item-code="{ code }">
              <router-link class="btn-link" :to="'/acc/accounting/view/' + code">
                {{ code }}
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
import { ref } from "vue";
export default {
  name: "list",
  components: {
   
  },
  data: () => {
    return {
      showTransferModal: {},
      transferQuickDoc:{},
      searchValue: '',
      loading: ref(true),
      items: [],
      headers: [
        { text: "عملیات", value: "operation" },
        { text: "شماره سند", value: "code", sortable: true },
        { text: "تاریخ", value: "date", sortable: true },
        { text: "از", value: "fromType", sortable: true },
        { text: "به", value: "toType", sortable: true },
        { text: "مبلغ", value: "amount", sortable: true },
        { text: "شرح", value: "des", sortable: true },
        { text: "ثبت کننده", value: "submitter", sortable: true },
      ]
    }
  },
  methods: {
    showModal(code) {
      var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('quickTransferView'));
      myModal.show();
    },
    hideModal() {
      var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('quickTransferView'));
      myModal.hide();
    },
    loadData() {
      axios.post('/api/transfer/search',)
        .then((response) => {
          this.items = response.data;
          this.items.forEach((item) => {
            item.amount = this.$filters.formatNumber(item.amount)
          })
          this.loading = false;
        })
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف این مورد مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
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
                text: 'سند انتقال با موفقیت حذف شد.',
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

<style scoped></style>