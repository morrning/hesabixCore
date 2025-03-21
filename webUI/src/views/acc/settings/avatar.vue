<template>
  <v-toolbar color="toolbar" :title="$t('drawer.avatar_settings')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container>
    <v-row>
      <v-col cols="12" sm="4" md="4">
        <v-card :title="$t('dialog.avatar_now')">
          <v-card-text class="text-center">
            <img class="img-fluid" style="max-width: 10rem;" :src="getImgSrc()" />
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="8" md="8">
        <v-card :title="$t('dialog.avatar')">
          <v-card-text>
            <ul class="text-primary">
              <li>
                نمایه یک تصویر است که در بالای فاکتورها و اسناد حسابداری قرار می گیرد.
              </li>
              <li>
                بهترین نمایش تصویر در لوگو و آرم‌های مربعی است
              </li>
              <li>
                حداکثر سایز 512 در 512 پیکسل است و بیشینه حجم فایل انتخابی نباید از ۱ مگابایت تجاوز کند.
              </li>
              <li>
                فرمت فایل قابل قبول jpg , jpeg , png است.
              </li>
            </ul>
            <form @submit.prevent="save()">
              <div class="input-group">
                <input type="file" required class="form-control" v-on:change="selectedFile($event)" id="avatarImgFile"
                  aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                <button :disabled="loading" class="btn btn-success" type="submit" id="inputGroupFileAddon04">
                  <i class="fa-solid fa-floppy-disk me-1"></i>
                  ذخیره نمایه
                </button>
              </div>
            </form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12" sm="4" md="4">
        <v-card :title="$t('dialog.seal_now')">
          <v-card-text class="text-center">
            <img class="img-fluid" style="max-width: 10rem;" :src="getSealImgSrc()" />
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="8" md="8">
        <v-card :title="$t('dialog.seal')">
          <v-card-text>
            <ul class="text-primary">
              <li>
                مهر کسب و کار در پایین اسناد حسابداری از جمله فاکتور های فروش و ... درج می‌شود 
              </li>
              <li>
                بهترین نتیجه ممکن در مهر های با پس زمینه سفید رنگ یا بدون رنگ است.
              </li>
              <li>
                حداکثر سایز 512 در 512 پیکسل است و بیشینه حجم فایل انتخابی نباید از ۱ مگابایت تجاوز کند.
              </li>
              <li>
                فرمت فایل قابل قبول jpg , jpeg , png است.
              </li>
            </ul>
            <form @submit.prevent="seal_save()">
              <div class="input-group">
                <input type="file" required class="form-control" v-on:change="seal_selectedFile($event)" id="sealImgFile"
                  aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                <button :disabled="loading" class="btn btn-success" type="submit" id="inputGroupFileAddon04">
                  <i class="fa-solid fa-floppy-disk me-1"></i>
                  ذخیره مهر
                </button>
              </div>
            </form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

</template>

<script>

import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";
import { getApiUrl, getSiteName } from "@/hesabixConfig"
import Loading from "vue-loading-overlay";
import { DialogHandler } from "maz-ui";

export default {
  name: "avatar",
  components: {
    Loading
  },
  data: () => {
    return {
      loading: true,
      file: null,
      lastImg: 'default.png',
      seal_file: null,
      seal_lastImg: 'default.png'
    }
  },
  methods: {
    loadData() {
      axios.post('/api/avatar/get')
        .then((response) => {
          this.loading = false;
          this.lastImg = response.data;
        })

        axios.post('/api/seal/get')
        .then((response) => {
          this.loading = false;
          this.seal_lastImg = response.data;
        })
    },
    save() {
      this.loading = true;
      const formData = new FormData();
      formData.append('bytes', this.file);
      axios.post('/api/avatar/post',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      ).then((response) => {
        this.loading = false;
        if (response.data == 'e') {
          Swal.fire({
            text: 'فرمت فایل اشتباه است.',
            icon: 'warning',
            confirmButtonText: 'قبول'
          })
        }
        else if (response.data == 's') {
          Swal.fire({
            text: 'حجم فایل ارسال باید کمتر از یک مگابایت باشد.',
            icon: 'warning',
            confirmButtonText: 'قبول'
          })
        }
        else if (response.data == 'is') {
          Swal.fire({
            text: 'سایز تصویر نامناسب است و طول و عرض آن باید کمتر از 512 پیکسل باشد.',
            icon: 'warning',
            confirmButtonText: 'قبول'
          })
        }
        else {
          Swal.fire({
            text: 'نمایه با موفقیت تغییر یافت.',
            icon: 'success',
            confirmButtonText: 'قبول'
          });
          this.loadData();
        }
      });
    },
    seal_save() {
      this.loading = true;
      const formData = new FormData();
      formData.append('bytes', this.seal_file);
      axios.post('/api/seal/post',
        formData,
        {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        }
      ).then((response) => {
        this.loading = false;
        if (response.data == 'e') {
          Swal.fire({
            text: 'فرمت فایل اشتباه است.',
            icon: 'warning',
            confirmButtonText: 'قبول'
          })
        }
        else if (response.data == 's') {
          Swal.fire({
            text: 'حجم فایل ارسال باید کمتر از یک مگابایت باشد.',
            icon: 'warning',
            confirmButtonText: 'قبول'
          })
        }
        else if (response.data == 'is') {
          Swal.fire({
            text: 'سایز تصویر نامناسب است و طول و عرض آن باید کمتر از 512 پیکسل باشد.',
            icon: 'warning',
            confirmButtonText: 'قبول'
          })
        }
        else {
          Swal.fire({
            text: 'مهر کسب‌و‌کار با موفقیت تغییر یافت.',
            icon: 'success',
            confirmButtonText: 'قبول'
          });
          this.loadData();
        }
      });
    },
    getImgSrc() {
      return getApiUrl() + '/api/avatar/get/file/' + this.lastImg;
    },
    selectedFile(event) {
      this.file = event.target.files[0]
    },

    getSealImgSrc() {
      return getApiUrl() + '/api/seal/get/file/' + this.seal_lastImg;
    },

    seal_selectedFile(event) {
      this.seal_file = event.target.files[0]
    },
  },
  beforeMount() {
    this.loadData();
  }
}
</script>
<style scoped></style>