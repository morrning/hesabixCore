<template>
  <v-toolbar color="toolbar" :title="$t('drawer.transfer')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>

    <archive-upload v-if="this.$route.params.id != ''" :docid="this.$route.params.id" doctype="transfer"
      cat="transfer"></archive-upload>
    <v-tooltip :text="$t('dialog.save')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" color="primary" @click="save()" variant="text" icon="mdi-content-save" :loading="loading" />
      </template>
    </v-tooltip>

  </v-toolbar>
  <v-container>
    <v-row>
      <v-col cols="12" md="6">
        <Hdatepicker v-model="date" label="تاریخ" />
      </v-col>
      <v-col cols="12" md="6">
        <v-text-field v-model="this.des" label="توضیحات" variant="outlined"></v-text-field>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <h3 class="text-primary text-h6 mb-4">از:</h3>
        <v-btn-group class="divided mb-4" :border="true">
          <v-btn :color="sideOne.type === 'bank' ? 'primary' : 'outlined'" @click="changeFrom('bank')">بانک</v-btn>
          <v-btn :color="sideOne.type === 'cashdesk' ? 'primary' : 'outlined'"
            @click="changeFrom('cashdesk')">صندوق</v-btn>
          <v-btn :color="sideOne.type === 'salary' ? 'primary' : 'outlined'"
            @click="changeFrom('salary')">تنخواه</v-btn>
        </v-btn-group>

        <v-row>
          <v-col cols="12">
            <v-select :hide-details="false" v-if="sideOne.type === 'bank'" v-model="sideOne.id" :items="banks"
              item-title="name" item-value="id" label="بانک" variant="outlined" :item-props="bankItemProps"></v-select>

            <v-select :hide-details="false" v-if="sideOne.type === 'cashdesk'" v-model="sideOne.id"
              :items="cashdesks" item-title="name" item-value="id" label="صندوق" variant="outlined"
              :item-props="cashdeskItemProps"></v-select>

            <v-select :hide-details="false" v-if="sideOne.type === 'salary'" v-model="sideOne.id"
              :items="salarys" item-title="name" item-value="id" label="تنخواه گردان" variant="outlined"
              :item-props="salaryItemProps"></v-select>

            <Hnumberinput :hide-details="false" v-model="sideOne.bs" label="مبلغ" variant="outlined" class="mb-4" />

            <Hnumberinput :hide-details="false" v-model="sideOne.tax" label="کارمزد خدمات بانکی" variant="outlined" class="mb-4" />

            <v-text-field :hide-details="false" v-model="sideOne.reference" label="ارجاع"
              variant="outlined" class="mb-4"></v-text-field>

            <v-text-field v-model="sideOne.des" label="شرح" variant="outlined"></v-text-field>
          </v-col>
        </v-row>
      </v-col>

      <v-col cols="12" md="6">
        <h3 class="text-primary text-h6 mb-4">به:</h3>
        <v-btn-group class="divided mb-4" :border="true">
          <v-btn :color="sideTwo.type === 'bank' ? 'primary' : 'outlined'" @click="changeDes('bank')">بانک</v-btn>
          <v-btn :color="sideTwo.type === 'cashdesk' ? 'primary' : 'outlined'"
            @click="changeDes('cashdesk')">صندوق</v-btn>
          <v-btn :color="sideTwo.type === 'salary' ? 'primary' : 'outlined'"
            @click="changeDes('salary')">تنخواه</v-btn>
        </v-btn-group>

        <v-row>
          <v-col cols="12">
            <v-select :hide-details="false" v-if="sideTwo.type === 'bank'" v-model="sideTwo.id" :items="banks"
              item-title="name" item-value="id" label="بانک" variant="outlined" :item-props="bankItemProps"></v-select>

            <v-select :hide-details="false" v-if="sideTwo.type === 'cashdesk'" v-model="sideTwo.id"
              :items="cashdesks" item-title="name" item-value="id" label="صندوق" variant="outlined"
              :item-props="cashdeskItemProps"></v-select>

            <v-select :hide-details="false" v-if="sideTwo.type === 'salary'" v-model="sideTwo.id"
              :items="salarys" item-title="name" item-value="id" label="تنخواه گردان" variant="outlined"
              :item-props="salaryItemProps"></v-select>

            <Hnumberinput :hide-details="false" v-model="sideTwo.bd" label="مبلغ" variant="outlined" class="mb-4" readonly />

            <Hnumberinput :hide-details="false" v-model="sideTwo.tax" label="کارمزد خدمات بانکی" class="mb-4" variant="outlined"
              readonly />

            <v-text-field :hide-details="false" v-model="sideTwo.reference" label="ارجاع"
              variant="outlined" class="mb-4"></v-text-field>

            <v-text-field v-model="sideTwo.des" label="شرح" variant="outlined"></v-text-field>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import archiveUpload from "../component/archive/archiveUpload.vue";
