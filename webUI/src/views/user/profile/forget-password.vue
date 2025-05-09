<template>
  <v-container>
    <v-row class="d-flex justify-center">
      <v-col md="5">
        <v-card :loading="loading ? 'blue' : undefined" :title="siteName" :subtitle="$t('user.forget_password')">
          <v-card-text>
            <v-form ref="form" :disabled="loading" fast-fail @submit.prevent="submit()">
              <v-text-field v-model="mobile" class="mb-2" :label="$t('user.mobile')"
                :placeholder="$t('user.mobile_placeholder')" single-line type="tel" variant="outlined"
                prepend-inner-icon="mdi-phone" :rules="rules.mobile"></v-text-field>

              <!-- بخش کپچا (همیشه نمایش داده می‌شه) -->
              <v-row class="mb-2" dense>
                <v-col cols="12" sm="6">
                  <v-img :src="captchaImage" max-height="50" max-width="150" class="captcha-img" contain></v-img>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field dense :label="$t('captcha.enter_code')" placeholder="کپچا" v-model.number="captcha"
                    variant="outlined" type="number" :rules="rules.captcha" required hide-details
                    prepend-inner-icon="mdi-refresh" @click:prepend-inner="loadCaptcha" :loading="captchaLoading">
                  </v-text-field>
                </v-col>
              </v-row>

              <v-btn :loading="loading" block class="text-none mb-4" color="indigo-darken-3" size="x-large"
                variant="flat" prepend-icon="mdi-send-circle" type="submit">
                {{ $t('user.send_code_forget_password') }}
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <div v-if="dialog" class="text-center">
    <v-dialog v-model="dialog" max-width="500" persistent>
      <v-card color="dangerLight" prepend-icon="mdi-close-octagon" :title="$t('dialog.error')"
        :text="response.message">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat"
            @click="dialog = false; loading = false; mobile = ''; captcha = ''" />
        </template>
      </v-card>
    </v-dialog>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import axios from "axios";
import { ref } from 'vue';
import { getSiteName } from '@/hesabixConfig';

axios.defaults.withCredentials = true;

export default defineComponent({
  name: "forget_password",
  data() {
    const self = this;
    return {
      loading: ref(false),
      captchaLoading: ref(false),
      dialog: ref(false),
      mobile: '',
      captcha: '',
      siteName:'',
      captchaImage: '',
      rules: {
        mobile: [
          (value: any) => self.validate(value, 'mobile'),
        ],
        captcha: [
          (value: any) => !!value || self.$t("captcha.required"),
        ],
      },
      response: {
        code: '',
        message: '',
        Success: false,
        data: {
          id: ''
        }
      }
    };
  },
  async created(){
    this.siteName = await getSiteName();
  },
  mounted() {
    this.loadCaptcha(); // لود اولیه کپچا
  },
  methods: {
    validate(input: string, type: string) {
      if (type === 'mobile') {
        const regex = new RegExp("^(\\+98|0)?9\\d{9}$");
        if (regex.test(input)) return true;
        return this.$t('validator.mobile_not_valid');
      }
    },
    async loadCaptcha() {
      this.captchaLoading = true;
      try {
        const response = await axios.get('/api/captcha/image', {
          responseType: 'blob',
          withCredentials: true,
        });
        const imageUrl = URL.createObjectURL(response.data);
        this.captchaImage = imageUrl;
      } catch (error) {
        this.response.message = 'خطا در بارگذاری کپچا';
        this.dialog = true;
      } finally {
        this.captchaLoading = false;
      }
    },
    async submit() {
      const { valid } = await (this.$refs.form as any).validate();
      if (valid) {
        this.loading = true;
        const requestData = {
          mobile: this.mobile,
          captcha_answer: this.captcha.toString(), // همیشه کپچا ارسال می‌شه
        };

        axios.post('/api/user/forget/password/send-code', requestData, {
          withCredentials: true,
        }).then((response) => {
          if (response.data.Success == true) {
            this.$router.push('/user/forget-password-submit-code/' + response.data.data.id);
          } else {
            this.response = response.data;
            this.dialog = true;
            this.loadCaptcha(); // نوسازی کپچا در صورت خطا
          }
        }).catch((error) => {
          this.response.message = error.response?.data?.error || 'خطا در ارسال کد';
          this.dialog = true;
          this.loadCaptcha(); // نوسازی کپچا در صورت خطا
        }).finally(() => {
          this.loading = false;
        });
      }
    }
  }
});
</script>

<style scoped>
.captcha-img {
  display: block;
  margin: 0 auto;
}
</style>