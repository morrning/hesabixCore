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
          <template v-for="(row, index) in form.rows" :key="index">
            <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '40px' }">
              <td class="text-center" style="min-width: 150px; padding: 0 4px;">
                <Haccountsearch
                  v-model="row.ref"
                  :rules="[v => !!v || 'حساب الزامی است']"
                  @account-selected="(account) => handleAccountSelect(row, account)"
                />
              </td>
              <td class="text-center" style="min-width: 100px; padding: 0 4px;">
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
                <v-text-field
                  v-model="row.bd"
                  label="بدهکار"
                  type="number"
                  density="compact"
                  @input="calculateTotals"
                  class="my-0"
                  style="font-size: 0.7rem;"
                  hide-details
                ></v-text-field>
              </td>
              <td class="text-center" style="width: 100px; padding: 0 4px;">
                <v-text-field
                  v-model="row.bs"
                  label="بستانکار"
                  type="number"
                  density="compact"
                  @input="calculateTotals"
                  class="my-0"
                  style="font-size: 0.7rem;"
                  hide-details
                ></v-text-field>
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
        </tbody>
      </v-table>

      <!-- جدول موبایل -->
      <div class="d-sm-none">
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
                <v-text-field
                  v-model="row.bd"
                  label="بدهکار"
                  type="number"
                  density="compact"
                  @input="calculateTotals"
                  class="my-0"
                  style="font-size: 0.8rem;"
                ></v-text-field>
              </div>
              <div style="width: 48%;">
                <v-text-field
                  v-model="row.bs"
                  label="بستانکار"
                  type="number"
                  density="compact"
                  @input="calculateTotals"
                  class="my-0"
                  style="font-size: 0.8rem;"
                ></v-text-field>
              </div>
            </div>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn icon="mdi-delete" variant="text" color="error" @click="removeRow(row)"></v-btn>
          </v-card-actions>
        </v-card>
        <v-btn color="primary" prepend-icon="mdi-plus" block class="mb-4" @click="addRow">افزودن ردیف جدید</v-btn>
      </div>

      <v-row class="mt-4">
        <v-col cols="6">
          <v-text-field
            :value="totalBd"
            label="جمع بدهکار"
            readonly
            dense
          ></v-text-field>
        </v-col>
        <v-col cols="6">
          <v-text-field
            :value="totalBs"
            label="جمع بستانکار"
            readonly
            dense
          ></v-text-field>
        </v-col>
      </v-row>

      <v-alert v-if="error" type="error" class="mt-4">{{ error }}</v-alert>
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
</template>

<script>
import axios from 'axios';
import moment from 'jalali-moment';
import Hdatepicker from '@/components/forms/Hdatepicker.vue';
import Haccountsearch from '@/components/forms/Haccountsearch.vue';

export default {
  components: {
    Hdatepicker,
    Haccountsearch
  },
  props: {
    docId: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      form: {
        date: '',
        des: '',
        rows: [
          { ref: null, refName: '', bd: '0', bs: '0', des: '', selectedAccounts: [] },
        ],
      },
      hesabdariTables: [],
      totalBd: 0,
      totalBs: 0,
      error: null,
      deleteDialog: false,
      loading: false,
    };
  },
  mounted() {
    this.fetchHesabdariTables();
    if (this.docId) {
      this.fetchDoc();
    }
  },
  methods: {
    async fetchHesabdariTables() {
      try {
        const response = await axios.get('/api/hesabdari/tables');
        this.hesabdariTables = response.data.data;
      } catch (error) {
        console.error('خطا در دریافت حساب‌ها:', error.response?.data || error.message);
        this.error = 'خطا در بارگذاری حساب‌ها: ' + (error.response?.data?.message || 'مشکل ناشناخته');
      }
    },
    async fetchDoc() {
      try {
        const response = await axios.get(`/api/hesabdari/doc/${this.docId}`);
        const serverDate = response.data.data.date;
        this.form.date = moment(serverDate, 'YYYY/MM/DD').format('YYYY-MM-DD');
        this.form.des = response.data.data.des || '';
        this.form.rows = response.data.data.rows.map(row => ({
          ref: row.ref.id,
          refName: row.ref.name,
          bd: row.bd,
          bs: row.bs,
          des: row.des,
          selectedAccounts: [{ id: row.ref.id, name: row.ref.name }],
        }));
        this.calculateTotals();
      } catch (error) {
        this.error = 'خطا در بارگذاری سند: ' + (error.response?.data?.message || 'مشکل ناشناخته');
      }
    },
    addRow() {
      this.form.rows.push({ ref: null, refName: '', bd: '0', bs: '0', des: '', selectedAccounts: [] });
    },
    removeRow(item) {
      const index = this.form.rows.indexOf(item);
      if (index >= 0) {
        this.form.rows.splice(index, 1);
        this.calculateTotals();
      }
    },
    calculateTotals() {
      this.totalBd = this.form.rows.reduce((sum, row) => sum + parseInt(row.bd || 0), 0);
      this.totalBs = this.form.rows.reduce((sum, row) => sum + parseInt(row.bs || 0), 0);
    },
    selectAccount(row, selected) {
      if (selected.length > 0) {
        const account = selected[0];
        row.ref = account.id;
        row.refName = account.name;
        row.selectedAccounts = [account];
      }
    },
    async submitForm() {
      this.error = null;
      if (this.totalBd !== this.totalBs) {
        this.error = 'جمع بدهکار و بستانکار باید برابر باشد';
        return;
      }

      const payload = {
        date: moment(this.form.date, 'YYYY-MM-DD').locale('fa').format('YYYY/MM/DD'),
        des: this.form.des,
        rows: this.form.rows.map(row => ({
          ref: row.ref,
          bd: row.bd,
          bs: row.bs,
          des: row.des,
        })),
      };

      try {
        this.loading = true;
        if (this.docId) {
          await axios.put(`/api/hesabdari/doc/${this.docId}`, payload);
          this.$emit('saved', 'سند با موفقیت ویرایش شد');
        } else {
          const response = await axios.post('/api/hesabdari/doc', payload);
          this.$emit('saved', 'سند با موفقیت ثبت شد', response.data.data.id);
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'خطا در ثبت سند';
      } finally {
        this.loading = false;
      }
    },
    async confirmDelete() {
      try {
        this.loading = true;
        await axios.delete(`/api/hesabdari/doc/${this.docId}`);
        this.$router.push('/acc/accounting/list');
      } catch (error) {
        this.error = 'خطا در حذف سند';
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