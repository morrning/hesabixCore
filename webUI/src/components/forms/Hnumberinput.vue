<template>
  <v-text-field
    v-model="inputValue"
    v-bind="$attrs"
    type="text"
    :rules="combinedRules"
    @keypress="restrictToNumbers"
    dir="ltr"
    dense
  />
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
    }
  },

  data() {
    return {
      inputValue: ''
    }
  },

  computed: {
    combinedRules() {
      return [
        v => !v || /^\d+$/.test(v.replace(/[^0-9]/g, '')) || 'فقط عدد انگلیسی مجاز است',
        ...this.rules
      ]
    }
  },

  watch: {
    modelValue: {
      immediate: true,
      handler(newVal) {
        if (newVal !== null && newVal !== undefined) {
          const cleaned = String(newVal).replace(/[^0-9]/g, '')
          this.inputValue = cleaned ? Number(cleaned).toLocaleString('en-US') : ''
        } else {
          this.inputValue = ''
        }
      }
    },
    inputValue(newVal) {
      if (newVal === '' || newVal === null || newVal === undefined) {
        this.$emit('update:modelValue', 0) // وقتی خالی است، صفر ارسال شود
      } else {
        const cleaned = String(newVal).replace(/[^0-9]/g, '')
        const numericValue = cleaned ? Number(cleaned) : 0
        this.$emit('update:modelValue', numericValue)
      }
    }
  },

  methods: {
    restrictToNumbers(event) {
      const charCode = event.charCode
      if (charCode < 48 || charCode > 57) {
        event.preventDefault()
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
</style>