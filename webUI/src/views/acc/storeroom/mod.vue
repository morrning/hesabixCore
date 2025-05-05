<template>
  <v-toolbar color="toolbar" title="مشخصات انبار">
    <template v-slot:prepend>
      <v-tooltip text="بازگشت" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer />
    <v-btn-toggle
      v-model="data.active"
      mandatory
      density="compact"
      class="mx-2"
    >
      <v-btn
        :value="true"
        size="small"
        :class="data.active ? 'bg-success' : ''"
      >
        <v-icon size="small" start>mdi-check-circle</v-icon>
        فعال
      </v-btn>
      <v-btn
        :value="false"
        size="small"
        :class="!data.active ? 'bg-error' : ''"
      >
        <v-icon size="small" start>mdi-close-circle</v-icon>
        غیرفعال
      </v-btn>
    </v-btn-toggle>
    <v-tooltip text="ثبت" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" color="primary" icon="mdi-content-save" @click="save" :loading="isLoading" />
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container fluid>
    <v-row>
      <v-col cols="12" md="6">
        <v-text-field 
          v-model="data.name" 
          label="نام انبار" 
          :rules="[v => !!v || 'نام انبار الزامی است']" 
          required
          variant="outlined" 
          density="compact"
        >
          <template v-slot:label>
            <span class="text-danger">(لازم)</span> نام انبار
          </template>
        </v-text-field>
      </v-col>

      <v-col cols="12" md="6">
        <v-text-field 
          v-model="data.manager" 
          label="انباردار" 
          variant="outlined" 
          density="compact"
        ></v-text-field>
      </v-col>

      <v-col cols="12" md="6">
        <v-text-field 
          v-model="data.tel" 
          label="تلفن" 
          variant="outlined" 
          density="compact"
        ></v-text-field>
      </v-col>

      <v-col cols="12">
        <v-text-field 
          v-model="data.adr" 
          label="آدرس" 
          variant="outlined" 
          density="compact"
        ></v-text-field>
      </v-col>
    </v-row>
  </v-container>

  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    :timeout="3000"
    location="bottom"
  >
    {{ snackbar.message }}
    <template v-slot:actions>
      <v-btn
        variant="text"
        @click="snackbar.show = false"
      >
        بستن
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

// Refs
const isLoading = ref(false);
const data = ref({
  id: 0,
  name: '',
  manager: '',
  active: true,
  tel: '',
  adr: '',
});

const snackbar = ref({
  show: false,
  message: '',
  color: 'primary'
});

// نمایش پیام
const showMessage = (message, color = 'primary') => {
  snackbar.value = {
    show: true,
    message,
    color
  };
};

// بارگذاری داده‌ها
const loadData = async (id = '') => {
  if (!id) return;

  isLoading.value = true;
  try {
    const response = await axios.post('/api/storeroom/info/' + id);
    data.value = response.data;
  } catch (error) {
    console.error('Error loading data:', error);
    showMessage('خطا در بارگذاری داده‌ها: ' + error.message, 'error');
  } finally {
    isLoading.value = false;
  }
};

// ذخیره داده‌ها
const save = async () => {
  if (!data.value.name) {
    showMessage('نام انبار الزامی است.', 'error');
    return;
  }

  isLoading.value = true;
  try {
    const response = await axios.post('/api/storeroom/mod/' + data.value.id, data.value);

    if (response.data.result === 2) {
      showMessage('قبلا ثبت شده است.', 'error');
    } else {
      showMessage('مشخصات انبار ثبت شد.', 'success');
      // تاخیر در انتقال به صفحه لیست برای نمایش اسنک‌بار
      setTimeout(() => {
        router.push('/acc/storeroom/list');
      }, 1000);
    }
  } catch (error) {
    console.error('Error saving data:', error);
    showMessage('خطا در ذخیره داده‌ها: ' + error.message, 'error');
  } finally {
    isLoading.value = false;
  }
};

// مانت کامپوننت
onMounted(() => {
  loadData(route.params.id);
});
</script>

<style>
.v-radio-group {
  display: flex;
  justify-content: center;
  gap: 1rem;
}
</style>