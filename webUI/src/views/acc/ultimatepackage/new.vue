<template>
    <v-app>
      <v-toolbar color="toolbar">
        <v-btn icon @click="$router.go(-1)">
          <v-icon>mdi-arrow-right</v-icon>
        </v-btn>
        <v-toolbar-title>سفارش بسته حسابداری نامحدود</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-toolbar>
      <v-container fluid>
        <!-- لودینگ اولیه -->
        <v-row v-if="initialLoading" justify="center" class="mt-5">
          <v-progress-circular indeterminate color="primary" :size="50"></v-progress-circular>
        </v-row>
  
        <div v-if="canShowForm && !initialLoading">
          <!-- بخش توضیحات خرید و مزایا -->
          <v-row justify="center">
            <v-col cols="12" md="10">
              <v-card flat>
                <v-card-text>
                  <p class="text-body-1">
                    با خرید بسته حسابداری نامحدود، می‌توانید بدون محدودیت اسناد حسابداری خود را ثبت کنید و از امکانات زیر بهره‌مند شوید:
                  </p>
                  <p class="text-body-2">
                    این بسته‌ها برای کسب‌وکارهایی که نیاز به مدیریت حجم بالای تراکنش دارند، ایده‌آل است. همین حالا بسته مناسب خود را انتخاب کنید و مدیریت مالی خود را ساده‌تر کنید!
                  </p>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
          <!-- بخش بسته‌ها -->
          <v-row justify="center">
            <v-col cols="12">
              <v-progress-circular v-if="loading" indeterminate color="primary" class="mb-4"></v-progress-circular>
              <v-row v-else>
                <v-col v-for="pkg in packages" :key="pkg.month" cols="12" sm="6" md="3">
                  <v-card :class="{ 'selected-package': selectedPackage === pkg.month }" hover @click="selectPackage(pkg.month)">
                    <v-card-title>{{ pkg.month }} ماهه</v-card-title>
                    <v-card-text>
                      <p class="text-h6">{{ formatPrice(pkg.price) }} ریال</p>
                      <p>ثبت نامحدود اسناد حسابداری به مدت {{ pkg.month }} ماه</p>
                    </v-card-text>
                    <v-card-actions>
                      <v-btn
                        :color="selectedPackage === pkg.month ? 'success' : 'grey'"
                        variant="elevated"
                        :disabled="selectedPackage !== pkg.month"
                        @click.stop="submitOrder"
                      >
                        <v-icon left>mdi-check-circle</v-icon>
                        انتخاب و پرداخت
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </v-col>
              </v-row>
            </v-col>
          </v-row>
        </div>
  
        <!-- Dialog برای خطاها -->
        <v-dialog v-model="showErrorDialog" max-width="400">
          <v-card>
            <v-card-title class="text-h6 red lighten-2">خطا</v-card-title>
            <v-card-text class="mt-2">{{ error }}</v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" text @click="showErrorDialog = false">بستن</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
  
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
    data() {
      return {
        packages: [],
        selectedPackage: null,
        loading: false, // لودینگ برای بسته‌ها
        initialLoading: true, // لودینگ اولیه
        error: null,
        showErrorDialog: false,
        canShowForm: false,
        showFreePackageDialog: false,
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
          } else {
            console.warn('مقدار غیرمنتظره از سرور:', value)
            this.error = 'پاسخ نامعتبر از سرور'
            this.showErrorDialog = true
          }
        } catch (error) {
          console.error('خطا در بررسی بسته رایگان:', error)
          this.error = 'خطا در بررسی دسترسی: ' + (error.response?.data?.message || error.message)
          this.showErrorDialog = true
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
          this.loading = true
          const response = await axios.post('/api/packagemanager/packages/list')
          this.packages = response.data.packages
        } catch (error) {
          this.error = 'خطا در بارگذاری پکیج‌ها: ' + (error.response?.data?.message || error.message)
          this.showErrorDialog = true
        } finally {
          this.loading = false
        }
      },
      selectPackage(month) {
        this.selectedPackage = month
      },
      async submitOrder() {
        if (!this.selectedPackage) {
          this.error = 'لطفاً یک پکیج انتخاب کنید.'
          this.showErrorDialog = true
          return
        }
  
        try {
          this.loading = true
          this.error = null
          const response = await axios.post('/api/packagemanager/package/order/new', {
            month: this.selectedPackage,
          })
  
          const { result, paymentUrl, message } = response.data
  
          if (result) {
            window.location.href = paymentUrl
          } else {
            this.error = message || 'خطا در ثبت سفارش.'
            this.showErrorDialog = true
          }
        } catch (error) {
          this.error = 'خطا در ارسال درخواست پرداخت: ' + (error.response?.data?.message || error.message)
          this.showErrorDialog = true
        } finally {
          this.loading = false
        }
      },
      formatPrice(price) {
        return Number(price).toLocaleString('fa-IR')
      },
    },
  }
  </script>
  
  <style scoped>
  .selected-package {
    border: 2px solid #4caf50 !important;
    background-color: #e8f5e9;
  }
  </style>