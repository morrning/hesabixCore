<script lang="ts">
import { defineComponent, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import Rec from '../component/rec.vue';
import RecList from '../component/recList.vue';
import ArchiveUpload from '../component/archive/archiveUpload.vue';
import Notes from '../component/notes.vue';
import PrintOptions from '@/components/widgets/PrintOptions.vue';
import ShareOptions from '@/components/widgets/ShareOptions.vue';
import { getApiUrl } from '@/hesabixConfig';

export default defineComponent({
  name: 'viewInvoice',
  components: {
    ArchiveUpload,
    Rec,
    RecList,
    Notes,
    PrintOptions,
    ShareOptions,
  },
  watch: {
    'PayWindowsState.submited'(newValue) {
      this.PayWindowsState.submited = false;
      if (newValue) {
        this.loadData();
        this.recDialog = false;
      }
    },
    'recListWindowsState.submited'(newValue) {
      this.recListWindowsState.submited = false;
      if (newValue) {
        this.loadData();
      }
    },
  },
  data: () => ({
    recDialog: false,
    recListDialog: false,
    activeTab: 'invoice-info',
    loading: ref(true),
    shortlink_url: '',
    PayWindowsState: { submited: false },
    recListWindowsState: { submited: false },
    notes: { count: 0 },
    bid: { legal_name: '', shortlinks: false },
    item: {
      doc: { id: 0, date: null, code: null, des: '', amount: 0, profit: 0, shortLink: null },
      relatedDocs: [],
      rows: [],
    },
    person: { nikename: null, mobile: '', tel: '', addres: '', postalcode: '' },
    commoditys: [],
    totalRec: 0,
    totalDiscount: 0,
    totalTax: 0,
    transferCost: 0,
    discountAll: 0,
    mobileHeaders: [
      { title: 'کالا', key: 'commodity.name' },
      { title: 'تعداد', key: 'count' },
      { title: 'مبلغ کل', key: 'sumTotal' },
    ],
    desktopHeaders: [
      { title: 'کالا', key: 'commodity.name' },
      { title: 'تعداد', key: 'count' },
      { title: 'قیمت واحد', key: 'price' },
      { title: 'تخفیف', key: 'discount' },
      { title: 'مالیات', key: 'tax' },
      { title: 'مبلغ کل', key: 'sumTotal' },
      { title: 'شرح', key: 'des' },
    ],
  }),
  computed: {
    formattedAmount() {
      return this.$filters.formatNumber(this.item.doc.amount);
    },
    statusText() {
      return parseInt(this.item.doc.amount) <= parseInt(this.totalRec) ? 'تسویه شده' : 'تسویه نشده';
    },
    statusColor() {
      return parseInt(this.item.doc.amount) <= parseInt(this.totalRec) ? 'success' : 'error';
    },
    profitText() {
      return this.$filters.formatNumber(Math.abs(this.item.doc.profit));
    },
    profitLabel() {
      return parseInt(this.item.doc.profit) >= 0 ? 'سود فاکتور' : 'زیان فاکتور';
    },
    profitColor() {
      return parseInt(this.item.doc.profit) >= 0 ? 'success' : 'error';
    },
    formattedTotalTax() {
      return this.$filters.formatNumber(this.totalTax);
    },
    formattedDiscountAll() {
      return this.$filters.formatNumber(this.discountAll);
    },
    formattedTransferCost() {
      return this.$filters.formatNumber(this.transferCost);
    },
  },
  methods: {
    loadData() {
      this.loading = true;
      this.commoditys = [];
      axios.post('/api/accounting/doc/get', { code: this.$route.params.id }).then((response) => {
        this.item = response.data;
        this.shortlink_url = this.item.doc.shortLink
          ? `${getApiUrl()}/sl/sell/${localStorage.getItem('activeBid')}/${this.item.doc.shortLink}`
          : `${getApiUrl()}/sl/sell/${localStorage.getItem('activeBid')}/${this.item.doc.id}`;
        this.totalRec = response.data.relatedDocs.reduce((sum: number, rdoc: any) => sum + parseInt(rdoc.amount), 0);
      });

      axios.get(`/api/sell/get/info/${this.$route.params.id}`).then((response) => {
        this.person = response.data.person;
        this.discountAll = response.data.discountAll;
        this.transferCost = response.data.transferCost;
        this.item.doc.profit = response.data.profit;
        this.commoditys = response.data.rows
          .filter((item: any) => item.commodity != null)
          .map((item: any) => {
            this.totalTax += parseInt(item.tax);
            this.totalDiscount += parseInt(item.discount);
            return {
              commodity: item.commodity,
              count: item.commodity_count,
              price: parseInt((parseInt(item.bs) - parseInt(item.tax) + parseInt(item.discount)) / parseFloat(item.commodity_count)),
              bs: item.bs,
              bd: item.bd,
              id: item.commodity.id,
              des: item.des,
              discount: item.discount,
              tax: item.tax,
              sumWithoutTax: item.bs - item.tax,
              sumTotal: item.bs,
              table: 53,
            };
          });
      });

      axios.post(`/api/business/get/info/${localStorage.getItem('activeBid')}`).then((response) => {
        this.bid = response.data;
        this.loading = false;
      });
    },
  },
  mounted() {
    this.loadData();
  },
});
</script>

<template>
  <v-toolbar color="toolbar" flat title="مشاهده فاکتور">
    <template v-slot:prepend>
      <v-tooltip text="بازگشت" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-btn icon v-if="item.doc.id !== 0">
      <archive-upload :docid="item.doc.id" doctype="sell" cat="sell" />
      <v-tooltip activator="parent" location="bottom">آرشیو</v-tooltip>
    </v-btn>
    <notes :stat="notes" :code="$route.params.id" type-note="sell" />
    <v-btn icon color="error" class="ml-2" v-if="parseInt(item.doc.amount) > parseInt(totalRec)" @click="recDialog = true">
      <v-icon>mdi-money</v-icon>
      <v-tooltip activator="parent" location="bottom">ثبت دریافت</v-tooltip>
    </v-btn>
    <v-btn icon color="info" class="ml-2" @click="recListDialog = true">
      <v-icon>mdi-arrow-down-circle</v-icon>
      <v-tooltip activator="parent" location="bottom">دریافت‌ها</v-tooltip>
    </v-btn>
    <share-options v-if="bid.shortlinks" :shortlink-url="shortlink_url" :mobile="person.mobile" :invoice-id="item.doc.id" />
    <print-options :invoice-id="$route.params.id" />
  </v-toolbar>

  <v-tabs v-model="activeTab" color="primary"  grow class="mt-0 bg-gray">
    <v-tab value="person-info">اطلاعات شخص</v-tab>
    <v-tab value="invoice-info">اقلام فاکتور</v-tab>
    <v-tab value="payments">دریافت‌ها</v-tab>
  </v-tabs>
  <v-container fluid>
    <v-window v-model="activeTab">
      <!-- تب اطلاعات شخص -->
      <v-window-item value="person-info">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="person.nikename" label="نام" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="person.mobile" label="موبایل" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="person.tel" label="تلفن" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-text-field v-model="person.postalcode" label="کد پستی" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="12" md="9">
              <v-text-field v-model="person.addres" label="آدرس" outlined readonly></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
      </v-window-item>

      <!-- تب اطلاعات فاکتور -->
      <v-window-item value="invoice-info">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="item.doc.code" label="شماره" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="item.doc.date" label="تاریخ" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="12" md="4">
              <v-text-field v-model="item.doc.des" label="شرح" outlined readonly></v-text-field>
            </v-col>
          </v-row>

          <v-list-subheader>اقلام</v-list-subheader>
          <v-data-table
            :headers="$vuetify.display.smAndDown ? mobileHeaders : desktopHeaders"
            :items="commoditys"
            :loading="loading"
            :show-expand="$vuetify.display.smAndDown"
            class="elevation-1"
            :header-props="{ class: 'custom-header' }"
          >
            <template v-slot:item.sumTotal="{ item }">
              {{ $filters.formatNumber(item.sumTotal) }}
            </template>
            <template v-slot:item.count="{ item }">
              {{ item.count }} {{ item.commodity.unit }}
            </template>
            <template v-slot:item.price="{ item }">
              {{ $filters.formatNumber(item.price) }}
            </template>
            <template v-slot:item.discount="{ item }">
              {{ $filters.formatNumber(item.discount) }}
            </template>
            <template v-slot:item.tax="{ item }">
              {{ $filters.formatNumber(item.tax) }}
            </template>
            <template v-slot:expanded-row="{ item }">
              <v-list dense>
                <v-list-item>
                  <v-list-item-title>قیمت واحد</v-list-item-title>
                  <v-list-item-subtitle>{{ $filters.formatNumber(item.price) }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>تخفیف</v-list-item-title>
                  <v-list-item-subtitle>{{ $filters.formatNumber(item.discount) }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>مالیات</v-list-item-title>
                  <v-list-item-subtitle>{{ $filters.formatNumber(item.tax) }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>شرح</v-list-item-title>
                  <v-list-item-subtitle>{{ item.des }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </template>
          </v-data-table>

          <v-row class="mt-2">
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="formattedAmount" label="جمع کل" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="statusText" :color="statusColor" label="وضعیت" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="profitText" :label="profitLabel" :color="profitColor" outlined readonly></v-text-field>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="formattedTotalTax" label="مالیات" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="formattedDiscountAll" label="تخفیف" outlined readonly></v-text-field>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-text-field v-model="formattedTransferCost" label="هزینه حمل و نقل" outlined readonly></v-text-field>
            </v-col>
          </v-row>
        </v-card-text>
      </v-window-item>

      <!-- تب دریافت‌ها -->
      <v-window-item value="payments">
        <v-card-text>
          <v-data-table v-if="item.relatedDocs.length" :header-props="{ class: 'custom-header' }" :headers="[
            { title: 'مشاهده', key: 'view' },
            { title: 'شماره', key: 'code' },
            { title: 'تاریخ', key: 'date' },
            { title: 'مبلغ', key: 'amount' },
          ]" :items="item.relatedDocs">
            <template v-slot:item.view="{ item }">
              <router-link :to="'/acc/accounting/view/' + item.code">
                <v-icon color="success">mdi-eye</v-icon>
              </router-link>
            </template>
            <template v-slot:item.amount="{ item }">
              {{ $filters.formatNumber(item.amount) }}
            </template>
          </v-data-table>
          <v-card-text v-else class="text-center text-danger">
            تاکنون سند دریافتی ثبت نشده است
          </v-card-text>
        </v-card-text>
      </v-window-item>
    </v-window>

    <v-dialog v-model="recDialog" max-width="600">
      <rec :windows-state="PayWindowsState" :person="person.id" :original-doc="item.doc.code" :total-amount="parseInt(item.doc.amount) - parseInt(totalRec)" />
    </v-dialog>

    <v-dialog v-model="recListDialog" max-width="800">
      <v-card>
        <v-card-title>
          <v-icon left>mdi-arrow-down-circle</v-icon>
          دریافت‌ها
        </v-card-title>
        <v-card-text>
          <rec-list :windows-state="recListWindowsState" :items="item.relatedDocs" />
        </v-card-text>
        <v-card-actions>
          <v-btn color="secondary" @click="recListDialog = false">بازگشت</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<style scoped>

</style>