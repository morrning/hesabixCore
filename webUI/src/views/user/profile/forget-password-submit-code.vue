<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import VueCountdown from '@chenfengyuan/vue-countdown';

export default defineComponent({
  name: "reset-password",
  components: {
    'vue-countdown': VueCountdown
  },
  data() {
    const self = this;
    return {
      dialog: false,
      dialogSuccess: false,
      loading: false,
      counting: true,
      disableSend: ref(true),
      code: '',
      response: {
        code: '',
        message: '',
        Success: false,
        data: {
          id: ''
        }
      },
      responseSuccess: {
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
    onCountdownEnd() {
      this.counting = false;
    },
    onResendCodeClick() {
      this.$router.push({ name: 'user_forget_password' });
    },
    async submit() {
      const { valid } = await this.$refs.form.validate()
      if (valid) {
        this.loading = true;
        axios.post('/api/user/reset/password/send-to-sms', {
          id: this.$route.params.id,
          code: this.code
        }).then((response: any) => {
          if (response.data.Success != true) {
            this.response = response.data;
            this.dialog = true;
          }
          else {
            this.responseSuccess = response.data;
            this.dialogSuccess = true;
          }
          this.loading = false;
        });
      }
    }
  }
})
</script>


<template>
  <v-container>
    <v-row class="d-flex justify-center">
      <v-col md="5">
        <v-card :loading="loading ? 'blue' : null" :title="$t('app.name')" :subtitle="$t('user.forget_password')">
          <v-card-text>
            <v-form ref="form" :disabled="loading" fast-fail @submit.prevent="submit()">
              <v-otp-input focus-all v-model="code" :disabled="loading" style="direction: ltr"></v-otp-input>
              <v-row>
                <v-col class="my-2 mx-4">
                  <v-btn class="float-end" color="indigo" :disabled="counting" @click="onResendCodeClick()">
                    <vue-countdown v-if="counting" :time="120000" v-slot="{minutes, seconds }" @end="onCountdownEnd">
                      {{ $t('user.resendCodeLabel') }}
                      {{minutes + ':' + seconds }}
                    </vue-countdown>
                    <span v-else>{{ $t('user.send_again') }}</span>
                  </v-btn>
                </v-col>
              </v-row>

              <v-btn :disabled="disableSend" block class="text-none mb-4" color="indigo-darken-3" size="x-large"
                variant="flat" prepend-icon="mdi-send" type="submit">
                {{ $t('user.send_new_password') }}
              </v-btn>
            </v-form>
          </v-card-text>
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
      <v-card color="successLight" prepend-icon="mdi-check-bold " :title="$t('dialog.title')"
        :text="responseSuccess.message">
        <template v-slot:actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" :text="$t('dialog.ok')" variant="flat"
            @click="dialogSuccess = false; loading = false; $router.push({ name: 'user_login' })" />
        </template>
      </v-card>
    </v-dialog>
  </div>
</template>

<style scoped></style>