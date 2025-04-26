<template>
  <v-toolbar color="toolbar" :title="$t('dialog.accounting_doc')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-tooltip text="ثبت سند" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-content-save" color="success" @click="submitForm" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
    <v-tooltip v-if="docId" text="حذف سند" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-delete" color="error" @click="deleteDialog = true" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  
  <v-container>
    <v-form @submit.prevent="submitForm">
      <v-row>
        <v-col cols="12" md="6">
          <Hdatepicker
            v-model="form.date"
            :rules="[v => !!v || 'تاریخ الزامی است']"
          />
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field
            v-model="form.des"
            label="توضیحات سند"
            placeholder="توضیحات مربوط به سند"
          ></v-text-field>
        </v-col>
      </v-row>

      <v-alert v-if="error" type="error" class="mt-4">
        {{ error }}
        <template v-slot:close>
          <v-btn icon="mdi-close" variant="text" @click="error = null"></v-btn>
        </template>
      </v-alert>

      <v-table class="border rounded d-none d-sm-table mt-3" style="width: 100%;">
        <thead>
          <tr style="background-color: #0D47A1; color: white; height: 40px;">
            <th class="text-center" style="font-size: 0.8rem; padding: 0 4px;">حساب</th>
            <th class="text-center" style="font-size: 0.8rem; padding: 0 4px;">تفصیل</th>
            <th class="text-center" style="font-size: 0.8rem; padding: 0 4px;">توضیحات</th>
            <th class="text-center" style="font-size: 0.8rem; padding: 0 4px;">بدهکار</th>
            <th class="text-center" style="font-size: 0.8rem; padding: 0 4px;">بستانکار</th>
            <th class="text-center" style="width: 50px; font-size: 0.8rem; padding: 0 4px;">عملیات</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="loading">
            <tr>
              <td colspan="6" class="text-center pa-4">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
                <span class="mr-2">در حال بارگذاری...</span>
              </td>
            </tr>
          </template>
          <template v-else>
            <template v-for="(row, index) in form.rows" :key="index">
              <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '40px' }">
                <td class="text-center" style="min-width: 150px; padding: 0 4px;">
                  <Haccountsearch
                    v-model="row.ref"
                    :rules="[v => !!v || 'حساب الزامی است']"
                    @account-selected="(account) => handleAccountSelect(row, account)"
                    @tableType="(type) => handleTableType(row, type)"
                  />
                </td>
                <td class="text-center" style="min-width: 150px; padding: 0 4px;">
                  <template v-if="row.tableType === 'bank'">
                    <Hbankaccountsearch
                      v-model="row.bankAccount"
                      :rules="[]"
                      @update:modelValue="(value) => handleBankAccountSelect(row, value)"
                      density="compact"
                      hide-details
                      class="my-0"
                      style="font-size: 0.7rem;"
                      :ref="`bankAccount_${row.ref}`"
                    />
                  </template>
                  <template v-else-if="row.tableType === 'cashdesk'">
                    <Hcashdesksearch
                      v-model="row.cashdesk"
                      :rules="[]"
                      @update:modelValue="(value) => handleCashdeskSelect(row, value)"
                      density="compact"
                      hide-details
                      class="my-0"
                      style="font-size: 0.7rem;"
                      :ref="`cashdesk_${row.ref}`"
                    />
                  </template>
                  <template v-else-if="row.tableType === 'salary'">
                    <Hsalarysearch
                      v-model="row.salary"
                      :rules="[]"
                      @update:modelValue="(value) => handleSalarySelect(row, value)"
                      density="compact"
                      hide-details
                      class="my-0"
                      style="font-size: 0.7rem;"
                      :ref="`salary_${row.ref}`"
                    />
                  </template>
                  <template v-else-if="row.tableType === 'person'">
                    <Hpersonsearch
                      v-model="row.person"
                      :rules="[]"
                      @update:modelValue="(value) => handlePersonSelect(row, value)"
                      density="compact"
                      hide-details
                      class="my-0"
                      style="font-size: 0.7rem;"
                      :ref="`person_${row.ref}`"
                    />
                  </template>
                  <template v-else-if="row.tableType === 'commodity'">
                    <div class="d-flex align-center">
                      <Hcommoditysearch
                        v-model="row.commodity"
                        :rules="[]"
                        @update:modelValue="(value) => handleCommoditySelect(row, value)"
                        density="compact"
                        hide-details
                        class="my-0"
                        style="font-size: 0.7rem;"
                        :ref="`commodity_${row.ref}`"
                        :key="row.ref"
                      />
                      <v-text-field
                        v-model="row.commodityCount"
                        label="تعداد"
                        type="number"
                        density="compact"
                        hide-details
                        class="my-0 mr-2"
                        style="font-size: 0.7rem; width: 80px;"
                        min="0"
                      ></v-text-field>
                    </div>
                  </template>
                  <template v-else>
                    <v-text-field 
                      v-model="row.detail" 
                      label="تفصیل" 
                      density="compact"
                      class="my-0"
                      style="font-size: 0.7rem;"
                      hide-details
                    ></v-text-field>
                  </template>
                </td>
                <td class="text-center" style="padding: 0 4px;">
                  <v-text-field 
                    v-model="row.des" 
                    label="توضیحات" 
                    density="compact"
                    class="my-0"
                    style="font-size: 0.7rem;"
                    hide-details
                  ></v-text-field>
                </td>
                <td class="text-center" style="width: 100px; padding: 0 4px;">
                  <Hnumberinput
                    v-model="row.bd"
                    label="بدهکار"
                    density="compact"
                    @input="calculateTotals"
                    class="my-0"
                    style="font-size: 0.7rem;"
                    hide-details
                  />
                </td>
                <td class="text-center" style="width: 100px; padding: 0 4px;">
                  <Hnumberinput
                    v-model="row.bs"
                    label="بستانکار"
                    density="compact"
                    @input="calculateTotals"
                    class="my-0"
                    style="font-size: 0.7rem;"
                    hide-details
                  />
                </td>
                <td class="text-center" style="width: 50px; padding: 0 4px;">
                  <v-tooltip text="حذف" location="bottom">
                    <template v-slot:activator="{ props }">
                      <v-btn v-bind="props" icon="mdi-delete" variant="text" size="x-small" color="error"
                        @click="removeRow(row)" style="min-width: 30px;"></v-btn>
                    </template>
                  </v-tooltip>
                </td>
              </tr>
            </template>
            <tr>
              <td colspan="6" class="text-center pa-1" style="height: 40px;">
                <v-btn color="primary" prepend-icon="mdi-plus" size="x-small" @click="addRow">افزودن سطر جدید</v-btn>
              </td>
            </tr>
          </template>
        </tbody>
      </v-table>

      <!-- جدول موبایل -->
      <div class="d-sm-none">
        <template v-if="loading">
          <v-card class="mb-4" variant="outlined">
            <v-card-text class="text-center">
              <v-progress-circular indeterminate color="primary"></v-progress-circular>
              <span class="mr-2">در حال بارگذاری...</span>
            </v-card-text>
          </v-card>
        </template>
        <template v-else>
          <v-card v-for="(row, index) in form.rows" :key="index" class="mb-4" variant="outlined">
            <v-card-text>
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-subtitle-2 font-weight-bold">ردیف:</span>
                <span>{{ index + 1 }}</span>
              </div>
              <div class="mb-2">
                <Haccountsearch
                  v-model="row.ref"
                  :rules="[v => !!v || 'حساب الزامی است']"
                  @account-selected="(account) => handleAccountSelect(row, account)"
                />
              </div>
              <div class="mb-2">
                <Hbankaccountsearch
                  v-model="row.bankAccount"
                  :rules="[]"
                  @update:modelValue="(value) => handleBankAccountSelect(row, value)"
                />
              </div>
              <div class="mb-2">
                <Hcashdesksearch
                  v-model="row.cashdesk"
                  :rules="[]"
                  @update:modelValue="(value) => handleCashdeskSelect(row, value)"
                />
              </div>
              <div class="mb-2">
                <Hpersonsearch
                  v-model="row.person"
                  :rules="[]"
                  @update:modelValue="(value) => handlePersonSelect(row, value)"
                />
              </div>
              <div class="mb-2">
                <v-text-field 
                  v-model="row.des" 
                  label="توضیحات" 
                  density="compact"
                  class="my-0"
                  style="font-size: 0.8rem;"
                ></v-text-field>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <div style="width: 48%;">
                  <Hnumberinput
                    v-model="row.bd"
                    label="بدهکار"
                    density="compact"
                    @input="calculateTotals"
                    class="my-0"
                    style="font-size: 0.8rem;"
                  />
                </div>
                <div style="width: 48%;">
                  <Hnumberinput
                    v-model="row.bs"
                    label="بستانکار"
                    density="compact"
                    @input="calculateTotals"
                    class="my-0"
                    style="font-size: 0.8rem;"
                  />
                </div>
              </div>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn icon="mdi-delete" variant="text" color="error" @click="removeRow(row)"></v-btn>
            </v-card-actions>
          </v-card>
        </template>
        <v-btn color="primary" prepend-icon="mdi-plus" block class="mb-4" @click="addRow">افزودن ردیف جدید</v-btn>
      </div>

      <v-row class="mt-4">
        <v-col cols="6">
          <v-text-field
            v-model="totalBd"
            label="جمع بدهکار"
            readonly
            dense
          ></v-text-field>
        </v-col>
        <v-col cols="6">
          <v-text-field
            v-model="totalBs"
            label="جمع بستانکار"
            readonly
            dense
          ></v-text-field>
        </v-col>
      </v-row>
    </v-form>
  </v-container>

  <!-- دیالوگ تأیید حذف -->
  <v-dialog v-model="deleteDialog" max-width="400">
    <v-card>
      <v-card-title class="text-h5">
        حذف سند
      </v-card-title>
      <v-card-text>
        آیا مطمئن هستید که می‌خواهید این سند را حذف کنید؟
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="grey-darken-1" variant="text" @click="deleteDialog = false">
          انصراف
        </v-btn>
        <v-btn color="error" variant="text" @click="confirmDelete" :loading="loading">
          حذف
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Snackbar برای نمایش پیام‌ها -->
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
</template>

