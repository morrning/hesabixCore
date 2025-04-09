<template>
  <v-toolbar color="toolbar" title="چک‌ها">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <template v-slot:extension>
      <v-tabs v-model="tab" color="primary" class="bg-light" grow>
        <v-tab value="input" block>
          <v-icon start>mdi-file-export</v-icon>
          چک‌های دریافتی
        </v-tab>
        <v-tab value="output" block>
          <v-icon start>mdi-file-import</v-icon>
          چک‌های واگذار شده
        </v-tab>
      </v-tabs>
    </template>
    <v-spacer />
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" color="primary" variant="text" icon="mdi-plus" />
      </template>

      <v-list>
        <v-list-item @click="$router.push('/acc/cheque/input')">
          <template v-slot:prepend>
            <v-icon>mdi-file-export</v-icon>
          </template>
          <v-list-item-title>چک دریافتی</v-list-item-title>
        </v-list-item>

        <v-list-item @click="$router.push('/acc/cheque/output')">
          <template v-slot:prepend>
            <v-icon>mdi-file-import</v-icon>
          </template>
          <v-list-item-title>چک پرداختی</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-toolbar>

  <v-window v-model="tab">
        <v-window-item value="input">
          <v-text-field class="pt-1" v-model="searchValueInput" prepend-inner-icon="mdi-magnify" label="جست و جو" variant="outlined"
          density="compact" :rounded="false"></v-text-field>

          <v-data-table :headers="headersInput" :items="itemsInput" :search="searchValueInput" :loading="loading"
            show-index density="comfortable" class="elevation-1" :header-props="{ class: 'custom-header' }">
            <template v-slot:item.operation="{ item }">
              <div class="d-flex">
                <pass-check v-if="!item.locked" :windows-state="passChequeWindowsState" :id="item.id" />
                <v-btn v-if="!item.rejected && !item.locked" icon variant="text" color="error" size="small"
                  @click="rejectCheque(item.id)" title="برگشت چک">
                  <v-icon>mdi-arrow-left</v-icon>
                </v-btn>
              </div>
            </template>

            <template v-slot:item.status="{ item }">
              <v-chip :color="getStatusColor(item.status)" size="small">
                {{ item.status }}
              </v-chip>
            </template>
          </v-data-table>
        </v-window-item>

        <v-window-item value="output">
          <v-text-field class="pt-1" v-model="searchValueOutput" prepend-inner-icon="mdi-magnify" label="جست و جو" variant="outlined"
            density="compact" :rounded="false"></v-text-field>

          <v-data-table :headers="headersInput" :items="itemsOutput" :search="searchValueOutput" :loading="loading"
            show-index density="comfortable" class="elevation-1" :header-props="{ class: 'custom-header' }">
            <template v-slot:item.operation="{ item }">
              <div class="d-flex">
                <pass-check v-if="!item.locked" :windows-state="passChequeWindowsState" :id="item.id" />
                <v-btn v-if="!item.rejected && !item.locked" icon variant="text" color="error" size="small"
                  @click="rejectCheque(item.id)" title="برگشت چک">
                  <v-icon>mdi-arrow-left</v-icon>
                </v-btn>
              </div>
            </template>

            <template v-slot:item.status="{ item }">
              <v-chip :color="getStatusColor(item.status)" size="small">
                {{ item.status }}
              </v-chip>
            </template>
          </v-data-table>
        </v-window-item>
      </v-window>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";
import passCheck from "../component/cheque/passCheck.vue";

export default {
  name: "list",
  components: {
    passCheck
  },
  watch: {
    'passChequeWindowsState.submited'(newValue) {
      this.passChequeWindowsState.submited = false;
      if (newValue) {
        this.loadData();
      }
    }
  },
  data: () => ({
    tab: 'input',
    passChequeWindowsState: {
      submited: false
    },
    loading: ref(true),
    searchValueInput: '',
    searchValueOutput: '',
    itemsInput: [],
    itemsOutput: [],
    headersInput: [
      { title: "عملیات", key: "operation", width: "100" },
      { title: "شماره", key: "number", width: "100" },
      { title: "کد صیاد", key: "sayadNum", width: "120" },
      { title: "مبلغ(ریال)", key: "amount", width: "140" },
      { title: "تاریخ", key: "datePay", width: "150" },
      { title: "پرداخت کننده", key: "person.nikename", width: "150" },
      { title: "بانک", key: "chequeBank", width: "150" },
      { title: "وضعیت", key: "status", width: "150", sortable: true },
      { title: "تاریخ وصول", key: "date", width: "150" },
      { title: "توضیحات", key: "des", width: "150" },
    ]
  }),
  methods: {
    getStatusColor(status) {
      switch (status) {
        case 'پاس شده':
          return 'success';
        case 'پاس نشده':
          return 'grey';
        case 'برگشت خورده':
          return 'error';
        default:
          return 'grey';
      }
    },
    loadData() {
      axios.post('/api/cheque/list')
        .then((response) => {
          this.itemsInput = response.data.input;
          this.itemsInput.forEach((item) => {
            item.amount = this.$filters.formatNumber(item.amount);
          });
          this.itemsOutput = response.data.output;
          this.itemsOutput.forEach((item) => {
            item.amount = this.$filters.formatNumber(item.amount);
          });
          this.loading = false;
        });
    },
    rejectCheque(id) {
      this.loading = true;
      axios.post('/api/cheque/info/' + id).then(() => {
        this.loading = false;
        Swal.fire({
          title: "آیا برای تغییر وضعیت چک به برگشتی مطمئن هستید؟",
          icon: "question",
          confirmButtonText: "بله",
          cancelButtonText: "خیر",
          showCancelButton: true,
          showCloseButton: true
        }).then((result) => {
          if (result.isConfirmed) {
            this.loading = true;
            axios.post('/api/cheque/reject/' + id).then(() => {
              this.loading = false;
              Swal.fire({
                title: "وضعیت چک تغییر یافت",
                icon: "success",
                confirmButtonText: "بله",
              });
              this.loadData();
            });
          }
        });
      });
    }
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<style scoped>
.v-data-table {
  direction: rtl;
}
</style>