<script lang="ts">
import axios from 'axios';
import { defineComponent } from 'vue'
import Swal from "sweetalert2";
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
export default defineComponent({
    name: "quickAdd",
    props: {
        code: {
            type: String
        },
        btn: {
            default: true,
            type: Boolean
        }
    },
    components: {
        Loading
    },
    data: () => {
        return {
            tabs: '',
            dialog: false,
            loading: true,
            account: {
                name: '',
                cardNum: '',
                shabaNum: '',
                accountNum: ''
            },
            person: {
                nikename: '',
                name: '',
                des: '',
                tel: '',
                mobile: '',
                mobile2: '',
                address: '',
                company: '',
                shenasemeli: '',
                codeeghtesadi: '',
                sabt: '',
                keshvar: '',
                ostan: '',
                shahr: '',
                postalcode: '',
                email: '',
                website: '',
                fax: '',
                code: 0,
                types: [],
                accounts: [],
            },
            personPattern: {
                nikename: '',
                name: '',
                des: '',
                tel: '',
                mobile: '',
                mobile2: '',
                address: '',
                company: '',
                shenasemeli: '',
                codeeghtesadi: '',
                sabt: '',
                keshvar: '',
                ostan: '',
                shahr: '',
                postalcode: '',
                email: '',
                website: '',
                fax: '',
                code: 0,
                types: [],
                accounts: [],
            }
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        addNewcard() {
            this.person.accounts.push({
                cardNum: '',
                accountNum: '',
                shabaNum: '',
                bank: ''
            });
        },
        removeCard(index) {
            this.person.accounts.splice(index, 1);
        },
        loadData(id = '') {
            axios.post('/api/person/types/get').then((response) => {
                this.person.types = response.data;
                this.loading = false;
            });
            this.person.code = 0;
        },
        save() {
            let canSubmit = true;
            if (this.person.mobile.length !== 0) {
                const regex = new RegExp("^(\\+98|0)?9\\d{9}$");
                if (!regex.test(this.person.mobile)) {
                    canSubmit = false;
                    Swal.fire({
                        text: 'شماره موبایل وارد شده نامعتبر است.',
                        icon: 'error',
                        confirmButtonText: 'قبول'
                    });
                }
            }
            if (this.person.nikename.length === 0) {
                canSubmit = false;
                Swal.fire({
                    text: 'نام مستعار الزامی است.',
                    icon: 'error',
                    confirmButtonText: 'قبول'
                });
            }
            this.person.accounts.forEach((item) => {
                if (item.bank == '') {
                    canSubmit = false;
                    Swal.fire({
                        text: 'بخش حساب‌های بانکی به درستی تکمیل نشده است.لطفا موارد الزامی را وارد کنید.',
                        icon: 'error',
                        confirmButtonText: 'قبول'
                    });
                }
            });
            if (canSubmit) {
                this.loading = true;
                axios.post('/api/person/mod/' + this.person.code, this.person).then((response) => {
                    this.loading = false;
                    if (response.data.result == 2) {
                        Swal.fire({
                            text: 'قبلا ثبت شده است.',
                            icon: 'error',
                            confirmButtonText: 'قبول'
                        });
                    }
                    else {
                        Swal.fire({
                            text: 'مشخصات شخص ثبت شد.',
                            icon: 'success',
                            confirmButtonText: 'قبول'
                        }).then(() => {
                            this.person = this.personPattern
                            this.dialog = false;
                        });
                    }
                })
            }

        }
    }
})
</script>

<template>
    <v-btn v-if="$props.btn == true" @click="loadData(); dialog = true"
        icon="mdi-plus" class="text-primary" variant="plain" density="compact" :title="$t('dialog.new')">
    </v-btn>

    <v-dialog v-model="dialog" transition="dialog-bottom-transition" fullscreen>
        <v-card class="bg-white" :loading="loading">
            <v-toolbar color="toolbar" :title="$t('drawer.person_info')">
                <template v-slot:prepend>
                    <v-tooltip :text="$t('dialog.back')" location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn icon="mdi-close" @click="dialog = false"></v-btn>
                        </template>
                    </v-tooltip>
                </template>
                <v-spacer></v-spacer>
                <v-btn :loading="loading" @click="save()" icon="" color="green">
                    <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
                    <v-icon icon="mdi-content-save"></v-icon>
                </v-btn>
                <template v-slot:extension>
                    <v-tabs color="primary" class="bg-light" grow v-model="tabs">
                        <v-tab value="0">
                            {{ $t('pages.person.basic_info') }}
                        </v-tab>
                        <v-tab value="1">
                            {{ $t('pages.person.eco_info') }}
                        </v-tab>
                        <v-tab value="2">
                            {{ $t('pages.person.contact_info') }}
                        </v-tab>
                        <v-tab value="3">
                            {{ $t('pages.person.address') }}
                        </v-tab>
                        <v-tab value="4">
                            {{ $t('pages.person.banks_accounts') }}
                        </v-tab>
                    </v-tabs>
                </template>
            </v-toolbar>
            <v-row class="pa-1">
                <v-col>
                    <v-tabs-window v-model="tabs">
                        <v-tabs-window-item value="0">
                            <v-card>
                                <v-card-text>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 mb-3">
                                            <div class="space-y-2">
                                                <div class="form-check form-switch">
                                                    <input v-model="person.speedAccess" class="form-check-input"
                                                        type="checkbox">
                                                    <label class="form-check-label">دسترسی سریع (در صدور فاکتور سریع
                                                        فروش و سایر
                                                        افزونه‌ها استفاده می
                                                        شود)</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-12 mb-3">
                                            <div class="space-x-2 border rounded p-3">
                                                <p class="py-0 my-0 text-primary">نوع مشتری</p>
                                                <div v-for="(item, index) in person.types"
                                                    class="form-check form-check-inline">
                                                    <input @change="console.log(this.person.types)"
                                                        v-model="person.types[index].checked" checked=""
                                                        class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">{{ item.label }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.nikename" class="form-control" type="text">
                                                <label class="form-label"><span class="text-danger">(لازم)</span> نام
                                                    مستعار</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.company" class="form-control" type="text">
                                                <label class="form-label"> شرکت </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.name" class="form-control" type="text">
                                                <label class="form-label">نام / نام خانوادگی</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.des" class="form-control" type="text">
                                                <label class="form-label">توضیحات</label>
                                            </div>
                                        </div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="1">
                            <v-card>
                                <v-card-text>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.shenasemeli" class="form-control" type="text">
                                                <label class="form-label">شناسه ملی</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.sabt" class="form-control" type="text">
                                                <label class="form-label">شماره ثبت</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.codeeghtesadi" class="form-control" type="text">
                                                <label class="form-label">کد اقتصادی</label>
                                            </div>
                                        </div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="2">
                            <v-card>
                                <v-card-text>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input style="direction: ltr;" v-model="person.mobile"
                                                    class="form-control" type="text">
                                                <label class="form-label">تلفن همراه</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input style="direction: ltr;" v-model="person.mobile2"
                                                    class="form-control" type="text">
                                                <label class="form-label">تلفن همراه دوم</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input style="direction: ltr;" v-model="person.tel" class="form-control"
                                                    type="text">
                                                <label class="form-label">تلفن</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input style="direction: ltr;" v-model="person.fax" class="form-control"
                                                    type="text">
                                                <label class="form-label">فکس</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input style="direction: ltr;" v-model="person.email"
                                                    class="form-control" type="text">
                                                <label class="form-label">پست الکترونیکی</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input style="direction: ltr;" v-model="person.website"
                                                    class="form-control" type="text">
                                                <label class="form-label">وب سایت</label>
                                            </div>
                                        </div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="3">
                            <v-card>
                                <v-card-text>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.keshvar" class="form-control" type="text">
                                                <label class="form-label">کشور</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.ostan" class="form-control" type="text">
                                                <label class="form-label">استان</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.shahr" class="form-control" type="text">
                                                <label class="form-label">شهر</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.postalcode" class="form-control" type="text">
                                                <label class="form-label">کد پستی</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-12">
                                            <div class="form-floating mb-4">
                                                <input v-model="person.address" class="form-control" type="text">
                                                <label class="form-label">آدرس</label>
                                            </div>
                                        </div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="4">
                            <v-card>
                                <v-card-text>
                                    <div class="row mb-3 justify-content-end text-end">
                                        <div class="col-sm-12 col-md-12">
                                            <button @click="addNewcard()" type="button" class="btn btn-primary">
                                                <i class="fa fa-add"></i>
                                                افزودن
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row" v-for="(item, index) in person.accounts">
                                        <div class="block block-rounded border border-gray mx-0 px-0">
                                            <div class="block-header bg-light">
                                                <h3 class="block-title">
                                                    <small class="text-dark">
                                                        <i class="fa fa-bank"></i>
                                                        حساب بانکی
                                                    </small>
                                                </h3>
                                                <span class="block-options">
                                                    <button class="btn rounded-circle btn-sm btn-danger"
                                                        @click="removeCard(index)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </span>
                                            </div>
                                            <div class="block-content">
                                                <div class="row">
                                                    <div class="col-sm-12 col-md-6">
                                                        <div class="form-floating mb-4">
                                                            <input v-model="person.accounts[index].bank"
                                                                class="form-control" type="text">
                                                            <label class="form-label"><span
                                                                    class="text-danger">(لازم)</span> بانک
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-6">
                                                        <div class="form-floating mb-4">
                                                            <input v-model="person.accounts[index].accountNum"
                                                                class="form-control" type="text">
                                                            <label class="form-label">شماره حساب</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-6">
                                                        <div class="form-floating mb-4">
                                                            <input v-model="person.accounts[index].cardNum"
                                                                class="form-control" type="text">
                                                            <label class="form-label">شماره کارت</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-6">
                                                        <div class="form-floating mb-4">
                                                            <input v-model="person.accounts[index].shabaNum"
                                                                class="form-control" type="text">
                                                            <label class="form-label">شماره شبا</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-tabs-window-item>
                    </v-tabs-window>
                </v-col>
            </v-row>
        </v-card>
    </v-dialog>
</template>

<style scoped></style>