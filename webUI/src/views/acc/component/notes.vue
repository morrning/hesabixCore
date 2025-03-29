<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';

export default defineComponent({
  name: 'Notes',
  props: {
    stat: Object,
    code: String,
    typeNote: String,
  },
  data() {
    return {
      dialog: false, // برای باز و بسته کردن دیالوگ
      loading: false,
      items: [],
      des: '',
      snackbar: false, // برای نمایش اسنک‌بار
      snackbarText: '', // متن پیام اسنک‌بار
      snackbarColor: 'success', // رنگ اسنک‌بار (success, error)
    };
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      this.loading = true;
      axios
        .post('/api/notes/list', {
          type: this.$props.typeNote,
          code: this.$props.code,
        })
        .then((response) => {
          this.items = response.data;
          this.$props.stat.count = response.data.length;
          this.loading = false;
        });
    },
    remove(id) {
      this.loading = true;
      axios.post(`/api/notes/remove/${id}`).then((response) => {
        this.loading = false;
        this.items = this.items.filter(item => item.id !== id); // حذف از لیست محلی
        this.$props.stat.count = this.items.length; // به‌روزرسانی تعداد
        this.snackbarText = 'یادداشت با موفقیت حذف شد.';
        this.snackbarColor = 'success';
        this.snackbar = true;
      }).catch((error) => {
        this.loading = false;
        this.snackbarText = 'خطایی در حذف یادداشت رخ داد.';
        this.snackbarColor = 'error';
        this.snackbar = true;
        console.error('خطا:', error);
      });
    },
    save() {
      if (this.des.trim() === '') {
        this.snackbarText = 'شرح الزامی است.';
        this.snackbarColor = 'error';
        this.snackbar = true;
      } else {
        this.loading = true;
        axios
          .post('/api/notes/add', {
            des: this.des,
            type: this.$props.typeNote,
            code: this.$route.params.id,
          })
          .then((response) => {
            this.loading = false;
            // اضافه کردن یادداشت جدید به لیست محلی
            this.items.unshift({
              id: response.data.id, // فرض می‌کنیم سرور id رو برمی‌گردونه
              des: this.des,
            });
            this.$props.stat.count = this.items.length; // به‌روزرسانی تعداد
            this.snackbarText = 'یادداشت با موفقیت ثبت شد.';
            this.snackbarColor = 'success';
            this.snackbar = true;
            this.des = ''; // ریست کردن ورودی
          })
          .catch((error) => {
            this.loading = false;
            this.snackbarText = 'خطایی در ثبت یادداشت رخ داد.';
            this.snackbarColor = 'error';
            this.snackbar = true;
            console.error('خطا:', error);
          });
      }
    },
  },
});
</script>

<template>
  <!-- دکمه در تولبار -->
  <v-btn icon color="warning" class="ml-2" @click="dialog = true">
    <v-badge :content="items.length.toString()" color="dark">
      <v-icon>mdi-note</v-icon>
    </v-badge>
    <v-tooltip activator="parent" location="bottom">یادداشت‌ها</v-tooltip>
  </v-btn>

  <!-- دیالوگ یادداشت‌ها -->
  <v-dialog v-model="dialog" max-width="600" persistent>
    <v-card>
      <v-toolbar color="toolbar" flat dark>
        <v-toolbar-title>
          <v-icon color="warning" left>mdi-note</v-icon>
          یادداشت‌ها
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text>
        <!-- فرم افزودن یادداشت -->
        <v-row>
          <v-col cols="12">
            <v-text-field
              v-model="des"
              label="شرح"
              placeholder="شرح یادداشت"
              outlined
              class="mt-2"
              :disabled="loading"
              @keyup.enter="save"
              :loading="loading"
            ></v-text-field>
            <v-btn
              color="success"
              @click="save"
              class="mt-2"
              :loading="loading"
            >
              <v-icon left>mdi-content-save</v-icon>
              ثبت یادداشت
            </v-btn>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <!-- لیست یادداشت‌ها با اسکرول -->
        <v-row>
          <v-col cols="12">
            <h5 class="text-primary">یادداشت‌های ثبت‌شده</h5>
            <v-list
              max-height="300"
              class="overflow-y-auto"
            >
              <v-list-item
                v-for="item in items"
                :key="item.id"
                class="my-1"
              >
                <v-list-item-title class="d-flex align-center">
                  <span>{{ item.des }}</span>
                  <v-spacer></v-spacer>
                  <v-btn
                    icon
                    variant="plain"
                    :disabled="loading"
                    @click="remove(item.id)"
                  >
                    <v-icon color="error">mdi-trash-can</v-icon>
                    <v-tooltip activator="parent" location="top">حذف</v-tooltip>
                  </v-btn>
                </v-list-item-title>
              </v-list-item>
              <v-list-item v-if="items.length === 0">
                <v-list-item-title class="text-muted text-center">
                  یادداشتی موجود نیست
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-dialog>

  <!-- اسنک‌بار برای نمایش پیام‌ها -->
  <v-snackbar
    v-model="snackbar"
    :color="snackbarColor"
    timeout="3000"
    location="top"
  >
    {{ snackbarText }}
    <template v-slot:actions>
      <v-btn
        color="white"
        variant="text"
        @click="snackbar = false"
      >
        بستن
      </v-btn>
    </template>
  </v-snackbar>
</template>

<style scoped>
.overflow-y-auto {
  overflow-y: auto;
}
</style>