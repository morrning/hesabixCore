<script lang="ts">
import {defineComponent,ref} from 'vue'
import axios from "axios";
import Swal from 'sweetalert2';

export default defineComponent({
  name: "change_password",
  data(){
    const self = this;
    return{
      loading: ref(false),
      formData:{
        password1:'',
        password2:''
      }
  }},
 
  methods:{
    async submit() {
      const { valid } = await this.$refs.form.validate()
      if(valid && (this.formData.password1.toString() == this.formData.password2.toString())){
        this.loading = true;
        axios.post('/api/user/change/password',{
          pass: this.formData.password1,
          repass: this.formData.password2
        }).then((response:any) => {
          this.loading = false;
          if(response.data.Success ==true){
            Swal.fire({
              text: this.$t('pages.reset_password.password_changed'),
              confirmButtonText: this.$t('dialog.ok'),
              icon:'success'
            }).then(()=>{
              this.$router.push('/profile/dashboard');
            });
          }
          else{
            Swal.fire({
              text: response.data.message,
              confirmButtonText: this.$t('dialog.ok'),
              icon:'error'
            });
          }
        });
      }
      else if(this.formData.password1.toString() != this.formData.password2.toString()){
        Swal.fire({
          text: this.$t('pages.reset_password.passwords_not_match'),
          confirmButtonText: this.$t('dialog.ok'),
          icon:'error'
        });
      }
      else{
        Swal.fire({
          text: this.$t('pages.reset_password.form_not_valid'),
          confirmButtonText: this.$t('dialog.ok'),
          icon:'error'
        });
      }
    }
  }
})
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('user.change_password')"></v-toolbar>
  <v-container class="ma-0 pa-0">
    <v-row>
      <v-col>
        <v-card :loading="loading ? 'red' : null" :disabled="loading">
          <v-card-text>
            <v-form ref="form" @submit.prevent >
              <v-text-field
                  class="mb-2"
                  :label="$t('user.password')"
                  :placeholder="$t('user.password_placeholder')"
                  single-line
                  type="password"
                  variant="outlined"
                  prepend-icon="mdi-lock"
                  v-model="formData.password1"
                  :rules="[() => formData.password1.length > 7 || $t('validator.password_len_lower')]"
              ></v-text-field>
              <v-text-field
                  :label="$t('user.password')"
                  :placeholder="$t('user.password_placeholder')"
                  single-line
                  type="password"
                  variant="outlined"
                  prepend-icon="mdi-lock"
                  v-model="formData.password2"
                  :rules="[() => formData.password2.length > 7 || $t('validator.password_len_lower')]"
              ></v-text-field>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-btn :loading="loading" @click="submit()" type="submit" prepend-icon="mdi-content-save-check" variant="flat" color="primary">{{$t('dialog.save')}}</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>

</style>