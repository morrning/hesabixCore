<script lang="ts">
import { defineComponent } from 'vue'
import { ref } from "vue";
import Swal from "sweetalert2";
import axios from "axios";

export default defineComponent({
  name: "recList",
  props: {
    items: {
      type: Array,
      required: true
    },
    windowsState: {
      type: Object,
      required: true
    }
  },
  data: () => ({
    dialog: false,
    searchValue: '',
    loading: false,
    headers: [
      {
        title: 'شماره سند',
        key: 'code',
        sortable: true,
        align: 'center'
      },
      {
        title: 'تاریخ',
        key: 'date',
        sortable: true,
        align: 'center'
      },
      {
        title: 'شرح',
        key: 'des',
        sortable: true,
        align: 'center'
      },
      {
        title: 'مبلغ',
        key: 'amount',
        sortable: true,
        align: 'center'
      },
      {
        title: 'نوع',
        key: 'type',
        sortable: true,
        align: 'center'
      },
      {
        title: 'عملیات',
        key: 'operation',
        sortable: false,
        align: 'center'
      }
    ]
  }),
  methods: {
    async deleteItem(code: string) {
      try {
        const result = await Swal.fire({
          text: 'آیا برای حذف این مورد مطمئن هستید؟',
          showCancelButton: true,
          confirmButtonText: 'بله',
          cancelButtonText: 'خیر',
          icon: 'warning'
        })

        if (result.isConfirmed) {
          const response = await axios.post('/api/accounting/remove', { code })

          if (response.data.result === 1) {
            await Swal.fire({
              text: 'سند دریافت فاکتور با موفقیت حذف شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            })
            this.windowsState.submited = true
          }

          if (response.data.result === 2) {
            await Swal.fire({
              text: response.data.message,
              icon: 'success',
              confirmButtonText: 'قبول'
            })
          }
        }
      } catch (error) {
        console.error('Error deleting item:', error)
        await Swal.fire({
          text: 'خطا در حذف سند',
          icon: 'error',
          confirmButtonText: 'قبول'
        })
      }
    }
  }
})
</script>

<template>
  <!-- دکمه نمایش لیست -->
  <v-btn icon color="info" class="ml-2" @click="dialog = true">
    <v-icon>mdi-format-list-bulleted</v-icon>
    <v-tooltip activator="parent" location="bottom">لیست دریافت‌ها</v-tooltip>
  </v-btn>

  <!-- دیالوگ نمایش لیست -->
  <v-dialog v-model="dialog" max-width="900" persistent>
    <v-card>
      <v-toolbar color="grey-lighten-4" flat>
        <v-toolbar-title>
          <v-icon color="info" class="ml-2">mdi-format-list-bulleted</v-icon>
          لیست دریافت‌ها
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text class="pa-0">
        <v-data-table
          :headers="headers"
          :items="items"
          :search="searchValue"
          :loading="loading"
          class="elevation-1"
          :header-props="{ class: 'custom-header' }"
          hover
        >
          <!-- ستون نوع -->
          <template v-slot:item.type="{ item }">
            <v-chip
              :color="item.type === 'sell_receive' || item.type === 'buy_send' ? 'error' : 'success'"
              size="small"
              variant="flat"
            >
              {{ item.type === 'sell_receive' || item.type === 'buy_send' ? 'سند حسابداری' : 'پرداخت آنلاین' }}
            </v-chip>
          </template>

          <!-- ستون مبلغ -->
          <template v-slot:item.amount="{ item }">
            {{ $filters.formatNumber(item.amount) }}
          </template>

          <!-- ستون عملیات -->
          <template v-slot:item.operation="{ item }">
            <v-btn
              v-if="item.type === 'sell_receive' || item.type === 'buy_send'"
              color="error"
              size="default"
              variant="plain"
              @click="deleteItem(item.code)"
            >
              <v-icon>mdi-trash-can</v-icon>
              <v-tooltip activator="parent" location="top">حذف</v-tooltip>
            </v-btn>
          </template>

          <!-- نمایش پیام خالی بودن جدول -->
          <template v-slot:no-data>
            <v-alert type="info" variant="tonal" class="ma-2">
              اطلاعاتی برای نمایش وجود ندارد
            </v-alert>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>

</style>