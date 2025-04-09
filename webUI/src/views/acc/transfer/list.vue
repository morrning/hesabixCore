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
          <v-btn icon variant="text" size="small" color="error" v-bind="props">
            <v-icon>mdi-menu</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item :to="'/acc/accounting/view/' + item.code" prepend-icon="mdi-file-document text-success">
            سند حسابداری
          </v-list-item>
          <v-list-item @click="showTransferDetails(item)" prepend-icon="mdi-eye text-primary">
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

  <v-dialog v-model="showDetailsDialog" max-width="600px">
    <v-card>
      <v-toolbar color="toolbar" title="جزئیات انتقال">
        <template v-slot:append>
          <archive-upload v-if="selectedTransfer?.code" :docid="selectedTransfer.code" doctype="transfer" cat="transfer"></archive-upload>
          <notes :code="selectedTransfer?.code" :type-note="'transfer'" :stat="{ count: 0 }"></notes>
          <document-log-button :doc-code="selectedTransfer?.code"></document-log-button>
          <v-tooltip text="ویرایش" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-pencil" variant="text" :to="'/acc/transfer/mod/' + selectedTransfer?.code" color="primary"></v-btn>
            </template>
          </v-tooltip>
          <v-tooltip text="بستن" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-close" variant="text" @click="showDetailsDialog = false"></v-btn>
            </template>
          </v-tooltip>
        </template>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col cols="6">
            <v-text-field
              label="شماره سند"
              :model-value="selectedTransfer?.code"
              readonly
              variant="outlined"
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="6">
            <v-text-field
              label="تاریخ"
              :model-value="selectedTransfer?.date"
              readonly
              variant="outlined"
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="6">
            <v-text-field
              label="از"
              :model-value="getFromTypeText(selectedTransfer)"
              readonly
              variant="outlined"
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="6">
            <v-text-field
              label="به"
              :model-value="getToTypeText(selectedTransfer)"
              readonly
              variant="outlined"
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="6">
            <v-text-field
              label="مبلغ"
              :model-value="selectedTransfer?.amount"
              readonly
              variant="outlined"
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="6">
            <v-text-field
              label="ثبت کننده"
              :model-value="selectedTransfer?.submitter"
              readonly
              variant="outlined"
              density="compact"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-textarea
              label="شرح"
              :model-value="selectedTransfer?.des"
              readonly
              variant="outlined"
              density="compact"
              auto-grow
              rows="2"
            ></v-textarea>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";
import archiveUpload from "../component/archive/archiveUpload.vue";
import documentLogButton from "../component/documentLogButton.vue";
import notes from "../component/notes.vue";

export default {
  name: "list",
  components: {
    archiveUpload,
    documentLogButton,
    notes
  },
  data: () => ({
    searchValue: '',
    loading: ref(true),
    items: [],
    showDetailsDialog: false,
    selectedTransfer: null,
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
    },
    showTransferDetails(transfer) {
      this.selectedTransfer = transfer;
      this.showDetailsDialog = true;
    },
    getFromTypeText(transfer) {
      if (!transfer) return '';
      if (transfer.fromType === 'bank') return `حساب بانکی: ${transfer.fromObject}`;
      if (transfer.fromType === 'salary') return `تنخواه گردان: ${transfer.fromObject}`;
      if (transfer.fromType === 'cashDesk') return `صندوق: ${transfer.fromObject}`;
      return '';
    },
    getToTypeText(transfer) {
      if (!transfer) return '';
      if (transfer.toType === 'bank') return `حساب بانکی: ${transfer.toObject}`;
      if (transfer.toType === 'salary') return `تنخواه گردان: ${transfer.toObject}`;
      if (transfer.toType === 'cashDesk') return `صندوق: ${transfer.toObject}`;
      return '';
    },
    showHistory() {
      if (this.selectedTransfer?.code) {
        this.$router.push(`/acc/accounting/history/${this.selectedTransfer.code}`);
      }
    }
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<style scoped></style>