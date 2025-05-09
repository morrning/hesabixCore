<template>
  <v-toolbar color="toolbar" :title="$route.params.id ? 'ویرایش واگذاری چک' : 'ثبت واگذاری چک'">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <template v-slot:append>
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
        <v-form ref="form" @submit.prevent="submitForm" :disabled="loading">
          <v-row>
            <v-col cols="12" md="6">
              <Hpersonsearch v-model="form.personId" label="شخص گیرنده" :rules="[v => !!v || 'شخص گیرنده الزامی است']" required :disabled="loading">
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
                required format="YYYY/MM/DD" :disabled="loading"></Hdatepicker>
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
        personId: null
      }
    }
  },

  methods: {
    async submitForm() {
      if (this.isSubmitting) return;
      
      const { valid } = await this.$refs.form.validate()
      
      if (!valid) {
        this.showSnackbar('لطفا تمام فیلدهای الزامی را پر کنید', 'warning')
        return
      }

      try {
        this.loading = true;
        this.isSubmitting = true;
        
        if (this.$route.params.id) {
          // ویرایش واگذاری چک
          await axios.put(`/api/cheque/modify/output/${this.$route.params.id}`, {
            number: this.form.chequeNumber,
            amount: this.form.amount,
            date: this.form.dueDate,
            person: { code: this.form.personId },
            sayadNumber: this.form.sayadNumber,
            description: this.form.description,
            bankoncheque: this.form.bankoncheque
          })
          this.showSnackbar('واگذاری چک با موفقیت ویرایش شد', 'success')
        } else {
          // ثبت واگذاری چک جدید
          await axios.post('/api/cheque/modify/output', {
            number: this.form.chequeNumber,
            amount: this.form.amount,
            date: this.form.dueDate,
            person: { code: this.form.personId },
            sayadNumber: this.form.sayadNumber,
            description: this.form.description,
            bankoncheque: this.form.bankoncheque
          })
          this.showSnackbar('واگذاری چک با موفقیت ثبت شد', 'success')
        }
        
        setTimeout(() => {
          this.$router.push('/acc/cheque/list')
        }, 1500)
      } catch (error) {
        console.error(error)
        this.showSnackbar('خطا در ثبت/ویرایش واگذاری چک', 'error')
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
        const response = await axios.get(`/api/cheque/output/get/${this.$route.params.id}`)
        const chequeData = response.data
        this.form = {
          chequeNumber: chequeData.number,
          bankoncheque: chequeData.bankoncheque,
          amount: chequeData.amount,
          dueDate: chequeData.date,
          description: chequeData.description,
          sayadNumber: chequeData.sayadNumber,
          personId: chequeData.person.id
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
