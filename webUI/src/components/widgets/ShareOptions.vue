<script lang="ts">
import { defineComponent, ref } from 'vue';
import axios from 'axios';

export default defineComponent({
  name: 'ShareOptions',
  props: {
    shortlinkUrl: { type: String, required: true },
    mobile: { type: String, required: true },
    invoiceId: { type: Number, required: true },
  },
  data: () => ({
    dialog: false,
    internalMobile: '',
    copyLabel: ref('کپی'),
    messageHint: ref('ارسال'),
    hintColor: ref('grey'),
    loading: ref(false),
  }),
  created() {
    this.internalMobile = this.mobile;
  },
  methods: {
    copyToClipboard() {
      navigator.clipboard.writeText(this.shortlinkUrl);
      this.copyLabel = 'کپی شد!';
    },
    sendSMS() {
      this.loading = true;
      const regex = new RegExp('^(\\+98|0)?9\\d{9}$');
      if (!regex.test(this.internalMobile)) {
        this.messageHint = 'شماره موبایل نامعتبر است.';
        this.hintColor = 'red';
        this.loading = false;
        return;
      }
      this.messageHint = 'در حال ارسال...';
      this.hintColor = 'grey';
      axios
        .post(`/api/sms/send/sell-invoice/${this.invoiceId}/${this.internalMobile}`)
        .then((response) => {
          if (response.data.result == 2) {
            this.messageHint = 'اعتبار سرویس پیامک کافی نیست.';
            this.hintColor = 'red';
          } else if (response.data.result == 1) {
            this.messageHint = 'پیامک ارسال شد!';
            this.hintColor = 'green';
          }
          this.loading = false;
        })
        .catch((error) => {
          console.error('خطا در ارسال پیامک:', error);
          this.messageHint = 'خطا در ارسال پیامک رخ داد.';
          this.hintColor = 'red';
          this.loading = false;
        });
    },
  },
});
</script>

<template>
  <v-btn icon color="success" class="ml-2" @click="dialog = true">
    <v-icon>mdi-share-variant</v-icon>
    <v-tooltip activator="parent" location="bottom">اشتراک‌گذاری</v-tooltip>
  </v-btn>

  <v-dialog v-model="dialog" max-width="500" persistent>
    <v-card>
      <v-toolbar color="grey-lighten-4" flat>
        <v-toolbar-title>
          <v-icon color="success" left>mdi-share-variant</v-icon>
          اشتراک‌گذاری
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text>
        <v-text-field
          v-model="shortlinkUrl"
          readonly
          append-inner-icon="mdi-content-copy"
          @click:append-inner="copyToClipboard"
          label="پیوند فاکتور"
          variant="outlined"
          :hint="copyLabel"
        ></v-text-field>
        <v-text-field
          class="mt-4"
          v-model="internalMobile"
          label="ارسال پیامک"
          variant="outlined"
          :hint="messageHint"
          :color="hintColor"
          persistent-hint
        >
          <template v-slot:append-inner>
            <v-btn
              color="primary"
              :loading="loading"
              @click="sendSMS"
            >
              ارسال
            </v-btn>
          </template>
        </v-text-field>
        <div class="mt-3">
          <v-icon left>mdi-share-variant</v-icon>
          اشتراک‌گذاری در شبکه‌های اجتماعی
          <div class="mt-2">
            <!-- تلگرام -->
            <a
              :href="'https://t.me/share/url?url=' + encodeURIComponent(shortlinkUrl)"
              target="_blank"
            >
              <img src="/img/icons/telegram.png" class="m-3" style="max-width: 30px;" />
            </a>
            <!-- ایتا -->
            <a
              :href="'https://eitaa.com/?text=' + encodeURIComponent(shortlinkUrl)"
              target="_blank"
            >
              <img src="/img/icons/eitaa.jpeg" class="m-3" style="max-width: 30px;" />
            </a>
            <!-- بله -->
            <a
              :href="'https://ble.ir/share/url?url=' + encodeURIComponent(shortlinkUrl)"
              target="_blank"
            >
              <img src="/img/icons/bale-logo.png" class="m-3" style="max-width: 30px;" />
            </a>
            <!-- روبیکا -->
            <a
              :href="'https://rubika.ir/app/share?text=' + encodeURIComponent(shortlinkUrl)"
              target="_blank"
            >
              <img src="/img/icons/robika.png" class="m-3" style="max-width: 30px;" />
            </a>
            <!-- واتساپ -->
            <a
              :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent(shortlinkUrl)"
              target="_blank"
            >
              <v-icon class="m-3" size="30" color="green">mdi-whatsapp</v-icon>
            </a>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>
/* استایل‌های اضافی در صورت نیاز */
</style>