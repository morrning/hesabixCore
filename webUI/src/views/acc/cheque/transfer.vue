<template>
    <v-toolbar color="toolbar" title="واگذاری چک">
        <template v-slot:prepend>
            <v-tooltip :text="$t('dialog.back')" location="bottom">
                <template v-slot:activator="{ props }">
                    <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
                        icon="mdi-arrow-right" />
                </template>
            </v-tooltip>
        </template>
        <template v-slot:append>
            <v-tooltip text="ثبت واگذاری" location="bottom">
                <template v-slot:activator="{ props }">
                    <v-btn color="success" v-bind="props" @click="submitForm" :loading="loading" variant="text"
                        icon="mdi-content-save" />
                </template>
            </v-tooltip>
        </template>
    </v-toolbar>
    <v-container>
        <v-row>
            <v-col cols="12">
                <v-expansion-panels>
                    <v-expansion-panel>
                        <v-expansion-panel-title>
                            <v-icon start>mdi-information</v-icon>
                            اطلاعات چک
                        </v-expansion-panel-title>
                        <v-expansion-panel-text>
                            <v-row>
                                <v-col cols="12" sm="6">
                                    <div class="text-subtitle-2 text-medium-emphasis">شماره چک</div>
                                    <div class="text-body-1 text-primary">{{ chequeInfo.number }}</div>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <div class="text-subtitle-2 text-medium-emphasis">کد صیاد</div>
                                    <div class="text-body-1 text-primary">{{ chequeInfo.sayadNum }}</div>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <div class="text-subtitle-2 text-medium-emphasis">مبلغ ({{ activeMoneyShortName }})</div>
                                    <div class="text-body-1 text-primary">{{ $filters.formatNumber(chequeInfo.amount) }}</div>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <div class="text-subtitle-2 text-medium-emphasis">تاریخ</div>
                                    <div class="text-body-1 text-primary">{{ chequeInfo.datePay }}</div>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <div class="text-subtitle-2 text-medium-emphasis">پرداخت کننده</div>
                                    <div class="text-body-1 text-primary">{{ chequeInfo.person?.nikename }}</div>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <div class="text-subtitle-2 text-medium-emphasis">بانک</div>
                                    <div class="text-body-1 text-primary">{{ chequeInfo.chequeBank }}</div>
                                </v-col>
                            </v-row>
                        </v-expansion-panel-text>
                    </v-expansion-panel>
                </v-expansion-panels>

                <v-form @submit.prevent="submitForm" class="mt-4">
                    <v-row>
                        <v-col cols="12">
                            <v-switch
                                v-model="sendSms"
                                label="ارسال پیامک به گیرنده"
                                color="primary"
                                hide-details
                            ></v-switch>
                        </v-col>
                        <v-col cols="12">
                            <Hpersonsearch v-model="selectedPerson" label="شخص گیرنده" :rules="[v => !!v || 'انتخاب شخص گیرنده الزامی است']" required />
                        </v-col>

                        <v-col cols="12">
                            <Hdatepicker v-model="transferDate" label="تاریخ واگذاری" :min="year.start" :max="year.end"
                                required />
                        </v-col>

                        <v-col cols="12">
                            <v-text-field v-model="description" label="توضیحات" variant="outlined" />
                        </v-col>
                    </v-row>
                </v-form>
            </v-col>
        </v-row>
    </v-container>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
        {{ snackbar.text }}
        <template v-slot:actions>
            <v-btn color="white" variant="text" @click="snackbar.show = false">
                بستن
            </v-btn>
        </template>
    </v-snackbar>
</template>

<script>
import axios from "axios";
import Hdatepicker from '@/components/forms/Hdatepicker.vue';
import Hpersonsearch from '@/components/forms/Hpersonsearch.vue';

export default {
    name: "cheque-transfer",
    components: {
        Hdatepicker,
        Hpersonsearch
    },
    data: () => ({
        loading: false,
        selectedPerson: null,
        transferDate: '',
        description: '',
        sendSms: false,
        persons: [],
        year: {
            start: '',
            end: '',
            now: ''
        },
        snackbar: {
            show: false,
            text: '',
            color: 'success'
        },
        chequeInfo: {
            number: '',
            sayadNum: '',
            amount: 0,
            datePay: '',
            person: {
                nikename: ''
            },
            chequeBank: ''
        }
    }),
    computed: {
        activeMoneyShortName() {
            return window.localStorage.getItem('activeMoneyShortName') || '';
        }
    },
    methods: {
        async loadData() {
            try {
                this.loading = true;
                const [personsResponse, yearResponse, chequeResponse] = await Promise.all([
                    axios.post('/api/person/list'),
                    axios.post('/api/year/get'),
                    axios.post(`/api/cheque/input/get/${this.$route.params.id}`)
                ]);

                this.persons = personsResponse.data.items;
                this.year = yearResponse.data;
                this.transferDate = yearResponse.data.now;
                this.chequeInfo = {
                    number: chequeResponse.data.number,
                    sayadNum: chequeResponse.data.sayadNumber,
                    amount: chequeResponse.data.amount,
                    datePay: chequeResponse.data.dueDate,
                    person: {
                        nikename: chequeResponse.data.person.name
                    },
                    chequeBank: chequeResponse.data.bankoncheque
                };

                // Load SMS notification preference from localStorage
                const smsPreference = localStorage.getItem('chequeTransferSmsNotification');
                this.sendSms = smsPreference === 'true';
            } catch (error) {
                console.error('خطا در بارگذاری اطلاعات:', error);
                this.snackbar = {
                    show: true,
                    text: 'خطا در بارگذاری اطلاعات',
                    color: 'error'
                };
            } finally {
                this.loading = false;
            }
        },
        async submitForm() {
            if (!this.selectedPerson) {
                this.snackbar = {
                    show: true,
                    text: 'لطفا شخص گیرنده را انتخاب کنید',
                    color: 'error'
                };
                return;
            }

            try {
                this.loading = true;
                // Save SMS notification preference to localStorage
                localStorage.setItem('chequeTransferSmsNotification', this.sendSms);

                await axios.post(`/api/cheque/transfer/${this.$route.params.id}`, {
                    personId: this.selectedPerson,
                    date: this.transferDate,
                    description: this.description,
                    sendSms: this.sendSms
                });

                this.snackbar = {
                    show: true,
                    text: 'واگذاری چک با موفقیت ثبت شد',
                    color: 'success'
                };

                setTimeout(() => {
                    this.$router.push('/acc/cheque/list');
                }, 1500);
            } catch (error) {
                console.error('خطا در ثبت اطلاعات:', error);
                this.snackbar = {
                    show: true,
                    text: 'خطا در ثبت اطلاعات',
                    color: 'error'
                };
            } finally {
                this.loading = false;
            }
        }
    },
    beforeMount() {
        this.loadData();
    }
}
</script>