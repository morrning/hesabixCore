<template>
  <v-text-field
    v-model="inputValue"
    v-bind="$attrs"
    :class="$attrs.class"
    type="text"
    :rules="combinedRules"
    :error-messages="errorMessages"
    @input="handleInput"
    @keydown="restrictInput"
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
import { debounce } from 'lodash'

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
      default: true
    },
    allowNegative: {
      type: Boolean,
      default: false
    },
    maxDecimals: {
      type: Number,
      default: 2
    },
    useThousandSeparator: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      inputValue: '',
      errorMessages: [],
      integerPart: '',
      decimalPart: '',
      isProcessing: false
    }
  },

  computed: {
    combinedRules() {
      return [
        v => {
          if (!v && v !== '0') return true
          const cleaned = v.replace(/,/g, '')
          const regex = this.allowDecimal
            ? this.allowNegative
              ? new RegExp(`^-?\\d*\\.?\\d{0,${this.maxDecimals}}$`)
              : new RegExp(`^\\d*\\.?\\d{0,${this.maxDecimals}}$`)
            : this.allowNegative
              ? /^-?\d+$/
              : /^\d+$/
          return regex.test(cleaned) || 'فقط عدد با ممیز اعشاری (.) مجاز است'
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
          this.integerPart = ''
          this.decimalPart = ''
        } else {
          const num = Number(newVal)
          if (!isNaN(num)) {
            this.setPartsFromNumber(num)
            this.inputValue = this.formatNumber()
          } else {
            this.setPartsFromString(String(newVal))
            this.inputValue = this.formatNumber()
          }
        }
      }
    },
    inputValue: {
      immediate: true,
      handler: debounce(function (newVal) {
        if (this.isProcessing) return
        this.isProcessing = true

        if (!newVal) {
          this.integerPart = ''
          this.decimalPart = ''
          this.$emit('update:modelValue', null)
          this.errorMessages = []
          this.isProcessing = false
          return
        }

        const cleaned = newVal.replace(/,/g, '').trim()

        const regex = this.allowDecimal
          ? this.allowNegative
            ? new RegExp(`^-?\\d*\\.?\\d{0,${this.maxDecimals}}$`)
            : new RegExp(`^\\d*\\.?\\d{0,${this.maxDecimals}}$`)
          : this.allowNegative
            ? /^-?\d+$/
            : /^\d+$/

        if (regex.test(cleaned)) {
          this.setPartsFromString(cleaned)
          const formatted = this.formatNumber()
          if (this.inputValue !== formatted) {
            this.inputValue = formatted
          }
          const numericValue = this.getNumericValue()
          this.$emit('update:modelValue', numericValue)
          this.errorMessages = []
        } else {
          this.errorMessages = ['فقط عدد با ممیز اعشاری (.) مجاز است']
          this.inputValue = this.formatNumber()
        }

        this.isProcessing = false
      }, 150)
    }
  },

  methods: {
    convertPersianToEnglish(str) {
      const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
      const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']

      let result = str || ''
      persianNumbers.forEach((num, index) => {
        result = result.replace(new RegExp(num, 'g'), englishNumbers[index])
      })

      return result
    },

    setPartsFromNumber(num) {
      const isNegative = num < 0
      const absValue = Math.abs(num)
      const strValue = this.allowDecimal
        ? absValue.toString()
        : Math.floor(absValue).toString()
      const parts = strValue.split('.')
      this.integerPart = isNegative ? `-${parts[0]}` : parts[0]
      this.decimalPart = parts[1] || ''
    },

    setPartsFromString(str) {
      const cleaned = this.convertPersianToEnglish(str).replace(/,/g, '')
      const isNegative = cleaned.startsWith('-')
      const absValue = cleaned.replace(/^-/, '')
      const parts = absValue.split('.')
      this.integerPart = parts[0] || ''
      this.integerPart = isNegative && this.integerPart ? `-${this.integerPart}` : this.integerPart
      this.decimalPart = this.allowDecimal && parts[1] ? parts[1].slice(0, this.maxDecimals) : ''
    },

    formatNumber() {
      if (!this.integerPart && !this.decimalPart) return ''

      let integer = this.integerPart.replace(/^-/, '')
      if (this.useThousandSeparator) {
        integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
      }
      const isNegative = this.integerPart.startsWith('-')
      let result = isNegative ? `-${integer}` : integer

      if (this.allowDecimal && this.decimalPart) {
        result += '.' + this.decimalPart
      } else if (this.allowDecimal && this.inputValue.endsWith('.')) {
        result += '.'
      }

      return result
    },

    getNumericValue() {
      const cleaned = `${this.integerPart}.${this.decimalPart || '0'}`
      const num = this.allowDecimal ? parseFloat(cleaned) : parseInt(cleaned, 10)
      return isNaN(num) ? null : num
    },

    restrictInput(event) {
      const key = event.key
      const input = this.inputValue || ''

      if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'].includes(key) ||
          (event.ctrlKey || event.metaKey)) {
        return
      }

      if (this.allowDecimal) {
        if (!/[0-9.]/.test(key)) {
          event.preventDefault()
          return
        }
      } else {
        if (!/[0-9-]/.test(key)) {
          event.preventDefault()
          return
        }
      }

      if (key === '.') {
        if (!this.allowDecimal) {
          event.preventDefault()
          return
        }
        if (input.includes('.') || this.decimalPart) {
          event.preventDefault()
          return
        }
        if (!this.integerPart) {
          event.preventDefault()
          return
        }
      }

      if (key === '-') {
        if (!this.allowNegative || input.includes('-')) {
          event.preventDefault()
          return
        }
      }

      if (this.allowDecimal && this.decimalPart && /[0-9]/.test(key)) {
        if (this.decimalPart.length >= this.maxDecimals) {
          event.preventDefault()
          return
        }
      }
    },

    handleInput(event) {
      let value = event.target.value || ''

      value = this.convertPersianToEnglish(value)
      value = value.replace(/,/g, '')

      const regex = this.allowDecimal ? /[^0-9.-]/g : /[^0-9-]/g
      value = value.replace(regex, '')

      // حذف نقاط اضافی
      const parts = value.split('.')
      if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('')
      }

      // اگر نقطه در انتهای عدد باشد، اجازه می‌دهیم باقی بماند
      if (value.endsWith('.')) {
        this.setPartsFromString(value.slice(0, -1))
        this.inputValue = this.formatNumber() + '.'
        return
      }

      this.setPartsFromString(value)
      this.inputValue = this.formatNumber()
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
  margin-right: auto;
}
</style>