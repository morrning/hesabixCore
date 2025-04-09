<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

interface FileItem {
  id: number;
  filename: string;
  filesize: number;
  dateSubmit: string;
  fileType: string;
  fileBin?: string;
}

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
      fileStack: [] as FileItem[],
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
    getFileIconColor(fileType: string): string {
      if (!fileType) return 'grey';
      const type = fileType.toLowerCase();
      if (type.includes('pdf')) return 'red';
      if (type.includes('word') || type.includes('doc')) return 'blue';
      if (type.includes('excel') || type.includes('sheet')) return 'green';
      if (type.includes('image')) return 'purple';
      return 'grey';
    },
    getFileIcon(fileType: string): string {
      if (!fileType) return 'mdi-file';
      const type = fileType.toLowerCase();
      if (type.includes('pdf')) return 'mdi-file-pdf-box';
      if (type.includes('word') || type.includes('doc')) return 'mdi-file-word-box';
      if (type.includes('excel') || type.includes('sheet')) return 'mdi-file-excel-box';
      if (type.includes('image')) return 'mdi-file-image-box';
      return 'mdi-file';
    },
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
        this.fileStack.forEach((item: FileItem) => {
          if (item.fileType && item.fileType.startsWith('image/')) {
            axios
              .get(`${this.$filters.getApiUrl()}/api/archive/file/get/${item.id}`, { responseType: 'arraybuffer' })
              .then((response) => {
                const b64 = btoa(String.fromCharCode(...new Uint8Array(response.data)));
                item.fileBin = `data:${response.headers['content-type']};base64,${b64}`;
              });
          }
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
      axios.get(`${this.$filters.getApiUrl()}/api/archive/file/get/${item.id}`, {
        responseType: 'blob'
      }).then(response => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', item.filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      });
    },
    submitArchive() {
      if (this.media.added.length === 0) {
        Swal.fire({
          text: 'لطفا حداقل یک فایل انتخاب کنید',
          icon: 'warning',
          confirmButtonText: 'قبول',
        });
        return;
      }

      const formData = new FormData(document.getElementById('archive-file-upload') as HTMLFormElement);
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
  <v-dialog v-model="dialog" max-width="800" scrollable>
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
                hide-details
              >
                <template v-slot:append>
                  <v-btn 
                    type="submit" 
                    color="success" 
                    icon
                    variant="flat"
                    class="ml-2"
                    height="100%"
                  >
                    <v-icon>mdi-content-save</v-icon>
                    <v-tooltip activator="parent" location="bottom">
                      ثبت بارگذاری و ذخیره فایل‌ها
                    </v-tooltip>
                  </v-btn>
                </template>
              </v-text-field>
            </form>
          </v-col>
        </v-row>
        <!-- لیست فایل‌ها -->
        <v-row>
          <v-col cols="12">
            <h5 class="text-primary">آرشیو فایل‌ها</h5>
            <v-table class="elevation-1">
              <thead>
                <tr>
                  <th class="text-center">پیش‌نمایش</th>
                  <th class="text-center">نام فایل</th>
                  <th class="text-center">سایز (MB)</th>
                  <th class="text-center">تاریخ</th>
                  <th class="text-center">عملیات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in fileStack" :key="item.id">
                  <td class="text-center">
                    <template v-if="item.fileType && item.fileType.startsWith('image/')">
                      <v-img 
                        :src="item.fileBin" 
                        max-width="50" 
                        max-height="50" 
                        class="mx-auto" 
                        alt="پیش‌نمایش"
                        cover
                      />
                    </template>
                    <template v-else>
                      <v-icon 
                        size="50" 
                        :color="getFileIconColor(item.fileType)"
                      >
                        {{ getFileIcon(item.fileType) }}
                      </v-icon>
                    </template>
                  </td>
                  <td class="text-center">{{ item.filename }}</td>
                  <td class="text-center">{{ item.filesize }}</td>
                  <td class="text-center">{{ item.dateSubmit }}</td>
                  <td class="text-center">
                    <div class="d-flex justify-center">
                      <v-btn variant="text" color="primary" @click="downloadFile(item)" class="px-1">
                        <v-icon>mdi-download</v-icon>
                      </v-btn>
                      <v-btn variant="text" color="error" @click="deleteItem(item)" class="px-1">
                        <v-icon>mdi-trash-can</v-icon>
                      </v-btn>
                    </div>
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
:deep(.v-table .v-table__wrapper > table > thead > tr > th) {
  background-color: #1d90ff !important;
  color: white !important;
}
</style>