<template>
  <v-text-field
    v-model="inputValue"
    v-bind="$attrs"
    :class="$attrs.class"
    type="text"
    :rules="combinedRules"
    :error-messages="errorMessages"
    @keypress="restrictToNumbers"
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
        v => !v || /^\d+$/.test(v.replace(/[^0-9]/g, '')) || this.$t('numberinput.invalid_number'),
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
        this.$emit('update:modelValue', 0)
        this.errorMessages = []
      } else {
        const cleaned = String(newVal).replace(/[^0-9]/g, '')
        if (/^\d+$/.test(cleaned)) {
          const numericValue = cleaned ? Number(cleaned) : 0
          this.$emit('update:modelValue', numericValue)
          this.errorMessages = []
        } else {
          this.errorMessages = [this.$t('numberinput.invalid_number')]
        }
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

:deep(.v-text-field .v-input__prepend-inner) {
  padding-right: 0;
  margin-right: 0;
}
</style>