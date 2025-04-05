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
        <v-form @submit.prevent="submitForm">
          <v-row>
            <v-col cols="12" md="6">
              <Hpersonsearch v-model="form.personId" label="شخص" :rules="[v => !!v || 'شخص الزامی است']" required>
              </Hpersonsearch>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field v-model="form.chequeNumber" label="شماره چک" :rules="[v => !!v || 'شماره چک الزامی است']"
                required></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field v-model="form.bankName" label="نام بانک" :rules="[v => !!v || 'نام بانک الزامی است']"
                required></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field v-model="form.amount" label="مبلغ" type="number" :rules="[v => !!v || 'مبلغ الزامی است']"
                required></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <Hdatepicker v-model="form.dueDate" label="تاریخ سررسید" :rules="[v => !!v || 'تاریخ سررسید الزامی است']"
                required></Hdatepicker>
            </v-col>

            <v-col cols="12">
              <v-textarea v-model="form.description" label="توضیحات" rows="3"></v-textarea>
            </v-col>
          </v-row>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Hdatepicker from '@/components/forms/Hdatepicker.vue'
import Hpersonsearch from '@/components/forms/Hpersonsearch.vue'
export default {
  components: {
    Hdatepicker,
    Hpersonsearch
  },
  data() {
    return {
      loading: false,
      form: {
        chequeNumber: '',
        bankName: '',
        amount: '',
        dueDate: '',
        description: ''
      }
    }
  },

  methods: {
    async submitForm() {
      try {
        this.loading = true
        if (this.$route.params.id) {
          // ویرایش چک
          await this.$axios.put(`/api/cheques/${this.$route.params.id}`, this.form)
        } else {
          // ثبت چک جدید
          await this.$axios.post('/api/cheques', this.form)
        }
        this.$router.push('/acc/cheque/list')
      } catch (error) {
        console.error(error)
      } finally {
        this.loading = false
      }
    }
  },

  async created() {
    if (this.$route.params.id) {
      try {
        const response = await this.$axios.get(`/api/cheques/${this.$route.params.id}`)
        this.form = response.data
      } catch (error) {
        console.error(error)
      }
    }
  }
}
</script>
