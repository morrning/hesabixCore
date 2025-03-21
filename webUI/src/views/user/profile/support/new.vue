<template>
  <v-toolbar color="toolbar" :title="$t('pages.support.new')"></v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-row>
      <v-col class="">
        <v-form fast-fail ref="form" @submit.prevent>
          <v-card class="pa-3" :loading="loading ? 'red' : null" :disabled="loading" flat>
            <v-row>
              <v-col cols="12" sm="12" md="12">
                <v-alert icon="mdi-information-slab-box-outline" :text="$t('pages.support.alert_top')"
                  type="info"></v-alert>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" sm="12" md="6">
                <v-text-field :label="$t('pages.support.title')" v-model="item.title" type="text"
                  prepend-inner-icon="mdi-format-title"
                  :rules="[() => item.title.length > 0 || $t('validator.required')]"></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-select :label="$t('pages.support.bid')" prepend-inner-icon="mdi-domain" :items="bidItems"
                  item-title="name" return-object v-model="item.bid" clearable></v-select>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" sm="12" md="12">
                <v-textarea :label="$t('pages.support.body')" v-model="item.body" type="text"
                  prepend-inner-icon="mdi-text"
                  :rules="[() => item.body.length > 0 || $t('validator.required')]"></v-textarea>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" sm="12" md="12">
                <v-file-input v-model="attachedFile" :label="$t('dialog.attach_file')"
                  prepend-icon="mdi-paperclip" accept=".png, .jpg, .jpeg, .pdf, .xls, .xlsx, .zip, .rar"
                  :rules="[validateFile]" :hint="$t('dialog.allowed_file_types_hint')"
                  persistent-hint></v-file-input>
              </v-col>
            </v-row>
            <v-btn @click="submit()" type="submit" color="primary" class="mt-3" prepend-icon="mdi-content-save"
              :loading="loading">
              {{ $t('dialog.save') }}
            </v-btn>
          </v-card>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "new",
  data() {
    return {
      item: {
        title: '',
        body: '',
        bid: null // مقدار پیش‌فرض null برای مدیریت بهتر
      },
      bidItems: [],
      attachedFile: null,
      loading: true
    };
  },
  beforeMount() {
    this.loadData();
  },
  methods: {
    loadData() {
      axios.post('/api/business/list').then((response) => {
        this.bidItems = response.data;
        this.loading = false;
        if (response.data.length === 0) {
          Swal.fire({
            text: this.$t('pages.support.no_business_alert'),
            showCancelButton: true,
            confirmButtonText: this.$t('pages.support.create_business'),
            cancelButtonText: this.$t('dialog.back'),
          }).then((result) => {
            if (result.isConfirmed) {
              this.$router.push('/profile/new-business');
            } else {
              this.$router.push('/profile/dashboard');
            }
          });
        } else {
          this.item.bid = null; // پیش‌فرض null، کاربر باید انتخاب کند
        }
      }).catch((error) => {
        this.loading = false;
        Swal.fire({
          text: this.$t('pages.support.load_error') + error.message,
          icon: 'error',
          confirmButtonText: this.$t('dialog.confirm'),
        });
      });
    },
    validateFile(value) {
      if (!value || (Array.isArray(value) && value.length === 0)) return true;

      const file = Array.isArray(value) ? value[0] : value;
      if (!(file instanceof File)) return this.$t('validator.invalid_file_type');

      const allowedTypes = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'application/pdf',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
        'application/x-rar-compressed'
      ];
      if (!allowedTypes.includes(file.type)) return this.$t('validator.invalid_file_type');

      if (file.size > 5 * 1024 * 1024) return this.$t('validator.file_size_limit');

      return true;
    },
    async submit() {
      const { valid } = await this.$refs.form.validate();
      if (valid) {
        this.loading = true;

        const formData = new FormData();
        formData.append('title', this.item.title);
        formData.append('body', this.item.body);
        formData.append('bid', this.item.bid ? this.item.bid.id : ''); // فقط id ارسال می‌شود

        const file = Array.isArray(this.attachedFile) && this.attachedFile.length > 0 ? this.attachedFile[0] : this.attachedFile;
        if (file instanceof File) {
          formData.append('files[0]', file);
        }

        axios.post('/api/support/mod', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }).then((response) => {
          this.loading = false;
          if (response.data.error === 0) {
            Swal.fire({
              text: this.$t('pages.support.saved'),
              icon: 'success',
              confirmButtonText: this.$t('dialog.ok'),
            }).then(() => {
              this.$router.push('/profile/support-list');
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
  }
};
</script>

<style scoped>
/* استایل‌های شما */
</style>