<script>
import { RouterView } from 'vue-router'
import axios from 'axios';
import { ref } from 'vue';
import "./assets/site.css";
export default {
  data: () => {
    return {
      loading:false,
      dialog: false,
      theme: ref('light'),
      hesabix: {
        version: '',
        lastUpdateDate: '',
        lastUpdateDes: '',
      },
    }
  },
  methods: {
    update() {
      this.dialog = false;
      localStorage.setItem('hesabixVersion', this.hesabix.version);
      window.location.reload();
    },
    gethesabix() {
      this.loading = true;
      this.dialog = false;
      axios.post('/api/general/stat').then((response) => {
        this.hesabix = response.data;
        let currentVersion = window.localStorage.getItem('hesabixVersion');

        if (currentVersion == undefined) {
          window.localStorage.setItem('hesabixVersion', this.hesabix.version);
        }
        else if (currentVersion != this.hesabix.version) {
          //set version Number
          this.dialog = true;
          this.loading = false;
        }

      });
    }
  },
  mounted() {
    this.gethesabix();
  }
}
</script>

<template>
  <v-app :theme="theme">
    <v-dialog v-model="dialog" max-width="600" persistent class="elevation-4">
      <v-card class="rounded-lg">
        <!-- نوار ابزار به‌عنوان هدر -->
        <v-toolbar color="primary" dark flat class="rounded-t-lg">
          <v-toolbar-title class="d-flex align-center">
            <v-icon start>mdi-update</v-icon>
            {{ $t('dialog.update') }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
        </v-toolbar>

        <!-- محتوای کارت -->
        <v-card-subtitle class="py-2 text-grey-darken-1">
          {{ hesabix.lastUpdateDate }}
        </v-card-subtitle>
        <v-card-text class="pa-5">
          <div class="text-primary" v-html="hesabix.lastUpdateDes"></div>
        </v-card-text>

        <!-- اکشن‌ها -->
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            variant="flat"
            :text="$t('dialog.update')"
            @click="update()"
          />
        </v-card-actions>
      </v-card>
    </v-dialog>
    <RouterView />
  </v-app>
</template>

<style>
.customize-table {
  --easy-table-header-font-color: #e1e1e1;
  --easy-table-header-background-color: #055bbb;
}

/* هدف قرار دادن اسکرول‌بار در v-navigation-drawer */
.v-navigation-drawer ::-webkit-scrollbar {
  width: 4px;
  /* عرض اسکرول‌بار را کاهش می‌دهد */
}

.v-navigation-drawer ::-webkit-scrollbar-track {
  background: transparent;
  /* پس‌زمینه اسکرول‌بار شفاف */
}

.v-navigation-drawer ::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.2);
  /* رنگ دسته اسکرول‌بار */
  border-radius: 4px;
  /* گوشه‌های گرد */
}

.v-navigation-drawer ::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.4);
  /* رنگ هنگام هاور */
}
</style>
