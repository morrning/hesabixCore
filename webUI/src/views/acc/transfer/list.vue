<template>
  <v-toolbar color="toolbar" title="انتقالات">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>

    <v-tooltip :text="$t('dialog.add_new_transfer')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon variant="text" color="primary" :to="'/acc/transfer/mod/'">
          <v-icon>mdi-plus</v-icon>
        </v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>

  <v-text-field v-model="searchValue" :rounded="false" prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" hide-details
    placeholder="جست و جو ..." class=""></v-text-field>

  <v-data-table :headers="headers" :items="items" :search="searchValue" :loading="loading" density="comfortable" hover
    :header-props="{ class: 'custom-header' }">
    <template v-slot:item.operation="{ item }">
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn icon variant="text" v-bind="props">
            <v-icon>mdi-dots-vertical</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item :to="'/acc/accounting/view/' + item.code" prepend-icon="mdi-file-document text-success">
            سند حسابداری
          </v-list-item>
          <v-list-item :to="'/acc/transfer/mod/' + item.code" prepend-icon="mdi-eye text-primary">
            مشاهده
          </v-list-item>
          <v-list-item :to="'/acc/transfer/mod/' + item.code" prepend-icon="mdi-pencil">
            ویرایش
          </v-list-item>
          <v-list-item @click="deleteItem(item.code)" prepend-icon="mdi-delete text-error">
            حذف
          </v-list-item>
        </v-list>
      </v-menu>
    </template>

    <template v-slot:item.fromType="{ item }">
      <span v-if="item.fromType === 'bank'">حساب بانکی: {{ item.fromObject }}</span>
      <span v-if="item.fromType === 'salary'">تنخواه گردان: {{ item.fromObject }}</span>
      <span v-if="item.fromType === 'cashDesk'">صندوق: {{ item.fromObject }}</span>
    </template>

    <template v-slot:item.toType="{ item }">
      <span v-if="item.toType === 'bank'">حساب بانکی: {{ item.toObject }}</span>
      <span v-if="item.toType === 'salary'">تنخواه گردان: {{ item.toObject }}</span>
      <span v-if="item.toType === 'cashDesk'">صندوق: {{ item.toObject }}</span>
    </template>

    <template v-slot:item.code="{ item }">
      <v-btn variant="text" :to="'/acc/accounting/view/' + item.code" class="text-none">
        {{ item.code }}
      </v-btn>
    </template>
  </v-data-table>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "list",
  data: () => ({
    searchValue: '',
    loading: ref(true),
    items: [],
    headers: [
      { title: "عملیات", key: "operation", sortable: false },
      { title: "شماره سند", key: "code", sortable: true },
      { title: "تاریخ", key: "date", sortable: true },
      { title: "از", key: "fromType", sortable: true },
      { title: "به", key: "toType", sortable: true },
      { title: "مبلغ", key: "amount", sortable: true },
      { title: "شرح", key: "des", sortable: true },
      { title: "ثبت کننده", key: "submitter", sortable: true },
    ]
  }),
  methods: {
    loadData() {
      axios.post('/api/transfer/search')
        .then((response) => {
          this.items = response.data;
          this.items.forEach((item) => {
            item.amount = this.$filters.formatNumber(item.amount)
          })
          this.loading = false;
        })
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف این مورد مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
      }).then((result) => {
        if (result.isConfirmed) {
          axios.post('/api/accounting/remove', {
            'code': code
          }).then((response) => {
            if (response.data.result == 1) {
              this.items = this.items.filter(item => item.code !== code);
              Swal.fire({
                text: 'سند انتقال با موفقیت حذف شد.',
                icon: 'success',
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
  }
}
</script>

<style scoped></style>