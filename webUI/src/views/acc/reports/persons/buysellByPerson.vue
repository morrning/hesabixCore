<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="fa-solid fa-chart-simple px-2"></i>
        گزارش خرید و فروش های اشخاص
      </h3>
      <div class="block-options">
        <div hidden class="dropdown">
          <a class="btn btn-sm btn-danger ms-2 dropdown-toggle text-end" href="#" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-file-pdf"></i>
          </a>
          <ul class="dropdown-menu">
            <li><a @click.prevent="print(false)" class="dropdown-item" href="#">انتخاب شده‌ها</a></li>
            <li><a @click.prevent="print(true)" class="dropdown-item" href="#">همه موارد</a></li>
          </ul>
        </div>
        <div class="dropdown">
          <a class="btn btn-sm btn-success ms-2 dropdown-toggle text-end" href="#" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-file-excel"></i>
          </a>
          <ul class="dropdown-menu">
            <li><a @click.prevent="excellOutput(false)" class="dropdown-item" href="#">انتخاب شده‌ها</a></li>
            <li><a @click.prevent="excellOutput(true)" class="dropdown-item" href="#">همه موارد</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="block-content pt-1 pb-3">
            <div class="row">
              <div class="col-sm-12 col-md-6">
                <div class="mb-2">
                  <label class="form-label">شخص</label>
                  <v-cob :filterable="false" dir="rtl" @search="searchPerson" :options="persons" label="nikename"
                    v-model="selectedPerson">
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                    <template v-slot:option="option">
                      <div class="row mb-1">
                        <div class="col-12">
                          <i class="fa fa-user me-2"></i>
                          {{ option.nikename }}
                        </div>
                        <div class="col-12">
                          <div class="row">
                            <div class="col-6">
                              <i class="fa fa-phone me-2"></i>
                              {{ option.mobile }}
                            </div>
                            <div class="col-6">
                              <i class="fa fa-bars"></i>
                              تراز:
                              {{ $filters.formatNumber(Math.abs(parseInt(option.bs) -
          parseInt(option.bd))) }}
                              <span class="text-danger" v-if="parseInt(option.bs) - parseInt(option.bd) < 0">
                                بدهکار </span>
                              <span class="text-success" v-if="parseInt(option.bs) - parseInt(option.bd) > 0">
                                بستانکار </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </template>
                  </v-cob>
                </div>
              </div>
              <div class="col-sm-12 col-md-6">
                <div class="mb-2">
                  <label class="form-label">نوع</label>
                  <v-cob dir="rtl" :options="types" label="label" v-model="selectedType">
                    <template #no-options="{ search, searching, loading }">
                      نتیجه‌ای یافت نشد!
                    </template>
                  </v-cob>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12 col-md-6 mb-2">
                <div class="">
                  <label class="form-label">تاریخ شروع:</label>
                  <date-picker class="" v-model="dateStart" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
                    :min="year.start" :max="year.end" />
                </div>
              </div>
              <div class="col-sm-12 col-md-6 mb-2">
                <div class="">
                  <label class="form-label">تاریخ پایان:</label>
                  <date-picker class="" v-model="dateEnd" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
                    :min="dateStart" :max="year.end" />
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-12 col-md-12 m-0 p-0">
                <div class="mb-1">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
                  </div>
                </div>
                <EasyDataTable table-class-name="customize-table" v-model:items-selected="itemsSelected" multi-sort show-index alternating
                  :search-value="searchValue" :headers="headers" :items="items" theme-color="#1d90ff"
                  header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
                  emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
                  <template #item-khadamat="{ khadamat }">
                    <label v-if="khadamat == false">کالا</label>
                    <label v-else>خدمات</label>
                  </template>
                  <template #item-docCode="{ docCode }">
                    <RouterLink :to="'/acc/accounting/view/' + docCode">{{ docCode }}</RouterLink>
                  </template>
                  <template #item-type="{ docCode, type }">
                    <RouterLink class="text-success" v-if="type == 'buy'" :to="'/acc/buy/view/' + docCode">خرید
                    </RouterLink>
                    <RouterLink class="text-danger" v-else-if="type == 'sell'" :to="'/acc/sell/view/' + docCode">فروش
                    </RouterLink>
                    <RouterLink class="text-info" v-else-if="type == 'rfbuy'" :to="'/acc/rfbuy/view/' + docCode">برگشت
                      از خرید</RouterLink>
                    <RouterLink class="text-warning" v-else-if="type == 'rfsell'" :to="'/acc/rfsell/view/' + docCode">
                      برگشت از فروش</RouterLink>
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
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";
import { RouterLink } from "vue-router";

