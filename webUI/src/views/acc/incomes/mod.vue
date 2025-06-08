<template>
  <v-container fluid class="pa-0">
    <!-- هدر -->
    <v-toolbar color="toolbar" title="درآمد" flat>
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>

      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn color="error" v-bind="props">
            افزودن حساب
            <v-icon end>mdi-chevron-down</v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item @click="addItem()">
            <template v-slot:prepend>
              <v-icon>mdi-plus</v-icon>
            </template>
            <v-list-item-title>مرکز درآمد</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBank()">
            <template v-slot:prepend>
              <v-icon>mdi-bank</v-icon>
            </template>
            <v-list-item-title>حساب بانکی</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addCashdesk()">
            <template v-slot:prepend>
              <v-icon>mdi-cash-register</v-icon>
            </template>
            <v-list-item-title>صندوق</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addSalary()">
            <template v-slot:prepend>
              <v-icon>mdi-wallet</v-icon>
            </template>
            <v-list-item-title>تنخواه گردان</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addPerson()">
            <template v-slot:prepend>
              <v-icon>mdi-account</v-icon>
            </template>
            <v-list-item-title>شخص</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
      <archive-upload v-if="$route.params.id" :docid="$route.params.id" doctype="income" cat="income" />

      <v-tooltip :text="$t('dialog.save')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" color="primary" :disabled="!canSubmit" @click="save()" class="ms-2" icon="mdi-content-save" />
        </template>
      </v-tooltip>
    </v-toolbar>

    <!-- محتوا -->
    <v-container>
      <v-row>
        <v-col cols="12" md="6">
          <Hdatepicker v-model="data.date" label="تاریخ" />
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field v-model="data.des" label="شرح" variant="outlined"></v-text-field>
        </v-col>
      </v-row>

      <v-card color="error" variant="outlined" class="mb-4 mt-2">
        <v-card-text>
          <v-row>
            <v-col cols="6">
              مجموع: {{ $filters.formatNumber(sum) }}
            </v-col>
            <v-col cols="6">
              باقی‌مانده: {{ $filters.formatNumber(balance) }}
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>

      <!-- مرکز درآمد -->
      <template v-for="(item, index) in incomes" :key="'income'+index">
        <v-card class="mb-4">
          <v-toolbar color="primary" density="compact">
            <v-toolbar-title class="text-white text--secondary">
              <span class="text-error me-2">{{ index + 1 }}</span>
              <v-icon color="white">mdi-ticket</v-icon>
                    مرکز درآمد
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon color="white" @click="removeItem(index)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <Htabletreeselect v-model="item.id" label="مرکز درآمد" tableType="income" />
              </v-col>
              <v-col cols="12" md="4">
                <Hnumberinput
                  v-model="item.amount"
                  label="مبلغ"
                  variant="outlined"
                  density="compact"
                  @update:model-value="calc"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="item.des"
                  label="شرح"
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </template>

      <!-- حساب بانکی -->
      <template v-for="(item, index) in banks" :key="'bank'+index">
        <v-card class="mb-4">
          <v-toolbar color="grey-lighten-2" density="compact">
            <v-toolbar-title>
              <span class="me-2">{{ index + 1 }}</span>
              <v-icon>mdi-bank</v-icon>
                    حساب بانکی
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon color="error" @click="removeBank(index)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <v-autocomplete
                  v-model="item.id"
                  :items="listBanks"
                  item-title="name"
                  item-value="id"
                  label="بانک"
                  variant="outlined"
                  density="compact"
                ></v-autocomplete>
              </v-col>
              <v-col cols="12" md="4">
                <Hnumberinput
                  v-model="item.amount"
                  label="مبلغ"
                  variant="outlined"
                  density="compact"
                  @update:model-value="calc"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="item.des"
                  label="شرح"
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </template>

      <!-- صندوق -->
      <template v-for="(item, index) in cashdesks" :key="'cashdesk'+index">
        <v-card class="mb-4">
          <v-toolbar color="grey-lighten-3" density="compact">
            <v-toolbar-title>
              <span class="me-2">{{ index + 1 }}</span>
              <v-icon>mdi-cash-register</v-icon>
                    صندوق
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon color="error" @click="removeCashdesk(index)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <v-autocomplete
                  v-model="item.id"
                  :items="listCashdesks"
                  item-title="name"
                  item-value="id"
                  label="صندوق"
                  variant="outlined"
                  density="compact"
                ></v-autocomplete>
              </v-col>
              <v-col cols="12" md="4">
                <Hnumberinput
                  v-model="item.amount"
                  label="مبلغ"
                  variant="outlined"
                  density="compact"
                  @update:model-value="calc"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="item.des"
                  label="شرح"
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </template>

      <!-- تنخواه گردان -->
      <template v-for="(item, index) in salarys" :key="'salary'+index">
        <v-card class="mb-4">
          <v-toolbar color="grey-lighten-3" density="compact">
            <v-toolbar-title>
              <span class="me-2">{{ index + 1 }}</span>
              <v-icon>mdi-wallet</v-icon>
                    تنخواه گردان
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon color="error" @click="removeSalary(index)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <v-autocomplete
                  v-model="item.id"
                  :items="listSalarys"
                  item-title="name"
                  item-value="id"
                  label="تنخواه گردان"
                  variant="outlined"
                  density="compact"
                ></v-autocomplete>
              </v-col>
              <v-col cols="12" md="4">
                <Hnumberinput
                  v-model="item.amount"
                  label="مبلغ"
                  variant="outlined"
                  density="compact"
                  @update:model-value="calc"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="item.des"
                  label="شرح"
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </template>

      <!-- شخص -->
      <template v-for="(item, index) in persons" :key="'person'+index">
        <v-card class="mb-4">
          <v-toolbar color="grey-lighten-3" density="compact">
            <v-toolbar-title>
              <span class="me-2">{{ index + 1 }}</span>
              <v-icon>mdi-account</v-icon>
                    شخص
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn icon color="error" @click="removePerson(index)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-toolbar>

          <v-card-text>
            <v-row>
              <v-col cols="12" md="4">
                <Hpersonsearch v-model="item.id" label="شخص" />  
              </v-col>
              <v-col cols="12" md="4">
                <Hnumberinput
                  v-model="item.amount"
                  label="مبلغ"
                  variant="outlined"
                  density="compact"
                  @update:model-value="calc"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="item.des"
                  label="شرح"
                  variant="outlined"
                  density="compact"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </template>
    </v-container>

    <!-- لودینگ -->
    <v-overlay v-model="isLoading" class="align-center justify-center">
      <v-progress-circular indeterminate size="64"></v-progress-circular>
    </v-overlay>
  </v-container>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import archiveUpload from "../component/archive/archiveUpload.vue";
