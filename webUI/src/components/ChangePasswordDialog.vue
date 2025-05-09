<template>
    <v-dialog v-model="dialog" max-width="500px" persistent>
      <v-card>
        <v-card-title>تغییر کلمه عبور</v-card-title>
        <v-card-text>
          <v-form ref="form" @submit.prevent="submitForm">
            <v-text-field
              v-model="password"
              label="کلمه عبور جدید"
              type="password"
              :rules="passwordRules"
              required
              :disabled="loading"
            ></v-text-field>
            <v-text-field
              v-model="confirmPassword"
              label="تکرار کلمه عبور"
              type="password"
              :rules="confirmPasswordRules"
              required
              class="mt-2"
              :disabled="loading"
            ></v-text-field>
            <v-switch
              v-model="notifyUser"
              label="اطلاع رسانی به کاربر"
              class="mt-4"
              color="primary"
              hide-details
              :disabled="loading"
            ></v-switch>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="submitForm" :loading="loading" :disabled="loading">
            ذخیره
          </v-btn>
          <v-btn @click="closeDialog" :disabled="loading">لغو</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  
    <!-- دیالوگ اطلاع‌رسانی -->
    <v-dialog v-model="notificationDialog" max-width="400px">
      <v-card>
        <v-card-title>{{ notificationTitle }}</v-card-title>
        <v-card-text>{{ notificationMessage }}</v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="notificationDialog = false">بستن</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref } from 'vue';
  import axios from 'axios';
  
  export default defineComponent({
    name: 'ChangePasswordDialog',
    props: {
      userId: {
        type: Number,
        required: true,
      },
      dialog: {
        type: Boolean,
        required: true,
      },
    },
    emits: ['update:dialog'],
  
    setup(props, { emit }) {
      // فرم و داده‌ها
      const form = ref<any>(null);
      const password = ref<string>('');
      const confirmPassword = ref<string>('');
      const notifyUser = ref<boolean>(true);
      const loading = ref<boolean>(false);
  
      // دیالوگ اطلاع‌رسانی
      const notificationDialog = ref<boolean>(false);
      const notificationTitle = ref<string>('');
      const notificationMessage = ref<string>('');
  
      // قوانین ولیدیشن
      const passwordRules = [
        (v: string) => !!v || 'کلمه عبور الزامی است',
        (v: string) => v.length >= 6 || 'کلمه عبور باید حداقل ۶ کاراکتر باشد',
      ];
      const confirmPasswordRules = [
        (v: string) => !!v || 'تکرار کلمه عبور الزامی است',
        (v: string) => v === password.value || 'کلمه عبور و تکرار آن باید یکسان باشند',
      ];
  
      // بستن دیالوگ
      const closeDialog = () => {
        if (!loading.value) {
          resetForm();
          emit('update:dialog', false);
        }
      };
  
      // ریست کردن فرم
      const resetForm = () => {
        password.value = '';
        confirmPassword.value = '';
        notifyUser.value = true;
        if (form.value) {
          form.value.reset();
        }
      };
  
      // ارسال فرم
      const submitForm = async () => {
        // بررسی ولیدیشن فرم
        const { valid } = await form.value.validate();
        if (!valid) {
          showNotification('خطا', 'لطفاً تمام فیلدها را به درستی پر کنید');
          return; // اگر ولیدیشن ناموفق بود، تابع متوقف می‌شود
        }
  
        // شروع لودینگ و ارسال درخواست
        loading.value = true;
        try {
          await axios.post(`/api/admin/user/change-password/${props.userId}`, {
            password: password.value,
            notifyUser: notifyUser.value,
          });
  
          // موفقیت
          showNotification('موفقیت', 'کلمه عبور با موفقیت تغییر یافت');
          resetForm();
          emit('update:dialog', false);
        } catch (error) {
          console.error('Error changing password:', error);
          showNotification('خطا', 'خطا در تغییر کلمه عبور');
        } finally {
          loading.value = false;
        }
      };
  
      // نمایش پیام اطلاع‌رسانی
      const showNotification = (title: string, message: string) => {
        notificationTitle.value = title;
        notificationMessage.value = message;
        notificationDialog.value = true;
      };
  
      return {
        form,
        password,
        confirmPassword,
        notifyUser,
        loading,
        notificationDialog,
        notificationTitle,
        notificationMessage,
        passwordRules,
        confirmPasswordRules,
        submitForm,
        closeDialog,
      };
    },
  });
  </script>