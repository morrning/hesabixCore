<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <i class="mx-2 fa fa-cash-register"></i>
        درآمدها
      </h3>
      <div class="block-options">
        <router-link to="/acc/incomes/mod/" class="block-options-item">
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
          <EasyDataTable table-class-name="customize-table" v-model:items-selected="itemsSelected" show-index alternating :search-value="searchValue"
            :headers="headers" :items="items" theme-color="#1d90ff" header-text-direction="center"
            body-text-direction="center" rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
            rowsOfPageSeparatorMessage="از" :loading="loading">
            <template #item-operation="{ code }">
              <div class="dropdown-center">
                <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link"
                  data-bs-toggle="dropdown" id="dropdown-align-center-alt-primary" type="button">
                  <i class="fa-solid fa-ellipsis"></i>
                </button>
                <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                  style="">
                  <router-link class="dropdown-item" :to="'/acc/accounting/view/' + code">
                    <i class="fa fa-file pe-2 text-primary"></i>
                    سند حسابداری
                  </router-link>
                  <router-link class="dropdown-item" :to="{ name: 'incomes_mod', params: { id: code } }">
                    <i class="fa fa-eye pe-2 text-success"></i>
                    مشاهده
                  </router-link>
                  <router-link class="dropdown-item" :to="{ name: 'incomes_mod', params: { id: code } }">
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
import { ref } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "list",
  data: () => {
    return {
      sumSelected: 0,
      sumTotal: 0,
      itemsSelected: [],
      searchValue: '',
      loading: ref(true),
      items: [],
      headers: [
        { text: "عملیات", value: "operation", width: "120" },
        { text: "کد", value: "code", width: "80" },
        { text: "تاریخ", value: "date" },
        { text: "شرح", value: "des" },
        { text: "مبلغ", value: "amount" },
      ]
    }
  },
  methods: {
    loadData() {
      axios.post('/api/accounting/search', {
        type: 'income'
      })
        .then((response) => {
          this.items = response.data;
          this.items.forEach((item) => {
            item.amount = this.$filters.formatNumber(item.amount);
            this.sumTotal += parseInt(item.amount.replaceAll(",", ''));
          })
          this.loading = false;
        })
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای این سند مطمئن هستید؟',
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
                text: 'سند با موفقیت حذف شد.',
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
  },
  watch: {
    itemsSelected: {
      handler: function (val, oldVal) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((item) => {
          this.sumSelected += parseInt(item.amount.replaceAll(",", ""))
        });
      },
      deep: true
    }
  }
}
</script>

<style scoped></style>