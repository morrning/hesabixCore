<template>
    <v-toolbar color="toolbar" :title="$t('dialog.person_with_det_report')">
        <template v-slot:prepend>
            <v-tooltip :text="$t('dialog.back')" location="bottom">
                <template v-slot:activator="{ props }">
                    <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
                        icon="mdi-arrow-right" />
                </template>
            </v-tooltip>
        </template>
    </v-toolbar>
    <v-container fluid>
        <v-row>
            <v-col cols="12" md="4">
                <Hpersonsearch
                    v-model="selectedPerson"
                    label="شخص"
                    :rules="[v => !!v || 'انتخاب شخص الزامی است']"
                />
            </v-col>
            <v-col cols="12" md="4">
                <Hdatepicker
                    v-model="startDate"
                    label="تاریخ شروع"
                    :rules="[v => !!v || 'تاریخ شروع الزامی است']"
                />
            </v-col>
            <v-col cols="12" md="4">
                <Hdatepicker
                    v-model="endDate"
                    label="تاریخ پایان"
                    :rules="[v => !!v || 'تاریخ پایان الزامی است']"
                />
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import Hpersonsearch from '@/components/forms/Hpersonsearch.vue'
import Hdatepicker from '@/components/forms/Hdatepicker.vue'

export default {
    name: 'PersonWithDetReport',
    components: {
        Hpersonsearch,
        Hdatepicker
    },
    data() {
        return {
            selectedPerson: null,
            startDate: '',
            endDate: ''
        }
    },
    methods: {
        // متدهای مورد نیاز گزارش
    },
    mounted() {
        // کدهای اجرایی در زمان بارگذاری کامپوننت
    }
}
</script>

<style scoped>
/* استایل‌های مورد نیاز */
</style>