<script>
import axios from 'axios';
import moment from 'jalali-moment';
import Hdatepicker from '@/components/forms/Hdatepicker.vue';
import Haccountsearch from '@/components/forms/Haccountsearch.vue';
import Hbankaccountsearch from '@/components/forms/Hbankaccountsearch.vue';
import Hcashdesksearch from '@/components/forms/Hcashdesksearch.vue';
import Hsalarysearch from '@/components/forms/Hsalarysearch.vue';
import Hcommoditysearch from '@/components/forms/Hcommoditysearch.vue';
import Hpersonsearch from '@/components/forms/Hpersonsearch.vue';
import Hnumberinput from '@/components/forms/Hnumberinput.vue';

export default {
  components: {
    Hdatepicker,
    Haccountsearch,
    Hbankaccountsearch,
    Hcashdesksearch,
    Hsalarysearch,
    Hcommoditysearch,
    Hpersonsearch,
    Hnumberinput
  },
  data() {
    return {
      form: {
        date: '',
        des: '',
        rows: [
          { ref: null, refName: '', bd: '0', bs: '0', des: '', detail: '', selectedAccounts: [], bankAccount: null, cashdesk: null, salary: null, commodity: null, commodityCount: null, person: null, tableType: null },
          { ref: null, refName: '', bd: '0', bs: '0', des: '', detail: '', selectedAccounts: [], bankAccount: null, cashdesk: null, salary: null, commodity: null, commodityCount: null, person: null, tableType: null },
        ],
      },
      totalBd: 0,
      totalBs: 0,
      error: null,
      deleteDialog: false,
      loading: false,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      }
    };
  },
  computed: {
    docId() {
      return this.$route.params.id;
    }
  },
  mounted() {
    this.loading = true;
    Promise.all([
      this.docId ? this.fetchDoc() : Promise.resolve()
    ]).finally(() => {
      this.loading = false;
    });
  },
  methods: {
    showSnackbar(text, color = 'success') {
      this.snackbar.text = text;
      this.snackbar.color = color;
      this.snackbar.show = true;
    },
    async fetchDoc() {
      try {
        const response = await axios.get(`/api/hesabdari/direct/doc/get/${this.docId}`);
        if (response.data.success) {
          const serverDate = response.data.data.date;
          this.form.date = moment(serverDate, 'YYYY/MM/DD').format('YYYY/MM/DD');
          this.form.des = response.data.data.des || '';
          
          // ایجاد یک آرایه موقت برای ذخیره ردیف‌ها
          const tempRows = response.data.data.rows.map(row => ({
            ref: row.ref.id,
            refName: row.ref.name,
            bd: row.bd,
            bs: row.bs,
            des: row.des,
            detail: row.detail || '',
            selectedAccounts: [{ id: row.ref.id, name: row.ref.name }],
            bankAccount: row.bankAccount,
            cashdesk: row.cashdesk,
            salary: row.salary,
            commodity: row.commodity,
            commodityCount: row.commodityCount,
            person: row.person,
            tableType: row.ref.tableType
          }));

          // یک‌باره تمام ردیف‌ها را تنظیم کنیم
          this.form.rows = tempRows;

          // تنظیم مقادیر کامپوننت‌های فرزند
          await this.$nextTick();
          
          // استفاده از Promise.all برای اجرای همزمان تنظیم مقادیر
          await Promise.all(this.form.rows.map(async (row) => {
            if (row.tableType === 'bank' && row.bankAccount) {
              const bankAccountRef = this.$refs[`bankAccount_${row.ref}`]?.[0];
              if (bankAccountRef) {
                await bankAccountRef.setValue(row.bankAccount);
              }
            }
            if (row.tableType === 'cashdesk' && row.cashdesk) {
              const cashdeskRef = this.$refs[`cashdesk_${row.ref}`]?.[0];
              if (cashdeskRef) {
                await cashdeskRef.setValue(row.cashdesk);
              }
            }
            if (row.tableType === 'person' && row.person) {
              const personRef = this.$refs[`person_${row.ref}`]?.[0];
              if (personRef) {
                await personRef.setValue(row.person);
              }
            }
            if (row.tableType === 'commodity' && row.commodity) {
              const commodityRef = this.$refs[`commodity_${row.ref}`]?.[0];
              if (commodityRef) {
                await commodityRef.setValue(row.commodity);
              }
            }
            if (row.tableType === 'salary' && row.salary) {
              const salaryRef = this.$refs[`salary_${row.ref}`]?.[0];
              if (salaryRef) {
                await salaryRef.setValue(row.salary);
              }
            }
          }));

          this.calculateTotals();
        } else {
          this.error = response.data.message || 'خطا در بارگذاری سند';
        }
      } catch (error) {
        this.error = 'خطا در بارگذاری سند: ' + (error.response?.data?.message || 'مشکل ناشناخته');
      }
    },
    addRow() {
      this.form.rows.push({ ref: null, refName: '', bd: '0', bs: '0', des: '', detail: '', selectedAccounts: [], bankAccount: null, cashdesk: null, salary: null, commodity: null, commodityCount: null, person: null, tableType: null });
    },
    removeRow(item) {
      const index = this.form.rows.indexOf(item);
      if (index >= 0) {
        this.form.rows.splice(index, 1);
        this.calculateTotals();
      }
    },
    calculateTotals() {
      let hasError = false;
      for (const row of this.form.rows) {
        if (!this.validateDebitCredit(row)) {
          hasError = true;
        }
      }
      if (hasError) {
        return;
      }
      this.error = null;
      this.totalBd = this.form.rows.reduce((sum, row) => sum + parseInt(row.bd || 0), 0);
      this.totalBs = this.form.rows.reduce((sum, row) => sum + parseInt(row.bs || 0), 0);
    },
    validateDebitCredit(row) {
      if (parseInt(row.bd) > 0 && parseInt(row.bs) > 0) {
        this.error = 'در هر سطر فقط یکی از فیلدهای بدهکار یا بستانکار می‌تواند مقدار داشته باشد';
        // صفر کردن مقدار نامعتبر
        if (row.bd > 0) {
          row.bd = '0';
        } else if (row.bs > 0) {
          row.bs = '0';
        }
        return false;
      }
      return true;
    },
    handleAccountSelect(row, account) {
      row.ref = account.id;
      row.refName = account.name;
      row.selectedAccounts = [account];
      // فقط tableType را تنظیم کنید اگر تغییر کرده باشد
      if (row.tableType !== account.tableType) {
        this.handleTableType(row, account.tableType);
      }
    },
    handleBankAccountSelect(row, bankAccount) {
      row.bankAccount = bankAccount;
    },
    handleCashdeskSelect(row, cashdesk) {
      row.cashdesk = cashdesk;
    },
    handleSalarySelect(row, salary) {
      row.salary = salary;
    },
    handleCommoditySelect(row, commodity) {
      row.commodity = commodity;
      row.commodityCount = commodity ? row.commodityCount : null;
    },
    handlePersonSelect(row, person) {
      row.person = person;
    },
    handleTableType(row, type) {
      if (row.tableType === type) return; // جلوگیری از تغییرات غیرضروری
      const prevCommodity = row.commodity; // ذخیره مقدار قبلی commodity
      const prevCommodityCount = row.commodityCount;

      row.tableType = type;
      // فقط فیلدهای غیرمرتبط را پاک کنید
      if (type !== 'bank') row.bankAccount = null;
      if (type !== 'cashdesk') row.cashdesk = null;
      if (type !== 'person') row.person = null;
      if (type !== 'salary') row.salary = null;
      if (type !== 'commodity') {
        row.commodity = null;
        row.commodityCount = null;
      } else {
        // بازیابی commodity اگر tableType به commodity برگردد
        row.commodity = prevCommodity;
        row.commodityCount = prevCommodityCount;
      }
      if (type !== 'calc') row.detail = '';
    },
    async submitForm() {
      this.error = null;
      if (this.form.rows.length < 2) {
        this.error = 'حداقل باید دو سطر در سند وجود داشته باشد';
        return;
      }

      if (this.totalBd !== this.totalBs) {
        this.error = 'جمع بدهکار و بستانکار باید برابر باشد';
        return;
      }

      for (const row of this.form.rows) {
        if (!row.ref) {
          this.error = 'انتخاب حساب در تمام سطرها الزامی است';
          return;
        }

        if (parseInt(row.bd) === 0 && parseInt(row.bs) === 0) {
          this.error = 'در هر سطر باید حداقل یکی از فیلدهای بدهکار یا بستانکار مقدار داشته باشد';
          return;
        }

        if (!this.validateDebitCredit(row)) {
          return;
        }

        if (row.tableType === 'bank' && !row.bankAccount) {
          this.error = 'انتخاب حساب بانکی در سطر مربوطه الزامی است';
          return;
        }

        if (row.tableType === 'cashdesk' && !row.cashdesk) {
          this.error = 'انتخاب صندوق در سطر مربوطه الزامی است';
          return;
        }

        if (row.tableType === 'salary' && !row.salary) {
          this.error = 'انتخاب حقوق در سطر مربوطه الزامی است';
          return;
        }

        if (row.tableType === 'person' && !row.person) {
          this.error = 'انتخاب شخص در سطر مربوطه الزامی است';
          return;
        }

        if (row.tableType === 'commodity' && !row.commodity) {
          this.error = 'انتخاب کالا در سطر مربوطه الزامی است';
          return;
        }

        if (row.tableType === 'commodity' && !row.commodityCount) {
          this.error = 'تعداد کالا در سطر مربوطه الزامی است';
          return;
        }
      }

      const payload = {
        date: this.form.date,
        des: this.form.des,
        rows: this.form.rows.map(row => ({
          ref: row.ref,
          bd: row.bd,
          bs: row.bs,
          des: row.des,
          detail: row.detail,
          bankAccount: row.bankAccount,
          cashdesk: row.cashdesk,
          salary: row.salary,
          commodity: row.commodity,
          commodityCount: row.commodityCount,
          person: row.person
        })),
      };

      this.loading = true;
      try {
        let response;
        if (this.docId) {
          response = await axios.put(`/api/hesabdari/direct/doc/update/${this.docId}`, payload);
        } else {
          response = await axios.post('/api/hesabdari/direct/doc/create', payload);
        }

        if (response && response.data && response.data.success) {
          this.showSnackbar(response.data.message);
          setTimeout(() => {
            this.$router.push('/acc/accounting/list');
          }, 1000);
        } else {
          this.error = response?.data?.message || 'خطا در انجام عملیات';
        }
      } catch (error) {
        if (error.response && error.response.data && error.response.data.success) {
          this.showSnackbar(error.response.data.message);
          setTimeout(() => {
            this.$router.push('/acc/accounting/list');
          }, 1000);
        } else {
          this.error = error.response?.data?.message || 'خطا در ارتباط با سرور';
        }
      } finally {
        this.loading = false;
      }
    },
    async confirmDelete() {
      try {
        this.loading = true;
        const response = await axios.delete(`/api/hesabdari/direct/doc/delete/${this.docId}`);
        if (response && response.data && response.data.success) {
          this.showSnackbar(response.data.message);
          setTimeout(() => {
            this.$router.push('/acc/accounting/list');
          }, 1000);
        } else {
          this.error = response?.data?.message || 'خطا در حذف سند';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'خطا در حذف سند';
        console.error(error);
      } finally {
        this.loading = false;
        this.deleteDialog = false;
      }
    },
  },
};
</script>

<style scoped>
.v-data-table {
  margin-top: 20px;
}
</style>