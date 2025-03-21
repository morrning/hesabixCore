<template>
  <v-toolbar color="toolbar" :title="$t('drawer.presells')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('dialog.add_new')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/acc/presell/mod/"></v-btn>
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
        <v-list-item v-for="item in types" class="text-dark" :title="$t('dialog.change_to') + ' ' + item.label"
          @click="changeLabel(item)">
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
  </v-toolbar>
  <v-row class="pa-1">
    <v-col>
      <v-text-field :loading="loading" color="green" class="mb-0 pt-0 rounded-0" hide-details="auto" density="compact"
        :placeholder="$t('dialog.search_txt')" v-model="searchValue" type="text" clearable>
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
                {{ $t('dialog.filters') }}</v-list-subheader>
              <v-list-item v-for="(item, index) in types" class="text-dark">
                <template v-slot:title>
                  <div class="form-check form-check-inline mx-1">
                    <input @change="filterTable()" v-model="types[index].checked" checked="" class="form-check-input"
                      type="checkbox">
                    <label class="form-check-label">{{ item.label }}</label>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-text-field>
      <EasyDataTable table-class-name="customize-table" :table-class-name="tableClassName"
        v-model:items-selected="itemsSelected" multi-sort show-index alternating :search-value="searchValue"
        :headers="headers" :items="items" theme-color="#1d90ff" header-text-direction="center"
        body-text-direction="center" rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
        rowsOfPageSeparatorMessage="از" :loading="loading">
        <template #item-operation="{ code, type }">
          <v-menu>
            <template v-slot:activator="{ props }">
              <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
            </template>
            <v-list>
              <v-list-item class="text-dark" :title="$t('dialog.view')" :to="'/acc/presell/view/' + code">
                <template v-slot:prepend>
                  <v-icon color="green-darken-4" icon="mdi-eye"></v-icon>
                </template>
              </v-list-item>
              <v-list-item class="text-dark" :title="$t('dialog.export_pdf')"
                @click="printOptions.selectedPrintCode = code; modal = true;">
                <template v-slot:prepend>
                  <v-icon icon="mdi-file-pdf-box"></v-icon>
                </template>
              </v-list-item>
              <v-list-item class="text-dark" :title="$t('dialog.edit')" @click="canEditItem(code)">
                <template v-slot:prepend>
                  <v-icon icon="mdi-file-edit"></v-icon>
                </template>
              </v-list-item>
              <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="deleteItem(code)">
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
          {{ des.replace("فاکتور فروش:", "") }}
        </template>
        <template #item-relatedDocsCount="{ relatedDocsCount, relatedDocsPays }">
          <span v-if="relatedDocsCount != '0'" class="text-success"><i class="fa fa-money"></i>
            {{ $filters.formatNumber(relatedDocsPays) }}
          </span>
        </template>
        <template #item-amount="{ amount }">
          <span class="text-dark">
            {{ $filters.formatNumber(amount) }}
          </span>
        </template>
        <template #item-profit="{ profit }">
          <span v-if="profit >= 0" class="text-dark">
            {{ $filters.formatNumber(profit) }}
          </span>
          <span v-else class="text-danger">
            {{ $filters.formatNumber(Math.abs(profit)) }}
            (زیان)
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
          <router-link :to="'/acc/presell/view/' + code">
            {{ code }}
          </router-link>
        </template>
      </EasyDataTable>
    </v-col>
  </v-row>
  <!-- Print Modal -->
  <v-dialog v-model="modal" width="auto">
    <v-card :subtitle="$t('dialog.print_info_des')" prepend-icon="mdi-file-pdf-box" :title="$t('dialog.export_pdf')">
      <template v-slot:text>
        <v-select class=mb-2 v-model="printOptions.paper" :items="paperSizes" :label="$t('dialog.paper_size')">
        </v-select>
        <v-switch inset v-model="printOptions.bidInfo" color="primary" :label="$t('dialog.bid_info_label')"
          hide-details></v-switch>
        <v-switch inset v-model="printOptions.pays" color="primary" :label="$t('dialog.invoice_pays')"
          hide-details></v-switch>
        <v-switch inset v-model="printOptions.note" color="primary" :label="$t('dialog.invoice_footer_note')"
          hide-details></v-switch>
        <v-switch inset v-model="printOptions.taxInfo" color="primary" :label="$t('dialog.tax_dexpo')"
          hide-details></v-switch>
        <v-switch inset v-model="printOptions.discountInfo" color="primary" :label="$t('dialog.discount_dexpo')"
          hide-details></v-switch>

      </template>
      <template v-slot:actions>
        <v-btn variant="tonal" class="" prepend-icon="mdi-printer" color="primary" :text="$t('dialog.print')"
          @click="modal = false; printInvoice()"></v-btn>
        <v-btn variant="tonal" class="" prepend-icon="mdi-undo" color="secondary" :text="$t('dialog.cancel')"
          @click="modal = false"></v-btn>
      </template>
    </v-card>
  </v-dialog>
  <!-- End Print Modal -->
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref ,defineComponent } from "vue";