import Hdatepicker from "../../../components/forms/Hdatepicker.vue";
import Hnumberinput from "../../../components/forms/Hnumberinput.vue";
import Htabletreeselect from '../../../components/forms/Htabletreeselect.vue'
import Hpersonsearch from '../../../components/forms/Hpersonsearch.vue'
import quickAdd from "../component/person/quickAdd.vue";

export default {
  name: "mod",
  components: {
    Loading,
    archiveUpload,
    quickAdd,
    Hdatepicker,
    Hnumberinput,
    Htabletreeselect,
    Hpersonsearch,
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
      incomes: [],
      banks: [],
      salarys: [],
      cashdesks: [],
      year: '',
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
        this.sum = parseInt(this.sum) + (parseInt(item.amount) || 0);
      });
      let side = 0;
      this.banks.forEach((item) => {
        side = parseInt(side) + (parseInt(item.amount) || 0);
      });
      this.salarys.forEach((item) => {
        side = parseInt(side) + (parseInt(item.amount) || 0);
      });
      this.cashdesks.forEach((item) => {
        side = parseInt(side) + (parseInt(item.amount) || 0);
      });
      this.persons.forEach((item) => {
        side = parseInt(side) + (parseInt(item.amount) || 0);
      });

      this.balance = parseInt(this.sum) - parseInt(side);
      this.funcCanSubmit();
    },
    funcCanSubmit() {
      if (parseInt(this.balance) == 0 && this.sum > 0) {
        this.canSubmit = true;
      } else {
        this.canSubmit = false;
      }
    },
    addItem() {
      this.incomes.push({
        id: null,
        amount: '',
        des: ''
      });
    },
    removeItem(index) {
      this.incomes.splice(index, 1);
    },
    addBank() {
      this.banks.push({
        id: null,
        amount: '',
        des: ''
      })
    },
    removeBank(index) {
      this.banks.splice(index, 1);
    },
    addCashdesk() {
      this.cashdesks.push({
        id: null,
        amount: '',
        des: ''
      })
    },
    removeCashdesk(index) {
      this.cashdesks.splice(index, 1);
    },
    addSalary() {
      this.salarys.push({
        id: null,
        amount: '',
        des: ''
      })
    },
    removeSalary(index) {
      this.salarys.splice(index, 1);
    },
    addPerson() {
      this.persons.push({
        id: null,
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
                id: item.bank.id,
                amount: item.bd,
                des: item.des
              });
            }
            else if (item.type == 'cashdesk') {
              this.cashdesks.push({
                id: item.cashdesk.id,
                amount: item.bd,
                des: item.des
              });
            }
            else if (item.type == 'salary') {
              this.salarys.push({
                id: item.salary.id,
                amount: item.bd,
                des: item.des
              });
            }
            else if (item.type == 'person') {
              this.persons.push({
                id: item.person.id,
                amount: item.bd,
                des: item.des
              });
            }
          })
          this.calc();
        });
      } else {
        //new
        this.addBank();
        this.addItem();
        //load year
        axios.post('/api/year/get').then((response) => {
          this.year = response.data;
          this.data.date = response.data.now;
        })
      }

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
    },
    save() {
      if (this.incomes.length == 0) {
        Swal.fire({
          text: 'انتخاب حداقل یک مرکز درآمد الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        return;
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
      });
      this.cashdesks.forEach((item) => {
        if (item.id == null || item.id == '') {
          sideOK = false;
        }
      });
      this.persons.forEach((item) => {
        if (item.id == null || item.id == '') {
          sideOK = false;
        }
      });
      if (sideOK == false) {
        Swal.fire({
          text: 'یکی از طرف‌های حساب انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        return;
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
        return;
      }

      //going to save in api
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
      });

      this.banks.forEach((item) => {
        if (item.des == '') item.des = 'درآمد'
        rows.push({
          id: item.id,
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
          id: item.id,
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
          id: item.id,
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
          id: item.id,
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
      });
    }
  }
}
</script>

<style scoped></style>