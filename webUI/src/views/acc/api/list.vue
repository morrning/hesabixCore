<template>
<!-- هدر -->
<v-toolbar color="toolbar" flat>
      <template v-slot:prepend>
        <v-tooltip text="بازگشت" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      <v-toolbar-title>
        دسترسی توسعه دهندگان
      </v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn color="success" @click="submitNew" prepend-icon="mdi-plus">
        ایجاد رابط جدید
      </v-btn>
    </v-toolbar>
  <v-text-field
            v-model="search"
            prepend-inner-icon="mdi-magnify"
            label="جست و جو ..."
            variant="outlined"
            density="compact"
            hide-details
            class="mb-0"
            :rounded="0"
          ></v-text-field>
   
      <!-- جدول -->
      <v-data-table
        :headers="headers"
        :items="items"
        :search="search"
        :loading="loading"
        hover
        :header-props="{ class: 'custom-header' }"

        class="elevation-1"
      >
        <!-- ستون عملیات -->
        <template v-slot:item.operation="{ item }">
          <v-btn
            icon="mdi-delete"
            color="error"
            size="small"
            variant="text"
            @click="deleteItem(item.raw.token)"
          >
          </v-btn>
        </template>

        <!-- لودینگ -->
        <template v-slot:loading>
          <v-skeleton-loader
            type="table-row"
            class="my-2"
          ></v-skeleton-loader>
        </template>

        <!-- پیام خالی بودن -->
        <template v-slot:no-data>
          اطلاعاتی برای نمایش وجود ندارد
        </template>

        <!-- پیام نتیجه جستجو -->
        <template v-slot:no-results>
          نتیجه‌ای یافت نشد
        </template>
      </v-data-table>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

export default {
  name: 'list',
  data() {
    return {
      search: '',
      loading: true,
      items: [],
      headers: [
        { 
          title: 'توکن دسترسی',
          align: 'center',
          key: 'token',
          sortable: true
        },
        {
          title: 'مهر زمان انقضا',
          align: 'center',
          key: 'dateExpire'
        },
        {
          title: 'عملیات',
          align: 'center',
          key: 'operation',
          sortable: false
        }
      ]
    }
  },
  methods: {
    async loadData() {
      this.loading = true
      try {
        const response = await axios.post('/api/business/api/list')
        this.items = response.data
      } catch (error) {
        console.error('خطا در دریافت اطلاعات:', error)
      } finally {
        this.loading = false
      }
    },

    async submitNew() {
      this.loading = true
      try {
        const response = await axios.post('/api/business/api/new')
        this.items.push(response.data)
        await Swal.fire({
          text: 'توکن ایجاد شد. رابط توکن: ' + response.data.token,
          confirmButtonText: 'قبول',
        })
      } catch (error) {
        console.error('خطا در ایجاد توکن:', error)
      } finally {
        this.loading = false
      }
    },

    async deleteItem(token) {
      const result = await Swal.fire({
        text: 'آیا برای این مورد مطمئن هستید؟ دسترسی برنامه‌هایی که از این رابط استفاده می‌کنند قطع خواهد شد.',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'خیر',
        icon: 'warning'
      })

      if (result.isConfirmed) {
        try {
          const response = await axios.post('/api/business/api/remove/' + token)
          if (response.data.result === 1) {
            this.items = this.items.filter(item => item.token !== token)
            await Swal.fire({
              text: 'توکن با موفقیت حذف شد.',
              icon: 'success',
              confirmButtonText: 'قبول'
            })
          }
        } catch (error) {
          console.error('خطا در حذف توکن:', error)
        }
      }
    }
  },
  mounted() {
    this.loadData()
  }
}
</script>

<style>
.v-data-table th {
  white-space: nowrap;
}
</style>