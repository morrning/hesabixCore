<script lang="ts">
import { defineComponent } from 'vue'
import { ref } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

export default defineComponent({
  name: "recList",
  props: {
    items: Array,
    windowsState: Object
  },
  data: () => {
    return {
      searchValue: '',
      loading: ref(false),
      headers: [
        { text: "شماره سند", value: "code", sortable: true },
        { text: "تاریخ", value: "date", sortable: true },
        { text: "شرح", value: "des", sortable: true },
        { text: "مبلغ", value: "amount", sortable: true },
        { text: "نوع", value: "type", sortable: true },
        { text: "عملیات", value: "operation" },
      ]
    }
  },
  methods: {
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف این مورد مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/accounting/remove', {
            'code': code
          }
          ).then((response) => {
            if (response.data.result == 1) {
              Swal.fire({
                text: 'سند دریافت فاکتور با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              }).then((result) => {
                this.$props.windowsState.submited = true;
              });
            }
            if (response.data.result == 2) {
              Swal.fire({
                text: response.data.message,
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    }
  }
})
</script>

<template>
  <div class="mb-1">
    <div class="input-group input-group-sm">
      <span class="input-group-text"><i class="fa fa-search"></i></span>
      <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
    </div>
  </div>
  <EasyDataTable table-class-name="customize-table" show-index alternating :search-value="searchValue" :headers="headers" :items="this.$props.items"
    theme-color="#1d90ff" header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
    emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
    <template #item-operation="{ code, type }">
      <span v-if="type == 'sell_receive' || type == 'buy_send'" class="text-danger px-1">
        <span class="text-danger px-1" @click="deleteItem(code)">
          <i class="fa fa-trash"></i>
        </span>
      </span>
    </template>
    <template #item-type="{ type }">
      <span v-if="type == 'sell_receive' || type == 'buy_send'" class="text-danger px-1">سند حسابداری</span>
      <span v-else class="text-success px-1">پرداخت آنلاین</span>
    </template>
    <template #item-amount="{ amount }">
      <span>{{ $filters.formatNumber(amount) }}</span>
    </template>
  </EasyDataTable>
</template>

<style scoped></style>