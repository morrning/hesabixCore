<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        دریافت از اشخاص
      </h3>
      <div class="block-options">
        <button :disabled="this.canSubmit != true" @click="save()" type="button" class="btn btn-sm btn-alt-primary">
          <i class="fa fa-save"></i>
          ثبت
        </button>
      </div>
    </div>
    <div class="block-content py-3 px-0 vl-parent">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false" />
      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-6 mb-2">
            <date-picker class="form-control" v-model="data.date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
              :min="year.start" :max="year.end" />
          </div>
          <div class="col-sm-12 col-md-6 mb-2">
            <div class="alert alert-sm alert-info">
              <i class="fa fa-info-circle me-2"></i>
              دکمه ثبت بعد از صفر بودن مبلغ باقی مانده فعال می شود
            </div>
          </div>
          <div class="col-sm-12 col-md-12">
            <div class="block block-rounded border">
              <div class="block-header block-header-default py-1">
                <h3 class="block-title text-primary">
                  <i class="fa-regular fa-note-sticky"></i>
                  شرح
                </h3>
                <div class="block-options">
                  <mostdes :submitData="desSubmit" type="personRecive"></mostdes>
                </div>
              </div>
              <div class="block-content p-0">
                <input v-model="data.des" class="form-control" type="text">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12 col-md-6 px-1" v-for="(item, index) in persons">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-default-dark">
                <h3 class="block-title">
                  <small class="text-white">
                    <span class="text-danger mx-2">{{ index + 1 }}</span>
                    <i class="fa fa-user"></i>
                    مشخصات شخص
                  </small>
                </h3>
                <span class="block-options">
                  <quickAdd :code="0"></quickAdd>
                  <button title="حذف" class="btn-block-option text-danger ps-2" @click="removePerson(index)">
                    <i class="fa fa-trash"></i>
                  </button>
                </span>
              </div>
              <div class="block-content-sm mx-2">
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="row">
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">شخص</small>
                        <v-cob dir="rtl" @search="searchPerson" :options="listPersons" label="nikename"
                          v-model="item.id" @option:deselecting="funcCanSubmit()" @option:selecting="funcCanSubmit()">
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
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="form-floating my-2">
                      <input v-model="item.des" type="text" class="form-control">
                      <label>شرح</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 px-1" v-for="(item, index) in banks">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-warning">
                <h3 class="block-title">
                  <small class="text-black">
                    <span class="mx-2">{{ index + 1 }}</span>
                    <i class="fa fa-bank"></i>
                    حساب بانکی
                  </small>
                </h3>
                <span class="block-options">
                  <button title="حذف" class="btn-block-option text-danger ps-2" @click="removeBank(index)">
                    <i class="fa fa-trash"></i>
                  </button>
                </span>
              </div>
              <div class="block-content-sm mx-2">
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="row">
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">بانک</small>
                        <v-cob dir="rtl" :options="listBanks" label="name" v-model="item.id"
                          @option:deselecting="funcCanSubmit()" @search:focus="funcCanSubmit()"
                          @option:selecting="funcCanSubmit()">
                          <template #no-options="{ search, searching, loading }">
                            نتیجه‌ای یافت نشد!
                          </template>
                        </v-cob>
                      </div>
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="form-floating my-2">
                      <input v-model="item.des" type="text" class="form-control">
                      <label>شرح</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 px-1" v-for="(item, index) in salarys">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-info">
                <h3 class="block-title">
                  <small class="text-black">
                    <span class="mx-2">{{ index + 1 }}</span>
                    <i class="fa fa-dot-circle"></i>
                    تنخواه گردان
                  </small>
                </h3>
                <span class="block-options">
                  <button title="حذف" class="btn-block-option text-danger ps-2" @click="removeSalary(index)">
                    <i class="fa fa-trash"></i>
                  </button>
                </span>
              </div>
              <div class="block-content-sm mx-2">
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="row">
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">تنخواه گردان</small>
                        <v-cob dir="rtl" :options="listSalarys" label="name" v-model="item.id"
                          @option:deselecting="funcCanSubmit()" @search:focus="funcCanSubmit()"
                          @option:selecting="funcCanSubmit()">
                          <template #no-options="{ search, searching, loading }">
                            نتیجه‌ای یافت نشد!
                          </template>
                        </v-cob>
                      </div>
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="form-floating my-2">
                      <input v-model="item.des" type="text" class="form-control">
                      <label>شرح</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-6 px-1" v-for="(item, index) in cashdesks">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-light">
                <h3 class="block-title">
                  <small class="text-black">
                    <span class="mx-2">{{ index + 1 }}</span>
                    <i class="fa fa-money-bill-wheat"></i>
                    صندوق
                  </small>
                </h3>
                <span class="block-options">
                  <button title="حذف" class="btn-block-option text-danger ps-2" @click="removeCashdesk(index)">
                    <i class="fa fa-trash"></i>
                  </button>
                </span>
              </div>
              <div class="block-content-sm mx-2">
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="row">
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">صندوق</small>
                        <v-cob dir="rtl" :options="listCashdesks" label="name" v-model="item.id"
                          @option:deselecting="funcCanSubmit()" @search:focus="funcCanSubmit()"
                          @option:selecting="funcCanSubmit()">
                          <template #no-options="{ search, searching, loading }">
                            نتیجه‌ای یافت نشد!
                          </template>
                        </v-cob>
                      </div>
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="form-floating my-2">
                      <input v-model="item.des" type="text" class="form-control">
                      <label>شرح</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12 text-end">
            <div class="row">
              <div class="col-6 text-end">
                <button @click="addPerson()" class="btn btn-primary mx-1">
                  <i class="fa fa-plus"></i>
                  افزودن شخص
                </button>
              </div>
              <div class="col-6 text-start">
                <div class="dropdown dropup">
                  <button aria-expanded="false" aria-haspopup="true" class="btn btn-danger dropdown-toggle"
                    data-bs-toggle="dropdown" id="dropdown-dropup-secondary" type="button"> افزودن حساب </button>
                  <div aria-labelledby="dropdown-dropup-secondary" class="border border-danger dropdown-menu" style="">
                    <button @click="addBank()" type="button" class="dropdown-item">
                      <i class="fa fa-bank"></i>
                      حساب بانکی
                    </button>
                    <button @click="addCashdesk()" type="button" class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-money-bill-wheat"></i>
                      صندوق
                    </button>
                    <button @click="addSalary()" type="button" class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-dot-circle"></i>
                      تنخواه گردان
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="container border border-danger rounded-2 my-3 p-3">
          <div class="row">
            <div class="row">
              <div class="col-12 border-bottom border-danger">
                مجموع دریافت‌ها:
                <span class="text-danger">{{ $filters.formatNumber(sum) }}</span>
              </div>
            </div>
            <div class="row">
              <div class="col-12 border-top border-danger">
                باقی‌مانده:
                <span class="text-danger">{{ $filters.formatNumber(balance) }}</span>
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
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import VuePersianDatetimePicker from 'vue-persian-datetime-picker'
import quickAdd from "../../component/person/quickAdd.vue";
import mostdes from "../../component/mostdes.vue";
export default {
  name: "mod",
  components: {
    Loading,
    quickAdd,
    mostdes
  },
  data: () => {
    return {
      desSubmit: {
        id: '',
        des: ''
      },
      isLoading: false,
      canSubmit: false,
      updateID: null,
      sum: 0,
      balance: 0,
      listPersons: [],
      listBanks: [],
      listCashdesks: [],
      listSalarys: [],
      persons: [],
      banks: [],
      salarys: [],
      cashdesks: [],
      year: '',
      currencyConfig: {
        masked: false,
        prefix: '',
        suffix: 'ریال',
        thousands: ',',
        decimal: '.',
        precision: 0,
        disableNegative: false,
        disabled: false,
        min: 0,
        max: null,
        allowBlank: false,
        minimumNumberOfCharacters: 0,
        shouldRound: true,
        focusOnRight: true,
      },
      data: {
        date: '',
        des: '',
      },
    }
  },
  watch: {
    'desSubmit.id': function () {
      this.data.des = this.desSubmit.des;
    },
  },
  mounted() {
    this.loadData();
  },
  beforeRouteUpdate(to, from) {
    this.loadData(to.params.id);
  },
  methods: {
    searchPerson(query, loading) {
      loading(true);
      axios.post('/api/person/list/search', { search: query }).then((response) => {
        this.listPersons = response.data;
        loading(false);
      });
    },
    calc() {

      this.sum = 0;
      this.persons.forEach((item) => {
        this.sum = parseInt(this.sum) + parseInt(item.amount);
      });
      let side = 0;
      this.banks.forEach((item) => {
        side = parseInt(side) + parseInt(item.amount);
      });
      this.salarys.forEach((item) => {
        side = parseInt(side) + parseInt(item.amount);
      });
      this.cashdesks.forEach((item) => {
        side = parseInt(side) + parseInt(item.amount);
      });
      this.balance = parseInt(this.sum) - parseInt(side);
      this.funcCanSubmit();

    },
    funcCanSubmit() {
      //check form can submit
      if (
        parseInt(this.balance) == 0 && this.sum > 0
      ) {
        this.canSubmit = true;
      }
      else {
        this.canSubmit = false;
      }
    },
    addPerson() {
      this.persons.push({
        id: '',
        amount: '',
        des: ''
      })
    },
    removePerson(index) {
      this.persons.splice(index, 1);
    },
    addBank() {
      this.banks.push({
        person: '',
        amount: '',
        des: ''
      })
    },
    removeBank(index) {
      this.banks.splice(index, 1);
    },
    addCashdesk() {
      this.cashdesks.push({
        person: '',
        amount: '',
        des: ''
      })
    },
    removeCashdesk(index) {
      this.cashdesks.splice(index, 1);
    },
    addSalary() {
      this.salarys.push({
        person: '',
        amount: '',
        des: ''
      })
    },
    removeSalary(index) {
      this.salarys.splice(index, 1);
    },
    loadData() {
      if (this.$route.params.id) {
        this.updateID = this.$route.params.id;
        axios.post('/api/accounting/doc/get', { code: this.updateID }).then((response) => {
          this.data.des = response.data.doc.des;
          this.data.date = response.data.doc.date;
          response.data.rows.forEach((item) => {
            if (item.type == 'person') {
              this.persons.push({
                id: item.person,
                amount: item.bs,
                des: item.des
              });
            }
            else if (item.type == 'bank') {
              this.banks.push({
                id: item.bank,
                amount: item.bd,
                des: item.des
              });
            }
            else if (item.type == 'cashdesk') {
              this.cashdesks.push({
                id: item.cashdesk,
                amount: item.bd,
                des: item.des
              });
            }
            else if (item.type == 'salary') {
              this.salarys.push({
                id: item.salary,
                amount: item.bd,
                des: item.des
              });
            }
          })
        });
      }
      else {
        //new
        this.addBank();
        this.addPerson();
        //load year
        axios.post('/api/year/get').then((response) => {
          this.year = response.data;
          this.data.date = response.data.now;
        })
      }
      //get list of persons
      axios.post('/api/person/list/search').then((response) => {
        this.listPersons = response.data;
      });

      //get list of banks
      axios.post('/api/bank/list').then((response) => {
        this.listBanks = response.data;
      });
      //get list of cashdesks
      axios.post('/api/cashdesk/list').then((response) => {
        this.listCashdesks = response.data;
      });
      //get list of salarys
      axios.post('/api/salary/list').then((response) => {
        this.listSalarys = response.data;
      })
    },
    save() {
      if (this.persons.length == 0) {
        Swal.fire({
          text: 'انتخاب حداقل یک فرد الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      let sideOK = true;
      this.banks.forEach((item) => {
        if (item.id == null || item.id == '') {
          sideOK = false;
        }
      });
      this.salarys.forEach((item) => {
        if (item.id == null || item.id == '') {
          sideOK = false;
        }
      })
      this.cashdesks.forEach((item) => {
        if (item.id == null || item.id == '') {
          sideOK = false;
        }
      })
      if (sideOK == false) {
        Swal.fire({
          text: 'یکی از طرف‌های حساب انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      let personOK = true;
      this.persons.forEach((item) => {
        if (item.id == null || item.id == '') {
          personOK = false;
        }
      })
      if (personOK == false) {
        Swal.fire({
          text: 'یکی از اشخاص انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      if (personOK && sideOK) {
        //going to save in api
        //save persons pattern
        let rows = [];
        if (this.data.des == '') this.data.des = 'دریافت از اشخاص:';
        this.persons.forEach((item) => {
          if (item.des == '') item.des = 'دریافت از اشخاص'
          rows.push({
            id: item.id.id,
            bs: parseInt(item.amount),
            bd: 0,
            des: item.des,
            type: 'person',
            table: 3
          });
        })
        this.banks.forEach((item) => {
          if (item.des == '') item.des = 'دریافت از اشخاص'
          rows.push({
            id: item.id.id,
            bs: 0,
            bd: parseInt(item.amount),
            des: item.des,
            type: 'bank',
            table: 9
          });
        })
        this.salarys.forEach((item) => {
          if (item.des == '') item.des = 'دریافت از اشخاص'
          rows.push({
            id: item.id.id,
            bs: 0,
            bd: parseInt(item.amount),
            des: item.des,
            type: 'salary',
            table: 122
          });
        })

        this.cashdesks.forEach((item) => {
          if (item.des == '') item.des = 'دریافت از اشخاص'
          rows.push({
            id: item.id.id,
            bs: 0,
            bd: parseInt(item.amount),
            des: item.des,
            type: 'cashdesk',
            table: 121
          });
        })

        axios.post('/api/accounting/insert', {
          update: this.updateID,
          date: this.data.date,
          type: 'person_receive',
          des: this.data.des,
          rows: rows

        }).then((response) => {
          if (response.data.result == 1) {
            Swal.fire({
              text: 'سند ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then((result) => {
              if (result.isConfirmed) {
                this.$router.push('/acc/persons/receive/list');
              }
            });
          }
          else if (response.data.result == 4) {
            Swal.fire({
              text: response.data.msg,
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          }
        })
      }

    }
  }
}
</script>

<style>
.vpd-body {
  background-color: white !important;
  color: black;
}

.vpd-addon-list-item {
  color: black;
}
</style>