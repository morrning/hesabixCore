<template>
    <v-toolbar color="toolbar">
        <v-toolbar-title>{{ isEdit ? 'ویرایش اطلاعیه' : 'افزودن اطلاعیه جدید' }}</v-toolbar-title>
        <v-spacer></v-spacer>
    </v-toolbar>
    <v-container class="pa-0 ma-0">
        <v-card>
            <v-card-text>
                <v-form ref="form" @submit.prevent="submitForm">
                    <v-text-field
                        v-model="form.title"
                        label="عنوان"
                        :rules="[v => !!v || 'عنوان الزامی است']"
                        required
                        variant="outlined"
                        density="comfortable"
                        class="mb-3"
                    ></v-text-field>

                    <v-textarea
                        v-model="form.body"
                        label="متن اطلاعیه"
                        :rules="[v => !!v || 'متن اطلاعیه الزامی است']"
                        required
                        variant="outlined"
                        density="comfortable"
                        class="mb-3"
                        auto-grow
                        rows="4"
                    ></v-textarea>

                    <Hdatepicker
                        v-model="form.dateSubmit"
                        label="تاریخ ارسال"
                        :rules="[(v: string) => !!v || 'تاریخ ارسال الزامی است']"
                        required
                        variant="outlined"
                        density="comfortable"
                        class="mb-3"
                    />

                    <v-btn
                        type="submit"
                        color="primary"
                        :loading="loading"
                        class="mt-4"
                        block
                        size="large"
                    >
                        {{ isEdit ? 'ویرایش' : 'افزودن' }}
                    </v-btn>
                </v-form>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import Hdatepicker from '@/components/forms/Hdatepicker.vue'

export default defineComponent({
    name: 'mod',
    components: {
        Hdatepicker
    },
    data() {
        return {
            loading: false,
            form: {
                title: '',
                body: '',
                dateSubmit: ''
            }
        }
    },
    computed: {
        isEdit() {
            return !!this.$route.params.id
        }
    },
    methods: {
        async submitForm() {
            const { valid } = await (this.$refs.form as any).validate()
            
            if (!valid) {
                return
            }

            this.loading = true
            try {
                if (this.isEdit) {
                    if (!this.$route.params.id) {
                        console.error('Invalid statement ID')
                        return
                    }
                    await axios.put(`/api/admin/statement/mod/${this.$route.params.id}`, this.form)
                } else {
                    await axios.post('/api/admin/statement/mod', this.form)
                }
                this.$router.push('/profile/manager/statments/list')
            } catch (error) {
                console.error('Error submitting form:', error)
            } finally {
                this.loading = false
            }
        },
        async fetchStatement() {
            if (this.isEdit) {
                if (!this.$route.params.id) {
                    console.error('Invalid statement ID')
                    return
                }

                this.loading = true
                try {
                    const response = await axios.get(`/api/admin/statement/mod/${this.$route.params.id}`)
                    this.form = response.data
                } catch (error) {
                    console.error('Error fetching statement:', error)
                } finally {
                    this.loading = false
                }
            }
        }
    },
    mounted() {
        this.fetchStatement()
    }
})
</script>

<style scoped>
.v-card {
    margin-top: 20px;
}
</style>
