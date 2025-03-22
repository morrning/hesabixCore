<script>
import { RouterLink, RouterView } from 'vue-router';
import axios from "axios";
import Swal from "sweetalert2";
import { getApiUrl, getBasePath, getSiteName } from "@/hesabixConfig";
import { ref } from 'vue';
import Profile_btn from '@/components/application/buttons/profile_btn.vue';
import Notifications_btn from '@/components/application/buttons/notifications_btn.vue';
import Year_cob from '@/components/application/combobox/year_cob.vue';
import Currency_cob from '@/components/application/combobox/currency_cob.vue';
import clock from '@/components/application/clock.vue';

export default {
  data() {
    return {
      drawer: ref(null),
      plugins: [],
      business: { id: '', name: '' },
      timeNow: '',
      apiUrl: '',
      siteName: '',
      siteSlogan: '',
      permissions: {},
      showShortcutsDialog: false,
      isEditingShortcuts: false,
      showShortcutHint: false,
      shortcutHint: '',
      shortcuts: [],
      defaultShortcuts: [],
      isCtrlShiftPressed: false,
      duplicateKeyDialog: false,
      duplicateKeyMessage: '',
      canFreeAccounting: true,
    };
  },
  mounted() {
    axios.post('/api/plugin/get/actives').then((response) => {
      this.plugins = response.data;
    });
    axios.post('/api/business/list/count').then((response) => {
      this.business_count = response.data.count;
    });
    axios.post('/api/business/get/user/permissions').then((response) => {
      this.permissions = response.data;
    });
    axios.post('/api/business/get/info/' + localStorage.getItem('activeBid')).then((response) => {
      this.business = response.data;
    });
    axios.post('/api/general/get/time').then((response) => {
      this.timeNow = response.data.timeNow;
    });
    // درخواست برای گرفتن canFreeAccounting
    axios.post('/api/settings/get/can-free-accounting')
      .then((response) => {
        // بررسی مقدار بازگشتی که "1" یا "0" هست
        this.canFreeAccounting = response.data.value == "1";
      })
      .catch((error) => {
        console.error('Error fetching canFreeAccounting:', error);
        this.canFreeAccounting = true; // در صورت خطا، منو مخفی می‌مونه
      });
    this.apiUrl = getApiUrl();

    this.initializeShortcuts();
    window.addEventListener('keydown', this.handleKeyDown);
    window.addEventListener('keyup', this.handleKeyUp);
  },
  async created() {
    this.siteName = await getSiteName();
    this.siteSlogan = await getSiteSlogan();
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeyDown);
    window.removeEventListener('keyup', this.handleKeyUp);
  },
  methods: {
    getbase() {
      return getBasePath();
    },
    deleteBusiness() {
      Swal.fire({
        text: 'آیا برای حذف این کسب‌و‌کار مطمئن هستید؟ بعد از تایید این عملیات کسب و کار شما به مدت یک ماه در پایگاه داده آرشیو و بعد از آن به صورت دائم حذف خواهد شد',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        if (result.isConfirmed) {
          axios.post('/api/business/delete').then((response) => {
            if (response.data.code == 0) {
              Swal.fire({
                text: 'کسب و کار با موفقیت حذف شد. به پروفایل کاربری باز می گردید.',
                icon: 'success',
                confirmButtonText: 'قبول'
              }).then(() => {
                document.location = '/';
              });
            }
          });
        }
      });
    },
    exitBusiness() {
      Swal.fire({
        text: 'آیا برای خروج و ترک کسب‌و‌کار مطمئن هستید؟ ترک کسب و کار موجب قطع دسترسی شما خواهد شد و تنها از طریق مالک کسب‌و‌کار قابل بازیابی است.',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        if (result.isConfirmed) {
          axios.post('/api/business/removeuser/me').then((response) => {
            if (response.data.result == 1) {
              Swal.fire({
                text: 'عملیات با موفقیت انجام شد. به صفحه پروفایل کاربری باز می گردید.',
                icon: 'success',
                confirmButtonText: 'قبول'
              }).then(() => {
                document.location = '/';
              });
            }
          });
        }
      });
    },
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
    getDefaultShortcuts() {
      return [
        { path: '/acc/dashboard', key: 'D', label: this.$t('drawer.dashboard'), ctrl: true, shift: true, permission: () => true },
        { path: '/acc/persons/list', key: 'P', label: this.$t('drawer.persons'), ctrl: true, shift: true, permission: () => this.permissions.persons },
        { path: '/acc/persons/receive/list', key: 'R', label: this.$t('drawer.gets'), ctrl: true, shift: true, permission: () => this.permissions.getpay },
        { path: '/acc/persons/send/list', key: 'S', label: this.$t('drawer.pays'), ctrl: true, shift: true, permission: () => this.permissions.getpay },
        { path: '/acc/shareholders/list', key: 'H', label: this.$t('drawer.shareholders'), ctrl: true, shift: true, permission: () => this.permissions.shareholder },
        { path: '/acc/commodity/list', key: 'C', label: this.$t('drawer.commodity'), ctrl: true, shift: true, permission: () => this.permissions.commodity },
        { path: '/acc/commodity/pricelist/list', key: 'L', label: this.$t('drawer.price_lists'), ctrl: true, shift: true, permission: () => this.permissions.commodity && this.isPluginActive('accpro') },
        { path: '/acc/commodity/cat/list', key: 'T', label: this.$t('drawer.cats'), ctrl: true, shift: true, permission: () => this.permissions.commodity },
        { path: '/acc/commodity/drop/list', key: 'O', label: this.$t('drawer.drops_list'), ctrl: true, shift: true, permission: () => this.permissions.commodity },
        { path: '/acc/banks/list', key: 'B', label: this.$t('drawer.banks_accounts'), ctrl: true, shift: true, permission: () => this.permissions.bank },
        { path: '/acc/cashdesk/list', key: 'K', label: this.$t('drawer.cashdesks'), ctrl: true, shift: true, permission: () => this.permissions.cashdesk },
        { path: '/acc/salary/list', key: 'Y', label: this.$t('drawer.salarys'), ctrl: true, shift: true, permission: () => this.permissions.salary },
        { path: '/acc/cheque/list', key: 'Q', label: this.$t('drawer.cheques'), ctrl: true, shift: true, permission: () => this.permissions.cheque },
        { path: '/acc/transfer/list', key: 'N', label: this.$t('drawer.transfers'), ctrl: true, shift: true, permission: () => this.permissions.bankTransfer },
        { path: '/acc/wallet/view', key: 'W', label: this.$t('static.wallet'), ctrl: true, shift: true, permission: () => this.permissions.wallet },
        { path: '/acc/storeroom/list', key: 'M', label: this.$t('drawer.storerooms'), ctrl: true, shift: true, permission: () => this.permissions.store },
        { path: '/acc/storeroom/tickets/list', key: 'I', label: this.$t('drawer.storeroom_ticket'), ctrl: true, shift: true, permission: () => this.permissions.store },
        { path: '/acc/storeroom/commodity/check/exist', key: 'E', label: this.$t('drawer.commodity_exist_count'), ctrl: true, shift: true, permission: () => this.permissions.store },
        { path: '/acc/buy/list', key: 'U', label: this.$t('drawer.buy_invoices'), ctrl: true, shift: true, permission: () => this.permissions.buy },
        { path: '/acc/rfbuy/list', key: 'F', label: this.$t('drawer.rfbuy_invoices'), ctrl: true, shift: true, permission: () => this.permissions.plugAccproRfbuy && this.isPluginActive('accpro') },
        { path: '/acc/costs/list', key: 'G', label: this.$t('drawer.costs'), ctrl: true, shift: true, permission: () => this.permissions.cost },
        { path: '/acc/sell/fast-mod', key: 'J', label: this.$t('drawer.fast_sell'), ctrl: true, shift: true, permission: () => this.permissions.sell },
        { path: '/acc/sell/list', key: 'V', label: this.$t('drawer.sell_invoices'), ctrl: true, shift: true, permission: () => this.permissions.sell },
        { path: '/acc/presell/list', key: 'X', label: this.$t('drawer.presells'), ctrl: true, shift: true, permission: () => this.permissions.sell && this.isPluginActive('accpro') && (1 == 2) },
        { path: '/acc/rfsell/list', key: 'Z', label: this.$t('drawer.rfsell_invoices'), ctrl: true, shift: true, permission: () => this.permissions.plugAccproRfsell && this.isPluginActive('accpro') },
        { path: '/acc/incomes/list', key: 'A', label: this.$t('drawer.incomes'), ctrl: true, shift: true, permission: () => this.permissions.income },
        { path: '/acc/accounting/list', key: '1', label: this.$t('drawer.accounting_docs'), ctrl: true, shift: true, permission: () => this.permissions.accounting },
        { path: '/acc/accounting/open_balance', key: '2', label: this.$t('drawer.open_balance'), ctrl: true, shift: true, permission: () => this.permissions.accounting },
        { path: '/acc/accounting/table', key: '3', label: this.$t('drawer.accounting_table'), ctrl: true, shift: true, permission: () => this.permissions.accounting },
        { path: '/acc/accounting/close_year', key: '4', label: this.$t('drawer.close_year'), ctrl: true, shift: true, permission: () => this.permissions.plugAccproCloseYear && this.isPluginActive('accpro') },
        { path: '/acc/reports/list', key: '5', label: this.$t('drawer.reports'), ctrl: true, shift: true, permission: () => this.permissions.report },
        { path: '/acc/business/settings', key: '6', label: this.$t('drawer.bid_settings'), ctrl: true, shift: true, permission: () => this.permissions.settings },
        { path: '/acc/business/printoptions', key: '7', label: this.$t('drawer.print_settings'), ctrl: true, shift: true, permission: () => this.permissions.settings },
        { path: '/acc/business/avatar', key: '8', label: this.$t('drawer.avatar_settings'), ctrl: true, shift: true, permission: () => this.permissions.settings && this.isPluginActive('accpro') },
        { path: '/acc/business/users', key: '9', label: this.$t('drawer.user_perms'), ctrl: true, shift: true, permission: () => this.permissions.permission },
        { path: '/acc/business/apis', key: '0', label: this.$t('drawer.tokens'), ctrl: true, shift: true, permission: () => this.permissions.owner },
        { path: '/acc/business/extramoneys', key: '-', label: this.$t('drawer.extra_moneys'), ctrl: true, shift: true, permission: () => this.permissions.settings && this.isPluginActive('accpro') },
        { path: '/acc/business/logs', key: '=', label: this.$t('drawer.history'), ctrl: true, shift: true, permission: () => this.permissions.log },
        { path: '/acc/plugin/repservice/order/list', key: '[', label: this.$t('drawer.repservice_reqs'), ctrl: true, shift: true, permission: () => this.permissions.plugRepservice && this.isPluginActive('repservice') },
        { path: '/acc/sms/panel', key: ']', label: this.$t('drawer.sms_panel'), ctrl: true, shift: true, permission: () => this.permissions.owner },
        { path: '/acc/printers/list', key: ';', label: this.$t('drawer.cloud_printers'), ctrl: true, shift: true, permission: () => this.permissions.owner },
        { path: '/acc/archive/list', key: '\'', label: this.$t('drawer.archive_files'), ctrl: true, shift: true, permission: () => this.permissions.archiveUpload || this.permissions.archiveMod || this.permissions.archiveDelete },
        { path: '/acc/archive/order/new', key: ',', label: this.$t('drawer.archive_order'), ctrl: true, shift: true, permission: () => this.permissions.owner },
        { path: '/acc/archive/order/list', key: '.', label: this.$t('drawer.archive_log'), ctrl: true, shift: true, permission: () => this.permissions.owner },
        { path: '/acc/plugin-center/list', key: '/', label: this.$t('drawer.plugins_list'), ctrl: true, shift: true, permission: () => this.permissions.owner },
        { path: '/acc/plugin-center/my', key: '\\', label: this.$t('drawer.my_plugins'), ctrl: true, shift: true, permission: () => this.permissions.owner },
        { path: '/acc/plugin-center/invoice', key: '`', label: this.$t('drawer.plugins_invoices'), ctrl: true, shift: true, permission: () => this.permissions.owner }
      ];
    },
    restorePermissions(shortcuts) {
      const defaultShortcuts = this.getDefaultShortcuts();
      return shortcuts.map(shortcut => {
        const defaultShortcut = defaultShortcuts.find(ds => ds.path === shortcut.path);
        return {
          ...shortcut,
          permission: defaultShortcut ? defaultShortcut.permission : () => true
        };
      });
    },
    initializeShortcuts() {
      this.defaultShortcuts = this.getDefaultShortcuts();
      const storedShortcuts = localStorage.getItem('customShortcuts');
      if (storedShortcuts) {
        const parsedShortcuts = JSON.parse(storedShortcuts);
        this.shortcuts = this.restorePermissions(parsedShortcuts);
      } else {
        this.shortcuts = [...this.defaultShortcuts];
        this.saveShortcuts();
      }
    },
    saveShortcuts() {
      const serializableShortcuts = this.shortcuts.map(({ path, key, label, ctrl, shift }) => ({
        path,
        key,
        label,
        ctrl,
        shift
      }));
      localStorage.setItem('customShortcuts', JSON.stringify(serializableShortcuts));
    },
    resetToDefault() {
      this.shortcuts = [...this.defaultShortcuts];
      this.saveShortcuts();
      this.isEditingShortcuts = false;
      this.showShortcutsDialog = false;
    },
    checkDuplicateKeys(newKey, currentIndex) {
      return this.shortcuts.some((shortcut, index) =>
        shortcut.key.toUpperCase() === newKey.toUpperCase() && index !== currentIndex
      );
    },
    updateShortcut(index, newKey) {
      if (!newKey) return;
      const upperKey = newKey.toUpperCase();
      if (this.checkDuplicateKeys(upperKey, index)) {
        this.duplicateKeyMessage = `کلید "${upperKey}" قبلاً استفاده شده است. لطفاً کلید دیگری انتخاب کنید.`;
        this.duplicateKeyDialog = true;
        return;
      }
      this.shortcuts[index].key = upperKey;
    },
    saveCustomShortcuts() {
      const keys = this.shortcuts.map(s => s.key);
      const duplicates = keys.filter((key, index) => keys.indexOf(key) !== index);
      if (duplicates.length > 0) {
        this.duplicateKeyMessage = `کلیدهای تکراری یافت شد: ${duplicates.join(', ')}. لطفاً آنها را اصلاح کنید.`;
        this.duplicateKeyDialog = true;
        return;
      }
      this.saveShortcuts();
      this.isEditingShortcuts = false;
      this.showShortcutsDialog = false;
    },
    handleKeyDown(event) {
      const { ctrlKey, shiftKey, keyCode } = event;

      if (ctrlKey && shiftKey && !this.showShortcutHint) {
        this.showShortcutHint = true;
        this.shortcutHint = this.$t('shortcuts.hint', { keys: 'Ctrl+Shift' });
        this.isCtrlShiftPressed = true;
      }

      this.shortcuts.forEach(shortcut => {
        const keyMatches = keyCode === shortcut.key.charCodeAt(0);
        if (ctrlKey && shiftKey && keyMatches && shortcut.permission()) {
          event.preventDefault();
          this.$router.push(shortcut.path);
          this.shortcutHint = `${shortcut.label} (${shortcut.key})`;
        }
      });
    },
    handleKeyUp(event) {
      const { ctrlKey, shiftKey } = event;
      if (!ctrlKey || !shiftKey) {
        this.showShortcutHint = false;
        this.shortcutHint = '';
        this.isCtrlShiftPressed = false;
      }
    },
    filteredShortcuts() {
      return this.shortcuts.filter(shortcut => shortcut.permission());
    },
    getShortcutKey(path) {
      const shortcut = this.shortcuts.find(s => s.path === path);
      return shortcut ? shortcut.key : '';
    }
  },
  components: {
    Profile_btn,
    Notifications_btn,
    Year_cob,
    Currency_cob,
    clock
  }
};
</script>

