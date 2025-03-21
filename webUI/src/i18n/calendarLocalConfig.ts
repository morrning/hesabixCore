import moment from 'moment'
export default {
    data() {
        return {
            localeConfig: {
                fa: {
                    daw: 6,
                    displayFormat: 'jYYYY/jMM/jDD',
                    lang: { label: 'FA' },
                    dir: 'rtl',
                },
                ar: {
                    dow: 0,
                    dir: 'rtl',
                    lang: {
                        label: 'AR',
                        submit: 'اختيار',
                        cancel: 'إلغاء',
                        now: 'الآن',
                        nextMonth: 'الشهر القادم',
                        prevMonth: 'الشهر الماضي'
                    }
                },
                en: {
                    daw:0,
                    dir: 'ltr',
                    lang: {
                        label:     'En',
                        submit:    'Select',
                        cancel:    'Cancel',
                        now:       'Now',
                        nextMonth: 'Next month',
                        prevMonth: 'Previous month',
                    }
                }
            }
        }
    },
    format(CalendarType:any){
        if(CalendarType == 'shamsi') return 'JYYY/JMM/JDD';
        else if(CalendarType == 'gregorian') return 'YYY/MM/DD';
    }
}
