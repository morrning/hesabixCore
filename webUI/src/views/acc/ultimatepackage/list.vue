<template>
    <v-app>
      <v-toolbar>
        <v-btn icon @click="$router.go(-1)">
          <v-icon>mdi-arrow-right</v-icon>
        </v-btn>
        <v-toolbar-title>سفارشات بسته‌های حسابداری نامحدود</v-toolbar-title>
      </v-toolbar>
  
      <v-container>
        <!-- لودینگ اولیه -->
        <v-row v-if="initialLoading" justify="center" class="mt-5">
          <v-progress-circular indeterminate color="primary" :size="50"></v-progress-circular>
        </v-row>
  
        <v-data-table
          v-if="canShowForm && !initialLoading"
          :headers="headers"
          :header-props="{ class: 'custom-header' }"
          :items="packages"
          :items-per-page="10"
          :loading="isLoading"
          loading-text="در حال بارگذاری اطلاعات..."
          class="elevation-1"
        >
          <template v-slot:item.dateSubmit="{ item }">
            {{ formatDate(item.dateSubmit) }}
          </template>
          <template v-slot:item.dateExpire="{ item }">
            {{ formatDate(item.dateExpire) }}
          </template>
          <template v-slot:item.price="{ item }">
            {{ formatPrice(item.price) }}
          </template>
          <template v-slot:item.status="{ item }">
            <v-chip :color="getStatusColor(item.status, item.dateExpire)">
              {{ getStatusText(item.status, item.dateExpire) }}
            </v-chip>
          </template>
          <template v-slot:item.daysRemaining="{ item }">
            <v-chip :color="getDaysRemainingColor(item.dateExpire, item.status)">
              {{ calculateDaysRemaining(item.dateExpire, item.status) }}
            </v-chip>
          </template>
        </v-data-table>
  
        <!-- Dialog برای بسته رایگان -->
        <v-dialog v-model="showFreePackageDialog" max-width="400">
          <v-card>
            <v-card-title class="text-h6 green lighten-2">بسته رایگان فعال است</v-card-title>
            <v-card-text class="mt-2">
              شما در حال حاضر از بسته حسابداری رایگان استفاده می‌کنید. به داشبورد منتقل خواهید شد.
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" text @click="redirectToDashboard">تأیید</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-container>
    </v-app>
  </template>
  
  <script>
  import axios from 'axios'
  
  export default {
    name: 'PackageList',
    data() {
      return {
        packages: [],
        isLoading: false,
        canShowForm: false,
        showFreePackageDialog: false,
        initialLoading: true, // لودینگ اولیه
        headers: [
          { title: 'تاریخ شروع', key: 'dateSubmit', align: 'center' },
          { title: 'تاریخ انقضا', key: 'dateExpire', align: 'center' },
          { title: 'مدت (ماه)', key: 'month', align: 'center' },
          { title: 'مبلغ', key: 'price', align: 'center' },
          { title: 'روزهای باقی‌مانده', key: 'daysRemaining', align: 'center' },
          { title: 'وضعیت', key: 'status', align: 'center' },
          { title: 'توضیحات', key: 'des', align: 'center' },
        ],
      }
    },
    async mounted() {
      await this.checkFreeAccounting()
      if (this.canShowForm) {
        this.fetchPackages()
      }
    },
    methods: {
      async checkFreeAccounting() {
        try {
          this.initialLoading = true
          const response = await axios.post('/api/settings/get/can-free-accounting')
          const { value } = response.data
          if (value === 1 || value === '1') {
            this.showFreePackageDialog = true
          } else if (value === 0 || value === '0') {
            this.canShowForm = true
          }
        } catch (error) {
          console.error('خطا در بررسی بسته رایگان:', error)
          this.$vuetify.notify({
            text: 'خطا در بررسی دسترسی',
            type: 'error',
          })
        } finally {
          this.initialLoading = false
        }
      },
      redirectToDashboard() {
        this.showFreePackageDialog = false
        this.$router.push('/acc/dashboard')
      },
      async fetchPackages() {
        try {
          this.isLoading = true
          const response = await axios.post('/api/packagemanager/packages/orders/list')
          const data = response.data
          if (data.result) {
            this.packages = data.orders
          } else {
            this.$vuetify.notify({
              text: data.message || 'خطا در دریافت اطلاعات',
              type: 'error',
            })
          }
        } catch (error) {
          console.error('خطا در دریافت بسته‌ها:', error)
          this.$vuetify.notify({
            text: 'خطا در ارتباط با سرور',
            type: 'error',
          })
        } finally {
          this.isLoading = false
        }
      },
      formatDate(timestamp) {
        const date = new Date(parseInt(timestamp) * 1000)
        return new Intl.DateTimeFormat('fa-IR').format(date)
      },
      formatPrice(price) {
        return new Intl.NumberFormat('fa-IR').format(price) + ' تومان'
      },
      getStatusColor(status, dateExpire) {
        const now = Math.floor(Date.now() / 1000)
        if (status === 100 && parseInt(dateExpire) > now) return 'green'
        if (status === 100 && parseInt(dateExpire) <= now) return 'red'
        return 'grey'
      },
      getStatusText(status, dateExpire) {
        const now = Math.floor(Date.now() / 1000)
        if (status === 100 && parseInt(dateExpire) > now) return 'فعال'
        if (status === 100 && parseInt(dateExpire) <= now) return 'منقضی شده'
        return 'در انتظار پرداخت'
      },
      calculateDaysRemaining(dateExpire, status) {
        const now = Math.floor(Date.now() / 1000)
        if (status !== 100 || parseInt(dateExpire) <= now) return 0
        const diffInSeconds = parseInt(dateExpire) - now
        const days = Math.floor(diffInSeconds / (24 * 60 * 60))
        return days >= 0 ? days : 0
      },
      getDaysRemainingColor(dateExpire, status) {
        const days = this.calculateDaysRemaining(dateExpire, status)
        if (days === 0) return 'grey'
        if (days <= 10) return 'red'
        return 'green'
      },
    },
  }
  </script>
  
  <style scoped>
  .v-data-table {
    direction: rtl;
  }
  </style>