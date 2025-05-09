<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "database_info",
  data: () => {
    return {
      loading: ref(false),
      backupLoading: ref(false),
      ftpLoading: ref(false),
      lastBackup: null as string | null,
      lastFtpBackup: null as string | null,
    }
  },
  methods: {
    async loadData() {
      this.loading = true;
      try {
        const response = await axios.get('/api/admin/database/backup/info');
        this.lastBackup = response.data.lastBackup;
        this.lastFtpBackup = response.data.lastFtpBackup;
      } catch (error) {
        console.error('Error loading backup info:', error);
      } finally {
        this.loading = false;
      }
    },
    async createLocalBackup() {
      this.backupLoading = true;
      try {
        const resp = await axios.post('/api/admin/database/backup/create');
        if (resp.data.result === 1) {
          await Swal.fire({
            text: `فایل پشتیبان از بانک اطلاعاتی با نام ${resp.data.filename} در پوشه Backup با موفقیت ایجاد شد.`,
            icon: 'success',
            confirmButtonText: 'قبول',
          });
          this.loadData(); // به‌روزرسانی اطلاعات
        } else {
          throw new Error(resp.data.message || 'خطا در ایجاد پشتیبان');
        }
      } catch (error: any) {
        await Swal.fire({
          text: error.response?.data?.message || this.$t('dialog.error_operation'),
          icon: 'error',
          confirmButtonText: this.$t('dialog.ok'),
        });
      } finally {
        this.backupLoading = false;
      }
    },
    async createAndUploadToFtp() {
      this.ftpLoading = true;
      try {
        const resp = await axios.post('/api/admin/database/backup/create-and-upload');
        if (resp.data.result === 1) {
          await Swal.fire({
            text: `فایل پشتیبان با نام ${resp.data.filename} ایجاد و با موفقیت به سرور FTP ارسال شد.`,
            icon: 'success',
            confirmButtonText: 'قبول',
          });
          this.loadData(); // به‌روزرسانی اطلاعات
        } else {
          throw new Error(resp.data.message || 'خطا در ایجاد و ارسال پشتیبان');
        }
      } catch (error: any) {
        await Swal.fire({
          text: error.response?.data?.message || this.$t('dialog.error_operation'),
          icon: 'error',
          confirmButtonText: this.$t('dialog.ok'),
        });
      } finally {
        this.ftpLoading = false;
      }
    }
  },
  beforeMount() {
    this.loadData();
  }
})
</script>

<template>
  <v-container fluid class="pa-0">
    <v-toolbar color="toolbar">
      <v-toolbar-title>{{ $t('pages.manager.database') }}</v-toolbar-title>
      <v-spacer></v-spacer>
    </v-toolbar>

    <v-card :loading="loading ? 'red' : undefined" :disabled="loading">
      <v-card-text>
        <v-row>
          <v-col cols="12">
            <v-alert :text="$t('pages.manager.database_info')" type="warning"></v-alert>
          </v-col>

          <v-col cols="12" md="6">
            <v-card variant="outlined" class="pa-4">
              <v-card-title class="text-h6">
                <v-icon start icon="mdi-database-export" class="me-2"></v-icon>
                پشتیبان‌گیری محلی
              </v-card-title>
              <v-card-text>
                <p class="text-body-2 mb-4">
                  با استفاده از این گزینه می‌توانید یک نسخه پشتیبان از بانک اطلاعاتی در سرور ایجاد کنید.
                </p>
                <p v-if="lastBackup" class="text-caption">
                  آخرین پشتیبان: {{ lastBackup }}
                </p>
                <v-btn
                  color="primary"
                  prepend-icon="mdi-database-export"
                  :loading="backupLoading"
                  @click="createLocalBackup"
                  block
                >
                  ایجاد پشتیبان در سرور
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>

          <v-col cols="12" md="6">
            <v-card variant="outlined" class="pa-4">
              <v-card-title class="text-h6">
                <v-icon start icon="mdi-cloud-upload" class="me-2"></v-icon>
                پشتیبان‌گیری و ارسال به FTP
              </v-card-title>
              <v-card-text>
                <p class="text-body-2 mb-4">
                  با استفاده از این گزینه می‌توانید یک نسخه پشتیبان ایجاد کرده و آن را به سرور FTP ارسال کنید.
                </p>
                <p v-if="lastFtpBackup" class="text-caption">
                  آخرین پشتیبان FTP: {{ lastFtpBackup }}
                </p>
                <v-btn
                  color="secondary"
                  prepend-icon="mdi-cloud-upload"
                  :loading="ftpLoading"
                  @click="createAndUploadToFtp"
                  block
                >
                  ایجاد و ارسال به FTP
                </v-btn>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped>
.v-card {
  margin-bottom: 16px;
}
</style>