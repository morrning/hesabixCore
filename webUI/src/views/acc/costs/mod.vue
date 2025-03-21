<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <router-link class="text-warning mx-2 px-2" to="/acc/costs/list">
          <i class="fa fw-bold fa-arrow-right"></i>
        </router-link>
        هزینه
      </h3>
      <div class="block-options">
        <archive-upload v-if="this.$route.params.id != ''" :docid="this.$route.params.id" doctype="cost"
          cat="cost"></archive-upload>
          <div class="dropdown me-2">
                  <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-danger dropdown-toggle"
                    data-bs-toggle="dropdown" type="button"> افزودن حساب </button>
                  <div aria-labelledby="dropdown-dropup-secondary" class="border border-danger dropdown-menu" style="">
                    <button @click="addItem()" type="button" class="dropdown-item">
                      <i class="fa fa-plus"></i>
                      مرکز هزینه
                    </button>
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
                    <button @click="addPerson()" type="button" class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-person"></i>
                      شخص
                    </button>
                  </div>
                </div>
        <button :disabled="this.canSubmit != true" @click="save()" type="button" class="btn btn-sm btn-alt-primary">
          <i class="fa fa-save"></i>
          ثبت
        </button>
      </div>
    </div>
    <div class="block-content py-3 px-0 vl-parent">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false" />
      <div class="container">
        <div class="row mb-2 px-1">
          <div class="col-sm-12 col-md-6">
            <date-picker class="" v-model="data.date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
              :min="year.start" :max="year.end" />
          </div>
          <div class="col-sm-12 col-md-6">
            <input placeholder="شرح" v-model="data.des" class="form-control form-control-sm" type="text">
          </div>
        </div>
        <div class="row border border-danger rounded-1 mx-1 mb-2">
              <div class="col-6">
                  مجموع :
                  <span class="text-danger">{{ $filters.formatNumber(sum) }}</span>
                </div>
                <div class="col-6">
                  باقی‌مانده:
                  <span class="text-danger">{{ $filters.formatNumber(balance) }}</span>
                </div>
            </div>
        <div class="row">
          <div class="col-sm-12 col-md-12" v-for="(item, index) in costs">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-default-dark py-1">
                <h3 class="block-title">
                  <small class="text-white">
                    <span class="text-danger mx-2">{{ index + 1 }}</span>
                    <i class="fa fa-ticket"></i>
                    مرکز هزینه
                  </small>
                </h3>
                <span class="block-options">
                  <button title="حذف" class="btn-block-option text-white ps-2" @click="removeItem(index)">
                    <i class="fa fa-trash"></i>
                  </button>
                </span>
              </div>
              <div class="block-content-sm mx-2">
                <div class="row mb-1">
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">مرکز هزینه</small>
                        <treeselect :disable-branch-nodes="true" v-model="item.id" :multiple="false"
                          :options="listscosts" placeholder="انتخاب  مرکز هزینه" noOptionsText="آیتمی انتخاب نشده است."
                          noChildrenText="فاقد زیرمجموعه" noResultsText="نتیجه‌ای یافت نشد" />
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control form-control-sm" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                      <div class="col-sm-12 col-md-4">
                    <small>شرح</small>
                    <input v-model="item.des" type="text" class="form-control form-control-sm">
                  </div>
                    </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-12 mt-2" v-for="(item, index) in banks">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-light py-1">
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
                <div class="row mb-1">
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">بانک</small>
                        <v-cob dir="rtl" :options="listBanks" label="name" v-model="item.id"
                          @option:deselecting="funcCanSubmit()" @search:focus="funcCanSubmit()"
                          @option:selecting="funcCanSubmit()">
                          <template #no-options="{ search, searching, loading }">
                            نتیجه‌ای یافت نشد!
                          </template>
                        </v-cob>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control form-control-sm" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <small>شرح</small>
                        <input v-model="item.des" type="text" class="form-control form-control-sm">
                      </div>
                    </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-12 " v-for="(item, index) in salarys">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-light py-1">
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
                <div class="row mb-1">
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">تنخواه گردان</small>
                        <v-cob dir="rtl" :options="listSalarys" label="name" v-model="item.id"
                          @option:deselecting="funcCanSubmit()" @search:focus="funcCanSubmit()"
                          @option:selecting="funcCanSubmit()">
                          <template #no-options="{ search, searching, loading }">
                            نتیجه‌ای یافت نشد!
                          </template>
                        </v-cob>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control form-control-sm" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                       <div class="col-sm-12 col-md-4">
                        <small>شرح</small>
                          <input v-model="item.des" type="text" class="form-control form-control-sm">
                      </div>
                    </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-12 " v-for="(item, index) in cashdesks">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-light py-1">
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
                <div class="row mb-1">
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">صندوق</small>
                        <v-cob dir="rtl" :options="listCashdesks" label="name" v-model="item.id"
                          @option:deselecting="funcCanSubmit()" @search:focus="funcCanSubmit()"
                          @option:selecting="funcCanSubmit()">
                          <template #no-options="{ search, searching, loading }">
                            نتیجه‌ای یافت نشد!
                          </template>
                        </v-cob>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control form-control-sm" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                       <div class="col-sm-12 col-md-4">
                        <small>شرح</small>
                          <input v-model="item.des" type="text" class="form-control form-control-sm">
                      </div>
                    </div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-12 " v-for="(item, index) in persons">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-light py-1">
                <h3 class="block-title">
                  <small class="text-black">
                    <span class="mx-2">{{ index + 1 }}</span>
                    <i class="fa fa-person"></i>
                    شخص
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
               <div class="row mb-1">
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">شخص</small>
                        <v-cob :filterable="false" @search="searchPerson" class="" dir="rtl" :options="listPersons"
                          label="nikename" v-model="item.id">
                          <template v-slot:option="option">
                            <div class="row mb-1">
                              <div class="col-12">
                                <i class="fa fa-user me-2"></i>
                                {{ option.nikename }}
                              </div>
                              <div class="col-12">
                                <div class="row">
                                  <div v-if="option.mobile != ''" class="col-6">
                                    <i class="fa fa-phone me-2"></i>
                                    {{ option.mobile }}
                                  </div>
                                  <div class="col-6" v-if="parseInt(option.bs) - parseInt(option.bd) != 0">
                                    <i class="fa fa-bars"></i>
                                    تراز:
                                    {{ $filters.formatNumber(Math.abs(parseInt(option.bs) -
          parseInt(option.bd))) }}
                                    <span class="" v-if="parseInt(option.bs) - parseInt(option.bd) < 0">
                                      بدهکار </span>
                                    <span class="" v-if="parseInt(option.bs) - parseInt(option.bd) > 0">
                                      بستانکار </span>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </template>
                        </v-cob>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <small class="mb-2">مبلغ</small>
                        <money3 @change="calc()" class="form-control form-control-sm" v-model="item.amount" v-bind="currencyConfig">
                        </money3>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <small>شرح</small>
                        <input v-model="item.des" type="text" class="form-control form-control-sm">
                      </div>
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
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import VuePersianDatetimePicker from 'vue-persian-datetime-picker'
import archiveUpload from "../component/archive/archiveUpload.vue";

