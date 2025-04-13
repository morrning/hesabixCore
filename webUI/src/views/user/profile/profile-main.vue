<template>
  <v-system-bar color="primaryLight2">
    <v-avatar :image="getbase() + 'img/logo-blue.png'" size="20" class="me-2" />
    <span>{{ siteSlogan }}</span>
    <v-spacer />
  </v-system-bar>
  <v-navigation-drawer v-model="drawer">
    <v-card height="64"  rounded="0" prepend-icon="mdi-account">
      <template v-slot:title>
        {{ siteName }}
      </template>
      <template v-slot:prepend>
        <v-avatar :image="getbase() + 'img/favw.png'" />
      </template>
    </v-card>
    <v-list class="px-0 pt-0">
      <v-list-item v-for="(item, i) in items" v-show="item.visible" :to="item.url" :key="i" :value="item">
        <template v-slot:prepend>
          <v-icon :icon="item.icon"></v-icon>
        </template>
        <v-list-item-title v-text="item.text"></v-list-item-title>
      </v-list-item>
      <v-list-subheader v-if="ROLE_ADMIN == true">{{ $t('pages.dashboard.admin_area') }}</v-list-subheader>
      <v-list-item v-if="ROLE_ADMIN" v-for="(item, i) in adminItems" v-show="item.visible" :to="item.url" :key="i"
        :value="item" color="primary">
        <template v-slot:prepend>
          <v-icon :icon="item.icon"></v-icon>
        </template>
        <v-list-item-title v-text="item.text"></v-list-item-title>
      </v-list-item>
      <v-list-group v-if="ROLE_ADMIN == true">
        <template v-slot:activator="{ props }">
          <v-list-item v-bind="props" prepend-icon="mdi-cog" :title="$t('dialog.settings')"></v-list-item>
        </template>
        <v-list-item v-for="(item, i) in adminSettings" :prepend-icon="item.icon" :to="item.url"
          :title="item.text"></v-list-item>
      </v-list-group>
      <v-list-item color="primary">
        <v-list-item-title>
          <small class="text-primary">{{ siteName }} : {{ hesabix.version }}</small>
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
  <v-app-bar scroll-behavior="inverted elevate" scroll-threshold="0">
    <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
    <v-app-bar-title>
      {{ siteName }}
    </v-app-bar-title>
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('dialog.exit')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" color="primary" icon="mdi-logout-variant" variant="flat" @click="logout" class="me-2" />
      </template>
    </v-tooltip>
  </v-app-bar>
  <v-main class="bg-surface-light">
    <div class="position-relative py-1 px-1">
      <RouterView />
    </div>
  </v-main>
</template>

<script lang="ts">
import axios from "axios";
import { getSiteName, getApiUrl, getBasePath, getSiteSlogan } from "@/hesabixConfig"
import { applicationStore } from "@/stores/applicationStore";
import { useUserStore } from "@/stores/userStore";
import { ref, defineComponent } from "vue";
import { mapActions, mapState, mapStores } from "pinia";
import Change_lang from "/src/components/application/buttons/change_lang.vue";

export default defineComponent({
  // eslint-disable-next-line vue/multi-word-component-names,vue/no-reserved-component-names
  name: "profile-main",
  data: (vm) => {
    const self = this;
    return {
      loading: false,
      hesabix: {
        version: '',
      },
      siteName: '',
      siteUrl: '',
      siteSlogan:'',
      ROLE_ADMIN: false,
      user: {
        mobile: '1'
      },
      business_count: 0,
      drawer: true,
      items: [
        { text: 'داشبورد', url: '/profile/dashboard', icon: 'mdi-view-dashboard', visible: true },
        { text: 'کسب‌و‌کار جدید', url: '/profile/new-business', icon: 'mdi-store-plus', visible: true },
        { text: 'کسب‌و‌کارها', url: '/profile/business', icon: 'mdi-format-list-text', visible: true },
        { text: 'پشتیبانی', url: '/profile/support-list', icon: 'mdi-forum', visible: true },
        { text: 'تغییر کلمه عبور', url: '/profile/change-password', icon: 'mdi-lock-reset', visible: true },
      ],
      adminItems: [
        { text: 'تیکت‌ها', url: '/profile/manager/support-list', icon: 'mdi-forum', visible: true },
        { text: 'کسب‌و‌کارها', url: '/profile/manager/business/list', icon: 'mdi-home-city', visible: true },
        { text: 'کاربران', url: '/profile/manager/users/list', icon: 'mdi-account-multiple', visible: true },
        { text: 'کاربران آنلاین', url: '/profile/manager/users/onlinelist', icon: 'mdi-account-badge', visible: true },
        { text: 'افزونه‌ها', url: '/profile/manager/plugins/list', icon: 'mdi-toy-brick', visible: true },
        { text: 'به روز رسانی هسته', url: '/profile/manager/update-core', icon: 'mdi-undo', visible: true },
        { text: 'تغییرات', url: '/profile/manager/changes/list', icon: 'mdi-cellphone-arrow-down', visible: true },
        { text: 'تاریخچه سیستم', url: '/profile/manager/logs/list', icon: 'mdi-history', visible: true },
        { text: 'کیف پول', url: '/profile/manager/wallet/list', icon: 'mdi-wallet', visible: true },
        { text: 'اطلاعیه‌ها', url: '/profile/manager/statments/list', icon: 'mdi-bell', visible: true },

      ],
      adminSettings: [
        { text: 'پیامک', url: '/profile/manager/system/sms/settings', icon: 'mdi-message-alert', visible: true },
        { text: 'سیستم', url: '/profile/manager/system/mod', icon: 'mdi-desktop-classic', visible: true },
        { text: 'رجیستری', url: '/profile/manager/registry/mod', icon: 'mdi-table-cog', visible: true },
        { text: 'بانک اطلاعاتی', url: '/profile/manager/database/info', icon: 'mdi-database-cog', visible: true },
      ],
    }
  },
  computed: {
    applicationStore() {
      return applicationStore
    },
    ...mapStores(useUserStore),
    // gives read access to this.userData and this.synced
    ...mapState(useUserStore, ['userData', 'synced']),
  },
  components: { Change_lang },
  async created() {
    this.siteName = await getSiteName();
    this.siteSlogan = await getSiteSlogan();
    this.siteUrl = getApiUrl();
  },
  methods: {
    ...mapActions(useUserStore, ['refresh']),
    getbase(){
      return getBasePath();
    },
    logout() {
      axios.post('/api/user/logout')
      .then((response) => {
          localStorage.removeItem('X-AUTH-TOKEN');
          delete axios.defaults.headers.common['X-AUTH-TOKEN'];
          this.$router.push('/user/login')
        });
    },
  },
  mounted() {
    //set drawer for display on mobile devices
    this.drawer = !this.$vuetify.display.mobile;

    this.loading = true;
    axios.post('/api/user/has/role/' + 'ROLE_ADMIN').then((response) => {
      this.ROLE_ADMIN = response.data.Success;
      this.loading = false;
    });

    axios.post('/api/general/stat').then((response) => {
      this.hesabix = response.data;
    });
  },
  async beforeMount() {
    await axios.post('/api/user/check/login')
      .then((response) => {
        if (response.data.Success == false) {
          this.$router.push('/user/login');
        }
        else {
          axios.post('/api/user/current/info')
            .then((res) => { this.user = res.data; });
          axios.post('/api/business/list/count')
            .then((response) => {
              this.business_count = response.data.count;
            });
        }

      }).catch(() => {
        this.$router.push('/user/login');
      });
  },
});
</script>

<style scoped></style>