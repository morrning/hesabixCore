<template>
  <v-container>
    <v-row class="d-flex justify-center">
      <v-col md="5">
        <v-card :loading="loading ? 'blue' : undefined" :disabled="loading" :title="$t('app.name')"
          :subtitle="$t('user.login_label')">
          <v-card-text class="text-justify">
            {{ $t("login.des") }}
          </v-card-text>

          <v-form :disabled="loading" ref="form" fast-fail @submit.prevent="submit()">
            <v-card-text>
              <v-text-field class="mb-2" :label="$t('user.mobile')" :placeholder="$t('user.mobile_placeholder')"
                single-line v-model="user.mobile" type="tel" variant="outlined" prepend-inner-icon="mdi-phone"
                :rules="rules.mobile" autocomplete="tel" name="mobile"></v-text-field>

              <v-text-field class="mb-2" :label="$t('user.password')" :placeholder="$t('user.password_placeholder')"
                single-line type="password" variant="outlined" prepend-inner-icon="mdi-lock" :rules="rules.password"
                v-model="user.password" autocomplete="current-password" name="password"></v-text-field>

              <!-- بخش کپچا -->
              <v-row v-if="showCaptcha" class="mb-2" dense>
                <v-col cols="12" sm="6">
                  <v-img :src="captchaImage" max-height="50" max-width="150" class="captcha-img" contain></v-img>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field dense :label="$t('captcha.enter_code')" placeholder="کپچا" v-model.number="user.captcha"
                    variant="outlined" type="number" :rules="rules.captcha" required hide-details
                    prepend-inner-icon="mdi-refresh" @click:prepend-inner="loadCaptcha" :loading="captchaLoading"
                    autocomplete="off" name="captcha"></v-text-field>
                </v-col>
              </v-row>

              <v-btn :disabled="loading" :loading="loading" block type="submit" class="text-none mb-4"
                color="indigo-darken-3" size="x-large" variant="flat" prepend-icon="mdi-login">
                {{ $t("user.login") }}
              </v-btn>
            </v-card-text>
          </v-form>
          <div class="d-flex justify-center pb-5">
            <v-btn :loading="loading" class="text-none" color="primary" variant="tonal" flat
              @click="goto_pwa_page()">نصب وب اپلیکیشن</v-btn>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <div v-if="dialog" class="text-center">
    <v-dialog v-model="dialog" max-width="500" persistent>
      <v-card color="dangerLight" prepend-icon="mdi-close-octagon" :title="$t('dialog.error')" :text="errorMsg">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat" @click="
            dialog = false;
          user.password = '';
          user.captcha = '';
          " />
        </template>
      </v-card>
    </v-dialog>
  </div>
</template>

<script lang="ts">
import axios from "axios";
import Swal from "sweetalert2";
import { onMounted, ref } from "vue";

axios.defaults.withCredentials = true;

export default {
  name: "login",
  data() {
    const self = this;
    return {
      loading: false,
      captchaLoading: false,
      dialog: false,
      showCaptcha: false,
      errorMsg: self.$t('login.input_fail'),
      captchaImage: '',
      user: {
        mobile: "",
        password: "",
        captcha: "",
        standard: true,
      },
      rules: {
        mobile: [
          (value: any) => self.validate(value, 'mobile'),
        ],
        password: [
          (value: any) => self.validate(value, "password"),
        ],
        captcha: [
          (value: any) => !!value || self.$t("captcha.required"),
        ],
      },
    };
  },
  methods: {
    validate(input: string, type: string) {
      if (type === "mobile") {
        const normalizedInput = this.convertPersianToEnglish(input.replace(/\s/g, ''));
        if (/^09\d{9}$/.test(normalizedInput)) return true;
        return this.$t("validator.mobile_not_valid");
      } else if (type === "password") {
        if (!input) return false;
        if (input.length > 5) return true;
        return this.$t("validator.password_len_lower");
      }
    },
    convertPersianToEnglish(input: string): string {
      const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
      const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
      return input.split('').map(char => {
        const index = persianNumbers.indexOf(char);
        return index !== -1 ? englishNumbers[index] : char;
      }).join('');
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
        this.errorMsg = 'خطا در بارگذاری کپچا';
        this.dialog = true;
      } finally {
        this.captchaLoading = false;
      }
    },
    async submit() {
      const { valid } = await (this.$refs.form as any).validate();
      if (valid) {
        this.loading = true;

        const userData: { mobile: string; password: string; standard: boolean; captcha_answer?: string } = {
          mobile: this.convertPersianToEnglish(this.user.mobile.replace(/\s/g, '')),
          password: this.user.password,
          standard: this.user.standard,
        };

        if (this.showCaptcha) {
          userData.captcha_answer = this.user.captcha.toString();
        }

        try {
          const response = await axios.post("/api/user/login", userData, {
            withCredentials: true,
          });
          if (response.data.Success === true) {
            this.setTokenAndRedirect(response);
          }
        } catch (error) {
          const errorData = (error as any).response?.data || {};
          this.errorMsg = errorData.error || this.$t('login.input_fail');
          this.dialog = true;

          if (errorData.captcha_required) {
            this.showCaptcha = true;
            await this.loadCaptcha();
          } else {
            this.showCaptcha = false;
          }
        } finally {
          this.loading = false;
        }
      }
    },
    async setTokenAndRedirect(response: { data: { data: { token: string; }; }; }) {
      await new Promise<void>((resolve) => {
        axios.defaults.headers.common['X-AUTH-TOKEN'] = response.data.data.token;
        localStorage.setItem("X-AUTH-TOKEN", response.data.data.token);
        resolve();
      });
      this.$router.push("/profile/business");
    },
    goto_pwa_page() {
      this.loading = true;
      this.$router.push("/install-pwa");
    },
  },
  mounted() {
    // کپچا در ابتدا نمایش داده نمی‌شه
  },
};
</script>

<style scoped>
.captcha-img {
  display: block;
  margin: 0 auto;
}
</style>