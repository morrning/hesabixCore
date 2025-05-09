<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa-solid fa-money-bill"></i>
        ارز‌های جانبی
      </h3>
      <div class="block-options">
        <button type="button" class="block-options-item btn btn-link border-0" title="افزودن ارز جدید" data-bs-toggle="modal"
          data-bs-target="#exampleModal">
          <span class="fa fa-plus fw-bolder"></span>
        </button>
      </div>
    </div>
    <div class="block-content p-0">
      <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <loading color="blue" loader="dots" v-model:active="loadingAdd" :is-full-page="false" />
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel">افزودن واحد ارزی جدید</h1>
              <div class="block-options">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
            </div>
            <div class="modal-body">
              <v-cob class="mb-1 mx-0" :filterable="true" dir="rtl" :options="moneyAll" label="label"
                v-model="selectedMoney">
                <template #no-options="{ search, searching, loading }">
                  نتیجه‌ای یافت نشد!
                </template>
                <template v-slot:option="option">
                  <div class="row">
                    <div class="col-12 p-1">
                      <i class="fa fa-flag me-2"></i>
                      {{ option.label }} >
                      {{ option.name }}
                    </div>
                  </div>
                </template>
              </v-cob>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بازگشت</button>
              <button type="button" @click="save()" class="btn btn-primary">
                <i class="fa fa-save me-1"></i>
                افزودن
              </button>
            </div>
          </div>
        </div>
      </div>

      <table class="table table-striped table-vcenter">
        <thead>
          <tr>
            <th class="text-center" style="width: 50px;">#</th>
            <th class="text-center">واحد پولی</th>
            <th class="text-center">اختصار</th>
            <th class="text-center" style="width: 100px;">عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, key) in moneys">
            <th class="text-center" scope="row">{{ key + 1 }}</th>
            <td class="text-center fw-semibold">
              {{ item.label }}
            </td>
            <td class="text-center">
              {{ item.name }}
            </td>
            <td class="text-center">
              <button v-if="item.name != bid.arzmain.name" @click="deleteItem(item.name)"
                class="btn btn-sm btn-alt-secondary" type="button" title=" حذف ارز ">
                <i class="fa fa-times"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>

import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";
import { getApiUrl, getSiteName } from "@/hesabixConfig"
import Loading from "vue-loading-overlay";

export default {
  name: "avatar",
  components: {
    Loading
  },
  data: () => {
    return {
      loading: true,
      loadingAdd: false,
      moneys: [],
      moneyAll: [],
      bid: {},
      selectedMoney: {},
      showModal: false,
      modal: ''
    }
  },
  mounted() {
    this.modal = new bootstrap.Modal('#exampleModal');
  },
  methods: {
    loadData() {
      axios.post('/api/business/get/info/' + localStorage.getItem('activeBid'))
        .then((response) => {
          this.loading = false;
          this.moneys = response.data.moneys;
          this.bid = response.data;
          this.selectedMoney = response.data.arzmain;
        });
      axios.post('/api/money/get/all')
        .then((response) => {
          this.moneyAll = response.data.data;
        })
    },
    save() {
      //this.loading = true;
      if (this.selectedMoney == null) {
        Swal.fire({
          text: 'گذینه‌ای انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      let canAdd = true;
      this.moneys.forEach((item) => {
        if (item.name == this.selectedMoney.name) {
          canAdd = false;
          Swal.fire({
            text: 'این ارز قبلا اضافه شده است.',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
        }
      })

      if (canAdd) {
        this.loadingAdd = true;
        axios.post('/api/money/add/to/business', {
          'name': this.selectedMoney.name
        }).then((response) => {
          this.loadData();
          this.loadingAdd = false;
          this.modal.hide();
          Swal.fire({
            text: 'با موفقیت افزوده شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          });
        })
      }
    },
    deleteItem(name) {
      Swal.fire({
        text: 'این عملیات غیر قابل بازگشت است. تنها در صورتی قادر خواهید بود یک ارز را حذف نمایید که هیچ سند حسابداری با این ارز در کسب و کار شما ثبت نشده باشد.آیا مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          this.loading = true;
          axios.post('/api/money/remove', {
            'name': name
          }
          ).then((response) => {
            this.loading = false;
            if (response.data.Success == 1) {
              this.loadData();
              Swal.fire({
                text: ' با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
            else if (response.data.Success == false) {
              Swal.fire({
                text: response.data.message,
                icon: 'warning',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    },
  },
  beforeMount() {
    this.loadData();
  }
}
</script>
<style scoped></style>