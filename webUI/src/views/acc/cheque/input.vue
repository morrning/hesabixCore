<template>
  <v-toolbar color="toolbar" :title="$route.params.id ? 'ویرایش چک دریافتی' : 'ثبت چک دریافتی'">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <template v-slot:append>
      <v-tooltip text="اطلاع رسانی پیامک به مشتری" location="bottom">
        <template v-slot:activator="{ props }">
          <div v-bind="props" class="d-flex align-center">
            <v-switch v-model="form.sendSms" hide-details color="primary" class="mx-2">
              <template v-slot:label>
                <v-icon icon="mdi-message-text" :color="form.sendSms ? 'primary' : 'grey'"></v-icon>
              </template>
            </v-switch>
          </div>
        </template>
      </v-tooltip>
      <v-tooltip :text="$route.params.id ? 'ویرایش' : 'ثبت'" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" color="success" @click="submitForm" :loading="loading" icon="mdi-content-save" />
        </template>
      </v-tooltip>
    </template>
  </v-toolbar>
  <v-container>
    <v-row>
      <v-col cols="12">
        <v-form ref="form" @submit.prevent="submitForm" v-model="valid" :disabled="loading">
          <v-row>
            <v-col cols="12" md="6">
              <Hpersonsearch v-model="form.personId" label="شخص" :rules="[v => !!v || 'شخص الزامی است']" required :disabled="loading">
              </Hpersonsearch>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="form.chequeNumber" label="شماره چک" :rules="[v => !!v || 'شماره چک الزامی است']"
                required :disabled="loading"></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field v-model="form.bankoncheque" label="نام بانک" :rules="[v => !!v || 'نام بانک الزامی است']"
                required :disabled="loading"></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <Hnumberinput v-model="form.amount" label="مبلغ" :rules="[v => !!v || 'مبلغ الزامی است']"
                required :disabled="loading"></Hnumberinput>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field v-model="form.sayadNumber" label="شماره صیاد" :rules="[v => !!v || 'شماره صیاد الزامی است']"
                required :disabled="loading"></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <Hdatepicker v-model="form.dueDate" label="تاریخ سررسید" :rules="[v => !!v || 'تاریخ سررسید الزامی است']"
                required :disabled="loading"></Hdatepicker>
            </v-col>

            <v-col cols="12">
              <v-textarea v-model="form.description" label="توضیحات" rows="3" :disabled="loading"></v-textarea>
            </v-col>
          </v-row>
        </v-form>
      </v-col>
    </v-row>
  </v-container>

  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    :timeout="3000"
    location="bottom"
  >
    {{ snackbar.text }}
    <template v-slot:actions>
      <v-btn
        color="white"
        variant="text"
        @click="snackbar.show = false"
      >
        بستن
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script>
import Hdatepicker from '@/components/forms/Hdatepicker.vue'
import Hpersonsearch from '@/components/forms/Hpersonsearch.vue'
import Hnumberinput from '@/components/forms/Hnumberinput.vue'
import axios from 'axios'
export default {
  components: {
    Hdatepicker,
    Hpersonsearch,
    Hnumberinput
  },
  data() {
    return {
      loading: false,
      valid: false,
      isSubmitting: false,
      snackbar: {
        show: false,
        text: '',
        color: 'success'
      },
      form: {
        chequeNumber: '',
        bankoncheque: '',
        amount: '',
        dueDate: '',
        description: '',
        sayadNumber: '',
        personId: null,
        sendSms: localStorage.getItem('chequeSendSms') === 'true' || false
      }
    }
  },

  methods: {
    async submitForm() {
      if (this.isSubmitting) return;
      
      const { valid } = await this.$refs.form.validate()
      
      if (!valid) {
        return
      }

      try {
        this.loading = true;
        this.isSubmitting = true;
        
        // ذخیره تنظیمات در localStorage
        localStorage.setItem('chequeSendSms', this.form.sendSms)
        
        if (this.$route.params.id) {
          // ویرایش چک
          await axios.put(`/api/cheque/modify/input/${this.$route.params.id}`, {
            number: this.form.chequeNumber,
            amount: this.form.amount,
            date: this.form.dueDate,
            person: { code: this.form.personId },
            sayadNumber: this.form.sayadNumber,
            description: this.form.description,
            bankoncheque: this.form.bankoncheque,
            sendSms: this.form.sendSms
          })
          this.showSnackbar('چک با موفقیت ویرایش شد', 'success')
        } else {
          // ثبت چک جدید
          await axios.post('/api/cheque/modify/input', {
            number: this.form.chequeNumber,
            amount: this.form.amount,
            date: this.form.dueDate,
            person: { code: this.form.personId },
            sayadNumber: this.form.sayadNumber,
            description: this.form.description,
            bankoncheque: this.form.bankoncheque,
            sendSms: this.form.sendSms
          })
          this.showSnackbar('چک با موفقیت ثبت شد', 'success')
        }
        
        setTimeout(() => {
          this.$router.push('/acc/cheque/list')
        }, 1500)
      } catch (error) {
        console.error(error)
        this.showSnackbar('خطا در ثبت/ویرایش چک', 'error')
      } finally {
        this.loading = false
        this.isSubmitting = false
      }
    },
    
    showSnackbar(text, color) {
      this.snackbar.text = text
      this.snackbar.color = color
      this.snackbar.show = true
    }
  },

  async created() {
    if (this.$route.params.id) {
      try {
        const response = await axios.get(`/api/cheque/input/get/${this.$route.params.id}`)
        const chequeData = response.data
        this.form = {
          chequeNumber: chequeData.number,
          bankoncheque: chequeData.bankoncheque,
          amount: chequeData.amount,
          dueDate: chequeData.date,
          description: chequeData.description,
          sayadNumber: chequeData.sayadNumber,
          personId: chequeData.person.id,
          sendSms: chequeData.sendSms || false
        }
      } catch (error) {
        await Swal.fire({
          text: 'خطا در دریافت اطلاعات چک',
          icon: 'error',
          confirmButtonText: 'قبول'
        })
        this.$router.push('/acc/cheque/list')
      }
    }
  }
}
</script>
