<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        قبض تحویل
      </h3>
      <div class="block-options">
        <span class="form-check form-switch  form-check-inline">
          <input :disabled="this.data.person.mobile == ''" v-model="data.sms" class="form-check-input" type="checkbox">
          <label class="form-check-label"> پیامک</label>
        </span>
        <button :disabled="isLoading" @click="save()" type="button" class="btn btn-sm btn-alt-primary">
          <div v-show="this.isLoading" class="spinner-grow spinner-grow-sm me-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <i class="fa fa-floppy-disk"></i>
          ثبت
        </button>
      </div>
    </div>
    <div class="block-content py-3 vl-parent px-0">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false" />
      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-4 mb-1">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa fa-calendar"></i>
                  تاریخ
                </h3>
                <div class="block-options">

                </div>
              </div>
              <div class="block-content pt-1 px-1">
                <p>
                  <date-picker class="my-0 py-0" v-model="data.date" format="jYYYY/jMM/jDD"
                    display-format="jYYYY/jMM/jDD" :min="year.start" :max="year.end" />
                </p>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-4 mb-1">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa fa-person"></i>
                  مشتری
                </h3>
                <div class="block-options">
                  <quickView v-if="this.data.person != null && this.data.person.code != '0'"
                    :code="this.data.person.code"></quickView>
                  <quickAdd :code="'0'"></quickAdd>
                </div>
              </div>
              <div class="block-content pt-1 px-1">
                <v-cob class="mb-1 mx-0" :filterable="false" dir="rtl" @search="searchPerson" :options="persons"
                  label="nikename" v-model="data.person">
                  <template #no-options="{ search, searching, loading }">
                    نتیجه‌ای یافت نشد!
                  </template>
                  <template v-slot:option="option">
                    <div class="row mb-1">
                      <div class="col-6">
                        <i class="fa fa-user me-2"></i>
                        {{ option.nikename }}
                      </div>
                      <div v-if="option.mobile != ''" class="col-6">
                        <i class="fa fa-phone me-2 text-success"></i>
                        {{ option.mobile }}
                      </div>
                    </div>
                  </template>
                </v-cob>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-4 mb-1">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa fa-box pe-2"></i>
                  کالا و خدمات
                </h3>
                <div class="block-options">
                  <!-- Button trigger modal -->
                  <quickAddCommodity></quickAddCommodity>
                </div>
              </div>
              <div class="block-content pt-1 px-1">
                <v-cob dir="rtl" @search="searchCommodity" :options="commodity" label="name" v-model="data.commodity"
                  class="">
                  <template #no-options="{ search, searching, loading }">
                    نتیجه‌ای یافت نشد!
                  </template>
                  <template v-slot:option="option">
                    <div class="row mb-1">
                      <div class="col-12">
                        <i class="fa fa-box me-1"></i>
                        {{ option.name }}
                      </div>
                    </div>
                  </template>
                </v-cob>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa-regular fa-note-sticky"></i>
                  شرح
                </h3>
                <div class="block-options">
                  <mostdes :submitData="desSubmit" type="repservice"></mostdes>
                </div>
              </div>
              <div class="block-content p-0">
                <input v-model="data.des" class="form-control" type="text">
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 mb-2">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa-regular fa-note-sticky"></i>
                  متعلقات
                </h3>
                <div class="block-options">

                </div>
              </div>
              <div class="block-content p-0">
                <input v-model="this.data.motaleghat" class="form-control" type="text">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row">
          <div class="col-sm-6 col-md-4 mb-2">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa-regular fa-note-sticky"></i>
                  مدل
                </h3>
                <div class="block-options">

                </div>
              </div>
              <div class="block-content p-0">
                <input v-model="this.data.model" class="form-control" type="text">
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 mb-2">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa-regular fa-note-sticky"></i>
                  رنگ
                </h3>
                <div class="block-options">

                </div>
              </div>
              <div class="block-content p-0">
                <input v-model="this.data.color" class="form-control" type="text">
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 mb-2">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa-regular fa-note-sticky"></i>
                  سریال
                </h3>
                <div class="block-options">

                </div>
              </div>
              <div class="block-content p-0">
                <input v-model="this.data.serial" class="form-control" type="text">
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 mb-2">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa-regular fa-note-sticky"></i>
                  پلاک
                </h3>
                <div class="block-options">

                </div>
              </div>
              <div class="block-content p-0">
                <input v-model="this.data.pelak" class="form-control" type="text">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import axios from "axios";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import { Money3 } from "v-money3";