export default {
  name: "buysellByPerson",
  data: () => {
    return {
      loading: ref(true),
      sumSelected: 0,
      sumTotal: 0,
      year: {},
      persons: [],
      types: [
        { id: 'buy', label: 'خرید' },
        { id: 'sell', label: 'فروش' },
        { id: 'rfbuy', label: 'برگشت از خرید' },
        { id: 'rfsell', label: 'برگشت از فروش' },
        { id: 'all', label: 'همه موارد' },
      ],
      dateStart: '',
      dateEnd: '',
      selectedType: {},
      selectedPerson: {},
      searchValue: '',
      loading: ref(true),
      items: [],
      itemsSelected: [],
      headers: [
        { text: "کد", value: "code" },
        { text: "سند حسابداری", value: "docCode" },
        { text: "نوع", value: "type" },
        { text: "تاریخ", value: "date" },
        { text: "کالا / خدمات", value: "khadamat", sortable: true },
        { text: "نام کالا و خدمات", value: "name", sortable: true },
        { text: "واحد شمارش", value: "unit", sortable: true },
        { text: "تعداد", value: "count", sortable: true },
        { text: "مبلغ فی", value: "priceOne", sortable: true },
        { text: "مبلغ کل", value: "priceAll", sortable: true },
        { text: "تجمعی", value: "amountInc", sortable: true },
      ],
    }
  },
  methods: {
    searchPerson(query, loading) {
      loading(true);
      axios.post('/api/person/list/search', { search: query }).then((response) => {
        this.persons = response.data;
        loading(false);
      });
    },
    loadData() {
      axios.post('/api/person/list/search')
        .then((response) => {
          this.persons = response.data;
          if (this.persons.length != 0) {
            this.selectedPerson = this.persons[0];
          }
          this.selectedType = this.types[0];
          this.loading = false;
        });

      axios.post('/api/year/get')
        .then((response) => {
          this.year = response.data;
          this.loading = false;
        });
    },
    filter() {
      this.loading = true;
      this.itemsSelected = [];
      axios.post('/api/report/person/buysell', {
        type: this.selectedType.id,
        person: this.selectedPerson.code,
        dateStart: this.dateStart,
        dateEnd: this.dateEnd
      }).then((response) => {
        this.items = response.data;
        let sum = 0;
        this.sumTotal = 0;
        this.items.forEach((item) => {
          sum += parseInt(item.priceAll.replaceAll(',', ''));
          item.amountInc = this.$filters.formatNumber(sum);
        });
        this.sumTotal = sum;
        this.loading = false;
      })
    },
    excellOutput(AllItems = true) {
      if (AllItems) {
        axios({
          method: 'post',
          url: '/api/report/person/buysell/export/excel',
          responseType: 'arraybuffer',
          data: { items: this.items }
        }).then((response) => {
          var FILE = window.URL.createObjectURL(new Blob([response.data]));
          var fileURL = window.URL.createObjectURL(new Blob([response.data]));
          var fileLink = document.createElement('a');

          fileLink.href = fileURL;
          fileLink.setAttribute('download', 'buysell-report-list.xlsx');
          document.body.appendChild(fileLink);
          fileLink.click();
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
          axios({
            method: 'post',
            url: '/api/report/person/buysell/export/excel',
            responseType: 'arraybuffer',
            data: { items: this.itemsSelected }
          }).then((response) => {
            var FILE = window.URL.createObjectURL(new Blob([response.data]));
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');

            fileLink.href = fileURL;
            fileLink.setAttribute('download', 'buysell-report-list.xlsx');
            document.body.appendChild(fileLink);
            fileLink.click();
          })
        }
      }
    },
    print(AllItems = true) {
      if (AllItems) {
        axios.post('/api/person/list/print').then((response) => {
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
          axios.post('/api/person/list/print', { items: this.itemsSelected }).then((response) => {
            this.printID = response.data.id;
            window.open(this.$API_URL + '/front/print/' + this.printID, '_blank', 'noreferrer');
          })
        }
      }
    }
  },
  beforeMount() {
    this.loadData();
  },
  watch: {
    dateStart: {
      handler: function (val, oldVal) {
        this.filter();
      },
      deep: false
    },
    dateEnd: {
      handler: function (val, oldVal) {
        this.filter();
      },
      deep: false
    },
    selectedPerson: {
      handler: function (val, oldVal) {
        this.filter();
      },
      deep: true
    },
    selectedType: {
      handler: function (val, oldVal) {
        this.filter();
      },
      deep: true
    },
    itemsSelected: {
      handler: function (val, oldVal) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((item) => {
          this.sumSelected += parseInt(item.priceAll.replaceAll(",", ""))
        });
      },
      deep: true
    }
  },
}
</script>

<style scoped></style>