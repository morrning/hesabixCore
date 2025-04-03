<template>
  <v-toolbar color="toolbar" :title="$t('drawer.wallets')">
    <v-spacer />

    <template v-slot:extension>
      <v-tabs v-model="activeTab" color="primary" fixed-tabs>
        <v-tab value="wallets">کیف پول‌ها</v-tab>
        <v-tab value="transactions">تراکنش‌ها</v-tab>
      </v-tabs>
    </template>
  </v-toolbar>


  <v-window v-model="activeTab">
    <!-- تب کیف پول‌ها -->
    <v-window-item value="wallets">
      <v-card :loading="loading ? 'red' : null" :disabled="loading">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="4" md="3">
              <v-text-field v-model="search" label="جستجو..." prepend-inner-icon="mdi-magnify" single-line hide-details
                density="compact" variant="outlined"></v-text-field>
            </v-col>
            <v-col cols="12" sm="4" md="3">
              <v-select v-model="statusFilter" :items="statusOptions" label="وضعیت" density="compact" variant="outlined"
                hide-details clearable></v-select>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-data-table :headers="headers" :items="filteredItems" :loading="loading" class="elevation-1"
                no-data-text="اطلاعاتی برای نمایش وجود ندارد" loading-text="در حال بارگذاری..." :items-per-page="10"
                :items-per-page-text="'تعداد سطر'" :header-props="{ class: 'custom-header' }">
                <template v-slot:item="{ item }">
                  <tr>
                    <td>{{ item.bidName }}</td>
                    <td>{{ item.bankAcName }}</td>
                    <td>{{ item.bankAcOwner }}</td>
                    <td>{{ item.bankAcShaba }}</td>
                    <td>{{ item.bankAcCardNum }}</td>
                    <td>{{ item.totalPays }}</td>
                    <td>{{ item.totalIncome }}</td>
                    <td>{{ calculateStatus(item) }}</td>
                    <td>
                      <v-tooltip v-if="calculateStatus(item) === 'در صف تسویه'" location="top">
                        <template v-slot:activator="{ props }">
                          <v-btn variant="text" icon v-bind="props" @click="openTransactionDialog(item)">
                            <v-icon>mdi-cash-register</v-icon>
                          </v-btn>
                        </template>
                        <span>ثبت تراکنش</span>
                      </v-tooltip>
                    </td>
                  </tr>
                </template>
              </v-data-table>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-window-item>

    <!-- تب تراکنش‌ها -->
    <v-window-item value="transactions">
      <v-card :loading="transactionsLoading ? 'red' : null" :disabled="transactionsLoading">
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="4" md="3">
              <v-text-field v-model="transactionSearch" label="جستجو..." prepend-inner-icon="mdi-magnify" single-line
                hide-details density="compact" variant="outlined"></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-data-table :headers="transactionHeaders" :items="filteredTransactions" :loading="transactionsLoading"
                class="elevation-1" no-data-text="اطلاعاتی برای نمایش وجود ندارد" loading-text="در حال بارگذاری..."
                :items-per-page="10" :items-per-page-text="'تعداد سطر'" :header-props="{ class: 'custom-header' }">
                <template v-slot:item="{ item }">
                  <tr>
                    <td>{{ item.bidName }}</td>
                    <td>{{ item.bankAcName }}</td>
                    <td>{{ item.type === 'pay' ? 'پرداخت' : 'دریافت' }}</td>
                    <td>{{ item.gatePay }}</td>
                    <td>{{ item.refID }}</td>
                    <td>{{ item.shaba }}</td>
                    <td>{{ item.cardPan }}</td>
                    <td>{{ item.dateSubmit }}</td>
                  </tr>
                </template>
              </v-data-table>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-window-item>
  </v-window>

  <v-dialog v-model="dialogVisible" max-width="500px">
    <v-card>
      <v-card-title>ثبت تراکنش تسویه کیف پول</v-card-title>
      <v-card-text>
        <v-form ref="form" v-model="formValid" @submit.prevent="submitTransaction">
          <v-row>
            <v-col cols="12">
              <v-select v-model="transaction.bank" :items="bankOptions" label="بانک پرداخت کننده"
                :rules="[v => !!v || 'بانک الزامی است']" variant="outlined" required></v-select>
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="transaction.refID" label="شناسه تراکنش"
                :rules="[v => !!v || 'شناسه تراکنش الزامی است']" variant="outlined" required></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="transaction.amount" label="مبلغ (ریال)" type="number"
                :rules="[v => !!v || 'مبلغ الزامی است']" variant="outlined" required></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="selectedItemShaba" label="شبا" disabled variant="outlined"></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field v-model="selectedItemCard" label="شماره کارت" disabled variant="outlined"></v-text-field>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="error" @click="dialogVisible = false">انصراف</v-btn>
        <v-btn color="primary" :loading="submitting" @click="submitTransaction">ثبت تراکنش</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
