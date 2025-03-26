<template>
  <div>
    <v-text-field v-model="displayDate" :label="label" prepend-inner-icon="mdi-calendar" persistent-placeholder
      class="v-date-input" :rules="rules" @input="updateDateFromInput" @click:prepend="togglePicker"></v-text-field>
    <date-picker v-model="displayDate" type="date" format="jYYYY/jMM/jDD" display-format="jYYYY/jMM/jDD"
      :min="minDatePersian" :max="maxDatePersian" custom-input=".v-date-input" :input-mode="false"
      :editable="pickerActive" @close="pickerActive = false"></date-picker>
  </div>
</template>

<script>
import axios from 'axios';
import moment from 'jalali-moment';

export default {
  props: {
    value: {
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
  },
  data() {
    return {
      displayDate: '', // تاریخ به فرمت شمسی
      pickerActive: false, // کنترل باز شدن تقویم
      minDatePersian: '', // تاریخ شروع سال مالی (شمسی برای پکیج)
      maxDatePersian: '', // تاریخ پایان سال مالی (شمسی برای پکیج)
    };
  },
  watch: {
    displayDate(newVal) {
      if (newVal) {
        this.$emit('input', newVal); // ارسال تاریخ شمسی به والد
      } else {
        this.$emit('input', '');
      }
    },
    value(newVal) {
      if (newVal) {
        this.displayDate = newVal;
      } else {
        this.displayDate = '';
      }
    },
  },
  async mounted() {
    await this.fetchYearData();
    if (!this.value && this.displayDate) {
      this.$emit('input', this.displayDate);
    }
  },
  methods: {
    async fetchYearData() {

      axios.get('/api/year/get').then((response) => {
        this.minDatePersian = response.data.start; // فرمت YYYY/MM/DD شمسی
        this.maxDatePersian = response.data.end; // فرمت YYYY/MM/DD شمسی
        this.displayDate = response.data.now; // تاریخ جاری شمسی
      });
    },
    updateDateFromInput(value) {
      // بررسی و اعتبارسنجی تاریخ وارد شده توسط کاربر
      if (value && moment(value, 'YYYY/MM/DD', 'fa', true).isValid()) {
        const parsedDate = moment(value, 'YYYY/MM/DD').locale('fa');
        if (
          parsedDate.isSameOrAfter(moment(this.minDatePersian, 'YYYY/MM/DD')) &&
          parsedDate.isSameOrBefore(moment(this.maxDatePersian, 'YYYY/MM/DD'))
        ) {
          this.displayDate = value;
        } else {
          this.displayDate = ''; // یا خطا نمایش بدید
        }
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