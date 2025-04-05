<template>
    <v-card>
      <v-card-title class="text-h5">
        {{ $route.params.id ? 'ویرایش چک دریافتی' : 'ثبت چک دریافتی' }}
      </v-card-title>
  
      <v-card-text>
        <v-form @submit.prevent="submitForm">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.chequeNumber"
                label="شماره چک"
                :rules="[v => !!v || 'شماره چک الزامی است']"
                required
              ></v-text-field>
            </v-col>
  
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.bankName"
                label="نام بانک"
                :rules="[v => !!v || 'نام بانک الزامی است']"
                required
              ></v-text-field>
            </v-col>
  
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.amount"
                label="مبلغ"
                type="number"
                :rules="[v => !!v || 'مبلغ الزامی است']"
                required
              ></v-text-field>
            </v-col>
  
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.dueDate"
                label="تاریخ سررسید"
                type="date"
                :rules="[v => !!v || 'تاریخ سررسید الزامی است']"
                required
              ></v-text-field>
            </v-col>
  
            <v-col cols="12">
              <v-textarea
                v-model="form.description"
                label="توضیحات"
                rows="3"
              ></v-textarea>
            </v-col>
          </v-row>
  
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="error"
              variant="text"
              @click="$router.back()"
            >
              انصراف
            </v-btn>
            <v-btn
              color="primary"
              type="submit"
              :loading="loading"
            >
              {{ $route.params.id ? 'ویرایش' : 'ثبت' }}
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card-text>
    </v-card>
  </template>
  
  <script>
  export default {
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
  