import Hdatepicker from "../../../components/forms/Hdatepicker.vue";
import Hnumberinput from "../../../components/forms/Hnumberinput.vue";

export default {
  name: "mod",
  components: {
    archiveUpload,
    Hdatepicker,
    Hnumberinput
  },
  watch: {
    'sideOne.bs': function () {
      this.sideTwo.bd = this.sideOne.bs;
    },
    'sideOne.tax': function () {
      this.sideTwo.tax = this.sideOne.tax;
    },
    'sideOne.bank': function () {
      this.sideOne.id = this.sideOne.bank?.id;
    },
    'sideOne.salary': function () {
      this.sideOne.id = this.sideOne.salary?.id;
    },
    'sideOne.cashdesk': function () {
      this.sideOne.id = this.sideOne.cashdesk?.id;
    },
    'sideTwo.bank': function () {
      this.sideTwo.id = this.sideTwo.bank?.id;
    },
    'sideTwo.salary': function () {
      this.sideTwo.id = this.sideTwo.salary?.id;
    },
    'sideTwo.cashdesk': function () {
      this.sideTwo.id = this.sideTwo.cashdesk?.id;
    },
  },
  data: () => ({
    year: {},
    date: '',
    des: '',
    loading: false,
    sideOne: {
      type: 'bank',
      bank: undefined,
      cashdesk: undefined,
      salary: undefined,
      bs: 0,
      bd: 0,
      tax: 0,
      reference: '',
      id: '',
      des: 'انتقال بین حساب‌های بانکی،صندوق،تنخواه گردان'
    },
    sideTwo: {
      type: 'bank',
      bank: undefined,
      cashdesk: undefined,
      salary: undefined,
      bs: 0,
      bd: 0,
      tax: 0,
      reference: '',
      id: '',
      des: ''
    },
    banks: [],
    cashdesks: [],
    salarys: []
  }),
  methods: {
    bankItemProps(item) {
      return {
        title: item.name,
        subtitle: `موجودی: ${this.$filters.formatNumber(item.balance)} ${item.balance < 0 ? 'بدهکار' : 'بستانکار'}`
      }
    },
    cashdeskItemProps(item) {
      return {
        title: item.name,
        subtitle: `موجودی: ${this.$filters.formatNumber(item.balance)} ${item.balance < 0 ? 'بدهکار' : 'بستانکار'}`
      }
    },
    salaryItemProps(item) {
      return {
        title: item.name,
        subtitle: `موجودی: ${this.$filters.formatNumber(item.balance)} ${item.balance < 0 ? 'بدهکار' : 'بستانکار'}`
      }
    },
    loadData() {
      axios.post('/api/bank/list').then((response) => {
        this.banks = response.data;
      });
      axios.post('/api/cashdesk/list').then((response) => {
        this.cashdesks = response.data;
      });
      axios.post('/api/salary/list').then((response) => {
        this.salarys = response.data;
      });
      axios.post('/api/year/get').then((response) => {
        this.year = response.data;
        this.date = response.data.now;
      });
      if (this.$route.params.id != '') {
        axios.post('/api/accounting/doc/get', {
          code: this.$route.params.id
        }).then((response) => {
          this.des = response.data.doc.des;
          this.date = response.data.doc.date;

          let taxAmount = 0;
          response.data.rows.forEach((item, key) => {
            if (item.refCode == '108') {
              taxAmount = item.bd;
              response.data.rows[key].id = 'ignore';
            }
          });

          response.data.rows.forEach((item, key) => {
            if (item.bs == taxAmount && item.bs != 0) {
              response.data.rows[key].id = 'ignore';
            }
          });

          response.data.rows.forEach((item) => {
            if (item.bs != 0 && item.id != 'ignore') {
              let opt = {
                type: '',
                bank: undefined,
                cashdesk: undefined,
                salary: undefined,
                bs: item.bs,
                bd: item.bd,
                tax: taxAmount,
                reference: item.referral,
                id: '',
                des: item.des
              };
              if (item.bank) {
                opt.bank = item.bank;
                opt.type = 'bank';
                opt.id = item.bank.id;
              }
              else if (item.cashdesk) {
                opt.cashdesk = item.cashdesk;
                opt.type = 'cashdesk';
                opt.id = item.cashdesk.id;
              }
              else if (item.salary) {
                opt.salary = item.salary;
                opt.type = 'salary';
                opt.id = item.salary.id;
              }
              this.sideOne = opt;
            }
            else if (parseInt(item.bd) != 0 && item.id != 'ignore') {
              let opt = {
                type: '',
                bank: undefined,
                cashdesk: undefined,
                salary: undefined,
                bs: item.bs,
                bd: item.bd,
                tax: taxAmount,
                reference: item.referral,
                id: '',
                des: item.des
              };
              if (item.bank) {
                opt.bank = item.bank;
                opt.type = 'bank';
                opt.id = item.bank.id;
              }
              else if (item.cashdesk) {
                opt.cashdesk = item.cashdesk;
                opt.type = 'cashdesk';
                opt.id = item.cashdesk.id;
              }
              else if (item.salary) {
                opt.salary = item.salary;
                opt.type = 'salary';
                opt.id = item.salary.id;
              }
              this.sideTwo = opt;
            }
          });
        });
      }
    },
    save() {
      if (this.sideOne.bs == 0) {
        Swal.fire({
          text: 'مبلغ انتقال وارد نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        return;
      }

      if (
        (this.sideOne.type == 'bank' && !this.sideOne.id) ||
        (this.sideOne.type == 'salary' && !this.sideOne.id) ||
        (this.sideOne.type == 'cashdesk' && !this.sideOne.id)
      ) {
        Swal.fire({
          text: 'انتقال دهنده انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        return;
      }

      if (
        (this.sideTwo.type == 'bank' && !this.sideTwo.id) ||
        (this.sideTwo.type == 'salary' && !this.sideTwo.id) ||
        (this.sideTwo.type == 'cashdesk' && !this.sideTwo.id)
      ) {
        Swal.fire({
          text: 'انتقال گیرنده انتخاب نشده است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        return;
      }

      this.loading = true;
      let PushData = {
        date: this.date,
        des: this.des,
        update: this.$route.params.id,
        rows: [
          {
            bs: this.sideOne.bs,
            bd: 0,
            type: this.sideOne.type,
            id: this.sideOne.id,
            referral: this.sideOne.reference,
            des: this.sideOne.des,
          },
          {
            bd: this.sideTwo.bd,
            bs: 0,
            type: this.sideTwo.type,
            id: this.sideTwo.id,
            referral: this.sideTwo.reference,
            des: this.sideTwo.des,
          }
        ]
      };

      if (this.sideOne.tax != 0) {
        PushData.rows.push({
          bd: this.sideOne.tax,
          bs: 0,
          type: 'calc',
          des: 'کارمزد هزینه‌های بانکی',
          referral: this.sideOne.reference
        });
        PushData.rows.push({
          bs: this.sideOne.tax,
          bd: 0,
          type: this.sideOne.type,
          id: this.sideOne.id,
          des: 'کارمزد هزینه‌های بانکی',
          referral: this.sideOne.reference
        });
      }

      axios.post('/api/transfer/insert', PushData).then((response) => {
        this.loading = false;
        if (response.data.result == '1') {
          Swal.fire({
            text: 'سند انتقال با موفقیت ثبت شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then(() => {
            this.$router.push('/acc/transfer/list');
          });
        }
        else if (response.data.result == '4') {
          Swal.fire({
            text: response.data.msg,
            icon: 'error',
            confirmButtonText: 'قبول'
          });
        }
      }).catch(() => {
        this.loading = false;
        Swal.fire({
          text: 'خطا در ارتباط با سرور',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      });
    },
    changeDes(type) {
      this.sideTwo.type = type;
    },
    changeFrom(type) {
      this.sideOne.type = type;
    },
  },
  mounted() {
    this.loadData();
  }
}
</script>

<style scoped>
.v-navigation-bar {
  direction: rtl;
}
</style>