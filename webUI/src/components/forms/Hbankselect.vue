<template>
    <v-select v-bind="$attrs" :items="banks" :model-value="modelValue" :item-title="itemTitle" :item-value="itemValue"
        :return-object="returnObject" @update:model-value="handleChange" />
</template>

<script>
import axios from 'axios'

export default {
    name: 'HBankSelect',
    inheritAttrs: false,
    props: {
        modelValue: {
            type: [String, Number, Object],
            default: null
        },
        returnObject: {
            type: Boolean,
            default: false
        },
        itemTitle: {
            type: String,
            default: 'name'
        },
        itemValue: {
            type: String,
            default: 'id'
        }
    },
    data() {
        return {
            banks: []
        }
    },
    created() {
        this.fetchBanks()
    },
    methods: {
        async fetchBanks() {
            try {
                const response = await axios.post('/api/bank/list')
                this.banks = response.data
            } catch (error) {
                console.error('خطا در دریافت لیست بانک‌ها:', error)
            }
        },
        handleChange(value) {
            this.$emit('update:modelValue', value)
        }
    }
}
</script>