export default defineComponent ({
  name: "list",
  data() {
    let self = this;
    return {
      paperSizes : [
        {
          title:self.$t('dialog.a4p'),
          value:'A4'
        },
        {
          title:self.$t('dialog.a4l'),
          value:'A4-L'
        },
        {
          title:self.$t('dialog.a5p'),
          value:'A5'
        },
        {
          title:self.$t('dialog.a5l'),
          value:'A5-L'
        },
      ],
      modal: false,
      printOptions: {
        pays: true,
        note: true,
        bidInfo: true,
        taxInfo: true,
        discountInfo: true,
        selectedPrintCode: 0,
        paper: 'A4-L'
      },
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
        { text: "سود فاکتور", value: "profit", sortable: true },
        { text: "پرداختی", value: "relatedDocsCount", sortable: true },
        { text: "برچسب", value: "label", width: 100 },
        { text: "شرح", value: "des", sortable: true },
      ]
    }
  },
  methods: {
    changeLabel(label) {
      if (this.itemsSelected.length == 0) {
        Swal.fire({
          text: 'هیچ موردی انتخاب نشده است.',
          icon: 'warning',
          confirmButtonText: 'قبول'
        });
      }
      else {
        this.loading = true;
        axios.post('/api/presell/label/change', {
          'items': this.itemsSelected,
          'label': label
        }
        ).then((response) => {
          this.loading == false;
          if (response.data.code == 0) {
            Swal.fire({
              text: 'فاکتور‌ها با موفقیت ویرایش شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            });
            this.itemsSelected = [];
          }
          else if (response.data.result == 2) {
            Swal.fire({
              text: response.data.message,
              icon: 'warning',
              confirmButtonText: 'قبول'
            });
          }
          this.loadData();
        })
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
      }
      else {
        this.orgItems.forEach((item) => {
          selectedTypes.forEach((st) => {
            if (item.label) {
              if (st.code == item.label.code) {
                let existBefore = false;
                calcItems.forEach((ri) => {
                  if (item.label.code == ri.code) {
                    existBefore = true;
                  }
                })
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
        this.printOptions = response.data.sell;
      });

      axios.post('/api/invoice/types', {
        type: 'sell'
      }).then((response) => {
        this.types = response.data;
      });

      axios.post('/api/presell/docs/search')
        .then((response) => {
          this.items = response.data;
          this.orgItems = response.data;
          this.items.forEach((item) => {
            this.sumTotal += parseInt(item.amount);
          })
          this.loading = false;
        })
    },
    deleteItems() {
      if (this.itemsSelected.length == 0) {
        Swal.fire({
          text: 'هیچ موردی انتخاب نشده است.',
          icon: 'warning',
          confirmButtonText: 'قبول'
        });
      }
      else {
        Swal.fire({
          text: 'آیا برای حذف این مورد مطمئن هستید؟ تمامی اسناد پرداخت و حواله های انبار همراه فاکتور نیز حذف خواهند شد.',
          showCancelButton: true,
          confirmButtonText: 'بله',
          cancelButtonText: `خیر`,
          icon: 'warning'
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            this.loading = true;
            axios.post('/api/accounting/remove/group', {
              'items': this.itemsSelected
            }
            ).then((response) => {
              this.loading = false;
              if (response.data.result == 1) {
                this.loadData();
                Swal.fire({
                  text: 'فاکتور ها با موفقیت حذف شد.',
                  icon: 'success',
                  confirmButtonText: 'قبول'
                });
              }
              else if (response.data.result == 2) {
                Swal.fire({
                  text: response.data.message,
                  icon: 'warning',
                  confirmButtonText: 'قبول'
                });
              }
            })
          }
        })
      }
    },
    printInvoice(pdf = true, cloudePrinters = true) {
      this.loading = true;
      axios.post('/api/presell/print/invoice', {
        'code': this.printOptions.selectedPrintCode,
        'pdf': pdf,
        'printers': cloudePrinters,
        'printOptions': this.printOptions
      }).then((response) => {
        this.loading = false;
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      });
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف این مورد مطمئن هستید؟ تمامی اسناد پرداخت و حواله های انبار همراه فاکتور نیز حذف خواهند شد.',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/accounting/remove', {
            'code': code
          }
          ).then((response) => {
            if (response.data.result == 1) {
              let index = 0;
              for (let z = 0; z < this.items.length; z++) {
                index++;
                if (this.items[z]['code'] == code) {
                  this.items.splice(index - 1, 1);
                }
              }
              Swal.fire({
                text: 'فاکتور فروش با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
            else if (response.data.result == 2) {
              Swal.fire({
                text: response.data.message,
                icon: 'warning',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    }
  },
  beforeMount() {
    this.loadData();
  },
  watch: {
    itemsSelected: {
      handler: function (val, oldVal) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((item) => {
          if (typeof item.amount.valueOf() === "string") {
            this.sumSelected += parseInt(item.amount.replaceAll(",", ""))
          }
          else {
            this.sumSelected += item.amount;
          }
        });
      },
      deep: true
    },
    searchValue: {
      handler: function (val, oldVal) {
        if (this.searchValue == '') {
          this.items = this.orgItems;
        }
        else {
          let temp = [];
          this.orgItems.forEach((item) => {
            if (item.person.nikename.includes(this.searchValue)) {
              temp.push(item)
            }
            else if (item.date.includes(this.searchValue)) {
              temp.push(item)
            }
            else if (item.amount.toString().includes(this.searchValue)) {
              temp.push(item)
            }
            else if (item.des.includes(this.searchValue)) {
              temp.push(item)
            }
            else if (item.code.includes(this.searchValue)) {
              temp.push(item)
            }
            else if (item.label) {
              if (item.label.label.includes(this.searchValue)) {
                temp.push(item)
              }
            }
          });
          this.items = temp;
        }
      },
      deep: false
    }
  }
})
</script>

<style scoped></style>