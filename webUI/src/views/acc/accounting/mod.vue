<template>
    <v-container>
      <v-form @submit.prevent="submitForm">
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field
              v-model="form.date"
              label="تاریخ (شمسی)"
              placeholder="1403/02/28"
              :rules="[v => !!v || 'تاریخ الزامی است']"
            ></v-text-field>
          </v-col>
        </v-row>
  
        <v-data-table
          :headers="headers"
          :items="form.rows"
          class="elevation-1"
          hide-default-footer
        >
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title>ردیف‌های سند</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-btn color="primary" @click="addRow">افزودن ردیف</v-btn>
            </v-toolbar>
          </template>
  
          <template v-slot:item.ref="{ item }">
            <v-menu offset-y>
              <template v-slot:activator="{ props }">
                <v-text-field
                  v-model="item.refName"
                  label="حساب"
                  dense
                  readonly
                  v-bind="props"
                  :rules="[v => !!item.ref || 'حساب الزامی است']"
                ></v-text-field>
              </template>
              <v-treeview
                :items="hesabdariTables"
                item-key="id"
                item-text="name"
                item-children="children"
                selectable
                return-object
                v-model="item.selectedAccounts"
                @update:active="selectAccount(item, $event)"
              >
                <template v-slot:label="{ item: treeItem }">
                  {{ treeItem.name }}
                </template>
              </v-treeview>
            </v-menu>
          </template>
  
          <template v-slot:item.bd="{ item }">
            <v-text-field
              v-model="item.bd"
              label="بدهکار"
              type="number"
              dense
              @input="calculateTotals"
            ></v-text-field>
          </template>
  
          <template v-slot:item.bs="{ item }">
            <v-text-field
              v-model="item.bs"
              label="بستانکار"
              type="number"
              dense
              @input="calculateTotals"
            ></v-text-field>
          </template>
  
          <template v-slot:item.des="{ item }">
            <v-text-field v-model="item.des" label="توضیحات" dense></v-text-field>
          </template>
  
          <template v-slot:item.actions="{ item }">
            <v-btn color="error" small @click="removeRow(item)">حذف</v-btn>
          </template>
        </v-data-table>
  
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
  
        <v-btn type="submit" color="success" class="mt-4" :disabled="totalBd !== totalBs || !form.date">
          ثبت سند
        </v-btn>
      </v-form>
    </v-container>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
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
          rows: [
            { ref: null, refName: '', bd: '0', bs: '0', des: '', selectedAccounts: [] },
          ],
        },
        hesabdariTables: [],
        totalBd: 0,
        totalBs: 0,
        error: null,
        headers: [
          { text: 'حساب', value: 'ref' },
          { text: 'بدهکار', value: 'bd' },
          { text: 'بستانکار', value: 'bs' },
          { text: 'توضیحات', value: 'des' },
          { text: 'عملیات', value: 'actions', sortable: false },
        ],
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
          console.log('دیتای دریافت‌شده از API:', response.data);
          this.hesabdariTables = response.data.data;
        } catch (error) {
          console.error('خطا در دریافت حساب‌ها:', error.response?.data || error.message);
          this.error = 'خطا در بارگذاری حساب‌ها: ' + (error.response?.data?.message || 'مشکل ناشناخته');
        }
      },
      async fetchDoc() {
        try {
          const response = await axios.get(`/api/hesabdari/doc/${this.docId}`);
          this.form.date = response.data.data.date;
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
          date: this.form.date,
          rows: this.form.rows.map(row => ({
            ref: row.ref,
            bd: row.bd,
            bs: row.bs,
            des: row.des,
          })),
        };
  
        try {
          if (this.docId) {
            await axios.put(`/api/hesabdari/doc/${this.docId}`, payload);
            this.$emit('saved', 'سند با موفقیت ویرایش شد');
          } else {
            const response = await axios.post('/api/hesabdari/doc', payload);
            this.$emit('saved', 'سند با موفقیت ثبت شد', response.data.data.id);
          }
        } catch (error) {
          this.error = error.response?.data?.message || 'خطا در ثبت سند';
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