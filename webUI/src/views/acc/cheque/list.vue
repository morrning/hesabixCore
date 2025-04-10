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

  <v-window v-model="tab">
    <v-window-item value="input">
      <v-text-field class="pt-1" v-model="searchValueInput" prepend-inner-icon="mdi-magnify" label="جست و جو"
        variant="outlined" density="compact" :rounded="false"></v-text-field>

      <v-data-table :headers="headersInput" :items="itemsInput" :search="searchValueInput" :loading="loading" show-index
        density="comfortable" class="elevation-1" :header-props="{ class: 'custom-header' }">
        <template v-slot:item.operation="{ item }">
          <div class="d-flex">
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon variant="text" size="small" color="error">
                  <v-icon>mdi-menu</v-icon>
                </v-btn>
              </template>
              <v-list>
                <v-list-item v-if="!item.rejected && item.status !== 'پاس شده'" @click="$router.push(`/acc/cheque/input/${item.id}`)">
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-pencil</v-icon>
                  </template>
                  <v-list-item-title>ویرایش چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="!item.locked && !item.rejected" @click="deleteCheque(item)">
                  <template v-slot:prepend>
                    <v-icon color="error">mdi-delete</v-icon>
                  </template>
                  <v-list-item-title>حذف چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="!item.locked" @click="openPassDialog(item.id)">
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-bank-check</v-icon>
                  </template>
                  <v-list-item-title>پاس کردن چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="item.rejected" @click="unrejectCheque(item)">
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-arrow-right</v-icon>
                  </template>
                  <v-list-item-title>رفع برگشت</v-list-item-title>
                </v-list-item>
                <v-list-item v-else-if="!item.locked" @click="rejectCheque(item)">
                  <template v-slot:prepend>
                    <v-icon color="error">mdi-arrow-left</v-icon>
                  </template>
                  <v-list-item-title>برگشت چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="!item.locked && !item.rejected" @click="$router.push(`/acc/cheque/transfer/${item.id}`)">
                  <template v-slot:prepend>
                    <v-icon color="info">mdi-account-arrow-right</v-icon>
                  </template>
                  <v-list-item-title>واگذاری چک</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
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
      <v-text-field class="pt-1" v-model="searchValueOutput" prepend-inner-icon="mdi-magnify" label="جست و جو"
        variant="outlined" density="compact" :rounded="false"></v-text-field>

      <v-data-table :headers="headersInput" :items="itemsOutput" :search="searchValueOutput" :loading="loading"
        show-index density="comfortable" class="elevation-1" :header-props="{ class: 'custom-header' }">
        <template v-slot:item.operation="{ item }">
          <div class="d-flex">
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon variant="text" size="small" color="error">
                  <v-icon>mdi-menu</v-icon>
                </v-btn>
              </template>
              <v-list>
                <v-list-item v-if="!item.rejected && item.status !== 'پاس شده'" @click="$router.push(`/acc/cheque/input/${item.id}`)">
                  <template v-slot:prepend>
                    <v-icon color="primary">mdi-pencil</v-icon>
                  </template>
                  <v-list-item-title>ویرایش چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="!item.locked && !item.rejected" @click="deleteCheque(item)">
                  <template v-slot:prepend>
                    <v-icon color="error">mdi-delete</v-icon>
                  </template>
                  <v-list-item-title>حذف چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="!item.locked" @click="openPassDialog(item.id)">
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-bank-check</v-icon>
                  </template>
                  <v-list-item-title>پاس کردن چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="item.rejected" @click="unrejectCheque(item)">
                  <template v-slot:prepend>
                    <v-icon color="success">mdi-arrow-right</v-icon>
                  </template>
                  <v-list-item-title>رفع برگشت</v-list-item-title>
                </v-list-item>
                <v-list-item v-else-if="!item.locked" @click="rejectCheque(item)">
                  <template v-slot:prepend>
                    <v-icon color="error">mdi-arrow-left</v-icon>
                  </template>
                  <v-list-item-title>برگشت چک</v-list-item-title>
                </v-list-item>
                <v-list-item v-if="!item.locked && !item.rejected" @click="$router.push(`/acc/cheque/transfer/${item.id}`)">
                  <template v-slot:prepend>
                    <v-icon color="info">mdi-account-arrow-right</v-icon>
                  </template>
                  <v-list-item-title>واگذاری چک</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
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

  <!-- دیالوگ پاس کردن چک -->
  <v-dialog v-model="passDialog" max-width="500px">
    <v-card>
      <v-toolbar color="toolbar" title="پاس کردن چک">
        <template v-slot:append>
          <v-tooltip text="ثبت" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" color="success" :loading="passLoading" @click="passCheque" icon="mdi-content-save" />
            </template>
          </v-tooltip>
          <v-tooltip text="بستن" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-close" variant="text" @click="closePassDialog" />
            </template>
          </v-tooltip>
        </template>
      </v-toolbar>

      <v-card-text class="pt-4">
        <v-form @submit.prevent="passCheque">
          <v-row>
            <v-col cols="12">
              <Hdatepicker
                v-model="passDate"
                label="تاریخ"
                :min="year.start"
                :max="year.end"
                required
              />
            </v-col>

            <v-col cols="12">
              <v-select
                v-model="bankSelected"
                :items="banks"
                item-title="name"
                item-value="id"
                label="بانک"
                :rules="[v => !!v || 'انتخاب بانک الزامی است']"
                required
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="passDescription"
                label="توضیحات"
                variant="outlined"
              />
            </v-col>

            <v-col cols="12">
              <v-switch
                v-model="sendPassSms"
                color="primary"
                label="ارسال پیامک اطلاع‌رسانی به مشتری"
                hide-details
              ></v-switch>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>

  <!-- دیالوگ تایید حذف چک -->
  <v-dialog v-model="deleteDialog" max-width="400px">
    <v-card>
      <v-card-title class="text-h5 d-flex align-center">
        <v-icon color="error" class="ml-2">mdi-alert-circle</v-icon>
        هشدار
      </v-card-title>
      <v-card-text>
        <div class="text-subtitle-1 mb-2">آیا از حذف این چک اطمینان دارید؟</div>
        <div class="text-body-2 text-error">
          <v-icon color="error" size="small" class="ml-1">mdi-information</v-icon>
          این عملیات غیر قابل بازگشت است
        </div>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="error" variant="text" @click="confirmDelete">
          <v-icon start>mdi-delete</v-icon>
          حذف
        </v-btn>
        <v-btn color="primary" variant="text" @click="deleteDialog = false">
          <v-icon start>mdi-close</v-icon>
          انصراف
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- دیالوگ تایید برگشت چک -->
  <v-dialog v-model="rejectDialog" max-width="400px">
    <v-card>
      <v-toolbar color="toolbar" title="تایید برگشت چک">
        <template v-slot:append>
          <v-tooltip text="بستن" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-close" variant="text" @click="rejectDialog = false" />
            </template>
          </v-tooltip>
        </template>
      </v-toolbar>

      <v-card-text>
        <div class="text-subtitle-1 mb-2">آیا از برگشت این چک اطمینان دارید؟</div>
        <v-switch
          v-model="sendSms"
          color="primary"
          label="ارسال پیامک اطلاع‌رسانی به مشتری"
          hide-details
          class="mt-4"
        ></v-switch>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="error" variant="text" @click="confirmReject">
          <v-icon start>mdi-arrow-left</v-icon>
          برگشت چک
        </v-btn>
        <v-btn color="primary" variant="text" @click="rejectDialog = false">
          <v-icon start>mdi-close</v-icon>
          انصراف
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from "axios";
import { ref } from "vue";
import Hdatepicker from '@/components/forms/Hdatepicker.vue';

