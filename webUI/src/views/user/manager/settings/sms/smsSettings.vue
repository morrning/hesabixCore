<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "smsSettings",
  data: () => {
    return {
      loading: false,
      items: [
        {
          title: 'ملی پیامک',
          value: 'melipayamak',
          props: { subtitle: 'melipayamak.com' },
        },
        {
          title: 'ایده پیام',
          value: 'idepayam',
          props: { subtitle: 'idehpayam.com' },
        },
        {
          title: 'ّIPPanel فراز اس ام اس',
          value: 'ippanel',
          props: { subtitle: 'ippanel.com' },
        }
      ],
      form: {
        plan: 'melipayamak',
        walletpay: '',
        changePassword: '',
        recPassword: '',
        f2a: '',
        ticketReplay: '',
        ticketRec: '',
        chequeInput: '',
        passChequeInput: '',
        rejectChequeInput: '',
        fromNum: '',
        sharefaktor: '',
        username: '',
        password: '',
        token: '',
        plugRepservice: {
          get: '',
          getback: '',
          repaired: '',
          unrepaired: '',
          creating: '',
          created: ''
        },
        plugAccpro: {
          sharefaktor: '',
          storeroomSmsBarbari: '',
          storeroomSmsOther: '',
          chequeInput: '',
          passChequeInput: '',
          rejectChequeInput: ''
        }
      }
    }
  },
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/admin/sms/plan/info').then((response) => {
        this.form = response.data;
        this.loading = false;
      });

    },
    submit() {
      this.loading = true;
      axios.post('/api/admin/sms/plan/info/save', this.form).then((response) => {
        this.loading = false;
        if (response.data.error == 0) {
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
  <v-toolbar color="toolbar" :title="$t('pages.manager.system_settings_sms')">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-0">
    <v-card :loading="loading ? 'red' : false" :disabled="loading">
      <v-card-text class="">
        <h4 class="text-primary">اطلاعات اپراتور پیامک</h4>
        <v-row class="mb-2">
          <v-col cols="12" sm="12" md="4">
            <v-select v-model="form.plan" hide-details="auto" prepend-inner-icon="mdi-signal" :items="items" item-title="title"
              item-value="value" label="Select" single-line>
            </v-select>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_username')"
              v-model="form.username" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.username) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_password')"
              v-model="form.password" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.password) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_token')"
              v-model="form.token" type="text" prepend-inner-icon="mdi-text"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_fromNum')"
              v-model="form.fromNum" type="text" prepend-inner-icon="mdi-text"></v-text-field>
          </v-col>
        </v-row>
        <h4 class="text-primary">شناسه الگوهای پیامک</h4>
        <v-row class="mb-2">
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_f2a')" v-model="form.f2a"
              type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.f2a) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_rec_password')"
              v-model="form.recPassword" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.recPassword) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_change_password')"
              v-model="form.changePassword" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.changePassword) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_rec_ticket')"
              v-model="form.ticketRec" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.ticketRec) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_replay_ticket')"
              v-model="form.ticketReplay" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.ticketReplay) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_wallet_pay')"
              v-model="form.walletpay" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.walletpay) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_share_faktor')"
              v-model="form.sharefaktor" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.sharefaktor) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_cheque_input')"
              v-model="form.chequeInput" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.chequeInput) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>  
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_pass_cheque_input')"
              v-model="form.passChequeInput" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.passChequeInput) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_reject_cheque_input')"
              v-model="form.rejectChequeInput" type="text" prepend-inner-icon="mdi-text"
              :rules="[() => Number(form.rejectChequeInput) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
        </v-row>
        <h4 class="text-primary">افزونه حسابداری پیشرفته</h4>
        <v-row class="mb-2">
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_share_faktor_accpro')"
              v-model="form.plugAccpro.sharefaktor" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugAccpro.sharefaktor) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_storeroom_other')"
              v-model="form.plugAccpro.storeroomSmsOther" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugAccpro.storeroomSmsOther) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_storeroom_barbari')"
              v-model="form.plugAccpro.storeroomSmsBarbari" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugAccpro.storeroomSmsBarbari) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_plug_accpro_cheque_input')"
              v-model="form.plugAccpro.chequeInput" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugAccpro.chequeInput) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_pass_cheque_input')"
              v-model="form.plugAccpro.passChequeInput" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugAccpro.passChequeInput) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_reject_cheque_input')"
              v-model="form.plugAccpro.rejectChequeInput" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugAccpro.rejectChequeInput) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
        </v-row>
        <h4 class="text-primary">افزونه تعمیرکاران</h4>
        <v-row class="mb-2">
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_repservice_get')"
              v-model="form.plugRepservice.get" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugRepservice.get) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_repservice_repired')"
              v-model="form.plugRepservice.repaired" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugRepservice.repaired) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_repservice_unrepaired')"
              v-model="form.plugRepservice.unrepaired" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugRepservice.unrepaired) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_repservice_getback')"
              v-model="form.plugRepservice.getback" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugRepservice.getback) > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="4">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.sms_settings_repservice_creating')"
              v-model="form.plugRepservice.creating" type="text" prepend-inner-icon="mdi-card-text"
              :rules="[() => Number(form.plugRepservice.creating) > 0 || $t('validator.required')]"></v-text-field>
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