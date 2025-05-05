<template>
  <v-toolbar color="toolbar" :title="$t('pages.support.view_ticket')">
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('pages.support.titlebar_admin')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-forum" color="primary" to="/profile/manager/support-list"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-row>
      <v-col>
        <v-form fast-fail ref="form" @submit.prevent>
          <v-card class="pa-3" :loading="loading ? 'red' : null" :disabled="loading" flat>
            <v-row>
              <v-col cols="12" md="12">
                <v-card
                  color="primary"
                  class="mx-auto"
                  prepend-icon="mdi-account"
                  :subtitle="item.dateSubmit + ' ' + item.state"
                  :title="item.title"
                >
                  <v-card-text>{{ item.body }}</v-card-text>
                  <v-card-actions>
                    {{ $t('pages.support.ticket_id') }} {{ item.id }}
                    <v-spacer></v-spacer>
                    <v-btn
                      v-if="item.fileName"
                      prepend-icon="mdi-paperclip"
                      size="small"
                      variant="outlined"
                      @click="downloadFile(item.id, item.fileName)"
                    >
                      {{ $t('dialog.attach_download') }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-col>
            </v-row>
            <v-timeline side="end" class="align-end">
              <v-timeline-item
                v-for="item in replays"
                :key="item.id"
                :dot-color="item.owner ? 'primary' : 'warning'"
                size="small"
              >
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
                      <v-btn
                        v-if="item.fileName"
                        prepend-icon="mdi-paperclip"
                        size="small"
                        variant="outlined"
                        @click="downloadFile(item.id, item.fileName)"
                      >
                        {{ $t('dialog.attach_download') }}
                      </v-btn>
                    </v-col>
                  </v-row>
                </v-alert>
              </v-timeline-item>
            </v-timeline>
            <v-row>
              <v-col>
                <v-card class="pa-3" :loading="loading ? 'red' : null" :disabled="loading" flat>
                  <v-row>
                    <v-col cols="12" sm="12" md="12">
                      <v-switch
                        v-model="sendSms"
                        hide-details="auto"
                        :label="$t('pages.support.send_sms')"
                        color="primary"
                        inset
                      ></v-switch>
                      <v-textarea
                        auto-grow
                        :label="$t('pages.support.replay')"
                        v-model="replay"
                        type="text"
                        prepend-inner-icon="mdi-text"
                        :rules="[() => replay.length > 0 || $t('validator.required')]"
                      ></v-textarea>
                    </v-col>
                    <v-col cols="12" sm="12" md="6">
                      <v-select
                        v-model="ticketState"
                        :items="states"
                        label="وضعیت تیکت"
                        prepend-inner-icon="mdi-list-status"
                        :rules="[() => !!ticketState || $t('validator.required')]"
                      ></v-select>
                    </v-col>
                    <v-col cols="12" sm="12" md="6">
                      <v-file-input
                        v-model="attachedFile"
                        :label="$t('dialog.attach_file')"
                        prepend-icon="mdi-paperclip"
                        accept=".png, .jpg, .jpeg, .pdf, .xls, .xlsx, .zip, .rar"
                        :rules="[validateFileType, validateFileSize]"
                        :hint="$t('dialog.allowed_file_types_hint')"
                        persistent-hint
                        optional
                      ></v-file-input>
                    </v-col>
                  </v-row>
                  <v-btn
                    @click="submit()"
                    type="submit"
                    color="primary"
                    class="mt-3"
                    prepend-icon="mdi-content-save"
                    :loading="loading"
                  >
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
import axios from "axios";
import Swal from "sweetalert2";
import { getApiUrl } from "@/hesabixConfig";

export default {
  name: "show",
  data() {
    return {
      item: {
        id: "",
        state: "",
        body: "",
        dateSubmit: "",
        fileName: null,
      },
      replays: [],
      replay: "",
      ticketState: null,
      states: ["در حال پیگیری", "پاسخ داده شده", "خاتمه یافته"],
      attachedFile: null,
      sendSms: true,
      loading: false,
    };
  },
  methods: {
    loadData() {
      this.loading = true;
      axios
        .post("/api/admin/support/view/" + this.$route.params.id)
        .then((response) => {
          this.loading = false;
          this.item = response.data.data.item;
          this.replays = response.data.data.replays;
          this.ticketState = this.item.state;
        })
        .catch((error) => {
          this.loading = false;
          Swal.fire({
            text: this.$t("pages.support.load_error") + error.message,
            icon: "error",
            confirmButtonText: this.$t("dialog.confirm"),
          });
        });
    },
    validateFileType(value) {
      if (!value || (Array.isArray(value) && value.length === 0)) return true; // اگر فایلی انتخاب نشده، معتبر است
      const file = Array.isArray(value) && value.length > 0 ? value[0] : value instanceof File ? value : null;
      if (!file) return this.$t("validator.invalid_file_type");
      const allowedTypes = [
        "image/png",
        "image/jpeg",
        "image/jpg",
        "application/pdf",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/zip",
        "application/x-rar-compressed",
      ];
      return allowedTypes.includes(file.type) || this.$t("validator.invalid_file_type");
    },
    validateFileSize(value) {
      if (!value || (Array.isArray(value) && value.length === 0)) return true; // اگر فایلی انتخاب نشده، معتبر است
      const file = Array.isArray(value) && value.length > 0 ? value[0] : value instanceof File ? value : null;
      if (!file) return this.$t("validator.invalid_file_type");
      return file.size < 5 * 1024 * 1024 || this.$t("validator.file_size_limit");
    },
    downloadFile(id, filename) {
      this.loading = true;
      axios
        .get(getApiUrl() + "/api/support/download/file/" + id, {
          responseType: "blob",
        })
        .then((response) => {
          this.loading = false;
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", filename);
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);
        })
        .catch((error) => {
          this.loading = false;
          Swal.fire({
            text: this.$t("pages.support.download_error") + (error.response?.data?.message || error.message),
            icon: "error",
            confirmButtonText: this.$t("dialog.confirm"),
          });
        });
    },
    async submit() {
      const { valid } = await this.$refs.form.validate();
      if (valid) {
        this.loading = true;

        const formData = new FormData();
        formData.append("body", this.replay);
        formData.append("sendSms", this.sendSms);
        formData.append("state", this.ticketState);
        const file = Array.isArray(this.attachedFile) && this.attachedFile.length > 0 ? this.attachedFile[0] : this.attachedFile;
        if (file) {
          formData.append("files[0]", file);
        }

        axios
          .post("/api/admin/support/mod/" + this.item.id, formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((response) => {
            this.loading = false;
            if (response.data.error === 0) {
              Swal.fire({
                text: this.$t("pages.support.reply_submitted"),
                icon: "success",
                confirmButtonText: this.$t("dialog.confirm"),
              }).then(() => {
                this.loadData();
                this.replay = "";
                this.attachedFile = null;
              });
            }
          })
          .catch((error) => {
            this.loading = false;
            Swal.fire({
              text: this.$t("pages.support.submit_error") + error.message,
              icon: "error",
              confirmButtonText: this.$t("dialog.confirm"),
            });
          });
      }
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped></style>