export default {
  name: "list",
  components: {
    Hdatepicker
  },
  data: () => ({
    tab: 'input',
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
    ],
    // متغیرهای مربوط به پاس کردن چک
    passDialog: false,
    passLoading: false,
    passDate: '',
    bankSelected: null,
    passDescription: '',
    banks: [],
    year: {
      start: '',
      end: '',
      now: ''
    },
    selectedChequeId: null,
    snackbar: {
      show: false,
      text: '',
      color: 'success'
    },
    // متغیرهای مربوط به حذف چک
    deleteDialog: false,
    selectedChequeForDelete: null,
    // متغیرهای مربوط به برگشت چک
    rejectDialog: false,
    selectedChequeForReject: null,
    sendSms: true,
    sendPassSms: true,
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
    async rejectCheque(item) {
      this.selectedChequeForReject = item;
      this.rejectDialog = true;
    },
    async confirmReject() {
      try {
        this.loading = true;
        await axios.post(`/api/cheque/reject/${this.selectedChequeForReject.id}`, {
          sendSms: this.sendSms
        });
        this.snackbar = {
          show: true,
          text: 'ثبت برگشت چک با موفقیت انجام شد',
          color: 'success'
        };
        this.loadData();
      } catch (error) {
        console.error(error);
        this.snackbar = {
          show: true,
          text: 'خطا در ثبت برگشت چک',
          color: 'error'
        };
      } finally {
        this.loading = false;
        this.rejectDialog = false;
        this.selectedChequeForReject = null;
        this.sendSms = true;
      }
    },
    async unrejectCheque(item) {
      try {
        this.loading = true;
        await axios.post(`/api/cheque/unreject/${item.id}`);
        this.snackbar = {
          show: true,
          text: 'رفع برگشت چک با موفقیت انجام شد',
          color: 'success'
        };
        this.loadData();
      } catch (error) {
        console.error(error);
        this.snackbar = {
          show: true,
          text: 'خطا در رفع برگشت چک',
          color: 'error'
        };
      } finally {
        this.loading = false;
      }
    },
    // متدهای مربوط به پاس کردن چک
    async openPassDialog(id) {
      this.selectedChequeId = id;
      this.passDialog = true;
      await this.loadPassData();
    },
    closePassDialog() {
      this.passDialog = false;
      this.bankSelected = null;
      this.passDescription = '';
      this.sendPassSms = true;
    },
    async loadPassData() {
      try {
        this.passLoading = true;
        const [banksResponse, yearResponse] = await Promise.all([
          axios.post('/api/bank/list'),
          axios.post('/api/year/get')
        ]);
        
        this.banks = banksResponse.data;
        this.year = yearResponse.data;
        this.passDate = yearResponse.data.now;
      } catch (error) {
        console.error('خطا در بارگذاری اطلاعات:', error);
      } finally {
        this.passLoading = false;
      }
    },
    async passCheque() {
      if (!this.bankSelected) {
        this.snackbar = {
          show: true,
          text: 'بانک انتخاب نشده است',
          color: 'error'
        };
        return;
      }

      try {
        this.passLoading = true;
        await axios.post(`/api/cheque/pass/${this.selectedChequeId}`, {
          bank: this.bankSelected,
          date: this.passDate,
          des: this.passDescription,
          sendSms: this.sendPassSms
        });

        this.snackbar = {
          show: true,
          text: 'ثبت وصول چک با موفقیت ثبت شد',
          color: 'success'
        };

        this.closePassDialog();
        this.loadData();
      } catch (error) {
        console.error('خطا در ثبت اطلاعات:', error);
        this.snackbar = {
          show: true,
          text: 'خطا در ثبت اطلاعات',
          color: 'error'
        };
      } finally {
        this.passLoading = false;
      }
    },
    async deleteCheque(item) {
      this.selectedChequeForDelete = item;
      this.deleteDialog = true;
    },
    async confirmDelete() {
      try {
        this.loading = true;
        await axios.post(`/api/cheque/input/delete/${this.selectedChequeForDelete.id}`);
        this.snackbar = {
          show: true,
          text: 'حذف چک با موفقیت انجام شد',
          color: 'success'
        };
        this.loadData();
      } catch (error) {
        console.error(error);
        this.snackbar = {
          show: true,
          text: 'خطا در حذف چک',
          color: 'error'
        };
      } finally {
        this.loading = false;
        this.deleteDialog = false;
        this.selectedChequeForDelete = null;
      }
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
.bg-primary-light {
  background-color: var(--v-primary-lighten1);
}
</style>