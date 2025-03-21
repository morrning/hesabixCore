<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "database_info",
  data: () => {
    return {
      loading: ref(true),
    }
  },
  methods: {
    loadData() {
      this.loading = false;
    },
    createDatabaseFile() {
      this.loading = true;
      axios.post('/api/admin/database/backup/create').then((resp) => {
        this.loading = false;
        if (resp.data.result == 0) {
          Swal.fire({
            text: 'فایل پشتیبان از بانک اطلاعاتی با نام ' + resp.data.filename + 'در پوشه Backup با موفقیت ایجاد شد.',
            icon: 'success',
            confirmButtonText: 'قبول',
          });
        }
      }).catch((response)=>{
        this.loading = false;
        Swal.fire({
            text: this.$t('dialog.error_operation'),
            icon: 'error',
            confirmButtonText: this.$t('dialog.ok'),
          });
      });

    }
  },
  beforeMount() {
    this.loadData();
  }
})
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('pages.manager.database')">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text class="">
        <v-row class="mb-2">
          <v-col cols="12" sm="12" md="12">
            <v-alert :text="$t('pages.manager.database_info')" type="warning"></v-alert>
          </v-col>
          <v-col cols="12" sm="12" md="12">
            <v-btn type="submit" @click="createDatabaseFile()" color="primary" prepend-icon="mdi-database-export"
              :loading="loading" :title="$t('dialog.database_export')">
              {{ $t('dialog.database_export') }}
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped></style>