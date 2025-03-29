<template>
    <v-text-field
      v-model="formattedValue"
      v-bind="$attrs"
      type="text"
      :rules="combinedRules"
      @keypress="handleKeypress"
      @input="syncValue"
      dir="ltr"
      dense
    />
  </template>
  
  <script>
  export default {
    name: 'Hnumberinput',
    inheritAttrs: false,
  
    props: {
      modelValue: {
        type: [Number, String],
        default: null
      },
      rules: {
        type: Array,
        default: () => []
      }
    },
  
    data() {
      return {
        internalValue: '' // مقدار خام به صورت رشته
      }
    },
  
    computed: {
      formattedValue() {
        if (!this.internalValue) return ''
        return Number(this.internalValue).toLocaleString('fa-IR')
      },
      combinedRules() {
        return [
          v => !v || !isNaN(this.normalizeNumber(v)) || 'فقط عدد مجاز است',
          ...this.rules
        ]
      }
    },
  
    watch: {
      modelValue(newVal) {
        this.internalValue = newVal !== null && newVal !== undefined ? String(newVal) : ''
      }
    },
  
    mounted() {
      this.internalValue = this.modelValue !== null && this.modelValue !== undefined ? String(this.modelValue) : ''
    },
  
    methods: {
      normalizeNumber(value) {
        if (!value) return ''
        let result = value.toString()
        const persianNumbers = [/۰/g, /۱/g, /۲/g, /۳/g, /۴/g, /۵/g, /۶/g, /۷/g, /۸/g, /۹/g]
        const arabicNumbers = [/٠/g, /١/g, /٢/g, /٣/g, /٤/g, /٥/g, /٦/g, /٧/g, /٨/g, /٩/g]
        for (let i = 0; i < 10; i++) {
          result = result.replace(persianNumbers[i], i).replace(arabicNumbers[i], i)
        }
        return result.replace(/[^0-9]/g, '')
      },
  
      handleKeypress(event) {
        event.preventDefault() // جلوگیری از رفتار پیش‌فرض برای کنترل کامل
        const charCode = event.charCode
        const char = String.fromCharCode(charCode)
  
        // فقط اعداد رو قبول کن
        if (charCode >= 48 && charCode <= 57) { // اعداد انگلیسی
          const newValue = this.internalValue + char
          const normalized = this.normalizeNumber(newValue)
          this.internalValue = normalized
          this.$emit('update:modelValue', normalized ? Number(normalized) : null)
        } else if (charCode >= 1632 && charCode <= 1641) { // اعداد عربی
          const arabicToEnglish = String.fromCharCode(charCode - 1584)
          const newValue = this.internalValue + arabicToEnglish
          const normalized = this.normalizeNumber(newValue)
          this.internalValue = normalized
          this.$emit('update:modelValue', normalized ? Number(normalized) : null)
        } else if (charCode >= 1776 && charCode <= 1785) { // اعداد فارسی
          const persianToEnglish = String.fromCharCode(charCode - 1728)
          const newValue = this.internalValue + persianToEnglish
          const normalized = this.normalizeNumber(newValue)
          this.internalValue = normalized
          this.$emit('update:modelValue', normalized ? Number(normalized) : null)
        }
      },
  
      syncValue(event) {
        const rawInput = event.target.value
        const normalized = this.normalizeNumber(rawInput)
        this.internalValue = normalized
        this.$emit('update:modelValue', normalized ? Number(normalized) : null)
        this.$nextTick(() => {
          event.target.value = this.formattedValue
        })
      }
    }
  }
  </script>
  
  <style scoped>
  :deep(.v-text-field input) {
    direction: ltr;
    text-align: left;
  }
  </style>