import quickAdd from "../../component/person/quickAdd.vue";
import quickAddCommodity from "../../component/commodity/quickAddCommodity.vue";
import mostdes from "../../component/mostdes.vue";
import quickView from "../../component/person/quickView.vue";
export default {
  name: "mod",
  components: {
    Loading,
    Money3,
    quickView,
    quickAdd,
    quickAddCommodity,
    mostdes
  },
  data: () => {
    return {
      isLoading: true,
      desSubmit: {
        id: '',
        des: ''
      },
      data: {
        update: '',
        date: '',
        dateOut: '',
        color: '',
        model: '',
        des: '',
        person: {
          nikename: '',
          mobile: '',
          code: '0'
        },
        pelak: '',
        serial: '',
        sms: false,
        motaleghat: '',
        commodity: {
          id: '',
          name: '',
          unit: '',
          unitData: {
            name: '',
            floatNumber: 0
          }
        },
      },
      year: '',
      persons: [],
      commodity: [],
    }
  },
  mounted() {
    this.loadData(this.$route.params.id);
  },
  beforeRouteUpdate(to, from) {
    this.loadData(to.params.id);
  },
  methods: {
    searchPerson(query, loading) {
      loading(true);
      axios.post('/api/person/list/search', { search: query }).then((response) => {
        this.persons = response.data;
        loading(false);
      });
    },
    searchCommodity(query, loading) {
      loading(true);
      axios.post('/api/commodity/list/search', { search: query }).then((response) => {
        this.commodity = response.data;
        loading(false);
      });
    },
    loadData() {
      //load year
      if (this.$route.params.id == '') {
        axios.post('/api/year/get').then((response) => {
          this.data.date = response.data.now;
        });
      }
      //load persons
      axios.post('/api/person/list/search').then((response) => {
        this.persons = response.data;
      });
      //load commodities
      axios.post('/api/commodity/list/search').then((response) => {
        this.commodity = response.data;
      });
      //load data for edit document
      if (this.$route.params.id != '') {
        axios.post('/api/repservice/order/info/' + this.$route.params.id).then((response) => {
          this.data = response.data;
        });
      }
      this.isLoading = false;
    },
    save() {
      let canSubmit = true;
      this.isLoading = true;
      if (this.data.commodity == null || this.data.commodity.id == '' || this.data.commodity.id == undefined) {
        Swal.fire({
          text: 'کالا انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        canSubmit = false;
      }
      else if (this.data.person == null || this.data.person.code == '0' || this.data.person.code == undefined) {
        Swal.fire({
          text: 'شخص انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        canSubmit = false;
      }
      if (canSubmit == true) {
        axios.post("/api/plug/repservice/order/mod", this.data).then((response) => {
          this.isLoading = false;
          if (response.data.code == '0') {
            Swal.fire({
              text: 'درخواست ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then((res) => {
              this.$router.push('/acc/plugin/repservice/order/list');
            });
          }
          else if (response.data.code == '11') {
            Swal.fire({
              text: ' درخواست ثبت شد.اما به دلیل کمبود اعتبار پیامک ارسال نشد.',
              icon: 'warning',
              confirmButtonText: 'قبول'
            }).then((res) => {
              this.$router.push('/acc/plugin/repservice/order/list')
            });
          }
          else {
            Swal.fire({
              text: response.data.message,
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          }
        })
      }
      else {
        this.isLoading = false;
      }
    }

  },
  watch: {
    'desSubmit.id': function () {
      this.data.des = this.desSubmit.des;
    },
    'data.person': function () {
      if (this.data.person.mobile == '') {
        this.data.sms = false;
      }
      else {
        this.data.sms = true;
      }
    },
  }
}
</script>

<style scoped></style>