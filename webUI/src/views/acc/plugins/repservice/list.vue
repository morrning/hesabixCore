<template>
  <v-toolbar color="toolbar" title="درخواست‌ها">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <template v-slot:append>
      <v-tooltip :text="$t('dialog.new')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" color="success" @click="$router.push('/acc/plugin/repservice/order/mod/')" icon="mdi-plus" />
        </template>
      </v-tooltip>
    </template>
  </v-toolbar>

  <v-dialog v-model="changeStateDialog" persistent max-width="800">
    <v-toolbar title="تغییر وضعیت درخواست" color="toolbar" density="compact">
      <v-spacer></v-spacer>
      <v-switch
        v-model="singleChangeStateSelected.sms"
        :disabled="!singleChangeStateSelected.person.mobile"
        label="ارسال پیامک"
        color="primary"
        density="compact"
        hide-details
        class="me-4"
      ></v-switch>
      <v-tooltip text="ثبت تغییرات" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn :loading="loading" v-bind="props" color="success" @click="changeStateSingle" icon="mdi-content-save" />
        </template>
      </v-tooltip>
      <v-tooltip :text="$t('dialog.close')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-close" variant="text" @click="changeStateDialog = false" />
        </template>
      </v-tooltip>
    </v-toolbar>
    <v-card :rounded="false">
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="singleChangeStateSelected.person.nikename"
              label="مشتری"
              readonly
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="singleChangeStateSelected.commodity.name"
              label="کالا"
              readonly
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-select
              v-model="singleChangeStateSelected.state"
              :items="orderStates"
              item-title="label"
              item-value="value"
              label="وضعیت"
              density="compact"
              color="success"
              return-object
            ></v-select>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>

  <v-dialog v-model="historyDialog" persistent>
    <v-toolbar title="تاریخچه" color="toolbar" density="compact">
      <v-spacer></v-spacer>
      <v-tooltip :text="$t('dialog.close')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" icon="mdi-close" variant="text" @click="historyDialog = false" />
        </template>
      </v-tooltip>
    </v-toolbar>
    <v-card :rounded="false">
      <v-card-text  class="pa-0 ma-0">
        <v-data-table
          :headers="logHeaders"
          :items="logItems"
          :loading="loading"
          density="compact"
          class="elevation-1"
          :header-props="{ class: 'custom-header' }"
        ></v-data-table>
      </v-card-text>
    </v-card>
  </v-dialog>

  <v-dialog v-model="deleteDialog" persistent max-width="400">
    <v-card>
      <v-card-title>حذف درخواست</v-card-title>
      <v-card-text>آیا برای حذف درخواست مطمئن هستید؟</v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="error" variant="text" @click="deleteDialog = false">خیر</v-btn>
        <v-btn color="success" variant="text" @click="confirmDelete">بله</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    :timeout="3000"
  >
    {{ snackbar.text }}
    <template v-slot:actions>
      <v-btn
        variant="text"
        @click="snackbar.show = false"
      >
        بستن
      </v-btn>
    </template>
  </v-snackbar>

  <v-row>
        <v-col cols="12" class="pb-0 mb-0">
          <v-text-field
            v-model="searchValue"
            :rounded="false"
            prepend-inner-icon="mdi-magnify"
            label="جست و جو ..."
            density="compact"
            hide-details
          >
            <template v-slot:append-inner>
              <v-menu>
                <template v-slot:activator="{ props: menuProps }">
                  <v-tooltip text="فیلتر درخواست‌ها" location="bottom">
                    <template v-slot:activator="{ props: tooltipProps }">
                      <v-btn
                        v-bind="{ ...menuProps, ...tooltipProps }"
                        icon="mdi-filter"
                        variant="text"
                        color="primary"
                      ></v-btn>
                    </template>
                  </v-tooltip>
                </template>
                <v-list>
                  <v-list-item v-for="(item, index) in orderStates" :key="index">
                    <v-checkbox
                      v-model="item.checked"
                      :label="item.label"
                      density="compact"
                      hide-details
                      @change="filterTable"
                    ></v-checkbox>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-text-field>
        </v-col>
        <v-col cols="12" class="py-0 my-0">
          <v-data-table
            :headers="headers"
            :items="items"
            :loading="loading"
            show-expand
            class="elevation-1"
            :header-props="{ class: 'custom-header' }"
          >
            <template v-slot:item.operation="{ item }">
              <v-menu>
                <template v-slot:activator="{ props }">
          <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
        </template>
                <v-list>
                  <v-list-item :to="'/acc/plugin/repservice/order/mod/' + item.code">
                    <template v-slot:prepend>
                      <v-icon class="me-2">mdi-pencil</v-icon>
                    </template>
                    <v-list-item-title>ویرایش</v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="openChangeStateDialog(item.code)">
                    <template v-slot:prepend>
                      <v-icon class="me-2">mdi-lightning-bolt</v-icon>
                    </template>
                    <v-list-item-title>تغییر وضعیت</v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="printInvoice(item.code)">
                    <template v-slot:prepend>
                      <v-icon class="me-2">mdi-printer</v-icon>
                    </template>
                    <v-list-item-title>چاپ قبض رسید</v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="openHistoryDialog(item.code)">
                    <template v-slot:prepend>
                      <v-icon class="me-2">mdi-history</v-icon>
                    </template>
                    <v-list-item-title>تاریخچه</v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="deleteItem(item.code)">
                    <template v-slot:prepend>
                      <v-icon color="error" class="me-2">mdi-delete</v-icon>
                    </template>
                    <v-list-item-title>حذف</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
            <template v-slot:item.person="{ item }">
              <router-link :to="'/acc/persons/card/view/' + item.person.code">
                {{ item.person.nikename }}
              </router-link>
            </template>
            <template v-slot:item.commodity="{ item }">
              {{ item.commodity.name }}
            </template>
              <template v-slot:item.state="{ item }">
                {{ item.state.label }}
              </template>
              <template v-slot:item.date="{ item }">
                {{ item.date }}
              </template>
              <template v-slot:item.dateOut="{ item }">
                {{ item.dateOut ? item.dateOut : 'تحویل تعمیرگاه' }}
              </template>
            <template v-slot:expanded-row="{ columns, item }">
              <v-container fluid>
                    <v-row>
                      <v-col cols="12" sm="12" md="6" class="text-right">
                        <strong>شرح: </strong>{{ item.des }}
                      </v-col>
                      <v-col cols="12" sm="12" md="6" class="text-right">
                        <strong>متعلقات: </strong>{{ item.motaleghat }}
                      </v-col>
                      <v-col cols="12" sm="12" md="6" class="text-right">
                        <strong>پلاک: </strong>{{ item.pelak }}
                      </v-col>
                      <v-col cols="12" sm="12" md="6" class="text-right">
                        <strong>سریال: </strong>{{ item.serial }}
                      </v-col>
                      <v-col cols="12" sm="12" md="6" class="text-right">
                        <strong>مدل: </strong>{{ item.model }}
                      </v-col>
                      <v-col cols="12" sm="12" md="6" class="text-right">
                        <strong>رنگ: </strong>{{ item.color }}
                      </v-col>
                    </v-row>
                  </v-container>
            </template>
          </v-data-table>
        </v-col>
      </v-row>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "list",
  data: () => ({
    orderStates: [],
    singleChangeStateSelected: {
      code: 0,
      person: {
        nikename: ''
      },
      commodity: {
        name: ''
      }
    },
    searchValue: '',
    loading: ref(true),
    items: [],
    orgItems: [],
    changeStateDialog: false,
    historyDialog: false,
    headers: [
      { title: "عملیات", key: "operation", sortable: false },
      { title: "کد", key: "code" },
      { title: "مشتری", key: "person", sortable: true },
      { title: "کالا", key: "commodity", sortable: true },
      { title: "تاریخ", key: "date", sortable: true },
      { title: "وضعیت", key: "state", sortable: true },
      { title: "تاریخ تحویل", key: "dateOut", sortable: true },
    ],
    logItems: [],
    logHeaders: [
      { title: "تاریخ", key: "date" },
      { title: "شرح", key: "des" },
      { title: "ثبت کننده", key: "user" },
    ],
    deleteDialog: false,
    deleteCode: null,
    snackbar: {
      show: false,
      text: '',
      color: 'success'
    }
  }),
  watch: {
    'singleChangeStateSelected.code'(newValue) {
      const item = this.items.find(item => item.code === newValue);
      if (item) {
        this.singleChangeStateSelected = { ...item };
        if (!this.singleChangeStateSelected.person.mobile) {
          this.singleChangeStateSelected.sms = false;
        }
      }
    },
    searchValue() {
      this.searchTable();
    }
  },
  methods: {
    openChangeStateDialog(code) {
      this.singleChangeStateSelected.code = code;
      this.changeStateDialog = true;
    },
    openHistoryDialog(code) {
      this.changeItem(code);
      this.historyDialog = true;
    },
    changeItem(code) {
      this.loading = true;
      axios.post('/api/plug/repservice/order/logs/' + code)
        .then((response) => {
          this.loading = false;
          this.logItems = response.data;
        });
    },
    filterTable() {
      this.loading = true;
      const selectedTypes = this.orderStates.filter(item => item.checked);
      
      if (selectedTypes.length === 0) {
        this.items = this.orgItems;
      } else {
        this.items = this.orgItems.filter(item => 
          selectedTypes.some(st => st.code === item.state.code)
        );
      }
      this.loading = false;
    },
    searchTable() {
      this.loading = true;
      if (!this.searchValue) {
        this.items = this.orgItems;
      } else {
        const searchLower = this.searchValue.toLowerCase();
        this.items = this.orgItems.filter(item => 
          item.commodity.name.toLowerCase().includes(searchLower) ||
          item.person.nikename.toLowerCase().includes(searchLower) ||
          item.des.toLowerCase().includes(searchLower) ||
          item.state.label.toLowerCase().includes(searchLower) ||
          item.date.toLowerCase().includes(searchLower)
        );
      }
      this.loading = false;
    },
    loadData() {
      axios.post('/api/plug/repservice/order/list')
        .then((response) => {
          this.items = response.data;
          this.orgItems = response.data;
          this.loading = false;
        });
      axios.post('/api/plug/repservice/order/state/list')
        .then((response) => {
          this.orderStates = response.data;
        });
    },
    changeStateSingle() {
      this.loading = true;
      axios.post('/api/plug/repservice/order/state/change', this.singleChangeStateSelected)
        .then((response) => {
          this.loading = false;
          if (response.data.Success == true) {
            this.snackbar = {
              show: true,
              text: `وضعیت درخواست ${this.singleChangeStateSelected.person.prelabel ? this.singleChangeStateSelected.person.prelabel + ' ' : ''} ${this.singleChangeStateSelected.person.nikename} برای کالای ${this.singleChangeStateSelected.commodity.name} به ${this.singleChangeStateSelected.state.label} تغییر یافت.`,
              color: 'success'
            };
          } else {
            this.snackbar = {
              show: true,
              text: 'متأسفانه تغییر وضعیت با مشکل مواجه شد. لطفاً دوباره تلاش کنید.',
              color: 'error'
            };
          }
          this.changeStateDialog = false;
          this.loadData();
        })
        .catch(() => {
          this.loading = false;
          this.snackbar = {
            show: true,
            text: 'متأسفانه ارتباط با سرور برقرار نشد. لطفاً دوباره تلاش کنید.',
            color: 'error'
          };
        });
    },
    deleteItem(code) {
      this.deleteCode = code;
      this.deleteDialog = true;
    },
    confirmDelete() {
      axios.post('/api/repservice/order/remove/' + this.deleteCode)
        .then((response) => {
          if (response.data.result === 1) {
            this.items = this.items.filter(item => item.code !== this.deleteCode);
            this.snackbar = {
              show: true,
              text: 'درخواست با موفقیت حذف شد.',
              color: 'success'
            };
          }
          this.deleteDialog = false;
        });
    },
    printInvoice(code) {
      this.loading = true;
      axios.post('/api/repservice/print/invoice', { code })
        .then((response) => {
          this.loading = false;
          window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
        });
    },
  },
  mounted() {
    this.loadData();
  }
}
</script>

<style scoped>

</style>