<template>
  <div class="sticky-container">
    <!-- تولبار بالای صفحه -->
    <v-toolbar color="toolbar" :title="$t('فاکتورهای خرید')">
      <template v-slot:prepend>
        <v-tooltip text="بازگشت" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-tooltip text="اضافه کردن جدید" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/buy/mod/"></v-btn>
        </template>
      </v-tooltip>
      <v-tooltip text="حذف" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-delete" color="danger" @click="deleteItems()"></v-btn>
        </template>
      </v-tooltip>
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="" color="green">
            <v-tooltip activator="parent" text="تغییر برچسب‌ها" location="bottom" />
            <v-icon icon="mdi-dots-horizontal-circle"></v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-subheader color="primary">تغییر برچسب‌ها</v-list-subheader>
          <v-list-item v-for="item in types" class="text-dark" :title="'تغییر به ' + item.label" @click="changeLabel(item)">
            <template v-slot:prepend>
              <v-icon color="green-darken-4" icon="mdi-label"></v-icon>
            </template>
          </v-list-item>
          <v-list-item class="text-dark" title="حذف برچسب‌ها" @click="changeLabel('clear')">
            <template v-slot:prepend>
              <v-icon color="red" icon="mdi-undo"></v-icon>
            </template>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-toolbar>

    <!-- فیلد جستجو و فیلتر -->
    <v-text-field
      hide-details
      color="green"
      class="pt-0 rounded-0 mb-0"
      density="compact"
      placeholder="جستجو ..."
      v-model="searchValue"
      type="text"
      clearable
    >
      <template v-slot:prepend-inner>
        <v-tooltip location="bottom" text="جستجو">
          <template v-slot:activator="{ props }">
            <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
          </template>
        </v-tooltip>
      </template>
      <template v-slot:append-inner>
        <v-menu :close-on-content-click="false">
          <template v-slot:activator="{ props }">
            <v-icon size="sm" v-bind="props" icon="" color="primary">
              <v-tooltip activator="parent" variant="plain" text="فیلترها" location="bottom" />
              <v-icon icon="mdi-filter"></v-icon>
            </v-icon>
          </template>
          <v-list>
            <v-list-subheader color="primary">
              <v-icon icon="mdi-filter"></v-icon>
              فیلترها
            </v-list-subheader>
            <v-list-item v-for="(item, index) in types" class="text-dark">
              <template v-slot:title>
                <div class="form-check form-check-inline mx-1">
                  <input @change="filterTable" v-model="types[index].checked" class="form-check-input" type="checkbox">
                  <label class="form-check-label">{{ item.label }}</label>
                </div>
              </template>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-text-field>

    <!-- جدول اصلی -->
    <EasyDataTable
      v-model:items-selected="itemsSelected"
      table-class-name="customize-table"
      show-index
      alternating
      :headers="headers"
      :items="items"
      theme-color="#1d90ff"
      header-text-direction="center"
      body-text-direction="center"
      rowsPerPageMessage="تعداد سطر"
      emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
      rowsOfPageSeparatorMessage="از"
      :loading="loading"
    >
      <template #item-operation="{ code }">
        <v-menu>
          <template v-slot:activator="{ props }">
            <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
          </template>
          <v-list>
            <v-list-item :to="'/acc/accounting/view/' + code" class="text-dark" title="سند حسابداری">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-file"></v-icon>
              </template>
            </v-list-item>
            <v-list-item :to="'/acc/buy/view/' + code" class="text-dark" title="مشاهده">
              <template v-slot:prepend>
                <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
              </template>
            </v-list-item>
            <v-list-item @click="openPrintModal(code)" class="text-dark" title="خروجی PDF">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-pdf-box"></v-icon>
              </template>
            </v-list-item>
            <v-list-item @click="canEditItem(code)" class="text-dark" title="ویرایش">
              <template v-slot:prepend>
                <v-icon icon="mdi-file-edit"></v-icon>
              </template>
            </v-list-item>
            <v-list-item @click="deleteItem(code)" class="text-dark" title="حذف">
              <template v-slot:prepend>
                <v-icon color="deep-orange-accent-4" icon="mdi-trash-can"></v-icon>
              </template>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
      <template #item-label="{ label }">
        <span v-if="label">
          <span v-if="label.code == 'payed'" class="text-success">{{ label.label }}</span>
          <span v-if="label.code == 'returned'" class="text-danger">{{ label.label }}</span>
          <span v-if="label.code == 'accepted'" class="text-info">{{ label.label }}</span>
        </span>
      </template>
      <template #item-des="{ des }">
        {{ des.replace("فاکتور خرید:", "") }}
      </template>
      <template #item-relatedDocsCount="{ relatedDocsCount, relatedDocsPays }">
        <span v-if="relatedDocsCount != '0'" class="text-success">
          <v-icon small>mdi-currency-usd</v-icon>
          {{ $filters.formatNumber(relatedDocsPays) }}
        </span>
      </template>
      <template #item-amount="{ amount }">
        <span class="text-dark">
          {{ $filters.formatNumber(amount) }}
        </span>
      </template>
      <template #item-transferCost="{ transferCost }">
        <span class="text-dark">
          {{ $filters.formatNumber(transferCost) }}
        </span>
      </template>
      <template #item-discountAll="{ discountAll }">
        <span class="text-dark">
          {{ $filters.formatNumber(discountAll) }}
        </span>
      </template>
      <template #item-person="{ person }">
        <router-link :to="'/acc/persons/card/view/' + person.code">
          {{ person.nikename }}
        </router-link>
      </template>
      <template #item-code="{ code }">
        <router-link :to="'/acc/buy/view/' + code">
          {{ code }}
        </router-link>
      </template>
    </EasyDataTable>

    <!-- مودال چاپ -->
    <v-dialog v-model="printModal" width="auto">
      <v-card subtitle="برای تغییر تنظیمات پیشفرض به بخش تنظیمات چاپ مراجعه کنید" prepend-icon="mdi-file-pdf-box" title="خروجی PDF">
        <template v-slot:text>
          <v-select
            class="mb-2"
            v-model="printOptions.paper"
            :items="paperSizes"
            label="سایز کاغذ و حالت چاپ"
          ></v-select>
          <v-switch inset v-model="printOptions.bidInfo" color="primary" label="اطلاعات کسب‌وکار" hide-details></v-switch>
          <v-switch inset v-model="printOptions.pays" color="primary" label="نمایش پرداخت‌های فاکتور" hide-details></v-switch>
          <v-switch inset v-model="printOptions.note" color="primary" label="یاداشت پایین فاکتور" hide-details></v-switch>
          <v-switch inset v-model="printOptions.taxInfo" color="primary" label="مالیات به تفکیک اقلام" hide-details></v-switch>
          <v-switch inset v-model="printOptions.discountInfo" color="primary" label="تخفیف به تفکیک اقلام" hide-details></v-switch>
        </template>
        <template v-slot:actions>
          <v-btn variant="tonal" prepend-icon="mdi-printer" color="primary" text="چاپ" @click="printModal = false; printInvoice()"></v-btn>
          <v-btn variant="tonal" prepend-icon="mdi-undo" color="secondary" text="لغو" @click="printModal = false"></v-btn>
        </template>
      </v-card>
    </v-dialog>

    <!-- جمع‌بندی پایین جدول -->
    <v-card class="mt-3" elevation="1">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <span class="text-dark">
              <v-icon left>mdi-format-list-bulleted</v-icon>
              مبلغ کل:
            </span>
            <span class="text-primary">
              {{ $filters.formatNumber(sumTotal) }}
              {{ $filters.getActiveMoney().shortName }}
            </span>
          </v-col>
          <v-col cols="12" md="6">
            <span class="text-dark">
              <v-icon left>mdi-check-all</v-icon>
              جمع مبلغ موارد انتخابی:
            </span>
            <span class="text-primary">
              {{ $filters.formatNumber(sumSelected) }}
              {{ $filters.getActiveMoney().shortName }}
            </span>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "list",
  data: () => ({
    printModal: false,
    printOptions: {
      pays: true,
      note: true,
      bidInfo: true,
      taxInfo: true,
      discountInfo: true,
      selectedPrintCode: 0,
      paper: 'A4-L'
    },
    paperSizes: [
      { title: 'A4 عمودی', value: 'A4' },
      { title: 'A4 افقی', value: 'A4-L' },
      { title: 'A5 عمودی', value: 'A5' },
      { title: 'A5 افقی', value: 'A5-L' },
    ],
    sumSelected: 0,
    sumTotal: 0,
    itemsSelected: [],
    searchValue: '',
    types: [],
    loading: ref(true),
    items: [],
    orgItems: [],
    headers: [
      { text: "عملیات", value: "operation" },
      { text: "فاکتور", value: "code", sortable: true },
      { text: "تاریخ", value: "date", sortable: true },
      { text: "خریدار", value: "person", sortable: true },
      { text: "تخفیف", value: "discountAll", sortable: true },
      { text: "حمل و نقل", value: "transferCost", sortable: true },
      { text: "مبلغ", value: "amount", sortable: true },
      { text: "پرداختی", value: "relatedDocsCount", sortable: true },
      { text: "برچسب", value: "label", width: 100 },
      { text: "شرح", value: "des", sortable: true },
    ]
  }),
  methods: {
    openPrintModal(code) {
      this.printOptions.selectedPrintCode = code;
      this.printModal = true;
    },
    changeLabel(label) {
      if (this.itemsSelected.length == 0) {
        Swal.fire({
          text: 'هیچ موردی انتخاب نشده است.',
          icon: 'warning',
          confirmButtonText: 'قبول'
        });
      } else {
        this.loading = true;
        axios.post('/api/buy/label/change', {
          'items': this.itemsSelected,
          'label': label
        }).then((response) => {
          this.loading = false;
          if (response.data.code == 0) {
            Swal.fire({
              text: 'فاکتور‌ها با موفقیت ویرایش شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            });
            this.itemsSelected = [];
          } else if (response.data.result == 2) {
            Swal.fire({
              text: response.data.message,
              icon: 'warning',
              confirmButtonText: 'قبول'
            });
          }
          this.loadData();
        });
      }
    },
    filterTable() {
      this.loading = true;
      let calcItems = [];
      let isAll = true;
      let selectedTypes = [];
      this.types.forEach((item) => {
        if (item.checked == true) {
          isAll = false;
          selectedTypes.push(item);
        }
      });
      if (isAll) {
        this.items = this.orgItems;
      } else {
        this.orgItems.forEach((item) => {
          selectedTypes.forEach((st) => {
            if (item.label) {
              if (st.code == item.label.code) {
                let existBefore = false;
                calcItems.forEach((ri) => {
                  if (item.label.code == ri.code) {
                    existBefore = true;
                  }
                });
                if (existBefore == false) {
                  calcItems.push(item);
                }
              }
            }
          });
        });
        this.items = calcItems;
      }
      this.loading = false;
    },
    loadData() {
      axios.post("/api/printers/options/info").then((response) => {
        this.printOptions = response.data.buy;
      });

      axios.post('/api/invoice/types', {
        type: 'buy'
      }).then((response) => {
        this.types = response.data;
      });

      axios.post('/api/buy/docs/search', {
        type: 'buy'
      }).then((response) => {
        this.items = response.data;
        this.orgItems = response.data;
        this.items.forEach((item) => {
          this.sumTotal += parseInt(item.amount);
        });
        this.loading = false;
      });
    },
    canEditItem(code) {
      this.loading = true;
      axios.post('/api/buy/edit/can/' + code).then((response) => {
        this.loading = false;
        if (response.data.result == false) {
          Swal.fire({
            text: 'این فاکتور به دلیل وجود اسناد پرداخت یا حواله‌های انبار مرتبط با آن قابل ویرایش نیست',
            confirmButtonText: 'قبول',
            icon: 'error'
          });
        } else {
          this.$router.push('/acc/buy/mod/' + code);
        }
      });
    },
    deleteItems() {
      if (this.itemsSelected.length == 0) {
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
            axios.post('/api/accounting/remove/group', {
              'items': this.itemsSelected
            }).then((response) => {
              this.loading = false;
              if (response.data.result == 1) {
                this.loadData();
                Swal.fire({
                  text: 'فاکتورها با موفقیت حذف شد.',
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
            });
          }
        });
      }
    },
    printInvoice(pdf = true, cloudPrinters = true) {
      this.loading = true;
      axios.post('/api/buy/print/invoice', { 
        'code': this.printOptions.selectedPrintCode,
        'pdf': pdf,
        'printers': cloudPrinters,
        'printOptions': this.printOptions
      }).then((response) => {
        this.loading = false;
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
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
          axios.post('/api/accounting/remove', {
            'code': code
          }).then((response) => {
            if (response.data.result == 1) {
              let index = 0;
              for (let z = 0; z < this.items.length; z++) {
                index++;
                if (this.items[z]['code'] == code) {
                  this.items.splice(index - 1, 1);
                }
              }
              Swal.fire({
                text: 'فاکتور خرید با موفقیت حذف شد.',
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
          });
        }
      });
    }
  },
  beforeMount() {
    this.loadData();
  },
  watch: {
    itemsSelected: {
      handler: function (val) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((item) => {
          if (typeof item.amount.valueOf() === "string") {
            this.sumSelected += parseInt(item.amount.replaceAll(",", ""));
          } else {
            this.sumSelected += item.amount;
          }
        });
      },
      deep: true
    },
    searchValue: {
      handler: function (val) {
        if (this.searchValue == '') {
          this.items = this.orgItems;
        } else {
          let temp = [];
          this.orgItems.forEach((item) => {
            if (item.person.nikename.includes(this.searchValue)) {
              temp.push(item);
            } else if (item.date.includes(this.searchValue)) {
              temp.push(item);
            } else if (item.amount.toString().includes(this.searchValue)) {
              temp.push(item);
            } else if (item.des.includes(this.searchValue)) {
              temp.push(item);
            } else if (item.code.includes(this.searchValue)) {
              temp.push(item);
            } else if (item.label) {
              if (item.label.label.includes(this.searchValue)) {
                temp.push(item);
              }
            }
          });
          this.items = temp;
        }
      },
      deep: false
    }
  }
}
</script>

<style scoped>
</style>