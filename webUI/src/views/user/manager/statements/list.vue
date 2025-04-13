<template>
    <v-toolbar color="toolbar">
        <v-toolbar-title>لیست اطلاعیه‌ها</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            to="/profile/manager/statments/mod"
        >
            افزودن اطلاعیه جدید
        </v-btn>
    </v-toolbar>
    <v-container class="pa-0 ma-0">
        <v-card>
            <v-card-text>
                <v-data-table
                    :headers="headers"
                    :items="statements"
                    :loading="loading"
                    class="elevation-1"
                    density="comfortable"
                >
                    <template v-slot:item.actions="{ item }">
                        <v-btn
                            icon
                            color="primary"
                            variant="text"
                            @click="editStatement(item)"
                            density="comfortable"
                        >
                            <v-icon>mdi-pencil</v-icon>
                            <v-tooltip activator="parent" location="top">ویرایش</v-tooltip>
                        </v-btn>
                        <v-btn
                            icon
                            color="error"
                            variant="text"
                            @click="deleteStatement(item)"
                            density="comfortable"
                        >
                            <v-icon>mdi-delete</v-icon>
                            <v-tooltip activator="parent" location="top">حذف</v-tooltip>
                        </v-btn>
                    </template>
                </v-data-table>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import axios from 'axios'

export default defineComponent({
    name: 'list',
    data() {
        return {
            loading: false,
            statements: [],
            headers: [
                { title: 'عنوان', key: 'title', sortable: true },
                { title: 'تاریخ ارسال', key: 'dateSubmit', sortable: true },
                { title: 'عملیات', key: 'actions', sortable: false, align: 'center' as const }
            ]
        }
    },
    methods: {
        async fetchStatements() {
            this.loading = true
            try {
                const response = await axios.get('/api/admin/statement/list')
                this.statements = response.data
            } catch (error) {
                console.error('Error fetching statements:', error)
            } finally {
                this.loading = false
            }
        },
        editStatement(item: any) {
            if (!item.id) {
                console.error('Invalid statement ID')
                return
            }
            this.$router.push(`/profile/manager/statments/mod/${item.id}`)
        },
        async deleteStatement(item: any) {
            if (!item.id) {
                console.error('Invalid statement ID')
                return
            }

            if (confirm('آیا از حذف این اطلاعیه اطمینان دارید؟')) {
                this.loading = true
                try {
                    await axios.delete(`/api/admin/statement/mod/${item.id}`)
                    await this.fetchStatements()
                } catch (error) {
                    console.error('Error deleting statement:', error)
                } finally {
                    this.loading = false
                }
            }
        }
    },
    mounted() {
        this.fetchStatements()
    }
})
</script>

<style scoped>
.v-card {
    margin-top: 20px;
}
</style>
