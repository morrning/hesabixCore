import { createRouter, createWebHashHistory, createWebHistory } from 'vue-router'
import HomeView from '../views/dashboard.vue'
import PersonHome from '../views/persons/list.vue'
const router = createRouter({
    history: createWebHashHistory(
        import.meta.env.BASE_URL),
    routes: [{
            path: '/',
            name: 'app_home',
            component: HomeView
        },
        {
            path: '/acc/business/printtemplates',
            name: 'business_printtemplates',
            component: () =>
                import ('../views/printers/templates.vue')
        },
        {
            path: '/acc/printers/list',
            name: 'printers_list',
            component: () =>
                import ('../views/printers/list.vue')
        }, {
            path: '/acc/reports/list',
            name: 'reports_list',
            component: () =>
                import ('../views/reports/reports.vue')
        },
        {
            path: '/acc/reports/persons/debtors',
            name: 'person_debtors_list',
            component: () =>
                import ('../views/reports/persons/debtors.vue'),
        },
        {
            path: '/acc/reports/acc/balance_sheet',
            name: 'acc_balanceSheet_list',
            component: () =>
                import ('../views/reports/balanceSheet.vue'),
        },
        {
            path: '/acc/reports/commodity/buysell',
            name: 'commodity_report_buysell',
            component: () =>
                import ('../views/reports/commodity/buysellByCommodity.vue'),
        },
        {
            path: '/acc/reports/persons/depositors',
            name: 'person_depositors_list',
            component: () =>
                import ('../views/reports/persons/depositors.vue'),
        },
        {
            path: '/acc/reports/persons/buysell',
            name: 'person_buysell_by_person',
            component: () =>
                import ('../views/reports/persons/buysellByPerson.vue'),
        },
        {
            path: '/acc/costs/list',
            name: 'costs_list',
            component: () =>
                import ('../views/costs/list.vue'),
        },
        {
            path: '/acc/costs/mod/:id?',
            name: 'costs_mod',
            component: () =>
                import ('../views/costs/mod.vue'),
        },
        {
            path: '/acc/incomes/list',
            name: 'incomes_list',
            component: () =>
                import ('../views/incomes/list.vue'),
        },
        {
            path: '/acc/incomes/mod/:id?',
            name: 'incomes_mod',
            component: () =>
                import ('../views/incomes/mod.vue'),
        },
        {
            path: '/acc/accounting/list',
            name: 'accounting_list_doc',
            component: () =>
                import ('../views/accounting/list.vue'),
        },
        {
            path: '/acc/accounting/table',
            name: 'accounting_table',
            component: () =>
                import ('../views/accounting/table.vue'),
        },
        {
            path: '/acc/accounting/close_year',
            name: 'accounting_close_year',
            component: () =>
                import ('../views/accounting/closeyear.vue'),
        },
        {
            path: '/acc/accounting/view/:id?',
            name: 'accounting_view_doc',
            component: () =>
                import ('../views/accounting/viewDoc.vue'),
        },
        {
            path: '/acc/banks/list',
            name: 'banks_list',
            component: () =>
                import ('../views/bank/list.vue')
        },
        {
            path: '/acc/banks/card/view/:id?',
            name: 'bank_card_view',
            component: () =>
                import ('../views/bank/card.vue'),
        },
        {
            path: '/acc/banks/mod/:id?',
            name: 'bank_mod',
            component: () =>
                import ('../views/bank/mod.vue'),
        },
        {
            path: '/acc/salary/list',
            name: 'salary_list',
            component: () =>
                import ('../views/salary/list.vue')
        },
        {
            path: '/acc/salary/card/view/:id?',
            name: 'salary_card_view',
            component: () =>
                import ('../views/salary/card.vue'),
        },
        {
            path: '/acc/salary/mod/:id?',
            name: 'salary_mod',
            component: () =>
                import ('../views/salary/mod.vue'),
        },
        {
            path: '/acc/wallet/view',
            name: 'wallet_view',
            component: () =>
                import ('../views/wallet/view.vue')
        },
        {
            path: '/acc/cashdesk/list',
            name: 'cashdesk_list',
            component: () =>
                import ('../views/cashdesk/list.vue')
        },
        {
            path: '/acc/cashdesk/card/view/:id?',
            name: 'cashdesk_card_view',
            component: () =>
                import ('../views/cashdesk/card.vue'),
        },
        {
            path: '/acc/cashdesk/mod/:id?',
            name: 'cashdesk_mod',
            component: () =>
                import ('../views/cashdesk/mod.vue'),
        },
        {
            path: '/acc/transfer/list',
            name: 'transfer_list',
            component: () =>
                import ('../views/transfer/list.vue')
        },
        {
            path: '/acc/transfer/mod/:id?',
            name: 'transfer_mod',
            component: () =>
                import ('../views/transfer/mod.vue'),
        },
        {
            path: '/acc/persons/receive/list',
            name: 'person_receive_list',
            component: () =>
                import ('../views/persons/receive/list.vue')
        },
        {
            path: '/acc/persons/send/list',
            name: 'person_send_list',
            component: () =>
                import ('../views/persons/send/list.vue')
        },
        {
            path: '/acc/persons/receive/mod/:id?',
            name: 'person_receive_mod',
            component: () =>
                import ('../views/persons/receive/mod.vue'),
        },
        {
            path: '/acc/persons/send/mod/:id?',
            name: 'person_send_mod',
            component: () =>
                import ('../views/persons/send/mod.vue'),
        },
        {
            path: '/acc/persons/card/view/:id?',
            name: 'person_card_view',
            component: () =>
                import ('../views/persons/card.vue'),
        },
        {
            path: '/acc/persons/list',
            name: 'person_list',
            component: PersonHome
        },
        {
            path: '/acc/persons/mod/:id?',
            name: 'person_new',
            component: () =>
                import ('../views/persons/insert.vue'),
        },
        {
            path: '/acc/business/settings',
            name: 'business_settings',
            component: () =>
                import ('../views/settings/bussiness.vue'),
        },
        {
            path: '/acc/business/printoptions',
            name: 'print_settings',
            component: () =>
                import ('../views/settings/print.vue'),
        },
        {
            path: '/acc/business/avatar',
            name: 'business_avatar',
            component: () =>
                import ('../views/settings/avatar.vue'),
        },
        {
            path: '/acc/business/extramoneys',
            name: 'business_extramoneys',
            component: () =>
                import ('../views/settings/extramoneys.vue'),
        },
        {
            path: '/acc/business/logs',
            name: 'business_logs',
            component: () =>
                import ('../views/settings/logs.vue'),
        },
        {
            path: '/acc/business/apis',
            name: 'business_apis',
            component: () =>
                import ('../views/api/list.vue'),
        },
        {
            path: '/acc/business/users',
            name: 'business_users',
            component: () =>
                import ('../views/settings/user_rolls.vue'),
        },
        {
            path: '/acc/business/user/roll/edit/:email',
            name: 'business_user_roll_edit',
            component: () =>
                import ('../views/settings/user_perm_edit.vue'),
        },
        {
            path: '/acc/commodity/cat/list',
            name: 'commodity_cat_list',
            component: () =>
                import ('../views/commodity/cat/list.vue'),
        },
        {
            path: '/acc/commodity/pricelist/list',
            name: 'commodity_pricelist_list',
            component: () =>
                import ('../views/commodity/priceList/list.vue'),
        },
        {
            path: '/acc/commodity/pricelist/mod/:id?',
            name: 'commodity_pricelist_mod',
            component: () =>
                import ('../views/commodity/priceList/mod.vue'),
        },
        {
            path: '/acc/commodity/pricelist/view/:id?',
            name: 'commodity_pricelist_view',
            component: () =>
                import ('../views/commodity/priceList/view.vue'),
        }, {
            path: '/acc/commodity/pricelist/list/mod/:id?',
            name: 'commodity_pricelist_list_mod',
            component: () =>
                import ('../views/commodity/priceList/pricelistedit.vue'),
        },
        {
            path: '/acc/commodity/drop/list',
            name: 'commodity_drop_list',
            component: () =>
                import ('../views/commodity/drop/list.vue'),
        },
        {
            path: '/acc/commodity/drop/mod/:id?',
            name: 'commodity_drop_mod',
            component: () =>
                import ('../views/commodity/drop/mod.vue'),
        },
        {
            path: '/acc/commodity/list',
            name: 'commodity_list',
            component: () =>
                import ('../views/commodity/list.vue'),
        },
        {
            path: '/acc/commodity/mod/:id?',
            name: 'commodity_mod',
            component: () =>
                import ('../views/commodity/mod.vue'),
        },
        {
            path: '/acc/cheque/mod/:id?',
            name: 'cheque_mod',
            component: () =>
                import ('../views/cheque/mod.vue'),
        },
        {
            path: '/acc/cheque/list',
            name: 'cheque_list',
            component: () =>
                import ('../views/cheque/list.vue'),
        },
        {
            path: '/acc/buy/mod/:id?',
            name: 'buy_mod',
            component: () =>
                import ('../views/buy/mod.vue'),
        },
        {
            path: '/acc/buy/list',
            name: 'buy_list',
            component: () =>
                import ('../views/buy/list.vue'),
        },
        {
            path: '/acc/buy/view/:id?',
            name: 'buy_view',
            component: () =>
                import ('../views/buy/viewInvoice.vue'),
        },
        {
            path: '/acc/sell/mod/:id?',
            name: 'sell_mod',
            component: () =>
                import ('../views/sell/mod.vue'),
        },
        {
            path: '/acc/sell/fast-mod/:id?',
            name: 'sell_fast_mod',
            component: () =>
                import ('../views/sell/fastMod.vue'),
        },
        {
            path: '/acc/sell/list',
            name: 'sell_list',
            component: () =>
                import ('../views/sell/list.vue'),
        },
        {
            path: '/acc/sell/view/:id?',
            name: 'sell_view',
            component: () =>
                import ('../views/sell/viewInvoice.vue'),
        },
        {
            path: '/acc/presell/mod/:id?',
            name: 'presell_mod',
            component: () =>
                import ('../views/presell/mod.vue'),
        },
        {
            path: '/acc/presell/list',
            name: 'presell_list',
            component: () =>
                import ('../views/presell/list.vue'),
        },
        {
            path: '/acc/presell/view/:id?',
            name: 'presell_view',
            component: () =>
                import ('../views/presell/viewInvoice.vue'),
        },
        {
            path: '/acc/rfbuy/mod/:id?',
            name: 'rfbuy_mod',
            component: () =>
                import ('../views/rfbuy/mod.vue'),
        },
        {
            path: '/acc/rfbuy/list',
            name: 'rfbuy_list',
            component: () =>
                import ('../views/rfbuy/list.vue'),
        },
        {
            path: '/acc/rfbuy/view/:id?',
            name: 'rfbuy_view',
            component: () =>
                import ('../views/rfbuy/viewInvoice.vue'),
        },
        {
            path: '/acc/rfsell/mod/:id?',
            name: 'rfsell_mod',
            component: () =>
                import ('../views/rfsell/mod.vue'),
        },
        {
            path: '/acc/rfsell/list',
            name: 'rfsell_list',
            component: () =>
                import ('../views/rfsell/list.vue'),
        },
        {
            path: '/acc/rfsell/view/:id?',
            name: 'rfsell_view',
            component: () =>
                import ('../views/rfsell/viewInvoice.vue'),
        },
        {
            path: '/acc/plugin-center/list',
            name: 'plugin_center_list',
            component: () =>
                import ('../views/store/plugin-world.vue'),
        },
        {
            path: '/acc/plugin-center/my',
            name: 'plugin_center_my',
            component: () =>
                import ('../views/store/plugin-my.vue'),
        },
        {
            path: '/acc/plugin-center/invoice',
            name: 'plugin_center_invoice',
            component: () =>
                import ('../views/store/plugin-invoice.vue'),
        },
        {
            path: '/acc/plugin-center/view-end/:id?',
            name: 'plugin_center_view_prodect',
            component: () =>
                import ('../views/store/viewProdect.vue'),
        },
        {
            path: '/acc/plugins/apartemanma/intro',
            name: 'plugin_apartemanma_intro',
            component: () =>
                import ('../views/plugins/amartemanma/intro.vue'),
        },
        {
            path: '/acc/plugins/accpro/intro',
            name: 'plugin_accpro_intro',
            component: () =>
                import ('../views/plugins/accpro/intro.vue'),
        },
        {
            path: '/acc/plugins/repservice/intro',
            name: 'plugin_repservice_intro',
            component: () =>
                import ('../views/plugins/repservice/intro.vue'),
        }, {
            path: '/acc/plugin/repservice/order/mod/:id?',
            name: 'plugin_repservice_order_mod',
            component: () =>
                import ('../views/plugins/repservice/mod.vue'),
        },
        {
            path: '/acc/plugin/repservice/order/view/:id?',
            name: 'plugin_repservice_order_view',
            component: () =>
                import ('../views/plugins/repservice/view.vue'),
        },
        {
            path: '/acc/plugin/repservice/order/list',
            name: 'plugin_repservice_order_list',
            component: () =>
                import ('../views/plugins/repservice/list.vue'),
        },
        {
            path: '/acc/plugins/restamap/intro',
            name: 'plugin_restamap_intro',
            component: () =>
                import ('../views/plugins/resamap/intro.vue'),
        },
        {
            path: '/acc/plugins/noghre/intro',
            name: 'plugin_noghre_intro',
            component: () =>
                import ('../views/plugins/noghre/intro.vue'),
        },
        {
            path: '/acc/plugins/cc/intro',
            name: 'plugin_cc_intro',
            component: () =>
                import ('../views/plugins/cc/intro.vue'),
        },
        {
            path: '/acc/plugins/onlinestore/intro',
            name: 'plugin_onlinestore_intro',
            component: () =>
                import ('../views/plugins/onlinestore/intro.vue'),
        },
        {
            path: '/acc/notifications/list',
            name: 'notification_list',
            component: () =>
                import ('../views/notifications/notifications.vue'),
        },
        {
            path: '/acc/sms/panel',
            name: 'sms_panel_dashboard',
            component: () =>
                import ('../views/smspanel/smspanel.vue'),
        },
        {
            path: '/acc/plugin/noghre/employees/list',
            name: 'plugin_noghre_employees_list',
            component: () =>
                import ('../views/plugins/noghre/employess/list.vue'),
        },
        {
            path: '/acc/plugin/noghre/employees/mod/:id?',
            name: 'plugin_noghre_employees_mod',
            component: () =>
                import ('../views/plugins/noghre/employess/mod.vue'),
        },
        {
            path: '/acc/plugin/noghre/order/list',
            name: 'plugin_noghre_order_list',
            component: () =>
                import ('../views/plugins/noghre/order/list.vue'),
        },
        {
            path: '/acc/plugin/noghre/order/mod/:id?',
            name: 'plugin_noghre_order_mod',
            component: () =>
                import ('../views/plugins/noghre/order/mod.vue'),
        },
        {
            path: '/acc/plugin/noghre/order/view/:id?',
            name: 'plugin_noghre_order_view',
            component: () =>
                import ('../views/plugins/noghre/order/view.vue'),
        },
        {
            path: '/acc/plugin/noghre/pays/view/:id?',
            name: 'plugin_noghre_pays_view',
            component: () =>
                import ('../views/plugins/noghre/pays.vue'),
        },
        {
            path: '/acc/storeroom/commodity/check/exist',
            name: 'storeroom_commodity_check_exist',
            component: () =>
                import ('../views/storeroom/commodityCheck/checkByStoreroom.vue'),
        },
        {
            path: '/acc/storeroom/new/ticket/type',
            name: 'storeroom_new_ticket_type',
            component: () =>
                import ('../views/storeroom/io/modalNew.vue'),
        },
        {
            path: '/acc/storeroom/tickets/list',
            name: 'storeroom_tickets_list',
            component: () =>
                import ('../views/storeroom/io/ticketList.vue'),
        },
        {
            path: '/acc/storeroom/ticket/view/:id',
            name: 'storeroom_ticket_view',
            component: () =>
                import ('../views/storeroom/io/view.vue'),
        },
        {
            path: '/acc/storeroom/new/ticket/buy/:doc/:storeID',
            name: 'storeroom_new_ticket_buy',
            component: () =>
                import ('../views/storeroom/io/buy.vue'),
        },
        {
            path: '/acc/storeroom/new/ticket/sell/:doc/:storeID',
            name: 'storeroom_new_ticket_sell',
            component: () =>
                import ('../views/storeroom/io/sell.vue'),
        },
        {
            path: '/acc/storeroom/new/ticket/rfbuy/:doc/:storeID',
            name: 'storeroom_new_ticket_rfbuy',
            component: () =>
                import ('../views/storeroom/io/rfbuy.vue'),
        },
        {
            path: '/acc/storeroom/new/ticket/rfsell/:doc/:storeID',
            name: 'storeroom_new_ticket_rfsell',
            component: () =>
                import ('../views/storeroom/io/rfsell.vue'),
        },
        {
            path: '/acc/storeroom/list',
            name: 'storeroom_list',
            component: () =>
                import ('../views/storeroom/list.vue'),
        },
        {
            path: '/acc/storeroom/mod/:id?',
            name: 'storeroom_mod',
            component: () =>
                import ('../views/storeroom/mod.vue'),
        },
        {
            path: '/acc/archive/list',
            name: 'archive_list',
            component: () =>
                import ('../views/archive/view_files.vue'),
        },
        {
            path: '/acc/archive/order/new',
            name: 'order_new',
            component: () =>
                import ('../views/archive/order_new.vue'),
        },
        {
            path: '/acc/archive/order/list',
            name: 'order_list',
            component: () =>
                import ('../views/archive/orders_list.vue'),
        },
        {
            path: '/acc/shareholders/list',
            name: 'shareholders_list',
            component: () =>
                import ('../views/shareholder/list.vue'),
        },
        {
            path: "/:catchAll(.*)",
            name: "not-found",
            component: () =>
                import ("../views/NotFound.vue"),
            meta: {
                'title': 'صفحه یافت نشد',
            }
        },
    ]
})
router.beforeEach((to, from) => {
    const width = Math.max(
        document.documentElement.clientWidth,
        window.innerWidth || 0
    )
    if (width <= 992) {
        Dashmix.layout('sidebar_close');
    }
    return true
})
export default router