<template>
  <v-toolbar color="toolbar" :title="$t('title.user.businesses')">
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('title.user.business_create')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-store-plus-outline" color="primary" to="/profile/new-business"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text>
        <!-- نمایش پیام در صورت خالی بودن لیست -->
        <v-row v-if="!loading && contents.length === 0" justify="center" class="text-center pa-5">
          <v-col cols="12">
            <v-icon size="60" color="grey" class="mb-4">mdi-store-off-outline</v-icon>
            <h3 class="text-h6 mb-3">
              هنوز هیچ کسب‌وکاری ندارید!
            </h3>
            <p class="text-body-1 text-grey-darken-1">
              برای شروع کار  ، ابتدا یک کسب‌وکار ایجاد کنید یا از دیگران بخواهید شما را به کسب‌وکارشان دعوت کنند.
            </p>
            <v-btn
              color="primary"
              class="mt-4"
              to="/profile/new-business"
              prepend-icon="mdi-store-plus-outline"
            >
              ایجاد کسب‌وکار جدید
            </v-btn>
          </v-col>
        </v-row>

        <!-- نمایش لودینگ یا لیست کسب‌وکارها -->
        <v-row v-else>
          <v-col v-if="loading" cols="12" sm="12" md="12" class="pa-0 ma-0">
            <v-skeleton-loader class="my-5 mx-5" :elevation="1" type="list-item-avatar"></v-skeleton-loader>
          </v-col>
          <v-col v-for="item in contents" cols="12" sm="12" md="6" class="px-1">
            <v-card class="mx-auto mb-3" border flat>
              <v-list-item class="px-6" height="88">
                <template v-slot:prepend>
                  <v-avatar color="surface-light" size="55" :image="apiurl + '/front/avatar/file/get/' + item.id"
                    :alt="item.name" />
                </template>
                <template v-slot:title>
                  {{ item.name }}
                </template>
                <template v-slot:subtitle>
                  {{ item.owner }}
                </template>
                <template v-slot:append>
                  <v-btn @click="runBid(item.id, item.name)" class="text-none" color="primary"
                    :text="$t('pages.dashboard.login')" append-icon="mdi-arrow-left" slim></v-btn>
                </template>
              </v-list-item>
              <v-card-text class="text-medium-emphasis pa-1 px-3">
                <v-row>
                  <v-col cols="12" sm="12" md="12" class="mb-0">
                    <v-select density="comfortable" prepend-inner-icon="mdi-cash" v-model="item.selectedMoney"
                      :label="$t('pages.dashboard.money')" variant="solo-filled" :items="item.moneys"
                      item-title="label"></v-select>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- دیالوگ زیباتر شده -->
    <v-dialog v-model="showProgress" persistent max-width="700px">
      <v-card class="pa-4" elevation="8" rounded="lg">
        <v-card-title class="text-h6 font-weight-bold text-center primary--text">
          <v-icon left>mdi-loading</v-icon> در حال آماده‌سازی...
        </v-card-title>
        <v-card-text class="pt-4">
          <v-progress-linear v-model="progress" color="primary" height="12" rounded striped
            class="progress-bar"></v-progress-linear>
          <div class="mt-6 text-center text-subtitle-1" v-html="currentMessage"></div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from "axios";
import { getApiUrl } from "@/hesabixConfig";
import { ref } from "vue";
import Loading from "vue-loading-overlay";

export default {
  name: "list",
  components: { Loading },
  data: () => {
    return {
      loading: ref(true),
      contents: [],
      apiurl: '',
      selectedMoney: {
        name: '',
        label: ''
      },
      showProgress: false,
      progress: 0,
      currentMessage: '',
      sponsorMessage: '',
    }
  },
  methods: {
    async loadData() {
      this.apiurl = getApiUrl();

      // دریافت همزمان اطلاعات کسب‌وکارها و پیام اسپانسر
      try {
        const [businessResponse, sponsorResponse] = await Promise.all([
          axios.post('/api/business/list'),
          axios.get('/api/general/sponsors')
        ]);

        this.contents = businessResponse.data;
        this.contents.forEach((bid) => {
          bid.selectedMoney = bid.arzmain;
        });

        // ذخیره پیام اسپانسر
        if (sponsorResponse.data && sponsorResponse.data.result) {
          this.sponsorMessage = sponsorResponse.data.result;
        }

      } catch (error) {
        console.error('خطا در دریافت اطلاعات:', error);
        this.sponsorMessage = '';
      } finally {
        this.loading = false;
      }
    },
    randomDelay(min = 500, max = 1500) {
      return new Promise(resolve => {
        const delay = Math.floor(Math.random() * (max - min + 1)) + min;
        setTimeout(resolve, delay);
      });
    },
    async showLoadingProgress(businessName) {
      this.showProgress = true;
      this.progress = 0;

      const steps = [
        { message: "در حال دریافت اطلاعات کسب و کار", progress: 20 },
        { message: "در حال بارگذاری داده‌های پایه", progress: 40 },
        { message: "تطبیق و ارزیابی داده‌ها", progress: 60 },
        { message: "بررسی مجوزها و دسترسی‌ها", progress: 80 },
        { message: `در حال انتقال به داشبورد کسب‌وکار "${businessName}"`, progress: 95 },
        {
          message: this.sponsorMessage || `در حال انتقال به داشبورد کسب‌وکار "${businessName}"`,
          progress: 100,
          delay: 5000
        }
      ];

      for (let i = 0; i < steps.length; i++) {
        this.currentMessage = steps[i].message;
        this.progress = steps[i].progress;
        const delay = steps[i].delay || await this.randomDelay();
        await new Promise(resolve => setTimeout(resolve, delay));
      }

      this.showProgress = false;
    },
    async runBid(id, businessName) {
      await this.showLoadingProgress(businessName);

      localStorage.setItem('activeBid', id);
      this.contents.forEach((item) => {
        if (item.id == id) {
          localStorage.setItem('activeMoney', item.selectedMoney.name);
          localStorage.setItem('activeMoneySymbol', item.selectedMoney.symbol);
          localStorage.setItem('activeMoneyShortName', item.selectedMoney.shortName);
          localStorage.setItem('activeMoneyLabel', item.selectedMoney.label);
        }
      });

      await axios.post('/api/year/list', {}, {
        headers: {
          activeBid: id
        }
      }).then((response) => {
        response.data.forEach((item) => {
          if (item.head == '1') {
            localStorage.setItem('activeYear', item.id);
          }
        });

        //set axios headers
        axios.defaults.headers.common['activeBid'] = localStorage.getItem('activeBid');
        axios.defaults.headers.common['activeYear'] = localStorage.getItem('activeYear');
        axios.defaults.headers.common['activeMoney'] = localStorage.getItem('activeMoney');
        this.jumpToBusiness();
      });
    },
    jumpToBusiness(){
      this.$router.push('/acc/dashboard');
    }
  },
  beforeMount() {
    this.loadData();
  }
}
</script>

<style scoped>
/* استایل برای دیالوگ و پراگرس بار */
.progress-bar {
  transition: width 0.3s ease-in-out;
}

.text-center {
  font-family: 'Roboto', sans-serif;
}

/* انیمیشن برای آیکون لودینگ */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.mdi-loading {
  animation: spin 1s linear infinite;
}
</style>