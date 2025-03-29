import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from "axios";
import "./registerServiceWorker";
import { vMaska } from "maska/vue"
import VueApexCharts from "vue3-apexcharts";
import Uploader from 'vue-media-upload';
import DateFnsJalaliAdapter from '@date-io/date-fns-jalali';
import faIR from 'date-fns-jalali/locale/fa-IR';

//pinia
import { createPinia } from 'pinia'
const pinia = createPinia();

import CKEditor from '@ckeditor/ckeditor5-vue';
// Import translations for the Persian language.
import '@ckeditor/ckeditor5-build-classic/build/translations/fa';

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import * as labsComponents from 'vuetify/labs/components'
import colors from 'vuetify/util/colors'
//i18n imports
import { createVueI18nAdapter } from 'vuetify/locale/adapters/vue-i18n'
import { createI18n, useI18n } from 'vue-i18n'
import i18n from '@/i18n/i18n'

// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { aliases, mdi } from "vuetify/iconsets/mdi";

const vuetify = createVuetify({
    defaults: {
        VNavigationDrawer: {
            color: 'primaryLight',
            VCard: {
                color: 'indigo-darken-2'
            },
            VList: {
                density: 'compact',
                VListGroup: {
                    density: 'compact',
                    VListItem: {
                        class: 'text-indigo-darken-4',
                        VListItemAction: {
                            class: 'text-caption text-danger'
                        },
                        VBtn: {
                            class: 'text-primary',
                            variant: 'flat'
                        },
                    },
                }
            },

        },
        VTextField: {
            hideDetails: "auto",
            variant: "outlined",
            size: "small",
            density: "comfortable"
        },
        VTextarea: {
            hideDetails: "auto",
            density: "comfortable"
        },
        VSelect: {
            hideDetails: "auto",
            variant: "outlined",
            density: "comfortable"
        },
        VDialog: {
            VCard: {
            }
        },
        VToolbar: {
            density: 'compact'
        },
        VAppBar: {
            color: 'indigo-darken-2'
        },
        VMenu: {
            VList: {
                density: "compact"
            }
        }
    },
    components: {
        ...components,
        ...labsComponents
    },
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
    locale: {
        adapter: createVueI18nAdapter({ i18n, useI18n }),
    },
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                dark: false,
                colors: {
                    primary: '#044792', // #E53935
                    secondary: colors.grey.darken4, // #FFCDD2
                    danger: colors.red.darken3,
                    error: colors.red.darken3,
                    dangerLight: colors.red.lighten4,
                    successLight: colors.green.lighten4,
                    primaryLight: '#f8f9fc',
                    primaryLight2: '#edf1fc',
                    success: '#457237',
                    toolbar: colors.indigo.lighten5,
                    primaryDark: colors.indigo.darken4,
                    btnAdd: '#5696bd'
                }
            },
        },
    },
    date: {
        adapter: DateFnsJalaliAdapter,
        locale: {
            fa:faIR // تنظیم زبان فارسی
        },
    },
});

// @ts-ignore
import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { LoadingPlugin } from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import '@/css/main.css';

import money from 'v-money3';
import Vue3PersianDatetimePicker from 'vue3-persian-datetime-picker';
import Swal from "sweetalert2";
import { getApiUrl } from "@/hesabixConfig";
const app = createApp(App)
app.component('EasyDataTable', Vue3EasyDataTable);
app.component('DatePicker', Vue3PersianDatetimePicker);
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';

app.component('v-cob', vSelect)
import Hdatepicker from "@/components/forms/Hdatepicker.vue";
import calendarLocalConfig from "@/i18n/calendarLocalConfig";
app.component('h-date-picker', Hdatepicker);
app.use(CKEditor)
app.use(Vue3PersianDatetimePicker, {
    name: 'CustomDatePicker',
    props: {
        locale: localStorage.getItem('UI_LANG'),
        color: "#3059ab",
        format: calendarLocalConfig.format(localStorage.getItem('UI_CALENDAR')),
        displayFormat: calendarLocalConfig.format(localStorage.getItem('UI_CALENDAR')),
        editable: false,
        autoSubmit: true,
        localeConfig: calendarLocalConfig.data().localeConfig
    }
})
axios.defaults.headers.common['X-AUTH-TOKEN'] = localStorage.getItem('X-AUTH-TOKEN');
app.config.globalProperties.$API_URL = getApiUrl();
axios.defaults.baseURL = app.config.globalProperties.$API_URL;
axios.defaults.withCredentials = true;
axios.defaults.headers.common['activeBid'] = localStorage.getItem('activeBid');
axios.defaults.headers.common['activeYear'] = localStorage.getItem('activeYear');
axios.defaults.headers.common['activeMoney'] = localStorage.getItem('activeMoney');
axios.interceptors.request.use(function (config) {
    // Do something before request is sent
    return config;
}, function (error) {
    // Do something with request error
    console.log('Error');
    return Promise.reject(error);
});

axios.interceptors.response.use(function (response) {
    // Do something with response data
    return response;
}, function (error) {
    if (error.code === 404) {
        // Do something with response error
        Swal.fire({
            text: 'اتصال با سرویس دهنده برقرار نشد. لطفا اتصال اینترنت خود را بررسی نمایید.',
            icon: 'error',
            confirmButtonText: 'قبول'
        });
    }
    return Promise.reject(error);
});
app.use(router)
app.use(money)
app.use(LoadingPlugin)
app.use(pinia)
app.use(i18n)
app.use(vuetify);
app.use(VueApexCharts);
app.directive("maska", vMaska);
app.component('Uploader', Uploader);
//global methods
app.config.globalProperties.$filters = {
    formatNumber(value: any, moneyLabel: any = false) {
        let result = parseFloat(value).toFixed(0).toString();
        result = result.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (moneyLabel == true) {
            result = result + ' ' + localStorage.getItem('activeMoneyShortName');
        }
        return result;
    },
    onlyNumber($event: any) {
        let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
        if ((keyCode < 48 || keyCode > 57)) { // 46 is dot
            $event.preventDefault();
        }
    },
    getApiUrl() {
        return getApiUrl();
    },
    getActiveMoney() {
        return {
            name: localStorage.getItem('activeMoney'),
            symbol: localStorage.getItem('activeMoneySymbol'),
            shortName: localStorage.getItem('activeMoneyShortName'),
        };
    },
    async isLogin() {
        let result = await axios.post('/api/user/check/login');
        return result.status === 200;
    }
}

app.config.globalProperties.app_isLogin = async () => {
    let result = await axios.post('/api/user/check/login');
    if (result.status === 200) {
        return result.data.result;
    } else {
        return false;
    }
}

app.config.compilerOptions.isCustomElement = (tag) => {
    return ['transition'].includes(tag)
}
app.mount('#page-container')