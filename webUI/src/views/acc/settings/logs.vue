<template>
    <!-- هدر -->
    <v-toolbar color="toolbar" title="تاریخچه رویدادها" flat>
      <template v-slot:prepend>
        <v-tooltip text="بازگشت" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
          </template>
        </v-tooltip>
      </template>
      
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

export default {
  name: 'logs',
  data() {
    return {
      search: '',
      loading: true,
      items: [],
      headers: [
        { 
          title: 'تاریخ',
          align: 'center',
          key: 'date',
        },
        {
          title: 'کاربر',
          align: 'center',
          key: 'user',
        },
        {
          title: 'توضیحات',
          align: 'center',
          key: 'des',
        },
        {
          title: 'بخش',
          align: 'center',
          key: 'part',
        },
        {
          title: 'آی پی آدرس',
          align: 'center',
          key: 'ipaddress',
        },
      ],
    }
  },
  methods: {
    async loadData() {
      try {
        const response = await axios.post('/api/business/logs/' + localStorage.getItem('activeBid'))
        this.items = response.data
      } catch (error) {
        console.error('خطا در دریافت اطلاعات:', error)
      } finally {
        this.loading = false
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