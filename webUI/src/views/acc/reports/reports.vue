<template>
  <v-toolbar flat color="toolbar" class="">
    <v-toolbar-title class="primary--text">گزارشات</v-toolbar-title>
  </v-toolbar>
  <v-container fluid class="reports-container">
    <v-row>
      <!-- اشخاص -->
      <v-col cols="12" md="6" lg="4">
        <v-card outlined class="report-card">
          <v-card-subtitle class="card-title">
            <v-icon class="mr-2">mdi-account</v-icon>
            اشخاص
          </v-card-subtitle>
          <v-list dense class="report-list">
            <template v-for="item in personReports" :key="item.to">
              <v-list-item v-if="item && (!item.showIf || isPluginActive(item.showIf))" :to="item.to" class="list-item">
                <template v-slot:default>
                  <v-icon small class="mr-2">mdi-chevron-left</v-icon>
                  {{ item.text }}
                </template>
              </v-list-item>
            </template>
          </v-list>
        </v-card>
      </v-col>

      <!-- بانکداری -->
      <v-col cols="12" md="6" lg="4">
        <v-card outlined class="report-card">
          <v-card-subtitle class="card-title">
            <v-icon class="mr-2">mdi-bank</v-icon>
            بانکداری
          </v-card-subtitle>
          <v-list dense class="report-list">
            <v-list-item v-for="item in bankReports" :key="item.to" :to="item.to" class="list-item">
              <template v-slot:default>
                <v-icon small class="mr-2">mdi-chevron-left</v-icon>
                {{ item.text }}
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- گزارشات پایه -->
      <v-col cols="12" md="6" lg="4">
        <v-card outlined class="report-card">
          <v-card-subtitle class="card-title">
            <v-icon class="mr-2">mdi-cog</v-icon>
            گزارشات پایه
          </v-card-subtitle>
          <v-list dense class="report-list">
            <v-list-item to="/acc/business/logs" class="list-item">
              <template v-slot:default>
                <v-icon small class="mr-2">mdi-chevron-left</v-icon>
                تاریخچه رویدادها
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- کالا و خدمات -->
      <v-col cols="12" md="6" lg="4">
        <v-card outlined class="report-card">
          <v-card-subtitle class="card-title">
            <v-icon class="mr-2">mdi-package-variant</v-icon>
            کالا و خدمات
          </v-card-subtitle>
          <v-list dense class="report-list">
            <v-list-item to="/acc/reports/commodity/buysell" class="list-item">
              <template v-slot:default>
                <v-icon small class="mr-2">mdi-chevron-left</v-icon>
                خرید و فروش به تفکیک کالا
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- حسابداری (conditional) -->
      <v-col v-if="isPluginActive('accpro')" cols="12" md="6" lg="4">
        <v-card outlined class="report-card">
          <v-card-subtitle class="card-title">
            <v-icon class="mr-2">mdi-format-list-bulleted</v-icon>
            حسابداری
          </v-card-subtitle>
          <v-list dense class="report-list">
            <v-list-item v-for="item in accountingReports" :key="item.to" :to="item.to" class="list-item">
              <template v-slot:default>
                <v-icon small class="mr-2">mdi-chevron-left</v-icon>
                {{ item.text }}
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: "reports",
  data() {
    return {
      plugins: [],
      personReports: [
        { text: 'کارت حساب', to: '/acc/persons/card/view/', showIf: null },
        { text: 'بدهکاران', to: '/acc/reports/persons/debtors', showIf: null },
        { text: 'بستانکاران', to: '/acc/reports/persons/depositors', showIf: null },
        { text: 'خرید و فروش های اشخاص', to: '/acc/reports/persons/buysell', showIf: null },
        { text: 'گزارش تفضیلی اشخاص', to: '/acc/reports/persons/withdet', showIf: 'accpro' }
      ],
      bankReports: [
        { text: 'گردش حساب بانک', to: '/acc/banks/card/view/' },
        { text: 'گردش حساب صندوق', to: '/acc/cashdesk/card/view/' },
        { text: 'گردش حساب تنخواه گردان', to: '/acc/salary/card/view/' }
      ],
      accountingReports: [
        { text: 'ترازنامه', to: '/acc/reports/acc/balance_sheet' },
        { text: this.$t('dialog.explore_accounts'), to: '/acc/reports/acc/explore_accounts' }
      ]
    }
  },
  methods: {
    loadData() {
      axios.post('/api/plugin/get/actives').then((response) => {
        this.plugins = response.data;
      });
    },
    isPluginActive(plugName) {
      return this.plugins[plugName] !== undefined;
    }
  },
  mounted() {
    this.loadData();
  }
}
</script>

<style scoped>
.reports-container {
  padding: 32px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  min-height: calc(100vh - 64px);
}

.report-card {
  height: 100%;
  border-radius: 16px;
  background-color: white;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.report-card:hover {
  border-color: #3b82f6;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  color: #334155;
  border-radius: 16px 16px 0 0;
  display: flex;
  align-items: center;
  position: relative;
  border-bottom: 1px solid #e2e8f0;
}

.card-title::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 3px;
  height: 100%;
  background: linear-gradient(to bottom, #2563eb, #3b82f6);
  border-radius: 0 16px 16px 0;
}

.card-title .v-icon {
  color: #2563eb;
  margin-left: 12px;
  background-color: #eff6ff;
  padding: 8px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
  font-size: 1.2rem;
}

.report-card:hover .card-title .v-icon {
  background-color: #dbeafe;
  box-shadow: 0 4px 6px rgba(37, 99, 235, 0.15);
}

.report-list {
  padding: 8px 0;
}

.list-item {
  margin: 4px 8px;
  border-radius: 10px;
  color: #475569;
  font-weight: 500;
  font-size: 0.95rem;
}

.list-item:hover {
  background: linear-gradient(135deg, #eff6ff, #f0f7ff);
  color: #2563eb;
}

.v-icon {
  color: #2563eb;
}
</style>