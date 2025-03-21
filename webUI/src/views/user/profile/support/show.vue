<template>
  <v-toolbar color="toolbar" :title="$t('pages.support.view_ticket')">
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('pages.support.new_ticket')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-chat-plus" color="primary" to="/profile/support-new"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-row>
      <v-col class="">
        <v-form fast-fail ref="form" @submit.prevent>
          <v-card class="pa-3" :loading="loading ? 'red' : null" :disabled="loading" flat>
            <v-row>
              <v-col cols="12" md="12">
                <v-card color="primary" class="mx-auto" prepend-icon="mdi-account"
                  :subtitle="item.dateSubmit + ' ' + item.state" :title="item.title">
                  <v-card-text>
                    {{ item.body }}
                  </v-card-text>
                  <v-card-actions>
                    {{ $t('pages.support.ticket_id') }}
                    {{ item.id }}
                    <v-spacer></v-spacer>
                    <v-btn v-if="item.fileName" prepend-icon="mdi-paperclip" size="small" variant="outlined"
                      @click="downloadFile(item.id, item.fileName)">
                      {{ $t('dialog.attach_download') }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-col>
            </v-row>
            <v-timeline side="end" class="align-end">
              <v-timeline-item v-for="item in replays" :key="item.id" :dot-color="item.owner ? 'primary' : 'warning'"
                size="small">
                <v-alert color="" icon="mdi-account" :value="true">
                  <span class="text-primary">{{ item.submitter.name }} :</span>
                  {{ item.body }}
                  <v-row>
                    <v-col>
                      <v-chip color="primary" prepend-icon="mdi-clock-outline" variant="tonal">
                        {{ item.dateSubmit }}
                      </v-chip>
                    </v-col>
                    <v-col>
                      <v-btn v-if="item.fileName" prepend-icon="mdi-paperclip" size="small" variant="outlined"
                        @click="downloadFile(item.id, item.fileName)">
                        {{ $t('dialog.attach_download') }}
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-alert>
              </v-timeline-item>
            </v-timeline>
            <v-row>
              <v-col class="">
                <v-card class="pa-3" :loading="loading ? 'red' : null" :disabled="loading" flat>
                  <v-row>
                    <v-col cols="12" sm="12" md="12">
                      <v-textarea auto-grow :label="$t('pages.support.replay')" v-model="replay" type="text"
                        prepend-inner-icon="mdi-text"
                        :rules="[() => replay.length > 0 || $t('validator.required')]"></v-textarea>
                    </v-col>
                    <v-col cols="12" sm="12" md="12">
                      <v-file-input v-model="attachedFiles" :label="$t('dialog.attach_file')"
                        prepend-icon="mdi-paperclip" accept=".png, .jpg, .jpeg, .pdf, .xls, .xlsx, .zip, .rar"
                        :rules="fileRules" :hint="$t('dialog.allowed_file_types_hint')" persistent-hint></v-file-input>
                    </v-col>
                  </v-row>
                  <v-btn @click="submit()" type="submit" color="primary" class="mt-3" prepend-icon="mdi-content-save"
                    :loading="loading">
                    {{ $t('dialog.save') }}
                  </v-btn>
                </v-card>
              </v-col>
            </v-row>
          </v-card>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { getApiUrl } from "@/hesabixConfig";
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "show",
  data() {
    return {
      item: {
        state: '',
        body: '',
        dateSubmit: '',
        fileName: null
      },
      replays: [],
      replay: '',
      attachedFiles: [],
      loading: false,
      fileRules: [
        (files) => {
          if (!files || files.length === 0) return true; // فایل اختیاری است
          return files.every(file => {
            const allowedTypes = [
              'image/png',
              'image/jpeg',
              'application/pdf',
              'application/vnd.ms-excel',
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/zip',
              'application/x-rar-compressed'
            ];
            return allowedTypes.includes(file.type) || this.$t('validator.invalid_file_type');
          });
        },
        (files) => {
          if (!files || files.length === 0) return true;
          return files.every(file => file.size < 5 * 1024 * 1024) || this.$t('validator.file_size_limit');
        }
      ]
    };
  },
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/support/view/' + this.$route.params.id).then((response) => {
        this.loading = false;
        this.item = response.data.item;
        this.replays = response.data.replays;
      }).catch((error) => {
        this.loading = false;
        Swal.fire({
          text: this.$t('pages.support.load_error') + error.message,
          icon: 'error',
          confirmButtonText: this.$t('dialog.confirm'),
        });
      });
    },
    getAttachSrc(id) {
      return getApiUrl() + '/api/support/download/file/' + id;
    },
    downloadFile(id, filename) {
      this.loading = true;
      axios.get(this.getAttachSrc(id), {
        responseType: 'blob' // برای دریافت فایل به‌صورت باینری
      }).then((response) => {
        this.loading = false;
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename); // نام فایل برای دانلود
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url); // آزاد کردن حافظه
      }).catch((error) => {
        this.loading = false;
        Swal.fire({
          text: this.$t('pages.support.download_error') + (error.response?.data?.message || error.message),
          icon: 'error',
          confirmButtonText: this.$t('dialog.confirm'),
        });
      });
    },
    async submit() {
      const { valid } = await this.$refs.form.validate();
      if (valid) {
        this.loading = true;

        const formData = new FormData();
        formData.append('body', this.replay);
        if (this.attachedFiles && this.attachedFiles.length > 0) {
          this.attachedFiles.forEach((file, index) => {
            formData.append(`files[${index}]`, file);
          });
        }

        axios.post('/api/support/mod/' + this.item.id, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }).then((response) => {
          this.loading = false;
          if (response.data.error === 0) {
            Swal.fire({
              text: this.$t('pages.support.reply_submitted'),
              icon: 'success',
              confirmButtonText: this.$t('dialog.confirm'),
            }).then(() => {
              this.loadData();
              this.replay = '';
              this.attachedFiles = [];
            });
          }
        }).catch((error) => {
          this.loading = false;
          Swal.fire({
            text: this.$t('pages.support.submit_error') + error.message,
            icon: 'error',
            confirmButtonText: this.$t('dialog.confirm'),
          });
        });
      }
    }
  },
  mounted() {
    this.loadData();
  }
};
</script>

<style scoped></style>