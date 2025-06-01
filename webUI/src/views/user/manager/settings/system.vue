<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "system",
  data: () => {
    return {
      gatepays: [
        {
          title: 'زرین‌پال',
          value: 'zarinpal',
          props: { subtitle: 'zarinpal.com' },
        },
        {
          title: 'تجارت الکترونیک پارسیان',
          value: 'pec',
          props: { subtitle: 'pec.ir' },
        },
        {
          title: 'پی‌پینگ',
          value: 'payping',
          props: { subtitle: 'payping.ir' },
        },
      ],
      systemInfo: {
        keywords: '',
        description: '',
        zarinpal: '',
        appSite: '',
        activeGateway:'zarinpal',
        parsianGatewayAPI: '',
        paypingKey: '',
      },
      loading: true,
    }
  },
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/admin/settings/system/info')
        .then((response) => {
          this.systemInfo = response.data;
          this.loading = false;
        })
    },
    submit() {
      this.loading = true;
      axios.post('/api/admin/settings/system/info/save', this.systemInfo).then((resp) => {
        this.loading = false;
        if (resp.data.result == 1) {
          Swal.fire({
            text: 'تنظیمات با موفقیت ذخیره شد.',
            icon: 'success',
            confirmButtonText: 'قبول',
          });
        }
      })

    }
  },
  beforeMount() {
    this.loadData();
  }
})
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('pages.manager.system_settings_basic')">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text class="">
        <v-row class="mb-2">
          <v-col cols="12" sm="12" md="12">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.app_site')"
              v-model="systemInfo.appSite" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => systemInfo.appSite.length > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-select v-model="systemInfo.activeGateway" hide-details="auto" prepend-inner-icon="mdi-signal" :items="gatepays" item-title="title"
              item-value="value" label="Select" single-line>
            </v-select>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.zarinpal_api')"
              v-model="systemInfo.zarinpal" type="text" prepend-inner-icon="mdi-text"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.parsian_api')"
              v-model="systemInfo.parsianGatewayAPI" type="text" prepend-inner-icon="mdi-text"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.payping_api')"
              v-model="systemInfo.paypingKey" type="text" prepend-inner-icon="mdi-text"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="12">
            <v-btn type="submit" @click="submit()" color="primary" prepend-icon="mdi-content-save" :loading="loading">
              {{ $t('dialog.save') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped></style>