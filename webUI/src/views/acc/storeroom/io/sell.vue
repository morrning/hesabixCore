<script lang="ts">
import { defineComponent } from 'vue'
import axios from "axios";
import Loading from "vue-loading-overlay";
import 'vue-loading-overlay/dist/css/index.css';
import VuePersianDatetimePicker from 'vue-persian-datetime-picker';
import Swal from "sweetalert2";

export default defineComponent({
  name: "sell",
  components: {
    Loading,
  },
  data: () => {
    return {
      loading: false,
      plugins: [],
      doc: {},
      ticket: {
        type: 'output',
        typeString: 'حواله خروج',
        date: '',
        des: '',
        transfer: '',
        receiver: '',
        code: '',
        store: {},
        person: {},
        transferType: {},
        referral: '',
        sms: false,
        senderTel: 0
      },
      transferTypes: [],
      year: {},
      items: [],
      headers: [
        { text: "کد", value: "commodity.code" },
        { text: "کالا", value: "commodity.name", sortable: true },
        { text: "واحد", value: "commodity.unit", sortable: true },
        { text: "مورد نیاز", value: "docCount" },
        { text: "از قبل", value: "countBefore" },
        { text: "باقی‌مانده", value: "remain" },
        { text: "تعداد", value: "commdityCount", sortable: true },
        { text: "ارجاع", value: "referal", sortable: true },
        { text: "توضیحات", value: "des" },
      ],
      currencyConfig: {
        masked: false,
        prefix: '',
        suffix: '',
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
    }
  },
  methods: {
    submit() {
      this.loading = true;
      let errors = [];
      let rowsWithZeroCount = 0;
      this.items.forEach((element, index) => {
        if (element.ticketCount === '') {
          errors.push('تعداد کالا در ردیف ' + (index + 1) + 'وارد نشده است.');
        }
        else if (element.ticketCount === 0) {
          rowsWithZeroCount++;
        }
      });
      //check all values is zero
      if (rowsWithZeroCount != 0) {
        errors.push('تعداد تمام کالاها صفر است!');
      }
      if (errors.length != 0) {
        let errorStr = '<ul>';
        errors.forEach((item) => { errorStr += '<li>' + item + '</li>' })
        errorStr += '</ul>'
        Swal.fire({
          html: errorStr,
          icon: 'error',
          confirmButtonText: 'قبول'
        }).then((response) => {
          this.loading = false;
        });
      }
      else {
        //going to save ticket
        axios.post('/api/storeroom/ticket/insert', {
          doc: this.doc,
          ticket: this.ticket,
          items: this.items
        }).then((resp) => {
          if (resp.data.result == 0) {
            Swal.fire({
              text: 'حواله انبار با موفقیت ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then((response) => {
              this.$router.push('/acc/storeroom/tickets/list');
              this.loading = false;
            });
          }
          else if(resp.data.result == 2){
            Swal.fire({
              text: 'حواله انبار با موفقیت ثبت شد اما به دلیل کمبود اعتبار،پیامک به مشتری ارسال نشد.لطفا برای ارسال پیامک حساب خود را شارژ نمایید..',
              icon: 'success',
              confirmButtonText: 'قبول'
            }).then((response) => {
              this.$router.push('/acc/storeroom/tickets/list');
              this.loading = false;
            });
          }

        });
      }
    },
    autofill() {
      this.items.forEach((element, index) => {
        this.items[index].ticketCount = this.items[index].remain;
        this.items[index].des = 'تعداد ' + this.items[index].remain + 'مورد تحویل شد. ';
        this.items[index].type = 'output';
      })
    },
    isNumber(evt: KeyboardEvent): void {
      const keysAllowed: string[] = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
      const keyPressed: string = evt.key;
      if (!keysAllowed.includes(keyPressed)) {
        evt.preventDefault()
      }
    },
    loadData() {
      axios.post('/api/storeroom/doc/get/info/' + this.$route.params.doc).then((res) => {
        this.doc = res.data;
        this.ticket.person = res.data.person;
        this.ticket.des = 'حواله خروج از انبار برای فاکتور فروش شماره # ' + this.doc.code;
        this.items = res.data.commodities;
        this.items.forEach((element, index) => {
          this.items[index].ticketCount = 0;
          this.items[index].docCount = element.commdityCount;
          this.items[index].des = '';
          this.items[index].type = 'output';
        })
      });
      axios.post('/api/storeroom/info/' + this.$route.params.storeID).then((res) => {
        this.ticket.store = res.data;
        this.ticket.store.des = this.ticket.store.name + ' انباردار : ' + this.ticket.store.manager
      });
      //load year
      axios.post('/api/year/get').then((response) => {
        this.year = response.data;
        this.ticket.date = response.data.now;
      })
      //load transfer types
      axios.post('/api/storeroom/transfertype/list').then((response) => {
        this.transferTypes = response.data;
        this.ticket.transferType = response.data[0];
      });
      //load plugins
      axios.post('/api/plugin/get/actives',).then((response) => {
        this.plugins = response.data;
      });
    },
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
  },
  mounted() {
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
        <i class="mx-2 fa fa-file-export"></i>
        حواله خروج از انبار
      </h3>
      <div class="block-options">
        <span v-if="isPluginActive('accpro')" class="form-check form-switch  form-check-inline">
          <input :disabled="this.ticket.person.mobile == ''" v-model="ticket.sms" class="form-check-input"
            type="checkbox">
          <label class="form-check-label"> پیامک</label>
        </span>
        <button @click="autofill()" class="btn btn-sm btn-outline-primary">
          <i class="fa fa-list-check me-2"></i>
          تکمیل خودکار
        </button>
        <button :disabled="this.loading" @click="submit()" type="button" class="mx-2 btn btn-sm btn-success">
          <i class="fa fa-save me-2"></i>
          ثبت حواله خروج
        </button>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-4">
          <label class="form-label">تاریخ</label>
          <date-picker class="" v-model="this.ticket.date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
            :min="year.start" :max="year.end" />
        </div>
        <div class="col-sm-12 col-md-4">
          <label class="form-label">انبار</label>
          <input disabled="disabled" readonly="readonly" v-model="this.ticket.store.des" type="text"
            class="form-control">
        </div>
        <div class="col-sm-12 col-md-4">
          <label class="form-label">خریدار</label>
          <input disabled="disabled" readonly="readonly" v-model="this.ticket.person.des" type="text"
            class="form-control">
        </div>
      </div>
      <div class="row mt-1">
        <div class="col-sm-12 col-md-12">
          <label class="form-label">شرح</label>
          <input v-model="this.ticket.des" type="text" class="form-control">
        </div>
      </div>
      <div class="row mt-1">
        <div class="col-sm-12 col-md-2">
          <label class="form-label">روش تحویل</label>
          <select class="form-select" v-model="ticket.transferType">
            <option v-for="transferType in transferTypes" :value="transferType">{{ transferType.name }}</option>
          </select>
        </div>
        <div class="col-sm-12 col-md-3">
          <label class="form-label">حمل‌و‌نقل/نام باربری</label>
          <input v-model="this.ticket.transfer" type="text" class="form-control">
        </div>
        <div class="col-sm-12 col-md-2">
          <label class="form-label">تحویل گیرنده</label>
          <input v-model="this.ticket.receiver" type="text" class="form-control">
        </div>
        <div class="col-sm-12 col-md-3">
          <label class="form-label">شماره پیگیری/قبض</label>
          <input v-model="this.ticket.referral" type="text" class="form-control">
        </div>
        <div class="col-sm-12 col-md-2">
          <label class="form-label">تلفن تحویل دهنده</label>
          <input v-model="this.ticket.senderTel" type="text" class="form-control">
        </div>
      </div>
      <div class="row mt-2">
        <div class="col-sm-12 col-md-12">
          <EasyDataTable table-class-name="customize-table" multi-sort show-index alternating :headers="headers"
            :items="items" theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
            rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از"
            :loading="this.loading">
            <template #item-commdityCount="{ index, commdityCount, ticketCount }">
              <input
                @blur="(event) => { if (this.items[index - 1].ticketCount === '') { this.items[index - 1].ticketCount = 0 } }"
                @keypress="isNumber($event)" class="form-control form-control-sm" type="number" min="0"
                :max="this.items[index - 1].remain" v-model="this.items[index - 1].ticketCount" />
            </template>
            <template #item-des="{ index, des }">
              <input class="form-control form-control-sm" type="text" v-model="this.items[index - 1].des" />
            </template>
            <template #item-referal="{ index }">
              <input class="form-control form-control-sm" type="text" v-model="this.items[index - 1].referral" />
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>