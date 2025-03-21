<script lang="ts">
import { defineComponent, ref } from 'vue'
import Swal from 'sweetalert2';
import axios from "axios";
export default defineComponent({
  name: "active_account",
  data() {
    const self = this;
    return {
      dialog: false,
      dialogSuccess: false,
      loading: false,
      disableSend: true,
      code: '',
      user: {
        mobile: '',
        email: '',
      },
      response: {
        code: '',
        message: '',
        Success: false,
        data: {
          id: ''
        }
      }
    }
  },
  watch: {
    code(cval, bval) {
      if (Object.keys(cval).length == 6) {
        this.disableSend = ref(false);
      }
      else {
        this.disableSend = ref(true);
      }
    }
  },
  methods: {
    onResendCodeClick() {
      axios.post('/api/user/register/resend-active-code', { 'mobile': this.$route.params.id }).then((response: any) => {
        if (response.data.Success == true) {
          Swal.fire({
            text: this.$t('user.resendCode'),
            confirmButtonText: this.$t('dialog.ok'),
            icon: 'success'
          });
        }
        else{
          Swal.fire({
            text: response.data.message,
            confirmButtonText: this.$t('dialog.ok'),
            icon: 'error'
          });
        }

      });

    },
    submit() {
      this.loading = true;
      axios.post('/api/user/active/account', {
        mobile: this.$route.params.id,
        code: this.code
      }).then((response: any) => {
        if (response.data.Success == false) {
          this.response = response.data;
          this.dialog = true;
        }
        else {
          this.response = response.data;
          this.dialogSuccess = true;
        }
        this.loading = false;
      });
    }
  }
})
</script>


<template>
  <v-container>
    <v-row class="d-flex justify-center">
      <v-col md="5">
        <v-card :loading="loading ? 'blue' : null" :disabled="loading" :title="$t('app.name')"
          :subtitle="$t('user.active_account')">
          <v-card-text>
            کد ارسالی از طریق پیامک و یا پست الکترونیکی دریافتی خود را در کادر زیر وارد نمایید.
          </v-card-text>
          <v-form ref="form" :disabled="loading" fast-fail @submit.prevent="submit()">
            <v-card-text>
              <v-otp-input focus-all v-model="code" style="direction: ltr"></v-otp-input>
              <v-row>
                <v-col class="my-2 mx-4">
                  <v-icon icon="mdi-phone" />
                  <b>
                    {{ $t('user.your_phone_number', { 'mobile': $route.params.id }) }}
                  </b>
                  <v-btn class="float-end" color="indigo" @click="onResendCodeClick()">
                    <span>{{ $t('user.send_again') }}</span>
                  </v-btn>
                </v-col>
              </v-row>
              <v-btn :disabled="disableSend" :loading="loading" block class="text-none mb-4" color="indigo-darken-3"
                size="x-large" variant="flat" prepend-icon="mdi-send" type="submit">
                {{ $t('dialog.active_account') }}
              </v-btn>
            </v-card-text>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <div v-if="dialog" class="text-center">
    <v-dialog v-model="dialog" max-width="500" persistent>
      <v-card color="dangerLight" prepend-icon="mdi-close-octagon " :title="$t('dialog.error')"
        :text="response.message">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat"
            @click="dialog = false; loading = false; code = ''" />
        </template>
      </v-card>
    </v-dialog>
  </div>
  <div v-if="dialogSuccess" class="text-center">
    <v-dialog v-model="dialogSuccess" max-width="500" persistent>
      <v-card color="successLight" prepend-icon="mdi-check-bold " :title="$t('dialog.title')" :text="response.message">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat"
            @click="dialog = false; loading = false; $router.push({ name: 'user_login' })" />
        </template>
      </v-card>
    </v-dialog>
  </div>
</template>

<style scoped></style>