<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa fa-bank px-2"></i>
        تنخواه‌گردان‌ها
      </h3>
      <div class="block-options">
        <router-link to="/acc/salary/mod/" class="block-options-item">
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
            <template #item-operation="{ code }">
              <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link"
                data-bs-toggle="dropdown" id="dropdown-align-center-alt-primary" type="button">
                <i class="fa-solid fa-ellipsis"></i>
              </button>
              <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                style="">
                <router-link class="dropdown-item" :to="'/acc/salary/card/view/' + code">
                  <i class="fa fa-eye text-success pe-2"></i>
                  مشاهده
                </router-link>
                <router-link class="dropdown-item" :to="'/acc/salary/mod/' + code">
                  <i class="fa fa-edit pe-2"></i>
                  ویرایش
                </router-link>
                <button type="button" @click="deleteItem(code)" class="dropdown-item text-danger">
                  <i class="fa fa-trash pe-2"></i>
                  حذف
                </button>
              </div>
            </template>
            <template #item-name="{ name, code }">
              <router-link :to="'/acc/salary/card/view/' + code">
                {{ name }}
              </router-link>
            </template>
            <template #item-balance="{ balance }">
              <label class="text-success" v-if="balance >= 0">{{ $filters.formatNumber(balance) }}</label>
              <label class="text-danger" v-else>{{ $filters.formatNumber(-1 * balance) }} منفی</label>
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
  data: () => {
    return {
      searchValue: '',
      loading: ref(true),
      items: [],
      headers: [
        { text: "عملیات", value: "operation", width: "130" },
        { text: "کد", value: "code", width: "70px" },
        { text: "نام تنخواه‌گردان", value: "name", width: "120px" },
        { text: "موجودی()", value: "balance", width: "140px" },
        { text: "توضیحات", value: "des", width: "150px" },
      ]
    }
  },
  methods: {
    loadData() {
      axios.post('/api/salary/list')
        .then((response) => {
          this.items = response.data;
          this.loading = false;
        })
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف تنخواه‌گردان مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/salary/delete/' + code).then((response) => {
            if (response.data.result == 1) {
              let index = 0;
              for (let z = 0; z < this.items.length; z++) {
                index++;
                if (this.items[z]['code'] == code) {
                  this.items.splice(index - 1, 1);
                }
              }
              Swal.fire({
                text: 'تنخواه‌گردان با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
            else if (response.data.result == 2) {
              Swal.fire({
                text: 'تنخواه‌گردان به دلیل داشتن تراکنش و اسناد حسابداری مرتبط قابل حذف نیست.',
                icon: 'error',
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