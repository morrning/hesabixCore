<template>
  <!-- Toolbar بالای صفحه -->
  <v-toolbar color="toolbar" :title="$t('drawer.dashboard')">
    <v-tooltip :text="$t('dialog.edit_dashboard')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-layers-edit" color="primary" @click="dialog = true"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>

  <!-- محتوای اصلی -->
  <v-container fluid class="pa-4">
    <v-row v-show="statements.length > 0 && dashboard.notif">
      <v-col cols="12">
        <v-alert type="info" color="blue-grey" variant="tonal" border class="animate__animated animate__fadeInDown">
          <v-row>
            <v-col v-for="statment in statements" :key="statment.dateSubmit" cols="12">
              <span class="font-weight-bold">{{ statment.dateSubmit }}: </span>
              <span>{{ statment.body }}</span>
            </v-col>
          </v-row>
        </v-alert>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" sm="12" md="12" v-show="permissions.sell && dashboard.sellChart && isPluginActive('accpro')">
        <v-card class="animate__animated animate__zoomIn card-equal-height_big" variant="outlined"
          prepend-icon="mdi-basket" :title="$t('drawer.sell_chart')" hover>
          <v-card-text class="pa-0">
            <sale-chart></sale-chart>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- کارت جدید: کالاهای پرفروش -->
      <v-col cols="12" sm="12" md="12" v-show="permissions.sell && dashboard.topCommodities">
        <top-commodities-chart class="animate__animated animate__zoomIn" />
      </v-col>

      <v-col cols="12" sm="12" md="6" v-show="permissions.cost && dashboard.topCostCenters">
        <v-card class="animate__animated animate__zoomIn" variant="outlined" prepend-icon="mdi-chart-pie"
          title="مراکز هزینه" hover>
          <v-card-text class="pa-2">
            <top-cost-centers-chart />
          </v-card-text>
        </v-card>
      </v-col>
      <!-- کارت جدید: مراکز درآمد -->
      <v-col cols="12" sm="12" md="6" v-show="permissions.income && dashboard.topIncomeCenters">
        <v-card class="animate__animated animate__zoomIn" variant="outlined" prepend-icon="mdi-chart-pie"
          title="مراکز درآمد" hover>
          <v-card-text class="pa-2">
            <top-income-centers-chart />
          </v-card-text>
        </v-card>
      </v-col>
      <!-- کارت‌های قبلی (بدون تغییر) -->
      <v-col cols="12" sm="6" md="4" v-show="permissions.wallet && dashboard.wallet">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="success-lighten-4" variant="elevated"
          prepend-icon="mdi-wallet" :title="$t('static.wallet')" to="/acc/wallet/view" hover>
          <v-card-text class="text-dark">
            <v-icon left small color="success">mdi-currency-usd</v-icon>
            {{ $t('static.deposit') }}: {{ $filters.formatNumber(wallet.deposit) || '0' }} {{ $t('currency.irr.short')
            }}
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4" v-show="permissions.persons && dashboard.persons">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="indigo-lighten-4" variant="elevated"
          prepend-icon="mdi-account-multiple" :title="$t('drawer.persons')" to="/acc/persons/list" hover>
          <v-card-text class="text-dark">
            <p><v-icon left small color="indigo">mdi-numeric</v-icon>{{ $t('static.count') }}: {{
              $filters.formatNumber(stat.personCount) || '0' }}</p>
            <p><v-icon left small color="indigo">mdi-arrow-down</v-icon>{{ $t('drawer.recs_today') }}: {{
              $filters.formatNumber(stat.recs_today, true) || '0' }}</p>
            <p><v-icon left small color="indigo">mdi-arrow-up</v-icon>{{ $t('drawer.sends_today') }}: {{
              $filters.formatNumber(stat.sends_today, true) || '0' }}</p>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4" v-show="permissions.cost && dashboard.costs">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="red-lighten-4" variant="elevated"
          prepend-icon="mdi-cash-minus" :title="$t('dashboard.costs.title')" hover>
          <v-card-text class="text-dark">
            <p class="my-0 py-1">
              <v-icon left small color="red">mdi-calendar-today</v-icon>{{ $t('dashboard.costs.today') }}: {{
                $filters.formatNumber(costData.today) || '0' }}
            </p>
            <p class="my-0 py-1"><v-icon left small color="red">mdi-calendar-week</v-icon>{{ $t('dashboard.costs.week')
            }}: {{
                $filters.formatNumber(costData.week) || '0' }}</p>
            <p class="my-0 py-1"><v-icon left small color="red">mdi-calendar-month</v-icon>{{
              $t('dashboard.costs.month')
            }}: {{
                $filters.formatNumber(costData.month) || '0' }}</p>
            <p class="my-0 py-1"><v-icon left small color="red">mdi-calendar-range</v-icon>{{ $t('dashboard.costs.year')
            }}: {{
                $filters.formatNumber(costData.year) || '0' }}</p>
          </v-card-text>
        </v-card>
      </v-col>
      <!-- کارت جدید: درآمد -->
      <v-col cols="12" sm="6" md="4" v-show="permissions.income && dashboard.incomes">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="green-lighten-4" variant="elevated"
          prepend-icon="mdi-cash-plus" :title="$t('dashboard.incomes.title')" hover>
          <v-card-text class="text-dark">
            <p class="my-0 py-1">
              <v-icon left small color="green">mdi-calendar-today</v-icon>{{ $t('dashboard.incomes.today') }}: {{
                $filters.formatNumber(incomeData.today) || '0' }}
            </p>
            <p class="my-0 py-1"><v-icon left small color="green">mdi-calendar-week</v-icon>{{
              $t('dashboard.incomes.week') }}: {{
                $filters.formatNumber(incomeData.week) || '0' }}</p>
            <p class="my-0 py-1"><v-icon left small color="green">mdi-calendar-month</v-icon>{{
              $t('dashboard.incomes.month') }}: {{
                $filters.formatNumber(incomeData.month) || '0' }}</p>
            <p class="my-0 py-1"><v-icon left small color="green">mdi-calendar-range</v-icon>{{
              $t('dashboard.incomes.year') }}: {{
                $filters.formatNumber(incomeData.year) || '0' }}</p>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4" v-show="permissions.sell && dashboard.sells">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="green-lighten-4" variant="elevated"
          prepend-icon="mdi-basket-unfill" :title="$t('drawer.sell')" to="/acc/sell/list" hover>
          <v-card-text class="text-dark">
            <p><v-icon left small color="green">mdi-sigma</v-icon>{{ $t('drawer.total') }}: {{
              $filters.formatNumber(stat.sells_total, true) || '0' }}</p>
            <p><v-icon left small color="green">mdi-calendar-today</v-icon>{{ $t('drawer.today') }}: {{
              $filters.formatNumber(stat.sells_today, true) || '0' }}</p>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- ردیف دوم -->
      <v-col cols="12" sm="6" md="4" v-show="permissions.buy && dashboard.buys">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="red-lighten-4" variant="elevated"
          prepend-icon="mdi-basket-fill" :title="$t('drawer.buy')" to="/acc/buy/list" hover>
          <v-card-text class="text-dark">
            <p><v-icon left small color="red">mdi-sigma</v-icon>{{ $t('drawer.total') }}: {{
              $filters.formatNumber(stat.buys_total, true) || '0' }}</p>
            <p><v-icon left small color="red">mdi-calendar-today</v-icon>{{ $t('drawer.today') }}: {{
              $filters.formatNumber(stat.buys_today, true) || '0' }}</p>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4" v-show="permissions.accounting && dashboard.accounting_total">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="orange-lighten-4" variant="elevated"
          prepend-icon="mdi-table-refresh" :title="$t('drawer.roller')" hover>
          <v-card-text class="text-dark">
            <v-icon left small color="orange">mdi-currency-usd</v-icon>
            {{ $filters.formatNumber(stat.income, true) || '0' }}
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4" v-show="permissions.bank && dashboard.banks">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="blue-lighten-4" variant="elevated"
          prepend-icon="mdi-bank" :title="$t('drawer.banks')" to="/acc/banks/list" hover>
          <v-card-text class="text-dark">
            <v-icon left small color="blue">mdi-numeric</v-icon>
            {{ $t('static.count') }}: {{ $filters.formatNumber(stat.bankCount) || '0' }}
          </v-card-text>
        </v-card>
      </v-col>

      <!-- ردیف سوم -->
      <v-col cols="12" sm="6" md="4" v-show="permissions.accounting && dashboard.acc_docs">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="purple-lighten-4" variant="elevated"
          prepend-icon="mdi-book-open-page-variant" :title="$t('drawer.docs')" to="/acc/accounting/list" hover>
          <v-card-text class="text-dark">
            <v-icon left small color="purple">mdi-numeric</v-icon>
            {{ $t('static.count') }}: {{ $filters.formatNumber(stat.docCount) || '0' }}
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4" v-show="permissions.commodity && dashboard.commodities">
        <v-card class="animate__animated animate__zoomIn card-equal-height" color="teal-lighten-4" variant="elevated"
          prepend-icon="mdi-package-variant" :title="$t('drawer.commodity')" to="/acc/commodity/list" hover>
          <v-card-text class="text-dark">
            <v-icon left small color="teal">mdi-numeric</v-icon>
            {{ $t('static.count') }}: {{ $filters.formatNumber(stat.commodity) || '0' }}
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

  <!-- دیالوگ تنظیمات داشبورد -->
  <v-dialog v-model="dialog">
    <v-card color="grey-lighten-4">
      <v-toolbar color="primary-dark" flat>
        <v-icon left class="ms-2" color="white">mdi-layers-edit</v-icon>
        <v-toolbar-title class="text-white text-subtitle-1">{{ $t('dialog.edit_dashboard') }}</v-toolbar-title>
        <v-spacer />
        <v-btn icon @click="dialog = false">
          <v-icon color="white">mdi-close</v-icon>
        </v-btn>
      </v-toolbar>
      <v-card-text class="pa-2">
        <v-row>
          <v-col cols="12" sm="6" md="4">
            <v-switch density="compact" color="primary" :label="$t('static.wallet')" v-model="dashboard.wallet"
              hide-details inset class="text-caption" />
            <v-switch color="primary" :label="$t('drawer.banks')" v-model="dashboard.banks" hide-details inset
              class="text-caption" />
            <v-switch color="primary" :label="$t('drawer.acc_docs')" v-model="dashboard.acc_docs" hide-details inset
              class="text-caption" />
            <v-switch color="primary" label="نمودار کالاهای پرفروش" v-model="dashboard.topCommodities" hide-details
              inset class="text-caption" />
            <v-switch color="primary" :label="$t('dashboard.incomes.title')" v-model="dashboard.incomes" hide-details
              inset class="text-caption" />
            <v-switch color="primary" :label="$t('dashboard.incomes.centers')" v-model="dashboard.topIncomeCenters"
              hide-details inset class="text-caption" />

          </v-col>
          <v-col cols="12" sm="6" md="4">
            <v-switch color="primary" :label="$t('drawer.persons')" v-model="dashboard.persons" hide-details inset
              class="text-caption" />
            <v-switch color="primary" :label="$t('drawer.buys')" v-model="dashboard.buys" hide-details inset
              class="text-caption" />
            <v-switch color="primary" :label="$t('drawer.sells')" v-model="dashboard.sells" hide-details inset
              class="text-caption" />
            <v-switch color="primary" :label="$t('drawer.sell_chart')" v-model="dashboard.sellChart" hide-details inset
              class="text-caption" />
          </v-col>
          <v-col cols="12" sm="6" md="4">
            <v-switch color="primary" :label="$t('dialog.commodities')" v-model="dashboard.commodities" hide-details
              inset class="text-caption" />
            <v-switch color="primary" :label="$t('dashboard.costs.title')" v-model="dashboard.costs" hide-details inset
              class="text-caption" />
            <v-switch color="primary" :label="$t('dashboard.costs.centers')" v-model="dashboard.topCostCenters"
              density="compact" hide-details inset class="text-caption" />
            <v-switch color="primary" :label="$t('drawer.accounting_total')" v-model="dashboard.accounting_total"
              hide-details inset class="text-caption" />
            <v-switch color="primary" :label="$t('drawer.notif')" v-model="dashboard.notif"
              :hint="$t('dialog.notif_msg')" persistent-hint inset class="text-caption" />
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions class="pa-2">
        <v-spacer />
        <v-btn prepend-icon="mdi-content-save" color="primary" variant="outlined" :text="$t('dialog.save')"
          @click="save" :loading="loading" />
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-overlay :value="loading" contained class="align-center justify-center">
    <v-progress-circular indeterminate size="64" color="primary" />
  </v-overlay>
