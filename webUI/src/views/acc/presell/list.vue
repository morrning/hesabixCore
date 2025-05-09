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
              <v-list-item class="text-dark" :title="$t('dialog.edit')" :to="'/acc/presell/mod/' + code">
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
        <template #item-des="{ des }">
          {{ des.replace("پیش فاکتور فروش:", "") }}
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
        <template #item-totalAmount="{ totalAmount }">
          <span class="text-dark">
            {{ $filters.formatNumber(totalAmount) }}
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
  <PrintDialog
    v-model="modal"
    :plugins="plugins"
    @print="printInvoice"
    @cancel="modal = false"
  />
  <!-- End Print Modal -->
  <!-- Delete Dialog -->
  <v-dialog v-model="deleteDialog" width="auto">
    <v-card>
      <v-card-title class="text-h5">
        حذف فاکتور
      </v-card-title>
      <v-card-text>
        آیا مطمئن هستید؟
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" variant="text" @click="confirmDelete">
          بله
        </v-btn>
        <v-btn color="error" variant="text" @click="deleteDialog = false">
          خیر
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Delete Group Dialog -->
  <v-dialog v-model="deleteGroupDialog" width="auto">
    <v-card>
      <v-card-title class="text-h5">
        حذف گروهی
      </v-card-title>
      <v-card-text>
        آیا مطمئن هستید؟
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" variant="text" @click="confirmDeleteGroup">
          بله
        </v-btn>
        <v-btn color="error" variant="text" @click="deleteGroupDialog = false">
          خیر
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
  <!-- Snackbar -->
  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    :timeout="3000"
  >
    {{ snackbar.text }}
    <template v-slot:actions>
      <v-btn
        color="white"
        variant="text"
        @click="snackbar.show = false"
      >
        بستن
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script>
import axios from "axios";
import { ref, defineComponent } from "vue";
import PrintDialog from '@/components/PrintDialog.vue';

export default defineComponent ({
  name: "list",
  components: {
    PrintDialog
  },
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
        { text: "مبلغ کل", value: "totalAmount", sortable: true },
        { text: "شرح", value: "des", sortable: true },
      ],
      deleteDialog: false,
      deleteGroupDialog: false,
      selectedCode: null,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      }
    }
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
    loadData() {
      this.loadPlugins();
      axios.post("/api/printers/options/info").then((response) => {
        this.printOptions = response.data.sell;
      });

      axios.post('/api/preinvoice/docs/search')
        .then((response) => {
          this.items = response.data;
          this.orgItems = response.data;
          this.items.forEach((item) => {
            this.sumTotal += parseInt(item.amount);
          })
          this.loading = false;
        })
    },
    showSnackbar(text, color = 'success') {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    },
    deleteItems() {
      if (this.itemsSelected.length == 0) {
        this.showSnackbar('لطفا حداقل یک پیش فاکتور را انتخاب کنید.', 'warning');
        return;
      }
      this.deleteGroupDialog = true;
    },
    confirmDeleteGroup() {
      this.loading = true;
      this.deleteGroupDialog = false;
      axios.post('/api/preinvoice/remove/group', {
        'items': this.itemsSelected
      }).then((response) => {
        this.loading = false;
        if (response.data.result == 1) {
          this.loadData();
          this.showSnackbar('فاکتورها با موفقیت حذف شد.', 'success');
        }
        else if (response.data.result == 2) {
          this.showSnackbar(response.data.message, 'warning');
        }
      });
    },
    deleteItem(code) {
      this.selectedCode = code;
      this.deleteDialog = true;
    },
    confirmDelete() {
      this.deleteDialog = false;
      axios.post('/api/preinvoice/delete/' + this.selectedCode).then((response) => {
        if (response.data.result == 1) {
          let index = 0;
          for (let z = 0; z < this.items.length; z++) {
            index++;
            if (this.items[z]['code'] == this.selectedCode) {
              this.items.splice(index - 1, 1);
            }
          }
          this.showSnackbar('فاکتور با موفقیت حذف شد.', 'success');
        }
        else if (response.data.result == 2) {
          this.showSnackbar(response.data.message, 'warning');
        }
      });
    },
    printInvoice(pdf = true, cloudePrinters = true) {
      this.loading = true;
      axios.post('/api/preinvoice/print/invoice', {
        'code': this.printOptions.selectedPrintCode,
        'pdf': pdf,
        'printers': cloudePrinters,
        'printOptions': this.printOptions
      }).then((response) => {
        this.loading = false;
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      });
    },
    isNumeric(str) {
      if (typeof str != "string") return false;
      return !isNaN(str) && !isNaN(parseFloat(str));
    },
  },
  beforeMount() {
    this.loadData();
  },
  watch: {
    itemsSelected: {
      handler: function (val, oldVal) {
        this.sumSelected = 0;
        this.itemsSelected.forEach((item) => {
          if (typeof item.totalAmount.valueOf() === "string") {
            this.sumSelected += parseInt(item.totalAmount.replaceAll(",", ""))
          }
          else {
            this.sumSelected += item.totalAmount;
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
          const searchTerm = this.searchValue.toLowerCase().replace(/,/g, '');
          let temp = [];
          this.orgItems.forEach((item) => {
            // جستجو در فیلدهای متنی
            const personFields = [
              item.person?.nikename,
              item.person?.name,
              item.person?.family,
              item.person?.mobile,
              item.person?.phone,
              item.person?.address
            ].filter(Boolean); // حذف مقادیر undefined

            const hasPersonMatch = personFields.some(field => 
              field.toLowerCase().includes(searchTerm)
            );

            if (hasPersonMatch ||
                item.des.toLowerCase().includes(searchTerm) ||
                item.code.includes(searchTerm)) {
              temp.push(item);
            }
            // جستجو در فیلدهای عددی
            else if (this.isNumeric(searchTerm)) {
              const searchNumber = parseFloat(searchTerm);
              if (item.amount == searchNumber ||
                  item.totalAmount == searchNumber ||
                  item.discountAll == searchNumber ||
                  item.transferCost == searchNumber ||
                  item.amount.toString().includes(searchTerm) ||
                  item.totalAmount.toString().includes(searchTerm) ||
                  item.discountAll.toString().includes(searchTerm) ||
                  item.transferCost.toString().includes(searchTerm)) {
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
})
</script>

<style scoped>
.v-card {
  border-radius: 8px;
}
.v-dialog {
  transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>