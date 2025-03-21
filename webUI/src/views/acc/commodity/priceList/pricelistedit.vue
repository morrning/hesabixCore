<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="mx-2 fa fa-list"></i>
        لیست قیمت کالا و خدمات 
      </h3>
      <div class="block-options">
        <router-link to="/acc/commodity/pricelist/mod/" class="btn btn-sm btn-primary ms-1" title="ایجاد لیست جدید">
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
          <EasyDataTable table-class-name="customize-table" multi-sort show-index alternating
            :search-value="searchValue" :headers="headers" :items="items" theme-color="#1d90ff"
            header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
            emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
            <template #item-operation="{ id }">
              <div class="dropdown-center">
                <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link" data-bs-toggle="dropdown"
                  id="dropdown-align-center-alt-primary" type="button">
                  <i class="fa-solid fa-ellipsis"></i>
                </button>
                <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                  style="">
                  <router-link class="dropdown-item" :to="'/acc/commodity/pricelist/mod/' + id">
                    <i class="fa fa-edit pe-2"></i>
                    ویرایش
                  </router-link>
                  <button type="button" @click="deleteItem(id)" class="dropdown-item text-danger">
                    <i class="fa fa-trash pe-2"></i>
                    حذف
                  </button>
                </div>
              </div>
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
      loading: ref(true),
      searchValue: '',
      items: [],
      headers: [
        { text: "نام ویژگی", value: "label", sortable: true },
        { text: "عملیات", value: "operation" },
      ]
    }
  },
  methods: {
    loadData() {
      axios.post('/api/commodity/pricelist/list')
        .then((response) => {
          this.items = response.data;
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
          axios.post('/api/commodity/pricelist/delete/' + code).then((response) => {
            if (response.data.result == 1) {
              let index = 0;
              for (let z = 0; z < this.items.length; z++) {
                index++;
                if (this.items[z]['id'] == code) {
                  this.items.splice(index - 1, 1);
                }
              }
              Swal.fire({
                text: 'فهرست با موفقیت حذف شد.',
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