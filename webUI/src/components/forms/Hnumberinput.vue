<template>
  <v-text-field
    v-model="inputValue"
    v-bind="$attrs"
    :class="$attrs.class"
    type="number"
    :rules="combinedRules"
    :error-messages="errorMessages"
    @keydown="restrictToNumbers"
    @input="handleInput"
    dir="ltr"
    dense
    :hide-details="$attrs['hide-details'] || 'auto'"
  >
    <template v-for="(_, slot) in $slots" #[slot]="props">
      <slot :name="slot" v-bind="props" />
    </template>
  </v-text-field>
</template>

<script>
export default {
  name: 'HNumberInput',
  inheritAttrs: false,

  props: {
    modelValue: {
      type: [Number, String],
      default: null
    },
    rules: {
      type: Array,
      default: () => []
    },
    allowDecimal: {
      type: Boolean,
      default: false
    },
    allowNegative: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      inputValue: '',
      errorMessages: []
    }
  },

  computed: {
    combinedRules() {
      return [
        v => {
          if (!v && v !== '0') return true // اجازه خالی بودن
          const pattern = this.allowDecimal
            ? this.allowNegative
              ? /^-?\d*\.?\d*$/
              : /^\d*\.?\d*$/
            : this.allowNegative
              ? /^-?\d+$/
              : /^\d+$/
          return pattern.test(v) || this.$t('numberinput.invalid_number')
        },
        ...this.rules
      ]
    }
  },

  watch: {
    modelValue: {
      immediate: true,
      handler(newVal) {
        if (newVal === null || newVal === undefined) {
          this.inputValue = ''
        } else {
          const cleaned = String(newVal).replace(this.allowDecimal ? /[^0-9.-]/g : /[^0-9-]/g, '')
          this.inputValue = cleaned
        }
      }
    },
    inputValue(newVal) {
      if (newVal === '' || newVal === null || newVal === undefined) {
        this.$emit('update:modelValue', null)
        this.errorMessages = []
        return
      }

      const cleaned = String(newVal).replace(this.allowDecimal ? /[^0-9.-]/g : /[^0-9-]/g, '')
      const pattern = this.allowDecimal
        ? this.allowNegative
          ? /^-?\d*\.?\d*$/
          : /^\d*\.?\d*$/
        : this.allowNegative
          ? /^-?\d+$/
          : /^\d+$/

      if (pattern.test(cleaned)) {
        let numericValue
        if (this.allowDecimal) {
          numericValue = cleaned === '' || cleaned === '-' ? null : parseFloat(cleaned)
        } else {
          numericValue = cleaned === '' || cleaned === '-' ? null : parseInt(cleaned, 10)
        }
        this.$emit('update:modelValue', isNaN(numericValue) ? null : numericValue)
        this.errorMessages = []
      } else {
        this.errorMessages = [this.$t('numberinput.invalid_number')]
      }
    }
  },

  methods: {
    restrictToNumbers(event) {
      const key = event.key
      const input = this.inputValue || ''

      // اجازه دادن به کلیدهای کنترلی
      if (
        ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'].includes(key) ||
        (event.ctrlKey || event.metaKey)
      ) {
        return
      }

      if (this.allowDecimal) {
        // اجازه ورود اعداد، ممیز، کاما (برای کیبوردهای محلی) و (در صورت اجازه) منفی
        if (!/[0-9.,]/.test(key) && (!this.allowNegative || key !== '-')) {
          event.preventDefault()
        }
        // جلوگیری از بیش از یک ممیز
        if ((key === '.' || key === ',') && input.includes('.')) {
          event.preventDefault()
        }
        // جلوگیری از ممیز در ابتدا یا بعد از منفی
        if ((key === '.' || key === ',') && (input === '' || input === '-')) {
          event.preventDefault()
        }
        // جلوگیری از بیش از یک منفی
        if (key === '-' && (input.includes('-') || !this.allowNegative)) {
          event.preventDefault()
        }
        // منفی فقط در ابتدا
        if (key === '-' && input !== '') {
          event.preventDefault()
        }
      } else {
        // فقط اعداد و (در صورت اجازه) منفی
        if (!/[0-9]/.test(key) && (!this.allowNegative || key !== '-')) {
          event.preventDefault()
        }
        // جلوگیری از بیش از یک منفی
        if (key === '-' && (input.includes('-') || !this.allowNegative)) {
          event.preventDefault()
        }
        // منفی فقط در ابتدا
        if (key === '-' && input !== '') {
          event.preventDefault()
        }
      }
    },
    handleInput(event) {
      // تبدیل کاما به ممیز برای کیبوردهای محلی
      if (this.allowDecimal && event.target.value.includes(',')) {
        this.inputValue = event.target.value.replace(',', '.')
      }
    }
  }
}
</script>

<style scoped>
:deep(.v-text-field input) {
  direction: ltr;
  text-align: left;
}

:deep(.v-text-field .v-input__prepend-inner) {
  padding-right: 0;
  margin-right: 0;
}
</style>