export default {
  name: "dashboard",
  data() {
    return {
      activeTab: 'wallets',
      loading: true,
      transactionsLoading: true,
      submitting: false,
      formValid: false,
      items: [],
      transactions: [],
      search: '',
      transactionSearch: '',
      statusFilter: null,
      transaction: {
        bank: null,
        refID: '',
        amount: '',
        shaba: '',
        card: '',
        bid: null
      },
      bankOptions: [
        { title: 'بانک ملی', value: 'بانک ملی' },
        { title: 'بانک ملت', value: 'بانک ملت' },
        { title: 'بانک صادرات', value: 'بانک صادرات' },
        { title: 'بانک تجارت', value: 'بانک تجارت' },
        { title: 'بانک پاسارگاد', value: 'بانک پاسارگاد' },
        { title: 'بانک سامان', value: 'بانک سامان' }
      ],
      statusOptions: [
        { title: 'تسویه شده', value: 'تسویه شده' },
        { title: 'در صف تسویه', value: 'در صف تسویه' },
        { title: 'در انتظار پرداخت', value: 'در انتظار پرداخت' },
      ],
      dialogVisible: false,
      selectedItem: null,
      headers: [
        { title: "کسب‌و‌کار", key: "bidName", sortable: false, align: 'center' },
        { title: "بانک", key: "bankAcName", sortable: false, align: 'center' },
        { title: "حساب", key: "bankAcOwner", sortable: false, align: 'center' },
        { title: "شبا", key: "bankAcShaba", sortable: false, align: 'center' },
        { title: "شماره کارت", key: "bankAcCardNum", sortable: false, align: 'center' },
        { title: "مبلغ پرداختی", key: "totalPays", sortable: false, align: 'center' },
        { title: "مبلغ دریافتی", key: "totalIncome", sortable: false, align: 'center' },
        { title: "وضعیت", key: "status", sortable: false, align: 'center' },
        { title: "عملیات", key: "operations", sortable: false, align: 'center' },
      ],
      transactionHeaders: [
        { title: "کسب‌و‌کار", key: "bidName", sortable: false, align: 'center' },
        { title: "بانک", key: "bankAcName", sortable: false, align: 'center' },
        { title: "نوع", key: "type", sortable: false, align: 'center' },
        { title: "درگاه پرداخت", key: "gatePay", sortable: false, align: 'center' },
        { title: "شناسه تراکنش", key: "refID", sortable: false, align: 'center' },
        { title: "شبا", key: "shaba", sortable: false, align: 'center' },
        { title: "شماره کارت", key: "cardPan", sortable: false, align: 'center' },
        { title: "تاریخ", key: "dateSubmit", sortable: false, align: 'center' },
      ],
    }
  },
  computed: {
    filteredItems() {
      let filtered = [...this.items];

      // اعمال فیلتر جستجو
      if (this.search) {
        const searchLower = this.search.toLowerCase();
        filtered = filtered.filter(item =>
          item.bidName?.toLowerCase().includes(searchLower) ||
          item.bankAcName?.toLowerCase().includes(searchLower) ||
          item.bankAcOwner?.toLowerCase().includes(searchLower) ||
          item.bankAcShaba?.toLowerCase().includes(searchLower) ||
          item.bankAcCardNum?.toLowerCase().includes(searchLower)
        );
      }

      // اعمال فیلتر وضعیت
      if (this.statusFilter) {
        filtered = filtered.filter(item =>
          this.calculateStatus(item) === this.statusFilter
        );
      }

      return filtered;
    },
    filteredTransactions() {
      if (!this.transactionSearch) {
        // مرتب‌سازی بر اساس تاریخ (جدیدترین اول)
        return [...this.transactions].sort((a, b) => {
          return a.dateSubmit < b.dateSubmit ? 1 : -1;
        });
      }

      const searchLower = this.transactionSearch.toLowerCase();
      return this.transactions.filter(item =>
        item.bidName?.toLowerCase().includes(searchLower) ||
        item.bankAcName?.toLowerCase().includes(searchLower) ||
        item.refID?.toLowerCase().includes(searchLower) ||
        item.shaba?.toLowerCase().includes(searchLower) ||
        item.cardPan?.toLowerCase().includes(searchLower) ||
        item.gatePay?.toLowerCase().includes(searchLower)
      ).sort((a, b) => {
        return a.dateSubmit < b.dateSubmit ? 1 : -1;
      });
    },
    selectedItemShaba() {
      return this.selectedItem ? this.selectedItem.bankAcShaba : '';
    },
    selectedItemCard() {
      return this.selectedItem ? this.selectedItem.bankAcCardNum : '';
    }
  },
  watch: {
    activeTab(newVal) {
      if (newVal === 'transactions' && this.transactions.length === 0) {
        this.loadTransactions();
      }
    }
  },
  methods: {
    loadData() {
      axios.post('/api/admin/wallets/list').then((response) => {
        this.items = response.data;
        this.loading = false;
      });
    },
    loadTransactions() {
      this.transactionsLoading = true;
      axios.post('/api/admin/wallets/transactions/list').then((response) => {
        this.transactions = response.data;
        this.transactionsLoading = false;
      }).catch(error => {
        console.error('Error loading transactions:', error);
        this.transactionsLoading = false;
      });
    },
    calculateStatus(item) {
      if (!item) {
        return 'نامشخص';
      }

      const totalPays = Number(item.totalPays || 0);
      const totalIncome = Number(item.totalIncome || 0);

      if (totalPays === totalIncome) {
        return 'تسویه شده';
      } else if (totalPays > totalIncome) {
        return 'در صف تسویه';
      } else {
        return 'در انتظار پرداخت';
      }
    },
    openTransactionDialog(item) {
      this.selectedItem = item;
      // محاسبه مبلغ قابل پرداخت (مبلغ دریافتی - پرداختی)
      const amount = Number(item.totalIncome || 0) - Number(item.totalPays || 0);

      // تنظیم مقادیر پیش‌فرض فرم
      this.transaction = {
        bank: null,
        refID: '',
        amount: Math.abs(amount),
        shaba: item.bankAcShaba,
        card: item.bankAcCardNum,
        bid: { id: item.id }
      };

      this.dialogVisible = true;
    },
    submitTransaction() {
      if (!this.$refs.form.validate()) {
        return;
      }

      this.submitting = true;

      axios.post('/api/admin/wallets/transactions/insert', this.transaction)
        .then(response => {
          if (response.data.result === 1) {
            this.dialogVisible = false;
            Swal.fire({
              icon: 'success',
              title: 'موفق',
              text: 'تراکنش با موفقیت ثبت شد',
              confirmButtonText: 'تایید'
            });

            // بارگذاری مجدد داده‌ها
            this.loadData();
            this.loadTransactions();
          } else {
            throw new Error('خطا در ثبت تراکنش');
          }
        })
        .catch(error => {
          console.error('Error submitting transaction:', error);
          Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: 'خطا در ثبت تراکنش. لطفا مجددا تلاش کنید.',
            confirmButtonText: 'تایید'
          });
        })
        .finally(() => {
          this.submitting = false;
        });
    }
  },
  mounted() {
    this.loadData();
  },
  beforeUnmount() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }
  },
}
</script>

<style scoped>
.v-window {
  margin-top: 16px;
}
</style>