</template>

<script>
import axios from "axios";
import SaleChart from "./component/widgets/saleChart.vue";
import TopCommoditiesChart from '@/components/widgets/TopCommoditiesChart.vue';
import TopCostCentersChart from '@/components/widgets/TopCostCentersChart.vue';
import TopIncomeCentersChart from '@/components/widgets/TopIncomeCentersChart.vue';

export default {
  name: "dashboard",
  components: {
    SaleChart,
    TopCommoditiesChart,
    TopCostCentersChart,
    TopIncomeCentersChart,
  },
  data() {
    const self = this;
    return {
      loading: false,
      dialog: false,
      stat: {},
      statements: [],
      permissions: {},
      plugins: [],
      wallet: {},
      costData: { today: 0, week: 0, month: 0, year: 0 },
      incomeData: { today: 0, week: 0, month: 0, year: 0 },
      dashboard: {
        banks: false,
        wallet: false,
        buys: false,
        sells: false,
        commodities: false,
        acc_docs: false,
        accounting_total: false,
        persons: false,
        notif: false,
        sellChart: false,
        topCommodities: false,
        costs: false,
        topCostCenters: false,
        incomes: false,
        topIncomeCenters: false,
      },
    };
  },
  methods: {
    async save() {
      this.loading = true;
      try {
        await axios.post('/api/dashboard/settings/save', this.dashboard);
        this.dialog = false;
      } catch (error) {
        console.error('Save error:', error);
      } finally {
        this.loading = false;
      }
    },
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    },
    async fetchCostData() {
      this.loading = true;
      try {
        const response = await axios.get('/api/cost/dashboard/data');
        this.costData = response.data;
      } catch (error) {
        console.error('Fetch cost data error:', error);
        this.costData = { today: 0, week: 0, month: 0, year: 0 };
      } finally {
        this.loading = false;
      }
    },
    async fetchIncomeData() {
      this.loading = true;
      try {
        const response = await axios.get('/api/income/dashboard/data');
        this.incomeData = response.data;
      } catch (error) {
        console.error('Fetch income data error:', error);
        this.incomeData = { today: 0, week: 0, month: 0, year: 0 };
      } finally {
        this.loading = false;
      }
    },
    async loadData() {
      this.loading = true;
      try {
        const [permissions, settings, plugins, wallet, statements, stats] = await Promise.all([
          axios.post('/api/business/get/user/permissions'),
          axios.post('/api/dashboard/settings/load'),
          axios.post('/api/plugin/get/actives'),
          axios.post('/api/wallet/info'),
          axios.post('/api/general/statements'),
          axios.post('/api/business/stat'),
        ]);
        this.permissions = permissions.data;
        this.dashboard = settings.data.data;
        this.plugins = plugins.data;
        this.wallet = wallet.data;
        this.statements = statements.data;
        this.stat = stats.data;

        if (this.dashboard.costs && this.permissions.cost) {
          await this.fetchCostData();
        }
        if (this.dashboard.incomes && this.permissions.income) {
          await this.fetchIncomeData();
        }
      } catch (error) {
        console.error('Load data error:', error);
      } finally {
        this.loading = false;
      }
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped>
@import 'animate.css';

.card-equal-height {
  height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
</style>