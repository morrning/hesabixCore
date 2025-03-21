<script lang="ts">
import {defineComponent,ref} from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "ticketList",
  data: ()=>{return {
    printID:'',
    loading : ref(false),
    inputItems:[],
    inputSearchValue: '',
    outputItems:[],
    outputSearchValue: '',
    headers: [
      { text: "عملیات", value: "operation", width: "120" },
      { text: "شماره", value: "code" },
      { text: "تاریخ", value: "date", sortable: true },
      { text: "شماره فاکتور", value: "doc.code", sortable: true, width: "100" },
      { text: "شخص", value: "person.nikename", sortable: true, width: "120" },
      { text: "توضیحات", value: "des", sortable: true, width: "300" },
    ]
  }
  },
  methods: {
    loadData() {
      axios.post('/api/storeroom/tickets/list/input')
        .then((response) => {
          this.inputItems = response.data;
          this.loading = false;
        });
      axios.post('/api/storeroom/tickets/list/output')
        .then((response) => {
          this.outputItems = response.data;
          this.loading = false;
        });
      axios.post('/api/storeroom/tickets/list/input')
        .then((response) => {
          this.inputItems = response.data;
          this.loading = false;
        });
    },
    deleteTicket(type, code) {
      Swal.fire({
        text: 'آیا برای حذف این حواله مطمئن هستید؟',
        icon: 'warning',
        confirmButtonText: 'قبول',
        showCancelButton: true,
        cancelButtonText: 'انصراف'
      }).then((result) => {
        if (result.isConfirmed) {
          this.loading = true;
          axios.post('/api/storeroom/ticket/remove/' + code)
            .then((response) => {
              this.loading = false;
              Swal.fire({
                text: 'حواله انبار حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              }).then((result) => {
                if (type == 'input') {
                  for (let z = 0; z < this.inputItems.length; z++) {
                    if (this.inputItems[z]['code'] == code) {
                      this.inputItems.splice(z, 1);
                    }
                  }
                }
                else if (type == 'output') {
                  for (let z = 0; z < this.outputItems.length; z++) {
                    if (this.outputItems[z]['code'] == code) {
                      this.outputItems.splice(z, 1);
                    }
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
})
</script>

<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="mx-2 fa fa-folder-tree"></i>
        حواله‌های انبار
      </h3>
      <div class="block-options">
        <router-link to="/acc/storeroom/new/ticket/type" class="btn btn-sm btn-primary ms-1">
          <span class="fa fa-plus fw-bolder"></span>
        </router-link>
      </div>
    </div>
    <div class="block-content p-0">
      <div class="col-sm-12 col-md-12 m-0 p-0">
        <ul class="nav nav-pills flex-column flex-sm-row border border-secondary" id="myTab" role="tablist">
          <button class="flex-sm-fill text-sm-center nav-link rounded-0 active" id="profile-tab" data-bs-toggle="tab"
            data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">
            <i class="fa fa-file-export me-2"></i>
            حواله‌های خروج
          </button>
          <button class="flex-sm-fill text-sm-center nav-link rounded-0" id="pays-tab" data-bs-toggle="tab"
            data-bs-target="#pays" type="button" role="tab" aria-controls="pays" aria-selected="false">
            <i class="fa fa-file-import me-2"></i>
            حواله‌های ورود
          </button>
        </ul>
        <div class="tab-content p-0" id="myTabContent">
          <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            <div class="m-1">
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
                <input v-model="outputSearchValue" class="form-control" type="text" placeholder="جست و جو ...">
              </div>
            </div>
            <EasyDataTable table-class-name="customize-table" multi-sort show-index alternating
              :search-value="outputSearchValue" :headers="headers" :items="outputItems" theme-color="#1d90ff"
              header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
              emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
              <template #item-operation="{ code }">
                <div class="dropdown-center">
                  <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link"
                    data-bs-toggle="dropdown" id="dropdown-align-center-alt-primary" type="button">
                    <i class="fa-solid fa-ellipsis"></i>
                  </button>
                  <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                    style="">
                    <router-link class="dropdown-item" :to="'/acc/storeroom/ticket/view/' + code">
                      <i class="fa fa-eye text-success pe-2"></i>
                      مشاهده
                    </router-link>
                    <button type="button" @click="deleteTicket('output', code)" class="dropdown-item text-danger">
                      <i class="fa fa-trash pe-2"></i>
                      حذف
                    </button>
                  </div>
                </div>
              </template>
            </EasyDataTable>
          </div>
          <div class="tab-pane fade" id="pays" role="tabpanel" aria-labelledby="pays-tab">
            <div class="m-1">
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
                <input v-model="inputSearchValue" class="form-control" type="text" placeholder="جست و جو ...">
              </div>
            </div>
            <EasyDataTable table-class-name="customize-table" multi-sort show-index alternating
              :search-value="inputSearchValue" :headers="headers" :items="inputItems" theme-color="#1d90ff"
              header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
              emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
              <template #item-operation="{ code }">
                <div class="dropdown-center">
                  <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link"
                    data-bs-toggle="dropdown" id="dropdown-align-center-alt-primary" type="button">
                    <i class="fa-solid fa-ellipsis"></i>
                  </button>
                  <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                    style="">
                    <router-link class="dropdown-item" :to="'/acc/storeroom/ticket/view/' + code">
                      <i class="fa fa-eye text-success pe-2"></i>
                      مشاهده
                    </router-link>
                    <button type="button" @click="deleteTicket('input', code)" class="dropdown-item text-danger">
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
  </div>
</template>

<style scoped>

</style>