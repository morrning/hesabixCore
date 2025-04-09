<template>
  <div>
    <v-text-field v-model="displayDate" :label="label" prepend-inner-icon="mdi-calendar" persistent-placeholder
      :class="['v-date-input', `date-input-${uniqueId}`]" :rules="rules" @input="updateDateFromInput" @click:prepend="togglePicker"></v-text-field>
    <date-picker v-model="displayDate" type="date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
      :min="ignoreYearRange ? (min || null) : minDatePersian" 
      :max="ignoreYearRange ? null : maxDatePersian" 
      :custom-input="`.date-input-${uniqueId}`" 
      :input-mode="true"
      :editable="pickerActive" 
      @close="pickerActive = false"></date-picker>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'jalali-moment';

export default {
  props: {
    modelValue: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: 'تاریخ',
    },
    rules: {
      type: Array,
      default: () => [],
    },
    ignoreYearRange: {
      type: Boolean,
      default: false
    },
    min: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      displayDate: '', // مقداردهی اولیه خالی
      pickerActive: false,
      minDatePersian: '',
      maxDatePersian: '',
      uniqueId: '',
      isInitialized: false,
    };
  },
  created() {
    this.uniqueId = Math.random().toString(36).substring(2, 15);
  },
  watch: {
    displayDate(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.$emit('update:modelValue', newVal);
      }
    },
    modelValue: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.displayDate = newVal;
        }
      }
    }
  },
  async mounted() {
    await this.fetchYearData();
  },
  methods: {
    async fetchYearData() {
      try {
        const response = await axios.get('/api/year/get');
        this.minDatePersian = response.data.start;
        this.maxDatePersian = response.data.end;
        
        // فقط اگر مقدار اولیه نداریم و هنوز مقداردهی نشده، از تاریخ جاری استفاده کنیم
        if (!this.modelValue && !this.displayDate && !this.isInitialized) {
          this.displayDate = response.data.now;
          this.$emit('update:modelValue', response.data.now);
          this.isInitialized = true;
        }
      } catch (error) {
        console.error('خطا در دریافت اطلاعات سال:', error);
      }
    },
    updateDateFromInput(value) {
      // بررسی و اعتبارسنجی تاریخ وارد شده توسط کاربر
      if (value && moment(value, 'YYYY/MM/DD', 'fa', true).isValid()) {
        this.displayDate = value;
      }
    },
    togglePicker() {
      this.pickerActive = !this.pickerActive; // تغییر وضعیت تقویم
    },
  },
};
</script>

<style scoped>
/* مطمئن شدن که تقویم فقط با آیکون فعال بشه */
.v-date-input {
  position: relative;
}
</style>