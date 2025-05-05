<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa-solid fa-chart-simple px-2"></i>
        گزارش بستانکاران
      </h3>
      <div class="block-options">
        <div class="dropdown">
          <a class="btn btn-sm btn-danger ms-2 dropdown-toggle text-end" href="#" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-file-pdf"></i>
          </a>
          <ul class="dropdown-menu">
            <li><a @click.prevent="print(false)" class="dropdown-item" href="#">انتخاب شده‌ها</a></li>
            <li><a @click.prevent="print(true)" class="dropdown-item" href="#">همه موارد</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="block-content pt-1 pb-3">
            <div class="row">
              <div class="col-sm-12 col-md-12 m-0 p-0">
                <div class="mb-1">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
                  </div>
                </div>
                <EasyDataTable table-class-name="customize-table" v-model:items-selected="itemsSelected" multi-sort
                  show-index alternating :search-value="searchValue" :headers="headers" :items="items"
                  theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
                  rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
                  rowsOfPageSeparatorMessage="از" :loading="loading">
                  <template #item-nikename="{ nikename, code }">
                    <router-link :to="'/acc/persons/card/view/' + code">
                      {{ nikename }}
                    </router-link>
                  </template>
                  <template #item-speedAccess="{ speedAccess }">
                    <i v-if="speedAccess" class="fa fa-check text-success"></i>
                  </template>
                  <template #item-status="{ balance }">
                    <span v-if="balance < 0" class="text-danger">بدهکار</span>
                    <span v-if="balance > 0" class="text-success">بستانکار</span>
                  </template>
                  <template #item-bs="{ bs }">
                    <span>{{ $filters.formatNumber(bs) }}</span>
                  </template>
                  <template #item-bd="{ bd }">
                    <span>{{ $filters.formatNumber(bd) }}</span>
                  </template>
                  <template #item-balance="{ balance }">
                    <span style="direction:ltr;">{{ $filters.formatNumber(balance) }}</span>
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
                            جمع بستانکار :
                          </span>
                          <span class="text-primary">
                            {{ $filters.formatNumber(this.sumTotal) }}
                            {{ $filters.getActiveMoney().shortName }}
                          </span>
                        </div>

                        <div class="col-sm-6 com-md-6">
                          <span class="text-dark">
                            <i class="fa fa-list-check"></i>
                            جمع بستانکاری موارد انتخابی:
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
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "depositors",
  data: () => {
    return {
      sumSelected: 0,
      sumTotal: 0,
      itemsSelected: [],
      searchValue: '',
      loading: ref(true),
      items: [],
      itemsSelected: [],
      headers: [
        { text: "کد", value: "code" },
        { text: "نام مستعار", value: "nikename", sortable: true, width: 150 },
        { text: "تراز حساب", value: "balance", sortable: true, width: 100 },
        { text: "وضعیت حساب", value: "status", sortable: true, width: 110 },
        { text: "بستانکار", value: "bs", sortable: true, width: 100 },
        { text: "نام و نام خانوادگی", value: "name", sortable: true, width: 150 },
        { text: "تاریخ تولد/ثبت", value: "birthday", sortable: true, width: 150 },
        { text: "شرکت", value: "company", sortable: true, width: 100 },
        { text: "شناسه ملی", value: "shenasemeli", sortable: true, width: 100 },
        { text: "کد اقتصادی", value: "codeeghtesadi", sortable: true, width: 100 },
        { text: "شماره ثبت", value: "sabt", sortable: true, width: 100 },
        { text: "کشور", value: "keshvar", sortable: true, width: 100 },
        { text: "استان", value: "ostan", sortable: true, width: 100 },
        { text: "شهر", value: "shahr", sortable: true, width: 100 },
        { text: "کد پستی", value: "postalcode", sortable: true, width: 100 },
        { text: "تلفن", value: "tel", width: 100 },
        { text: "تلفن همراه", value: "mobile", width: 100 },
        { text: "ایمیل", value: "email", sortable: true, width: 100 },
        { text: "وب سایت", value: "website", sortable: true, width: 100 },
        { text: "فکس", value: "fax", sortable: true, width: 100 },
      ]
    }
  },
  watch: {
    itemsSelected: {
      handler: function (val, oldVal) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((item) => {
          this.sumSelected += (item.bs - item.bd);
        });
      },
      deep: true
    }
  },
  methods: {
    loadData() {
      axios.post('/api/person/list/depositors/0')
        .then((response) => {
          this.items = response.data;
          this.items.forEach((item) => {
            this.sumTotal += (item.bs - item.bd);
          })
          this.loading = false;
        })
    },
    print(AllItems = true) {
      if (AllItems) {
        axios.post('/api/person/list/depositors/print/0').then((response) => {
          this.printID = response.data.id;
          window.open(this.$API_URL + '/front/print/' + this.printID, '_blank', 'noreferrer');
        })
      }
      else {
        if (this.itemsSelected.length === 0) {
          Swal.fire({
            text: 'هیچ آیتمی انتخاب نشده است.',
            icon: 'info',
            confirmButtonText: 'قبول'
          });
        }
        else {
          axios.post('/api/person/list/depositors/print/0', { items: this.itemsSelected }).then((response) => {
            this.printID = response.data.id;
            window.open(this.$API_URL + '/front/print/' + this.printID, '_blank', 'noreferrer');
          })
        }
      }
    },
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<style scoped></style>