import Treeselect from 'vue3-treeselect'
// import the styles
import 'vue3-treeselect/dist/vue3-treeselect.css'
import quickAdd from "../component/person/quickAdd.vue";
export default {
  name: "mod",
  components: {
    Loading,
    Treeselect,
    archiveUpload,
    quickAdd
  },
  data: () => {
    return {
      isLoading: false,
      canSubmit: false,
      updateID: null,
      sum: 0,
      balance: 0,
      listPersons: [],
      listscosts: [],
      listBanks: [],
      listCashdesks: [],
      listSalarys: [],
      persons: [],
      costs: [],
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
  beforeMount() {
    this.loadData();
  },
  beforeRouteUpdate(to, from) {
    this.loadData(to.params.id);
  },
  methods: {
    calc() {

      this.sum = 0;
      this.costs.forEach((item) => {
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
      this.persons.forEach((item) => {
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
    addItem() {
      this.costs.push({
        id: this.costs[1],
        amount: '',
        des: ''
      });
    },
    removeItem(index){
      this.costs.splice(index, 1);
    },
    addBank() {
      this.banks.push({
        person: null,
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
    addPerson() {
      this.persons.push({
        person: '',
        amount: '',
        des: ''
      })
    },
    removePerson(index) {
      this.persons.splice(index, 1);
    },
    searchPerson(query, loading) {
      loading(true);
      axios.post('/api/person/list/search', { search: query }).then((response) => {
        this.listPersons = response.data;
        loading(false);
      });
    },
    loadData() {
      if (this.$route.params.id) {
        this.updateID = this.$route.params.id;
        axios.post('/api/accounting/doc/get', { code: this.updateID }).then((response) => {
          this.data.des = response.data.doc.des;
          this.data.date = response.data.doc.date;
          response.data.rows.forEach((item) => {
            if (item.type == 'calc') {
              this.costs.push({
                id: item.refCode,
                amount: item.bd,
                des: item.des
              });
            }
            else if (item.type == 'bank') {
              this.banks.push({
                id: item.bank,
                amount: item.bs,
                des: item.des
              });
            }
            else if (item.type == 'cashdesk') {
              this.cashdesks.push({
                id: item.cashdesk,
                amount: item.bs,
                des: item.des
              });
            }
            else if (item.type == 'salary') {
              this.salarys.push({
                id: item.salary,
                amount: item.bs,
                des: item.des
              });
            }
            else if (item.type == 'person') {
              this.persons.push({
                id: item.person,
                amount: item.bs,
                des: item.des
              });
            }
          })
        });
      }
      else {
        //new
        this.addBank();
        this.addItem();
        //load year
        axios.post('/api/year/get').then((response) => {
          this.year = response.data;
          this.data.date = response.data.now;
        })
      }
      //get list of items
      axios.post('/api/accounting/table/childs/cost').then((response) => {
        this.listscosts = response.data;
      });

      //get list of banks
      axios.post('/api/bank/list').then((response) => {
        this.listBanks = response.data;
      })

      //get list of cashdesks
      axios.post('/api/cashdesk/list').then((response) => {
        this.listCashdesks = response.data;
      });

      //get list of salarys
      axios.post('/api/salary/list').then((response) => {
        this.listSalarys = response.data;
      });

      //get list of persons
      axios.post('/api/person/list/search').then((response) => {
        this.listPersons = response.data;
      });
    },
    save() {
      let haszero = false;
      this.costs.forEach((item) => {
        if (item.amount == null || item.amount == 0) {
          Swal.fire({
          text: 'مبلغ صفر نامعتبر است',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        haszero =  true;
        }
      })

      if (this.costs.length == 0) {
        Swal.fire({
          text: 'انتخاب حداقل یک مرکز هزینه الزامی است.',
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
      this.persons.forEach((item) => {
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
      this.costs.forEach((item) => {
        if (item.id == null || item.id == '') {
          personOK = false;
        }
      })
      if (personOK == false) {
        Swal.fire({
          text: 'یکی از مراکز هزینه انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      if (personOK && sideOK && ! haszero) {
        //going to save in api
        //save costs pattern
        let rows = [];
        if (this.data.des == '') this.data.des = 'هزینه‌ها';
        this.costs.forEach((item) => {
          if (item.des == '') item.des = 'هزینه'
          rows.push({
            id: item.id,
            bs: 0,
            bd: parseInt(item.amount),
            des: item.des,
            type: 'calc',
            table: item.id
          });
        })
        this.banks.forEach((item) => {
          if (item.des == '') item.des = 'هزینه'
          rows.push({
            id: item.id.id,
            bs: parseInt(item.amount),
            bd: 0,
            des: item.des,
            type: 'bank',
            table: 5
          });
        })

        this.salarys.forEach((item) => {
          if (item.des == '') item.des = 'هزینه'
          rows.push({
            id: item.id.id,
            bs: parseInt(item.amount),
            bd: 0,
            des: item.des,
            type: 'salary',
            table: 124
          });
        })

        this.cashdesks.forEach((item) => {
          if (item.des == '') item.des = 'هزینه'
          rows.push({
            id: item.id.id,
            bs: parseInt(item.amount),
            bd: 0,
            des: item.des,
            type: 'cashdesk',
            table: 123
          });
        })

        this.persons.forEach((item) => {
          if (item.des == '') item.des = 'هزینه'
          rows.push({
            id: item.id.id,
            bs: parseInt(item.amount),
            bd: 0,
            des: item.des,
            type: 'person',
            table: 3
          });
        })

        axios.post('/api/accounting/insert', {
          update: this.updateID,
          date: this.data.date,
          type: 'cost',
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
                this.$router.push('/acc/costs/list');
              }
            });
          }
          else if (response.data.result == '4') {
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

<style scoped></style>