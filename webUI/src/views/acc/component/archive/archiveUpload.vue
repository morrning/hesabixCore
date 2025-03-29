<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default defineComponent({
  name: 'archiveUpload',
  props: {
    doctype: String,
    docid: [String, Number],
    cat: String,
  },
  data() {
    return {
      dialog: false, // برای باز و بسته کردن دیالوگ
      fileStack: [],
      des: '',
      media: {
        saved: [],
        added: [],
        removed: [],
      },
    };
  },
  mounted() {
    this.getFilesList();
  },
  methods: {
    changeMedia(media) {
      this.media = media;
    },
    addMedia(addedImage, addedMedia) {
      this.media.added = addedMedia;
    },
    removeMedia(removedImage, removedMedia) {
      this.media.removed = removedMedia;
    },
    getFilesList() {
      axios.post('/api/archive/files/list', {
        id: this.$props.docid,
        type: this.$props.doctype,
      }).then((resp) => {
        this.media.added = [];
        this.fileStack = resp.data;
        this.fileStack.forEach((item) => {
          axios
            .post(`${this.$filters.getApiUrl()}/api/archive/file/get/${item.id}`, { responseType: 'arraybuffer' })
            .then((response) => {
              const b64 = btoa(String.fromCharCode(...new Uint8Array(response.data)));
              item.fileBin = `data:${response.headers['content-type']};base64,${b64}`;
            });
        });
      });
    },
    deleteItem(item) {
      Swal.fire({
        text: 'آیا برای حذف فایل مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'خیر',
      }).then((result) => {
        if (result.isConfirmed) {
          axios.post(`/api/archive/file/remove/${item.id}`).then((response) => {
            if (response.data.result === 1) {
              this.getFilesList();
              Swal.fire({
                text: 'فایل با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول',
              });
            }
          });
        }
      });
    },
    downloadFile(item) {
      axios
        .post(`${this.$filters.getApiUrl()}/api/archive/file/get/${item.id}`, { responseType: 'arraybuffer' })
        .then((response) => {
          const blob = new Blob([response.data], { type: item.fileType });
          const link = document.createElement('a');
          link.href = URL.createObjectURL(blob);
          link.download = item.filename;
          link.click();
          URL.revokeObjectURL(link.href);
        });
    },
    submitArchive() {
      const formData = new FormData(document.getElementById('archive-file-upload'));
      axios
        .post('/api/archive/file/save', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        .then((resp) => {
          if (resp.data.result === 'nem') {
            Swal.fire({
              text: 'فضای کافی وجود ندارد لطفا حساب کاربری خود را شارژ نمایید.',
              icon: 'error',
              confirmButtonText: 'قبول',
            });
          } else {
            Swal.fire({
              text: 'فایل‌های انتخابی ذخیره شدند.',
              icon: 'success',
              confirmButtonText: 'قبول',
            });
            this.getFilesList();
            this.des = ''; // ریست کردن توضیحات
          }
        })
        .catch((error) => {
          console.error('خطا در آپلود:', error);
          Swal.fire({
            text: 'خطایی در بارگذاری فایل رخ داد.',
            icon: 'error',
            confirmButtonText: 'قبول',
          });
        });
    },
  },
});
</script>

<template>
  <!-- دکمه در تولبار -->
  <v-btn icon color="success" class="ml-2" @click="dialog = true">
    <v-badge :content="fileStack.length.toString()" color="dark">
      <v-icon>mdi-archive</v-icon>
    </v-badge>
    <v-tooltip activator="parent" location="bottom">آرشیو</v-tooltip>
  </v-btn>

  <!-- دیالوگ آرشیو -->
  <v-dialog v-model="dialog" max-width="800" persistent>
    <v-card>
      <v-toolbar color="grey-lighten-4" flat>
        <v-toolbar-title>
          <v-icon  color="success" left>mdi-archive</v-icon>
          آرشیو فایل
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text>
        <!-- بخش افزودن فایل جدید -->
        <v-row>
          <v-col cols="12">
            <form id="archive-file-upload" @submit.prevent="submitArchive">
              <input type="hidden" name="doctype" :value="$props.doctype" />
              <input type="hidden" name="docid" :value="$props.docid" />
              <input type="hidden" name="cat" :value="$props.cat" />
              <Uploader
                :server="$filters.getApiUrl() + '/api/archive/file/upload'"
                :media="media.saved"
                path="/storage/media"
                @add="addMedia"
                @remove="removeMedia"
                @change="changeMedia"
                :maxFilesize="5"
              />
              <v-text-field
                v-model="des"
                label="توضیحات"
                placeholder="توضیحات"
                outlined
                name="des"
                class="mt-2"
              ></v-text-field>
              <v-btn type="submit" color="success" class="mt-2">
                <v-icon left>mdi-content-save</v-icon>
                بارگذاری فایل‌ها
              </v-btn>
            </form>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <!-- لیست فایل‌ها -->
        <v-row>
          <v-col cols="12">
            <h5 class="text-primary">آرشیو فایل‌ها</h5>
            <v-table class="elevation-1">
              <thead>
                <tr>
                  <th class="text-center">پیش‌نمایش</th>
                  <th class="text-center">نام فایل</th>
                  <th class="text-center">سایز فایل (مگابایت)</th>
                  <th class="text-center">تاریخ</th>
                  <th class="text-center">عملیات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in fileStack" :key="item.id">
                  <td class="text-center">
                    <v-img :src="item.fileBin" max-width="50" max-height="50" class="mx-auto" alt="پیش‌نمایش" />
                  </td>
                  <td class="text-center">{{ item.filename }}</td>
                  <td class="text-center">{{ item.filesize }}</td>
                  <td class="text-center">{{ item.dateSubmit }}</td>
                  <td class="text-center">
                    <v-btn icon small color="primary" @click="downloadFile(item)">
                      <v-icon>mdi-download</v-icon>
                    </v-btn>
                    <v-btn icon small color="error" class="ml-2" @click="deleteItem(item)">
                      <v-icon>mdi-trash-can</v-icon>
                    </v-btn>
                  </td>
                </tr>
                <tr v-if="fileStack.length === 0">
                  <td colspan="5" class="text-center text-muted">فایلی موجود نیست</td>
                </tr>
              </tbody>
            </v-table>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>
/* استایل‌های اضافی اگه نیاز باشه */
</style>