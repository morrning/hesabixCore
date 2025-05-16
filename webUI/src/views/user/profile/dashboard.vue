<template>
  <v-container fluid class="pa-0 ma-0">
    <v-tabs v-model="activeTab" color="primary" grow>
      <v-tab value="personal" :text="$t('tabs.personal_info')"></v-tab>
      <v-tab value="marketing" :text="$t('tabs.marketing_info')"></v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <!-- تب اطلاعات فعلی -->
      <v-window-item value="personal">
        <v-card :loading="loading ? 'red' : null" :disabled="loading" class="ma-4">
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="12" md="2" class="text-center">
                <v-avatar image="/img/avatar.png" size="155"></v-avatar>
              </v-col>
              <v-col cols="12" sm="12" md="10">
                <v-row>
                  <v-col cols="12">
                    <v-text-field v-model="user_email" :label="$t('user.email')" prepend-inner-icon="mdi-email"
                      :disabled="true" color="primary" variant="solo"></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field v-model="user_mobile" :label="$t('user.mobile')" prepend-inner-icon="mdi-cellphone"
                      :disabled="true" color="primary" variant="solo"></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field v-model="user_fullname" :label="$t('user.name')" prepend-inner-icon="mdi-account"
                      :disabled="loading" color="primary" variant="solo"
                      :rules="[() => user_fullname.length > 0 || $t('validator.required')]"></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field v-model="referralLink" :label="$t('user.referral_link')" prepend-inner-icon="mdi-link"
                      append-inner-icon="mdi-content-copy" :readonly="true" color="primary" variant="solo"
                      @click:append-inner="copyReferralLink"></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-btn color="primary" :loading="loading" prepend-icon="mdi-content-save" @click="updateProfile">
                      {{ $t('dialog.save') }}
                    </v-btn>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-window-item>

      <!-- تب اطلاعات بازاریابی -->
      <v-window-item value="marketing">
        <v-card class="ma-4">
          <v-card-text>
            <v-row>
              <!-- تعداد اعضا -->
              <v-col cols="12" sm="6" md="3">
                <v-card color="blue-lighten-5" flat>
                  <v-card-title>{{ $t('marketing.joined_today') }}</v-card-title>
                  <v-card-text class="text-h5">{{ stats.joinedToday }}</v-card-text>
                </v-card>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-card color="green-lighten-5" flat>
                  <v-card-title>{{ $t('marketing.joined_this_month') }}</v-card-title>
                  <v-card-text class="text-h5">{{ stats.joinedThisMonth }}</v-card-text>
                </v-card>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-card color="purple-lighten-5" flat>
                  <v-card-title>{{ $t('marketing.joined_this_year') }}</v-card-title>
                  <v-card-text class="text-h5">{{ stats.joinedThisYear }}</v-card-text>
                </v-card>
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-card color="orange-lighten-5" flat>
                  <v-card-title>{{ $t('marketing.total_invited') }}</v-card-title>
                  <v-card-text class="text-h5">{{ stats.totalInvited }}</v-card-text>
                </v-card>
              </v-col>
            </v-row>

            <!-- ۱۰ کاربر اخیر -->
            <v-row class="mt-4">
              <v-col cols="12">
                <v-card>
                  <v-card-title>{{ $t('marketing.recent_users') }}</v-card-title>
                  <v-data-table :header-props="{ class: 'custom-header' }" :headers="computedHeaders"
                    :items="recentUsers" :loading="loading" hide-default-footer class="elevation-1">
                    <!-- اسلات سفارشی برای ستون وضعیت -->
                    <template v-slot:item.active="{ item }">
                      <v-icon :color="item.active ? 'green' : 'red'" small>
                        mdi-circle
                      </v-icon>
                    </template>
                  </v-data-table>
                </v-card>
              </v-col>
            </v-row>

            <!-- نمودار ۶ ماه گذشته -->
            <v-row class="mt-4">
              <v-col cols="12">
                <v-card>
                  <v-card-title>{{ $t('marketing.six_months_chart') }}</v-card-title>
                  <apexchart type="bar" height="350" :options="chartOptions" :series="chartSeries"></apexchart>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-window-item>
    </v-window>

    <!-- دیالوگ‌ها -->
    <v-dialog v-model="dialog" max-width="500" persistent>
      <v-card color="successLight" prepend-icon="mdi-check-circle" :title="$t('dialog.result')"
        :text="$t('dialog.save_ok')">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat" @click="dialog = false" />
        </template>
      </v-card>
    </v-dialog>

    <v-dialog v-model="errorDialog" max-width="500" persistent>
      <v-card color="dangerLight" prepend-icon="mdi-close-octagon" :title="$t('dialog.error')" :text="errorMessage">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat" @click="errorDialog = false" />
        </template>
      </v-card>
    </v-dialog>

    <v-dialog v-model="copySuccessDialog" max-width="500">
      <v-card color="successLight" prepend-icon="mdi-check-circle" :title="$t('dialog.success')"
        :text="$t('user.link_copied')">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat" @click="copySuccessDialog = false" />
        </template>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from "axios";
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { ref } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { getBasePath } from "@/hesabixConfig.js";
export default {
  name: "Dashboard",
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    return {
      activeTab: "personal",
      loading: ref(true),
      dialog: ref(false),
      errorDialog: ref(false),
      copySuccessDialog: ref(false),
      user_fullname: "",
      user_email: "",
      user_mobile: "",
      referralCode: "",
      referralLink: "",
      errorMessage: "",
      stats: {
        joinedToday: 0,
        joinedThisMonth: 0,
        joinedThisYear: 0,
        totalInvited: 0,
      },
      recentUsers: [],
      recentUsersHeadersFull: [
        { title: this.$t('marketing.user_email'), key: "email", align: "start" },
        { title: this.$t('marketing.user_name'), key: "fullName", align: "start" },
        { title: this.$t('marketing.join_date'), key: "dateRegister", align: "start" },
        { title: this.$t('marketing.status'), key: "active", align: "center" },
      ],
      recentUsersHeadersMobile: [
        { title: this.$t('marketing.user_name'), key: "fullName", align: "start" },
        { title: this.$t('marketing.join_date'), key: "dateRegister", align: "start" },
      ],
      chartOptions: {
        chart: {
          type: "bar",
          height: 350,
          fontFamily: "Vazirmatn FD, sans-serif",
        },
        xaxis: {
          categories: [],
          labels: {
            style: {
              fontFamily: "Vazirmatn FD, sans-serif",
            },
          },
        },
        yaxis: {
          labels: {
            style: {
              fontFamily: "Vazirmatn FD, sans-serif",
            },
          },
        },
        plotOptions: {
          bar: {
            horizontal: false,
          },
        },
        dataLabels: {
          enabled: false,
        },
        title: {
          text: this.$t('marketing.six_months_chart'),
          align: "center",
          style: {
            fontFamily: "Vazirmatn FD, sans-serif",
          },
        },
      },
      chartSeries: [{ name: this.$t('marketing.users'), data: [] }],
    };
  },
  computed: {
    computedHeaders() {
      // اگر اندازه صفحه موبایل باشد (smAndDown)، فقط نام و تاریخ نمایش داده شود
      return this.$vuetify.display.smAndDown
        ? this.recentUsersHeadersMobile
        : this.recentUsersHeadersFull;
    },
  },
  methods: {
    async updateProfile() {
      const result = await this.v$.$validate();
      if (!result) {
        this.errorMessage = this.$t("validator.form_invalid");
        this.errorDialog = true;
      } else {
        this.loading = true;
        try {
          await axios.post("/api/user/update/info", {
            fullname: this.user_fullname,
            mobile: this.user_mobile,
          });
          this.loading = false;
          this.dialog = true;
        } catch (error) {
          this.loading = false;
          this.errorMessage =
            error.response?.data?.message || this.$t("dialog.error_unknown");
          this.errorDialog = true;
        }
      }
    },
    copyReferralLink() {
      if (!this.referralLink) {
        this.errorMessage = this.$t("user.referral_not_available");
        this.errorDialog = true;
        return;
      }

      // روش اول: استفاده از Clipboard API
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(this.referralLink)
          .then(() => {
            this.copySuccessDialog = true;
          })
          .catch(() => {
            // اگر روش اول شکست خورد، از روش دوم استفاده می‌کنیم
            this.fallbackCopyToClipboard(this.referralLink);
          });
      } else {
        // اگر Clipboard API در دسترس نیست، از روش دوم استفاده می‌کنیم
        this.fallbackCopyToClipboard(this.referralLink);
      }
    },
    fallbackCopyToClipboard(text) {
      try {
        // ایجاد یک المان موقت
        const textArea = document.createElement("textarea");
        textArea.value = text;

        // تنظیمات استایل برای مخفی کردن المان
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.opacity = '0';

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        // تلاش برای کپی کردن
        const successful = document.execCommand('copy');
        document.body.removeChild(textArea);

        if (successful) {
          this.copySuccessDialog = true;
        } else {
          throw new Error('Copy failed');
        }
      } catch (err) {
        this.errorMessage = this.$t("user.copy_failed");
        this.errorDialog = true;
      }
    },
    async fetchMarketingData() {
      try {
        const res = await axios.get("/api/membership/stats");
        if (!res.data || !res.data.data) {
          throw new Error("Invalid server response structure");
        }

        const responseData = res.data.data;

        // تنظیم آمار
        this.stats = responseData.stats || {
          joinedToday: 0,
          joinedThisMonth: 0,
          joinedThisYear: 0,
          totalInvited: 0,
        };

        // تابع برای مخفی کردن ۲ یا ۳ کاراکتر تصادفی از بخش قبل از @
        const maskEmail = (email) => {
          const [username, domain] = email.split('@');
          if (username.length <= 3) return `${username}@${domain}`;
          const maskCount = Math.min(3, username.length - 1);
          const indices = [...Array(username.length).keys()].sort(() => Math.random() - 0.5).slice(0, maskCount);
          let maskedUsername = username.split('');
          indices.forEach(i => (maskedUsername[i] = '*'));
          return `${maskedUsername.join('')}@${domain}`;
        };

        // تنظیم کاربران اخیر
        this.recentUsers = Array.isArray(responseData.recentUsers)
          ? responseData.recentUsers.map(user => ({
            ...user,
            email: maskEmail(user.email),
            dateRegister: user.dateRegister
              ? new Date(user.dateRegister * 1000).toLocaleString("fa-IR")
              : "نامشخص",
            active: user.active, // اضافه کردن فیلد active
          }))
          : [];

        // تنظیم داده‌های نمودار
        this.chartOptions.xaxis.categories = responseData.chart?.months || [];
        this.chartSeries[0].data = responseData.chart?.users || [];
      } catch (error) {
        this.errorMessage = error.message || this.$t("dialog.error_unknown");
        this.errorDialog = true;
      }
    },
  },
  async mounted() {
    try {
      const res = await axios.post("/api/user/current/info");
      this.user_email = res.data.email;
      this.user_fullname = res.data.fullname;
      this.user_mobile = res.data.mobile;
      this.referralCode = res.data.invateCode || "";
      this.referralLink = this.referralCode
        ? `${window.location.origin + getBasePath()}ms/${this.referralCode}`
        : "";
      await this.fetchMarketingData();
    } catch (error) {
      this.errorMessage =
        error.response?.data?.message || this.$t("dialog.error_unknown");
      this.errorDialog = true;
    } finally {
      this.loading = false;
    }
  },
  validations() {
    return {
      user_fullname: { required },
    };
  },
  setup() {
    return {
      v$: useVuelidate(),
    };
  },
};
</script>

<style scoped>
.v-container {
  max-width: 1200px;
}
</style>