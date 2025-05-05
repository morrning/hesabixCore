<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <router-link class="text-warning mx-2 px-2" to="/acc/incomes/list">
          <i class="fa fw-bold fa-arrow-right"></i>
        </router-link>
        درآمد
      </h3>
      <div class="block-options">
        <archive-upload v-if="this.$route.params.id != ''" :docid="this.$route.params.id" doctype="income" cat="income"></archive-upload>
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
              دکمه ثبت بعد از صفر بودن مبلغ باقی مانده و تکمیل شدن حساب‌ها فعال می شود
            </div>
          </div>
          <div class="col-sm-12 col-md-12">
            <div class="form-floating mb-2">
              <input v-model="data.des" class="form-control" type="text">
              <label class="form-label">شرح</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-12 col-md-6 px-1" v-for="(item, index) in incomes">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-default-dark">
                <h3 class="block-title">
                  <small class="text-white">
                    <span class="text-danger mx-2">{{ index + 1 }}</span>
                    <i class="fa fa-ticket"></i>
                    مرکز درآمد
                  </small>
                </h3>
                <span class="block-options">
                  <button title="حذف" class="btn-block-option text-white ps-2" @click="removeItem(index)">
                    <i class="fa fa-trash"></i>
                  </button>
                </span>
              </div>
              <div class="block-content-sm mx-2">
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="row">
                      <div class="col-sm-12 col-md-12">
                        <small class="mb-2">مرکز درآمد</small>
                        <treeselect :disable-branch-nodes="true" v-model="item.id" :multiple="false"
                          :options="listIncomes" placeholder="انتخاب  مرکز درآمد" noOptionsText="آیتمی انتخاب نشده است."
                          noChildrenText="فاقد زیرمجموعه" noResultsText="نتیجه‌ای یافت نشد" />
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
                  <button title="حذف" class="btn-block-option text-white ps-2" @click="removeBank(index)">
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
                  <button title="حذف" class="btn-block-option text-white ps-2" @click="removeSalary(index)">
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
                        <v-cob @change="alert()" dir="rtl" :options="listSalarys" label="name" v-model="item.id"
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
                        <v-cob @change="alert()" dir="rtl" :options="listCashdesks" label="name" v-model="item.id"
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
          <div class="col-sm-12 col-md-6 px-1" v-for="(item, index) in persons">
            <div class="block block-rounded border border-gray">
              <div class="block-header bg-light">
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
                <div class="row">
                  <div class="col-sm-12 col-md-12">
                    <div class="row">
                      <div class="col-sm-12 col-md-12">
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
                <button @click="addItem()" class="btn btn-primary mx-1">
                  <i class="fa fa-plus"></i>
                  افزودن آیتم
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
                    <button @click="addPerson()" type="button" class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-person"></i>
                      شخص
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
import Treeselect from 'vue3-treeselect'
// import the styles
import 'vue3-treeselect/dist/vue3-treeselect.css'
import archiveUpload from "../component/archive/archiveUpload.vue";
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
      listBanks: [],
      listCashdesks: [],
      listSalarys: [],
      persons: [],
      incomes:[],
      listIncomes:[],
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
      this.incomes.forEach((item) => {
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
      this.incomes.push({
        id: this.incomes[1],
        amount: '',
        des: ''
      });
    },
    removeItem(index){
      this.incomes.splice(index, 1);
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
    loadData() {
      if (this.$route.params.id) {
        this.updateID = this.$route.params.id;
        axios.post('/api/accounting/doc/get', { code: this.updateID }).then((response) => {
          this.data.des = response.data.doc.des;
          this.data.date = response.data.doc.date;
          response.data.rows.forEach((item) => {
            if (item.type == 'calc') {
              this.incomes.push({
                id: item.refCode,
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
            else if (item.type == 'person') {
              this.persons.push({
                id: item.person,
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
        this.addItem();
        //load year
        axios.post('/api/year/get').then((response) => {
          this.year = response.data;
          this.data.date = response.data.now;
        })
      }
      //get list of items
      axios.post('/api/accounting/table/childs/income').then((response) => {
        this.listIncomes = response.data;
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
      });

       //get list of persons
       axios.post('/api/person/list/search').then((response) => {
        this.listPersons = response.data;
      });
    },
    searchPerson(query, loading) {
      loading(true);
      axios.post('/api/person/list/search', { search: query }).then((response) => {
        this.listPersons = response.data;
        loading(false);
      });
    },
    save() {
      if (this.incomes.length == 0) {
        Swal.fire({
          text: 'انتخاب حداقل یک مرکز درآمد الزامی است.',
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
      this.incomes.forEach((item) => {
        if (item.id == null || item.id == '') {
          personOK = false;
        }
      })
      if (personOK == false) {
        Swal.fire({
          text: 'یکی از مراکز درآمد انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      if (personOK && sideOK) {
        //going to save in api
        //save persons pattern
        let rows = [];
        if (this.data.des == '') this.data.des = 'درآمد‌ها';
        this.incomes.forEach((item) => {
          if (item.des == '') item.des = 'درآمد'
          rows.push({
            id: item.id,
            bd: 0,
            bs: parseInt(item.amount),
            des: item.des,
            type: 'calc',
            table: item.id
          });
        })
        this.banks.forEach((item) => {
          if (item.des == '') item.des = 'درآمد'
          rows.push({
            id: item.id.id,
            bd: parseInt(item.amount),
            bs: 0,
            des: item.des,
            type: 'bank',
            table: 9
          });
        });

        this.salarys.forEach((item) => {
          if (item.des == '') item.des = 'درآمد'
          rows.push({
            id: item.id.id,
            bs: 0,
            bd: parseInt(item.amount),
            des: item.des,
            type: 'salary',
            table: 122
          });
        });

        this.persons.forEach((item) => {
          if (item.des == '') item.des = 'درآمد'
          rows.push({
            id: item.id.id,
            bs: 0,
            bd: parseInt(item.amount),
            des: item.des,
            type: 'person',
            table: 8
          });
        });

        this.cashdesks.forEach((item) => {
          if (item.des == '') item.des = 'درآمد'
          rows.push({
            id: item.id.id,
            bs: 0,
            bd: parseInt(item.amount),
            des: item.des,
            type: 'cashdesk',
            table: 121
          });
        });

        axios.post('/api/accounting/insert', {
          update: this.updateID,
          date: this.data.date,
          type: 'income',
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
                this.$router.push('/acc/incomes/list');
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