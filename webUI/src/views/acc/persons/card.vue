<template>
  <!-- Toolbar بالای صفحه -->
  <v-toolbar color="toolbar" dense flat>
    <v-btn icon @click="$router.back()" class="d-none d-md-flex">
      <v-icon>mdi-arrow-right</v-icon>
    </v-btn>
    <v-toolbar-title class="text-primary-dark">
      {{ $t('pages.person_card.title') }}
    </v-toolbar-title>
    <v-spacer />
    <v-btn color="primary" size="small" @click="dialog = true" :loading="loading" prepend-icon="mdi-bank">
      {{ $t('dialog.banks_accounts') }}
    </v-btn>
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="" color="red">
          <v-tooltip activator="parent" :text="$t('dialog.export_pdf')" location="bottom" />
          <v-icon icon="mdi-file-pdf-box"></v-icon>
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_pdf') }}</v-list-subheader>
        <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="print(false)">
          <template v-slot:prepend>
            <v-icon color="green-darken-4" icon="mdi-check"></v-icon>
          </template>
        </v-list-item>
        <v-list-item class="text-dark" :title="$t('dialog.selected_all')" @click="print(true)">
          <template v-slot:prepend>
            <v-icon color="indigo-darken-4" icon="mdi-expand-all"></v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-menu>
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="" color="green">
          <v-tooltip activator="parent" :text="$t('dialog.export_excel')" location="bottom" />
          <v-icon icon="mdi-file-excel-box"></v-icon>
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_excel') }}</v-list-subheader>
        <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="excellOutput(false)">
          <template v-slot:prepend>
            <v-icon color="green-darken-4" icon="mdi-check"></v-icon>
          </template>
        </v-list-item>
        <v-list-item class="text-dark" :title="$t('dialog.selected_all')" @click="excellOutput(true)">
          <template v-slot:prepend>
            <v-icon color="indigo-darken-4" icon="mdi-expand-all"></v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-toolbar>

  <!-- دیالوگ حساب‌های بانکی -->
  <v-dialog v-model="dialog" max-width="500" persistent>
    <v-card>
      <v-toolbar color="primary-dark" dense flat>
        <v-toolbar-title class="text-white">{{ $t('dialog.banks_accounts') }}</v-toolbar-title>
        <v-spacer />
        <v-btn icon @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text class="pa-2">
        <template v-if="selectedPerson.accounts && selectedPerson.accounts.length > 0">
          <v-list dense>
            <v-list-item v-for="item in selectedPerson.accounts" :key="item.accountNum" class="pa-1">
              <template v-slot:title>
                <span class="bg-primary-dark text-white pa-1 rounded d-block">
                  {{ item.bank }}: {{ item.accountNum }}
                </span>
              </template>
              <template v-slot:subtitle>
                <div>{{ $t('pages.person.card_number') }}: {{ item.cardNum }}</div>
                <div>{{ $t('pages.person.shaba_number') }}: {{ item.shabaNum }}</div>
              </template>
            </v-list-item>
          </v-list>
        </template>
        <v-alert v-else type="error" dense text class="ma-0">
          {{ $t('pages.person_card.no_bank_accounts') }}
        </v-alert>
      </v-card-text>
      <v-card-actions>
        <v-btn color="secondary" text @click="dialog = false">{{ $t('dialog.cancel') }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- محتوای اصلی -->
  <v-container fluid class="pa-2">
    <v-row dense>
      <v-col cols="12" md="12">
        <v-autocomplete v-model="selectedPerson" :items="listPersons" item-title="nikename" item-value="code"
          return-object :label="$t('dialog.user_info')" dense hide-details prepend-inner-icon="mdi-account"
          :loading="loading" @update:search="debouncedSearchPerson" @update:model-value="updateRoute">
          <template v-slot:no-data>
            {{ $t('pages.person_card.no_results') }}
          </template>
          <template v-slot:item="{ props, item }">
            <v-list-item v-bind="props">
              <v-list-item-title>
                <v-icon small left>mdi-account</v-icon>
                {{ item.raw.nikename }}
              </v-list-item-title>
              <v-list-item-subtitle>
                <v-row dense>
                  <v-col cols="6">
                    <v-icon small left>mdi-phone</v-icon>
                    {{ item.raw.mobile }}
                  </v-col>
                  <v-col cols="6">
                    <v-icon small left>mdi-scale-balance</v-icon>
                    {{ $t('pages.person_card.balance') }}: {{ $filters.formatNumber(Math.abs(parseInt(item.raw.bs) -
                      parseInt(item.raw.bd))) }}
                    <span :class="parseInt(item.raw.bs) - parseInt(item.raw.bd) < 0 ? 'text-danger' : 'text-success'">
                      {{ parseInt(item.raw.bs) - parseInt(item.raw.bd) < 0 ? $t('pages.person_card.debtor') :
                        $t('pages.person_card.creditor') }} </span>
                  </v-col>
                </v-row>
              </v-list-item-subtitle>
            </v-list-item>
          </template>
        </v-autocomplete>
      </v-col>
      <v-col cols="12" md="6">
        <v-card flat outlined>
          <v-toolbar color="primary-dark" dense flat>
            <v-toolbar-title class="text-white">
              {{ $t('pages.person_card.account_card') }}
              <small class="text-info-light" v-if="selectedPerson">{{ selectedPerson.nikename }}</small>
            </v-toolbar-title>
          </v-toolbar>
          <v-card-text class="pa-2">
            <div class="text-subtitle-2">{{ $t('pages.person_card.accounting_code') }}: <span class="text-primary">{{
              selectedPerson.code || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person.nickname') }}: <span class="text-primary">{{
              selectedPerson.nikename || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person.name') }}: <span class="text-primary">{{
              selectedPerson.name || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person.phone') }}: <span class="text-primary">{{
              selectedPerson.tel || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person.mobile') }}: <span class="text-primary">{{
              selectedPerson.mobile || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person.address') }}: <span class="text-primary">{{
              selectedPerson.address || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person.description') }}: <span class="text-primary">{{
              selectedPerson.des || '-' }}</span></div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card flat outlined>
          <v-toolbar color="primary-dark" dense flat>
            <v-toolbar-title class="text-white">
              {{ $t('pages.person_card.account_status') }}
              <small class="text-info-light" v-if="selectedPerson">{{ selectedPerson.nikename }}</small>
            </v-toolbar-title>
          </v-toolbar>
          <v-card-text class="pa-2">
            <div class="text-subtitle-2">
              {{ $t('pages.person_card.accounting_status') }}:
              <span :class="{
                'text-success': selectedPerson.balance > 0,
                'text-danger': selectedPerson.balance < 0,
                'text-dark': selectedPerson.balance == 0
              }">
                {{ selectedPerson.balance > 0 ? $t('pages.person_card.creditor') : selectedPerson.balance < 0 ?
                  $t('pages.person_card.debtor') : $t('pages.person_card.settled') }} </span>
            </div>
            <div class="text-subtitle-2">{{ $t('pages.person_card.credit') }}: <span class="text-primary">{{
              $filters.formatNumber(selectedPerson.bs) || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person_card.debit') }}: <span class="text-primary">{{
              $filters.formatNumber(selectedPerson.bd) || '-' }}</span></div>
            <div class="text-subtitle-2">{{ $t('pages.person_card.accounting_balance') }}: <span class="text-primary">{{
              $filters.formatNumber(selectedPerson.balance) || '-' }}</span></div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- جدول تراکنش‌ها -->
    <v-row dense>
      <v-col cols="12">
        <v-data-table v-model="itemsSelected" :headers="headers" :items="items" :search="searchValue" :loading="loading"
          show-select dense :items-per-page="25" class="elevation-1" :header-props="{ class: 'custom-header' }">
          <template v-slot:top>
            <v-toolbar flat dense color="grey-lighten-4">
              <v-toolbar-title class="text-subtitle-1">{{ $t('pages.person_card.transactions') }}</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-text-field v-model="searchValue" dense hide-details
                prepend-inner-icon="mdi-magnify" />
            </v-toolbar>
          </template>
          <template v-slot:item.operation="{ item }">
            <v-btn variant="plain" icon size="small" :to="'/acc/accounting/view/' + item.code" color="success">
              <v-icon small>mdi-eye</v-icon>
            </v-btn>
          </template>
          <template v-slot:item.code="{ item }">
            {{ $filters.formatNumber(item.code) }}
          </template>
          <template v-slot:item.type="{ item }">
            <v-btn variant="plain" text size="small" :to="getTypeRoute(item.type, item.code)" class="text-none">
              {{ getTypeLabel(item.type) }}
            </v-btn>
          </template>
          <template v-slot:no-data>
            {{ $t('pages.person_card.no_data') }}
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </v-container>

  <v-overlay :value="loading" contained class="align-center justify-center">
    <v-progress-circular indeterminate size="64" />
  </v-overlay>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "card",
  data() {
    return {
      searchValue: '',
      listPersons: [],
      itemsSelected: [],
      selectedPerson: { accounts: [], balance: 0, bs: 0, bd: 0 },
      items: [],
      loading: ref(false),
      dialog: false,
      debounceTimeout: null, // برای مدیریت debounce
      headers: [
        { title: this.$t('dialog.operation'), key: "operation", align: "center", sortable: false },
        { title: this.$t('dialog.type'), key: "type", align: "center", sortable: true },
        { title: this.$t('dialog.invoice_num'), key: "code", align: "center", sortable: true },
        { title: this.$t('dialog.date'), key: "date", align: "center", sortable: true },
        { title: this.$t('app.body'), key: "des", align: "center" },
        { title: this.$t('pages.person_card.detail'), key: "ref", align: "center", sortable: true },
        { title: this.$t('pages.person_card.debit'), key: "bd", align: "center", sortable: true },
        { title: this.$t('pages.person_card.credit'), key: "bs", align: "center", sortable: true },
      ],
    };
  },
  mounted() {
    this.loadData();
  },
  methods: {
    debouncedSearchPerson(search) {
      // لغو تایمر قبلی اگه وجود داشته باشه
      if (this.debounceTimeout) {
        clearTimeout(this.debounceTimeout);
      }

      // تنظیم تایمر جدید برای ۱ ثانیه
      this.debounceTimeout = setTimeout(() => {
        this.searchPerson(search);
      }, 1000);
    },
    async searchPerson(search) {
      if (!search || search.length < 1) {
        this.listPersons = [];
        return;
      }
      this.loading = true;
      try {
        const response = await axios.post('/api/person/list/search', { search });
        this.listPersons = response.data;
      } catch (error) {
        console.error('Search error:', error);
        this.listPersons = [];
      } finally {
        this.loading = false;
      }
    },
    updateRoute() {
      if (this.selectedPerson && this.selectedPerson.code) {
        this.$router.push(this.selectedPerson.code);
        this.loadPerson(this.selectedPerson.code);
      }
    },
    async loadData() {
      this.loading = true;
      try {
        const response = await axios.post('/api/person/list/search');
        this.listPersons = response.data;
        const id = this.$route.params.id;
        if (id) {
          await this.loadPerson(id);
        } else if (response.data.length > 0) {
          this.selectedPerson = response.data[0];
          await this.loadPerson(this.selectedPerson.code);
        }
      } catch (error) {
        console.error('Load data error:', error);
      } finally {
        this.loading = false;
      }
    },
    async loadPerson(id) {
      this.loading = true;
      try {
        const personResponse = await axios.post('/api/person/info/' + id);
        this.selectedPerson = personResponse.data;

        const rowsResponse = await axios.post('/api/accounting/rows/search', { type: 'person', id });
        this.items = rowsResponse.data;
      } catch (error) {
        console.error('Load person error:', error);
        this.selectedPerson = { accounts: [], balance: 0, bs: 0, bd: 0 };
        this.items = [];
      } finally {
        this.loading = false;
      }
    },
    async excellOutput(allItems = true) {
      if (!allItems && this.itemsSelected.length === 0) {
        Swal.fire({ text: this.$t('pages.person_card.no_items_selected'), icon: 'info', confirmButtonText: this.$t('dialog.confirm') });
        return;
      }
      try {
        const response = await axios({
          method: 'post',
          url: '/api/person/card/list/excel',
          data: allItems ? { code: this.selectedPerson.code } : { code: this.selectedPerson.code, items: this.itemsSelected },
          responseType: 'arraybuffer',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'person-card-view.xlsx');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } catch (error) {
        console.error('Excel output error:', error);
      }
    },
    async print(allItems = true) {
      if (!this.selectedPerson) {
        Swal.fire({ text: this.$t('pages.person_card.no_items_selected'), icon: 'info', confirmButtonText: this.$t('dialog.confirm') });
        return;
      }
      if (!allItems && this.itemsSelected.length === 0) {
        Swal.fire({ text: this.$t('pages.person_card.no_items_selected'), icon: 'info', confirmButtonText: this.$t('dialog.confirm') });
        return;
      }
      try {
        const response = await axios.post('/api/person/card/list/print', allItems ? { code: this.selectedPerson.code } : { code: this.selectedPerson.code, items: this.itemsSelected });
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      } catch (error) {
        console.error('Print error:', error);
      }
    },
    getTypeRoute(type, code) {
      const routes = {
        sell: '/acc/sell/view/',
        buy: '/acc/buy/view/',
        rfbuy: '/acc/rfbuy/view/',
        rfsell: '/acc/rfsell/view/',
        person_send: '/acc/accounting/view/',
        person_receive: '/acc/accounting/view/',
        cost: '/acc/accounting/view/',
        income: '/acc/accounting/view/',
        sell_receive: '/acc/accounting/view/',
        buy_send: '/acc/accounting/view/',
        reject_cheque: '/acc/accounting/view/',
        modify_cheque: '/acc/accounting/view/',
        modify_cheque_output: '/acc/accounting/view/',
        pass_cheque: '/acc/accounting/view/',
      };
      return routes[type] + code;
    },
    getTypeLabel(type) {
      const labels = {
        sell: this.$t('pages.person_card.sell_invoice'),
        buy: this.$t('pages.person_card.buy_invoice'),
        rfbuy: this.$t('pages.person_card.return_buy'),
        rfsell: this.$t('pages.person_card.return_sell'),
        person_send: this.$t('pages.person_card.payment'),
        person_receive: this.$t('pages.person_card.receipt'),
        cost: this.$t('pages.person_card.cost'),
        income: this.$t('pages.person_card.income'),
        sell_receive: this.$t('pages.person_card.sell_receive'),
        buy_send: this.$t('pages.person_card.buy_send'),
        reject_cheque: this.$t('pages.person_card.reject_cheque'),
        modify_cheque: this.$t('pages.person_card.modify_cheque'),
        pass_cheque: this.$t('pages.person_card.pass_cheque'),
        modify_cheque_output: this.$t('pages.person_card.modify_cheque_output'),
      };
      return labels[type] || type;
    },
  },
};
</script>

<style scoped>
/* استایل‌های اضافی حذف شده چون Vuetify بیشتر نیازها رو پوشش می‌ده */
</style>