<template>
  <v-system-bar color="primaryLight2">
    <v-avatar image="/img/logo-blue.png" size="20" class="me-2 d-none d-sm-flex" />
    <span class="d-none d-sm-flex">{{ siteSlogan }}</span>
    <v-avatar :image="apiUrl + '/front/avatar/file/get/' + business.id" size="20" class="me-2 d-flex d-sm-none" />
    <span class="d-flex d-sm-none">{{ business.name }}</span>
    <v-spacer />
    <clock />
  </v-system-bar>
  <v-navigation-drawer v-model="drawer" :width="300">
    <v-card height="64" rounded="0" prepend-icon="mdi-account">
      <template v-slot:title>{{ siteName }}</template>
      <template v-slot:prepend>
        <v-avatar class="d-none d-sm-flex" :image="apiUrl + '/front/avatar/file/get/' + business.id" />
        <v-avatar class="d-flex d-sm-none" :image="getbase() + 'img/favw.png'" />
      </template>
    </v-card>
    <v-list class="px-0 pt-0">
      <v-list-item to="/acc/dashboard">
        <template v-slot:prepend><v-icon icon="mdi-view-dashboard"></v-icon></template>
        <v-list-item-title>
          {{ $t('drawer.dashboard') }}
          <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/dashboard') }}</span>
        </v-list-item-title>
      </v-list-item>
      <v-list-subheader color="primary">{{ $t('drawer.basic_tools') }}</v-list-subheader>
      <v-list-group v-show="permissions.getpay || permissions.persons">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-account-multiple"
            :title="$t('drawer.persons')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.persons" to="/acc/persons/list">
          <v-list-item-title>
            {{ $t('drawer.persons') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/persons/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/persons/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.getpay" to="/acc/persons/receive/list">
          <v-list-item-title>
            {{ $t('drawer.gets') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/persons/receive/list')
            }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/persons/receive/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.getpay" to="/acc/persons/send/list">
          <v-list-item-title>
            {{ $t('drawer.pays') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/persons/send/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/persons/send/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.shareholder" to="/acc/shareholders/list">
          <v-list-item-title>
            {{ $t('drawer.shareholders') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/shareholders/list') }}</span>
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
      <v-list-group v-show="permissions.commodity">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-package-variant"
            :title="$t('drawer.commodity')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.commodity" to="/acc/commodity/list">
          <v-list-item-title>
            {{ $t('drawer.commodity') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/commodity/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/commodity/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.commodity && this.isPluginActive('accpro')" to="/acc/commodity/pricelist/list">
          <v-list-item-title>
            {{ $t('drawer.price_lists') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/commodity/pricelist/list')
            }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/commodity/pricelist/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.commodity" to="/acc/commodity/cat/list">
          <v-list-item-title>
            {{ $t('drawer.cats') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/commodity/cat/list') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.commodity" to="/acc/commodity/drop/list">
          <v-list-item-title>
            {{ $t('drawer.drops_list') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/commodity/drop/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/commodity/drop/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
      </v-list-group>
      <v-list-group
        v-show="permissions.bank || permissions.wallet || permissions.cashdesk || permissions.salary || permissions.cheque || permissions.bankTransfer">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-bank"
            :title="$t('drawer.bank_label')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.bank" to="/acc/banks/list">
          <v-list-item-title>
            {{ $t('drawer.banks_accounts') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/banks/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/banks/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.cashdesk" to="/acc/cashdesk/list">
          <v-list-item-title>
            {{ $t('drawer.cashdesks') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/cashdesk/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/cashdesk/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.salary" to="/acc/salary/list">
          <v-list-item-title>
            {{ $t('drawer.salarys') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/salary/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/salary/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.cheque" to="/acc/cheque/list">
          <v-list-item-title>
            {{ $t('drawer.cheques') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/cheque/list') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.bankTransfer" to="/acc/transfer/list">
          <v-list-item-title>
            {{ $t('drawer.transfers') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/transfer/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/transfer/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.wallet" to="/acc/wallet/view">
          <v-list-item-title>
            {{ $t('static.wallet') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/wallet/view') }}</span>
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
      <v-list-subheader color="primary">{{ $t('drawer.acc_store_tools') }}</v-list-subheader>
      <v-list-group v-show="permissions.store">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-store"
            :title="$t('drawer.storeroom_title')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.store" to="/acc/storeroom/list">
          <v-list-item-title>
            {{ $t('drawer.storerooms') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/storeroom/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/storeroom/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.store" to="/acc/storeroom/tickets/list">
          <v-list-item-title>
            {{ $t('drawer.storeroom_ticket') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/storeroom/tickets/list')
            }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/storeroom/new/ticket/type" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.store" to="/acc/storeroom/commodity/check/exist">
          <v-list-item-title>
            {{ $t('drawer.commodity_exist_count') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{
              getShortcutKey('/acc/storeroom/commodity/check/exist')
            }}</span>
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
      <v-list-group v-show="permissions.buy || permissions.cost || permissions.plugAccproRfbuy">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-cash-fast"
            :title="$t('drawer.buy_cost')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.buy" to="/acc/buy/list">
          <v-list-item-title>
            {{ $t('drawer.buy_invoices') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/buy/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/buy/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.plugAccproRfbuy && this.isPluginActive('accpro')" to="/acc/rfbuy/list">
          <v-list-item-title>
            {{ $t('drawer.rfbuy_invoices') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/rfbuy/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/rfbuy/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.cost" to="/acc/costs/list">
          <v-list-item-title>
            {{ $t('drawer.costs') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/costs/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/costs/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
      </v-list-group>
      <v-list-group v-show="permissions.sell || permissions.income || permissions.plugAccproRfsell">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-cart-arrow-down"
            :title="$t('drawer.sell_income')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.sell" to="/acc/sell/fast-mod">
          <v-list-item-title>
            {{ $t('drawer.fast_sell') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/sell/fast-mod') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.sell" to="/acc/sell/list">
          <v-list-item-title>
            {{ $t('drawer.sell_invoices') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/sell/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/sell/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="this.isPluginActive('accpro') && permissions.sell && (1 == 2)" to="/acc/presell/list">
          <v-list-item-title>
            {{ $t('drawer.presells') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/presell/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/presell/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.plugAccproRfsell && this.isPluginActive('accpro')" to="/acc/rfsell/list">
          <v-list-item-title>
            {{ $t('drawer.rfsell_invoices') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/rfsell/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/rfsell/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.income" to="/acc/incomes/list">
          <v-list-item-title>
            {{ $t('drawer.incomes') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/incomes/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/incomes/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
      </v-list-group>
      <v-list-group v-show="permissions.accounting || permissions.plugAccproCloseYear">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-file-document-multiple"
            :title="$t('drawer.accounting')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.accounting" to="/acc/accounting/list">
          <v-list-item-title>
            {{ $t('drawer.accounting_docs') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/accounting/list') }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <!--
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/accounting/mod/" />
              </template>
      </v-tooltip>
      -->
          </template>
        </v-list-item>
        <v-list-item v-if="permissions.accounting" to="/acc/accounting/open_balance">
          <v-list-item-title>
            {{ $t('drawer.open_balance') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/accounting/open_balance')
            }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.accounting" to="/acc/accounting/table">
          <v-list-item-title>
            {{ $t('drawer.accounting_table') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/accounting/table') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.plugAccproCloseYear && this.isPluginActive('accpro')"
          to="/acc/accounting/close_year">
          <v-list-item-title>
            {{ $t('drawer.close_year') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/accounting/close_year')
            }}</span>
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
      <v-list-item v-show="permissions.report" to="/acc/reports/list">
        <template v-slot:prepend><v-icon icon="mdi-poll"></v-icon></template>
        <v-list-item-title>
          {{ $t('drawer.reports') }}
          <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/reports/list') }}</span>
        </v-list-item-title>
      </v-list-item>
      <v-list-subheader color="primary">{{ $t('drawer.settings') }}</v-list-subheader>
      <v-list-group v-show="permissions.settings || permissions.log || permissions.permission">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" prepend-icon="mdi-cogs"
            :title="$t('drawer.settings')"></v-list-item>
        </template>
        <v-list-item v-if="permissions.settings" to="/acc/business/settings">
          <v-list-item-title>
            {{ $t('drawer.bid_settings') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/business/settings') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.settings" to="/acc/business/printoptions">
          <v-list-item-title>
            {{ $t('drawer.print_settings') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/business/printoptions')
            }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.settings && this.isPluginActive('accpro')" to="/acc/business/avatar">
          <v-list-item-title>
            {{ $t('drawer.avatar_settings') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/business/avatar') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.permission" to="/acc/business/users">
          <v-list-item-title>
            {{ $t('drawer.user_perms') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/business/users') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.owner" to="/acc/business/apis">
          <v-list-item-title>
            {{ $t('drawer.tokens') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/business/apis') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.settings && this.isPluginActive('accpro')" to="/acc/business/extramoneys">
          <v-list-item-title>
            {{ $t('drawer.extra_moneys') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/business/extramoneys')
            }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.log" to="/acc/business/logs">
          <v-list-item-title>
            {{ $t('drawer.history') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/business/logs') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item class="text-danger" v-if="permissions.owner" @click="deleteBusiness()"
          :title="$t('drawer.bid_delete')">
        </v-list-item>
      </v-list-group>
      <v-list-subheader color="primary">{{ $t('drawer.services') }}</v-list-subheader>
      <v-list-group v-show="permissions.plugRepservice && isPluginActive('repservice')">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" :title="$t('drawer.repservice')">
            <template v-slot:prepend><v-icon icon="mdi-progress-wrench" color="primary"></v-icon></template>
          </v-list-item>
        </template>
        <v-list-item v-if="permissions.plugRepservice" to="/acc/plugin/repservice/order/list">
          <v-list-item-title>
            {{ $t('drawer.repservice_reqs') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/plugin/repservice/order/list')
            }}</span>
          </v-list-item-title>
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.add_new')" location="end">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" icon="mdi-plus-box" variant="plain" to="/acc/plugin/repservice/order/mod/" />
              </template>
            </v-tooltip>
          </template>
        </v-list-item>
      </v-list-group>
      <v-list-item class="text-dark" v-if="permissions.owner" to="/acc/sms/panel">
        <template v-slot:prepend><v-icon icon="mdi-message-cog" color="primary"></v-icon></template>
        <v-list-item-title>
          {{ $t('drawer.sms_panel') }}
          <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/sms/panel') }}</span>
        </v-list-item-title>
      </v-list-item>
      <v-list-item class="text-dark" v-if="permissions.owner" to="/acc/printers/list">
        <template v-slot:prepend><v-icon icon="mdi-printer-pos-network" color="primary"></v-icon></template>
        <v-list-item-title>
          {{ $t('drawer.cloud_printers') }}
          <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/printers/list') }}</span>
        </v-list-item-title>
      </v-list-item>
      <v-list-group
        v-show="permissions.owner || permissions.archiveUpload || permissions.archiveMod || permissions.archiveDelete">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" :title="$t('drawer.archive_panel')">
            <template v-slot:prepend><v-icon icon="mdi-file-cloud" color="primary"></v-icon></template>
          </v-list-item>
        </template>
        <v-list-item v-if="permissions.archiveUpload || permissions.archiveMod || permissions.archiveDelete"
          to="/acc/archive/list">
          <v-list-item-title>
            {{ $t('drawer.archive_files') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/archive/list') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.owner" to="/acc/archive/order/new">
          <v-list-item-title>
            {{ $t('drawer.archive_order') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/archive/order/new') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.owner" to="/acc/archive/order/list">
          <v-list-item-title>
            {{ $t('drawer.archive_log') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/archive/order/list') }}</span>
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
      <v-list-group v-show="permissions.owner && !canFreeAccounting">
        <template v-slot:activator="{ props }">
          <v-list-item class="text-dark" v-bind="props" :title="$t('drawer.ultimate_package')">
            <template v-slot:prepend><v-icon icon="mdi-package" color="primary"></v-icon></template>
          </v-list-item>
        </template>
        <v-list-item v-if="permissions.owner" to="/acc/package/order/new">
          <v-list-item-title>
            {{ $t('drawer.archive_order') }}
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.owner" to="/acc/package/order/list">
          <v-list-item-title>
            {{ $t('drawer.archive_log') }}
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
      <v-list-group v-show="permissions.owner">
        <template v-slot:activator="{ props }">
          <v-list-item class="bg-blue-grey-lighten-4 ma-2" v-bind="props" :title="$t('drawer.market')">
            <template v-slot:prepend><v-icon icon="mdi-shopping" color="green-darken-5"></v-icon></template>
          </v-list-item>
        </template>
        <v-list-item v-if="permissions.owner" to="/acc/plugin-center/list">
          <v-list-item-title>
            {{ $t('drawer.plugins_list') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/plugin-center/list') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.owner" to="/acc/plugin-center/my">
          <v-list-item-title>
            {{ $t('drawer.my_plugins') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/plugin-center/my') }}</span>
          </v-list-item-title>
        </v-list-item>
        <v-list-item v-if="permissions.owner" to="/acc/plugin-center/invoice">
          <v-list-item-title>
            {{ $t('drawer.plugins_invoices') }}
            <span v-if="isCtrlShiftPressed" class="shortcut-key">{{ getShortcutKey('/acc/plugin-center/invoice')
            }}</span>
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
      <v-list-item class="text-dark bg-red-darken-3 ma-2 rounded-2" v-if="permissions.owner == false"
        @click="exitBusiness()">
        <template v-slot:prepend><v-icon color="white" icon="mdi-logout"></v-icon></template>
        <v-list-item-title class="text-white" v-text="$t('drawer.exit_bus')" />
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
  <v-app-bar scroll-behavior="inverted elevate" scroll-threshold="0">
    <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
    <v-app-bar-title>
      <span class="d-none d-sm-flex">{{ business.name }}</span>
    </v-app-bar-title>
    <v-spacer></v-spacer>
    <v-btn class="d-none d-sm-flex" stacked @click="showShortcutsDialog = true; isEditingShortcuts = false">
      <v-icon>mdi-help-circle</v-icon>
    </v-btn>
    <v-dialog v-model="showShortcutsDialog" max-width="800" scrollable>
      <v-card>
        <v-card-title class="text-h5 pa-4">
          {{ isEditingShortcuts ? 'ویرایش کلید‌های میانبر' : 'راهنمای کلید‌های میانبر' }}
        </v-card-title>
        <v-card-text class="pa-4" style="max-height: 500px; overflow-y: auto;">
          <v-row>
            <v-col v-for="(shortcut, index) in filteredShortcuts()" :key="shortcut.path" cols="12" sm="6" md="6">
              <div class="shortcut-item pa-2">
                <v-row align="center" no-gutters>
                  <v-col cols="12">
                    <span v-if="!isEditingShortcuts" class="font-weight-bold shortcut-display">
                      Ctrl+Shift+{{ shortcut.key }}
                    </span>
                    <v-text-field v-else v-model="shortcut.key" @input="updateShortcut(index, $event)" :label="کلید"
                      maxlength="1" dense hide-details class="shortcut-input" />
                  </v-col>
                  <v-col cols="12" class="text-right">
                    <span>{{ shortcut.label }}</span>
                  </v-col>
                </v-row>
              </div>
            </v-col>
          </v-row>
          <v-alert v-if="isEditingShortcuts" type="info" dense class="mt-4">
            تغییرات در حافظه مرورگر ذخیره می‌شود و فقط در این مرورگر قابل استفاده است.
          </v-alert>
        </v-card-text>
        <v-card-actions class="pa-4">
          <v-btn v-if="!isEditingShortcuts" color="primary" @click="isEditingShortcuts = true">ویرایش</v-btn>
          <v-btn v-if="isEditingShortcuts" color="primary" @click="saveCustomShortcuts">ثبت تغییرات</v-btn>
          <v-btn v-if="isEditingShortcuts" color="warning" @click="resetToDefault">بازگشت به پیش‌فرض</v-btn>
          <v-spacer />
          <v-btn text @click="showShortcutsDialog = false">بستن</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="duplicateKeyDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h6">خطا</v-card-title>
        <v-card-text>{{ duplicateKeyMessage }}</v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="duplicateKeyDialog = false">بستن</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-tooltip v-model="showShortcutHint" location="bottom" class="shortcut-hint">
      <template v-slot:activator="{ props }">
        <div v-bind="props"></div>
      </template>
      <span>{{ shortcutHint }}</span>
    </v-tooltip>
    <v-bottom-sheet inset>
      <template v-slot:activator="{ props }">
        <v-btn stacked v-bind="props"><v-icon>mdi-cog</v-icon></v-btn>
      </template>
      <v-card :subtitle="$t('dialog.fiscal_settings_info')" prepend-icon="mdi-cog"
        :title="$t('dialog.fiscal_settings')">
        <v-card-text>
          <v-row class="text-center">
            <v-col cols="12" sm="12" md="6">
              <Year_cob />
            </v-col>
            <v-col cols="12" sm="12" md="6">
              <Currency_cob />
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-bottom-sheet>
    <Notifications_btn />
    <Profile_btn />
  </v-app-bar>
  <v-main>
    <div class="position-relative">
      <RouterView />
    </div>
  </v-main>
</template>

<style global>
.shortcut-key {
  font-size: 0.75rem;
  color: #888;
  margin-right: 4px;
}

.shortcut-hint {
  z-index: 1000;
}

.shortcut-item {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background-color: #fafafa;
}

.shortcut-display {
  font-size: 0.9rem;
  color: #555;
}

.shortcut-input {
  max-width: 60px;
}

.v-data-table {
  overflow-x: auto;
}

.expanded-row {
  background-color: #f5f5f5 !important;
  padding: 8px;
}

.custom-header {
  background-color: #213e8b !important;
  color: #ffffff !important;
}
</style>