<script lang="ts">
import { defineComponent } from 'vue'
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import axios from "axios";
import Swal from "sweetalert2";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
export default defineComponent({
  name: "mod",
  components: { Loading },
  data: () => {
    return {
      loading: true,
      id: '',
      version: '',
      body: '',
      editor: ClassicEditor,
      editorConfig: {
        language: 'fa',
        fontFamily: {
          options: [
            'default',
            'vazir', 'sans-serif',
            'Ubuntu Mono, Courier New, Courier, monospace'
          ]
        },
      }
    }
  },
  mounted() {
    this.id = this.$route.params.id;
    if (this.id != 0) {
      axios.post('/api/admin/reportchange/get/' + this.id).then((response) => {
        this.version = response.data.version;
        this.body = response.data.body;
        this.loading = false;
      });
    }
    else {
      this.loading = false;
    }
  },
  methods: {
    submit() {
      if (this.version.trim() === '' || this.body.trim() === '') {
        Swal.fire({
          text: 'تمام موارد به درستی تکمیل نشده است!',
          icon: 'error',
          confirmButtonText: 'قبول',
        });
      }
      else {
        this.loading = true;
        axios.post('/api/admin/reportchange/mod/' + this.id, {
          id: this.id,
          version: this.version,
          body: this.body
        }).then((response) => {
          if (response.data.result == 1) {
            this.loading = false;
            Swal.fire({
              text: 'گزارش ثبت شد',
              icon: 'success',
              confirmButtonText: 'قبول',
            }).then((res) => {
              this.$router.push('/profile/manager/changes/list');
            })
          }
        })
      }
    }
  }
})
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('user.history')">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text class="pa-2">
        <v-row>
          <v-col cols="12" sm="12" md="6">
            <v-text-field class="" hide-details="auto" :label="$t('pages.manager.version')" v-model="version" type="text"
              prepend-inner-icon="mdi-power-socket-uk"
              :rules="[() => version.length > 0 || $t('validator.required')]"></v-text-field>
          </v-col>
          <v-col cols="12" sm="12" md="12">
            <h3 class="mb-2">{{ $t('app.body') }}</h3>
            <ckeditor :editor="editor" v-model="body" :config="editorConfig"
              :rules="[() => version.length > 0 || $t('validator.required')]"></ckeditor>
          </v-col>
          <v-col cols="12" sm="12" md="12">
            <v-btn type="submit" @click="submit()" color="primary" prepend-icon="mdi-content-save" :loading="loading"
              :title="$t('dialog.save')">
              {{ $t('dialog.save') }}
              </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped></style>