<template>
  <div class="sticky-container">
    <v-toolbar color="toolbar" :title="$t('drawer.sell_invoices_long')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.back')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-tooltip :text="$t('dialog.add_new')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/sell/mod/"></v-btn>
        </template>
      </v-tooltip>
      <v-tooltip :text="$t('dialog.delete')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-delete" color="danger" @click="deleteItems()"></v-btn>
        </template>
      </v-tooltip>
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="" color="green">
            <v-tooltip activator="parent" :text="$t('dialog.change_labels')" location="bottom" />
            <v-icon icon="mdi-dots-horizontal-circle"></v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-subheader color="primary">{{ $t('dialog.change_labels') }}</v-list-subheader>
          <v-list-item v-for="item in types" class="text-dark" :title="$t('dialog.change_to') + ' ' + item.label" @click="changeLabel(item)">
            <template v-slot:prepend>
              <v-icon color="green-darken-4" icon="mdi-label"></v-icon>
            </template>
          </v-list-item>
          <v-list-item class="text-dark" :title="$t('dialog.delete_labels')" @click="changeLabel('clear')">
            <template v-slot:prepend>
              <v-icon color="red" icon="mdi-undo"></v-icon>
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
      <!-- دکمه تنظیمات ستون‌ها -->
      <v-tooltip :text="$t('dialog.column_settings')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-table-cog" color="primary" @click="showColumnDialog = true" />
        </template>
      </v-tooltip>
    </v-toolbar>
    <v-text-field hide-details color="green" class="pt-0 rounded-0 mb-0" density="compact" :placeholder="$t('dialog.search_txt')" v-model="searchValue" type="text" clearable>
      <template v-slot:prepend-inner>
        <v-tooltip location="bottom" :text="$t('dialog.search')">
          <template v-slot:activator="{ props }">
            <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
          </template>
        </v-tooltip>
      </template>
      <template v-slot:append-inner>
        <v-menu :close-on-content-click="false">
          <template v-slot:activator="{ props }">
            <v-icon size="sm" v-bind="props" icon="" color="primary">
              <v-tooltip activator="parent" variant="plain" :text="$t('dialog.filters')" location="bottom" />
              <v-icon icon="mdi-filter"></v-icon>
            </v-icon>
          </template>
          <v-list>
            <v-list-subheader color="primary">
              <v-icon icon="mdi-filter"></v-icon>
              {{ $t('dialog.filters') }}
            </v-list-subheader>
            <v-list-item v-for="(item, index) in types" class="text-dark">
              <template v-slot:title>
                <div class="form-check form-check-inline mx-1">
                  <input @change="filterTable" v-model="types[index].checked" class="form-check-input" type="checkbox">
                  <label class="form-check-label">{{ item.label }}</label>
                </div>
              </template>
            </v-list-item>
            <v-list-item>
              <v-select class="py-2 my-2" v-model="dateFilter" :items="dateFilterOptions" label="فیلتر تاریخ" @update:model-value="filterTable" dense />
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-text-field>
    <v-data-table-server
      v-model:items-per-page="serverOptions.rowsPerPage"
      v-model:page="serverOptions.page"
      :headers="visibleHeaders"
      :items="items"
      :items-length="total"
      :loading="loading"
      :no-data-text="$t('table.no_data')"
      v-model="itemsSelected"
      v-model:expanded="expanded"
      @update:options="updateServerOptions"
      show-select
      class="elevation-1 data-table-wrapper"
      item-value="code"
      :max-height="tableHeight"
      :header-props="{ class: 'custom-header' }"
      @update:expanded="onExpandedUpdate"
      multi-sort
    >
      <template v-slot:item.expand="{ item }">
        <v-btn variant="text" size="small" color="primary" :icon="expanded.includes(item.code) ? 'mdi-chevron-up' : 'mdi-chevron-down'" @click.stop="toggleExpand(item.code)" />
      </template>
      <template v-slot:item.receivedAmount="{ item }">
        <span class="text-dark">
          {{ $filters.formatNumber(item.receivedAmount) }}
        </span>
      </template>
      <template v-slot:item.operation="{ item }">
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
          </template>
          <v-list>
            <v-list-item class="text-dark" :title="$t('dialog.accounting_doc')" :to="'/acc/accounting/view/' + item.code">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-file"></v-icon>
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.view')" :to="'/acc/sell/view/' + item.code">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.export_pdf')" @click="printOptions.selectedPrintCode = item.code; modal = true;">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-pdf-box"></v-icon>
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.edit')" @click="canEditItem(item.code)">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-edit"></v-icon>
              </template>
            </v-list-item>
            <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="deleteItem(item.code)">
              <template v-slot:prepend>
                <v-icon color="deep-orange-accent-4" icon="mdi-trash-can"></v-icon>
              </template>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
      <template v-slot:item.label="{ item }">
        <span v-if="item.label">
          <span v-if="item.label.code == 'payed'" class="text-success">{{ item.label.label }}</span>
          <span v-if="item.label.code == 'returned'" class="text-danger">{{ item.label.label }}</span>
          <span v-if="item.label.code == 'accepted'" class="text-info">{{ item.label.label }}</span>
        </span>
      </template>
      <template v-slot:item.des="{ item }">
        {{ item.des.replace("فاکتور فروش:", "") }}
      </template>
      <template v-slot:item.relatedDocsCount="{ item }">
        <span v-if="item.relatedDocsCount != '0'" class="text-success"><i class="fa fa-money"></i>
          {{ $filters.formatNumber(item.relatedDocsPays) }}
        </span>
      </template>
      <template v-slot:item.amount="{ item }">
        <span class="text-dark">
          {{ $filters.formatNumber(item.amount) }}
        </span>
      </template>
      <template v-slot:item.profit="{ item }">
        <span v-if="item.profit >= 0" class="text-dark">
          {{ $filters.formatNumber(item.profit) }}
        </span>
        <span v-else class="text-danger">
          {{ $filters.formatNumber(Math.abs(item.profit)) }}
          (زیان)
        </span>
      </template>
      <template v-slot:item.transferCost="{ item }">
        <span class="text-dark">
          {{ $filters.formatNumber(item.transferCost) }}
        </span>
      </template>
      <template v-slot:item.discountAll="{ item }">
        <span class="text-dark">
          {{ $filters.formatNumber(item.discountAll) }}
        </span>
      </template>
      <template v-slot:item.person="{ item }">
        <router-link v-if="item.person" :to="'/acc/persons/card/view/' + item.person.code">
          {{ item.person.nikename }}
        </router-link>
        <span v-else>-</span>
      </template>
      <template v-slot:item.code="{ item }">
        <router-link :to="'/acc/sell/view/' + item.code">
          {{ item.code }}
        </router-link>
      </template>
      <template v-slot:expanded-row="{ item }">
        <tr>
          <td :colspan="visibleHeaders.length" class="expanded-row">
            <v-progress-circular v-if="loading && expanded.includes(item.code)" indeterminate color="primary" class="my-2" />
            <v-list v-else dense class="expanded-list">
              <v-list-item v-for="row in item.rows" :key="row.id">
                <v-list-item-subtitle>
                  <v-chip color="blue lighten-4" small class="mr-1">{{ row.commodity?.name || '-' }}</v-chip>
                  <v-chip color="green lighten-4" small class="mr-1">تعداد: {{ row.commdityCount }}</v-chip>
                  <v-chip color="orange lighten-4" small>قیمت کل ردیف: {{ $filters.formatNumber(row.bs) }}</v-chip>
                </v-list-item-subtitle>
                <v-list-item-title class="font-weight-bold my-1">{{ row.des }}</v-list-item-title>
              </v-list-item>
              <v-list-item v-if="!item.rows || item.rows.length === 0">
                <v-list-item-title class="text-grey">داده‌ای برای نمایش وجود ندارد</v-list-item-title>
              </v-list-item>
            </v-list>
          </td>
        </tr>
      </template>
    </v-data-table-server>
    <div class="footer-summary">
    <div class="summary-item">
      <span class="summary-label">جمع کل فاکتورهای صفحه:</span>
      <span class="summary-value">{{ $filters.formatNumber(sumTotal) }}</span>
    </div>
    <div class="summary-item">
      <span class="summary-label">جمع موارد انتخاب شده:</span>
      <span class="summary-value">{{ $filters.formatNumber(sumSelected) }}</span>
    </div>
  </div>
    <v-dialog v-model="showColumnDialog" max-width="500px">
      <v-card>
        <v-toolbar dark>
          <v-toolbar-title>{{ $t('dialog.manage_columns') }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click="showColumnDialog = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        <v-card-text>
          <v-row>
            <v-col v-for="header in allHeaders" :key="header.value" cols="12" sm="4" class="my-0 py-0">
              <v-checkbox v-model="header.visible" :label="header.title" @update:model-value="updateColumnVisibility" hide-details="auto" />
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="modal" width="auto">
      <v-card>
        <v-toolbar color="primary">
          <v-toolbar-title class="text-white">
            <v-icon icon="mdi-file-pdf-box" class="ml-2"></v-icon>
            {{ $t('dialog.export_pdf') }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-tooltip :text="$t('dialog.print')" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-printer" color="white" @click="modal = false; printInvoice()"></v-btn>
            </template>
          </v-tooltip>
          <v-tooltip :text="$t('dialog.cancel')" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-close" color="white" @click="modal = false"></v-btn>
            </template>
          </v-tooltip>
        </v-toolbar>
        <v-card-text>
          <v-select class="mb-2" v-model="printOptions.paper" :items="paperSizes" :label="$t('dialog.paper_size')">
          </v-select>
          <v-row>
            <v-col cols="12" sm="6">
              <v-tooltip :text="$t('dialog.bid_info_label')" location="right">
                <template v-slot:activator="{ props }">
                  <v-switch v-bind="props" inset v-model="printOptions.bidInfo" color="primary" :label="$t('dialog.bid_info_label')" hide-details></v-switch>
                </template>
              </v-tooltip>
              <v-tooltip :text="$t('dialog.invoice_pays')" location="right">
                <template v-slot:activator="{ props }">
                  <v-switch v-bind="props" inset v-model="printOptions.pays" color="primary" :label="$t('dialog.invoice_pays')" hide-details></v-switch>
                </template>
              </v-tooltip>
              <v-tooltip :text="$t('dialog.invoice_footer_note')" location="right">
                <template v-slot:activator="{ props }">
                  <v-switch v-bind="props" inset v-model="printOptions.note" color="primary" :label="$t('dialog.invoice_footer_note')" hide-details></v-switch>
                </template>
              </v-tooltip>
            </v-col>
            <v-col cols="12" sm="6">
              <v-tooltip :text="$t('dialog.tax_dexpo')" location="right">
                <template v-slot:activator="{ props }">
                  <v-switch v-bind="props" inset v-model="printOptions.taxInfo" color="primary" :label="$t('dialog.tax_dexpo')" hide-details></v-switch>
                </template>
              </v-tooltip>
              <v-tooltip :text="$t('dialog.discount_dexpo')" location="right">
                <template v-slot:activator="{ props }">
                  <v-switch v-bind="props" inset v-model="printOptions.discountInfo" color="primary" :label="$t('dialog.discount_dexpo')" hide-details></v-switch>
                </template>
              </v-tooltip>
              <v-tooltip :text="$t('dialog.business_stamp')" location="right">
                <template v-slot:activator="{ props }">
                  <v-switch v-if="isPluginActive('accpro')" v-bind="props" inset v-model="printOptions.businessStamp" color="primary" :label="$t('dialog.business_stamp')" hide-details></v-switch>
                </template>
              </v-tooltip>
              <v-tooltip :text="$t('dialog.invoice_index')" location="right">
                <template v-slot:activator="{ props }">
                  <v-switch v-if="isPluginActive('accpro')" v-bind="props" inset v-model="printOptions.invoiceIndex" color="primary" :label="$t('dialog.invoice_index')" hide-details></v-switch>
                </template>
              </v-tooltip>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { defineComponent, reactive, watch } from "vue";
import debounce from "lodash/debounce";

export default defineComponent({
  name: "list",
  data() {
    let self = this;
    return {
      paperSizes: [
        { title: self.$t('dialog.a4p'), value: 'A4' },
        { title: self.$t('dialog.a4l'), value: 'A4-L' },
        { title: self.$t('dialog.a5p'), value: 'A5' },
        { title: self.$t('dialog.a5l'), value: 'A5-L' },
      ],
      modal: false,
      printOptions: {
        pays: true,
        note: true,
        bidInfo: true,
        taxInfo: true,
        discountInfo: true,
        selectedPrintCode: 0,
        paper: 'A4-L',
        businessStamp: true,
        invoiceIndex: true
      },
      plugins: {},
      sumSelected: 0,
      sumTotal: 0,
      itemsSelected: [],
      searchValue: '',
      types: [],
      loading: false,
      items: [],
      total: 0,
      expanded: [],
      serverOptions: reactive({
        page: 1,
        rowsPerPage: 10,
        sortBy: [], // برای پشتیبانی از Multi-Sort
      }),
      allHeaders: [
        { title: "جزئیات", value: "expand", sortable: false, visible: true, width: 80 },
        { title: "عملیات", value: "operation", sortable: false, visible: true, width: 100 },
        { title: "فاکتور", value: "code", sortable: true, visible: true, width: 120 },
        { title: "تاریخ", value: "date", sortable: true, visible: true, width: 120 },
        { title: "خریدار", value: "person", sortable: true, visible: true, width: 150 },
        { title: "تخفیف", value: "discountAll", sortable: true, visible: true, width: 120 },
        { title: "حمل و نقل", value: "transferCost", sortable: true, visible: true, width: 120 },
        { title: "مبلغ", value: "amount", sortable: true, visible: true, width: 150 },
        { title: "سود فاکتور", value: "profit", sortable: true, visible: true, width: 150 },
        { title: "پرداختی", value: "receivedAmount", sortable: true, visible: true, width: 150 },
        { title: "برچسب", value: "label", sortable: true, visible: true, width: 120 },
        { title: "شرح", value: "des", sortable: true, visible: true, minWidth: 200 }
      ],
      showColumnDialog: false,
      dateFilter: 'all',
      dateFilterOptions: [
        { title: 'همه', value: 'all' },
        { title: 'امروز', value: 'today' },
        { title: 'این هفته', value: 'week' },
        { title: 'این ماه', value: 'month' },
      ],
      menu: {},
    };
  },
  computed: {
    visibleHeaders() {
      return this.allHeaders.filter(header => header.visible);
    },
    tableHeight() {
      return window.innerHeight - 200;
    },
  },
  methods: {
    isPluginActive(pluginCode) {
      return this.plugins && this.plugins[pluginCode] !== undefined;
    },
    async loadPlugins() {
      try {
        const response = await axios.post('/api/plugin/get/actives');
        this.plugins = response.data || {};
      } catch (error) {
        console.error('Error loading plugins:', error);
        this.plugins = {};
      }
    },
    changeLabel(label) {
      if (this.itemsSelected.length === 0) {
        Swal.fire({
          text: 'هیچ موردی انتخاب نشده است.',
          icon: 'warning',
          confirmButtonText: 'قبول'
        });
      } else {
        this.loading = true;
        const selectedItems = this.itemsSelected.map(code => ({ code }));
        axios.post('/api/sell/label/change', {
          'items': selectedItems,
          'label': label
        }).then((response) => {
          this.loading = false;
          if (response.data.code === 0) {
            Swal.fire({
              text: 'فاکتور‌ها با موفقیت ویرایش شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            });
            this.itemsSelected = [];
            this.loadData();
          } else if (response.data.result === 2) {
            Swal.fire({
              text: response.data.message,
              icon: 'warning',
              confirmButtonText: 'قبول'
            });
          }
        }).catch(() => {
          this.loading = false;
        });
      }
    },
    filterTable() {
      this.serverOptions.page = 1;
      this.loadData();
    },
    async loadData() {
      this.loading = true;
      try {
        if (!this.printOptions.selectedPrintCode) {
          const printResponse = await axios.post("/api/printers/options/info");
          this.printOptions = printResponse.data.sell || this.printOptions;
        }

        const typesResponse = await axios.post('/api/invoice/types', { type: 'sell' });
        this.types = typesResponse.data.map(t => ({
          ...t,
          checked: this.types.find(x => x.code === t.code)?.checked ?? false
        }));

        const response = await axios.post('/api/sell/docs/search', {
          type: 'sell',
          search: this.searchValue,
          page: this.serverOptions.page,
          perPage: this.serverOptions.rowsPerPage,
          types: this.types.filter(t => t.checked).map(t => t.code),
          dateFilter: this.dateFilter,
          sortBy: this.serverOptions.sortBy, // ارسال اطلاعات مرتب‌سازی
        });

        this.items = (response.data.items || []).map(item => ({
          ...item,
          receivedAmount: item.relatedDocsPays || 0, // نگاشت به receivedAmount
        })).filter(item => item.code && typeof item.code !== 'undefined');
        this.total = Number(response.data.total) || 0;
        this.sumTotal = this.items.reduce((sum, item) => sum + parseInt(item.amount || 0), 0);
      } catch (error) {
        console.error('Error loading data:', error);
        this.items = [];
        this.total = 0;
        this.sumTotal = 0;
        Swal.fire({
          text: 'خطا در بارگذاری داده‌ها: ' + error.message,
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      } finally {
        this.loading = false;
      }
    },
    canEditItem(code) {
      this.loading = true;
      axios.post('/api/sell/edit/can/' + code).then((response) => {
        this.loading = false;
        if (response.data.result == false) {
          Swal.fire({
            text: 'این فاکتور به دلیل وجود اسناد پرداخت یا حواله‌های انبار مرتبط با آن قابل ویرایش نیست',
            confirmButtonText: 'قبول',
            icon: 'error'
          });
        } else {
          this.$router.push('/acc/sell/mod/' + code);
        }
      }).catch(() => {
        this.loading = false;
      });
    },
    deleteItems() {
      if (this.itemsSelected.length === 0) {
        Swal.fire({
          text: 'هیچ موردی انتخاب نشده است.',
          icon: 'warning',
          confirmButtonText: 'قبول'
        });
      } else {
        Swal.fire({
          text: 'آیا برای حذف این مورد مطمئن هستید؟ تمامی اسناد پرداخت و حواله‌های انبار همراه فاکتور نیز حذف خواهند شد.',
          showCancelButton: true,
          confirmButtonText: 'بله',
          cancelButtonText: 'خیر',
          icon: 'warning'
        }).then((result) => {
          if (result.isConfirmed) {
            this.loading = true;
            const selectedItems = this.itemsSelected.map(code => ({ code }));
            axios.post('/api/accounting/remove/group', {
              'items': selectedItems,
              'type': 'sell'
            }).then((response) => {
              this.loading = false;
              if (response.data.result === 1) {
                this.loadData();
                Swal.fire({
                  text: 'فاکتورها با موفقیت حذف شد.',
                  icon: 'success',
                  confirmButtonText: 'قبول'
                });
              } else if (response.data.result === 2) {
                Swal.fire({
                  text: response.data.message,
                  icon: 'warning',
                  confirmButtonText: 'قبول'
                });
              }
            }).catch((error) => {
              this.loading = false;
              console.error('Error:', error.response?.data || error.message);
              Swal.fire({
                text: 'خطایی در حذف فاکتورها رخ داد.',
                icon: 'error',
                confirmButtonText: 'قبول'
              });
            });
          }
        });
      }
    },
    printInvoice(pdf = true, cloudePrinters = true) {
      this.loading = true;
      axios.post('/api/sell/print/invoice', {
        'code': this.printOptions.selectedPrintCode,
        'pdf': pdf,
        'printers': cloudePrinters,
        'printOptions': this.printOptions
      }).then((response) => {
        this.loading = false;
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      }).catch(() => {
        this.loading = false;
      });
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف این مورد مطمئن هستید؟ تمامی اسناد پرداخت و حواله‌های انبار همراه فاکتور نیز حذف خواهند شد.',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'خیر',
        icon: 'warning'
      }).then((result) => {
        if (result.isConfirmed) {
          this.loading = true;
          axios.post('/api/accounting/remove', {
            'code': code
          }).then((response) => {
            this.loading = false;
            if (response.data.result == 1) {
              this.items = this.items.filter(item => item.code !== code);
              this.sumTotal = this.items.reduce((sum, item) => sum + parseInt(item.amount || 0), 0);
              Swal.fire({
                text: 'فاکتور فروش با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            } else if (response.data.result == 2) {
              Swal.fire({
                text: response.data.message,
                icon: 'warning',
                confirmButtonText: 'قبول'
              });
            }
          }).catch(() => {
            this.loading = false;
          });
        }
      });
    },
    updateServerOptions(options) {
      this.serverOptions.page = options.page;
      this.serverOptions.rowsPerPage = options.itemsPerPage;
      this.serverOptions.sortBy = options.sortBy || []; // مدیریت Multi-Sort
      this.loadData();
    },
    debouncedLoadData: debounce(function () {
      this.loadData();
    }, 300),
    updateColumnVisibility() {
      localStorage.setItem('sellInvoiceColumns', JSON.stringify(this.allHeaders));
    },
    loadColumnSettings() {
      const savedColumns = localStorage.getItem('sellInvoiceColumns');
      if (savedColumns) {
        const parsedColumns = JSON.parse(savedColumns);
        this.allHeaders = this.allHeaders.map(header => ({
          ...header,
          visible: parsedColumns.find(h => h.value === header.value)?.visible ?? true,
        }));
      }
    },
    async onExpandedUpdate(expandedItems) {
      const newlyExpandedCode = expandedItems.find(code => {
        const item = this.items.find(i => i.code === code);
        return item && !item.rows;
      });

      if (newlyExpandedCode) {
        const item = this.items.find(i => i.code === newlyExpandedCode);
        if (!item) {
          Swal.fire({
            text: 'آیتم با این کد یافت نشد.',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
          return;
        }

        this.loading = true;
        try {
          const response = await axios.get(`/api/sell/rows/${newlyExpandedCode}`);
          item.rows = response.data.rows.filter(row => row.commodity !== null) || [];
          if (item.rows.length === 0) {
            Swal.fire({
              text: 'هیچ ردیفی برای این فاکتور یافت نشد.',
              icon: 'warning',
              confirmButtonText: 'قبول'
            });
          }
        } catch (error) {
          item.rows = [];
          Swal.fire({
            text: 'خطا در بارگذاری اقلام فاکتور: ' + (error.response?.data?.detail || error.message),
            icon: 'error',
            confirmButtonText: 'قبول'
          });
        } finally {
          this.loading = false;
        }
      }
      this.expanded = expandedItems;
    },
    showMenu(code) {
      this.menu[code] = true;
    },
    toggleExpand(code) {
      if (this.expanded.includes(code)) {
        this.expanded = this.expanded.filter(item => item !== code);
      } else {
        this.expanded = [code];
      }
      this.onExpandedUpdate(this.expanded);
    },
  },
  created() {
    this.loadColumnSettings();
    this.loadPlugins();
    this.loadData();
  },
  watch: {
    searchValue: {
      handler() {
        this.serverOptions.page = 1;
        this.debouncedLoadData();
      },
      immediate: false,
    },
    itemsSelected: {
      handler(val) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((code) => {
          const selectedItem = this.items.find(item => item.code === code);
          if (selectedItem) {
            const amount = selectedItem.amount || 0;
            if (typeof amount === "string") {
              this.sumSelected += parseInt(amount.replaceAll(",", "") || 0);
            } else {
              this.sumSelected += amount;
            }
          }
        });
      },
      deep: true
    }
  }
});
</script>

<style>
.footer-summary {
  background-color: #f5f5f5;
  padding: 12px 24px;
  display: flex;
  justify-content: space-between;
  border-top: 1px solid #e0e0e0;
  margin-top: 8px;
}

.summary-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.summary-label {
  font-weight: 500;
  color: #666;
}

.summary-value {
  font-weight: bold;
  color: #1976d2;
}

.data-table-wrapper {
  margin-bottom: 0;
}
</style>