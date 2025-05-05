<template>
  <v-toolbar flat color="grey-lighten-3" class="">
    <v-toolbar-title class="primary--text">گزارشات</v-toolbar-title>
  </v-toolbar>
  <v-container fluid class="">
    <v-row>
      <!-- اشخاص -->
      <v-col cols="12" md="6">
        <v-card outlined color="grey-lighten-2">
          <v-card-subtitle class="pb-0">
            <v-icon small left>mdi-account</v-icon>
            اشخاص
          </v-card-subtitle>
          <v-list dense color="grey-lighten-2">
            <v-list-item v-for="item in personReports" :key="item.to" :to="item.to">
              <v-list-item-content>{{ item.text }}</v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- بانکداری -->
      <v-col cols="12" md="6">
        <v-card outlined color="grey-lighten-2">
          <v-card-subtitle class="pb-0">
            <v-icon small left>mdi-bank</v-icon>
            بانکداری
          </v-card-subtitle>
          <v-list dense color="grey-lighten-2">
            <v-list-item v-for="item in bankReports" :key="item.to" :to="item.to">
              <v-list-item-content>{{ item.text }}</v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- گزارشات پایه -->
      <v-col cols="12" md="6">
        <v-card outlined color="grey-lighten-2">
          <v-card-subtitle class="pb-0">
            <v-icon small left>mdi-cog</v-icon>
            گزارشات پایه
          </v-card-subtitle>
          <v-list dense color="grey-lighten-2">
            <v-list-item to="/acc/business/logs">
              <v-list-item-content>تاریخچه رویدادها</v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- کالا و خدمات -->
      <v-col cols="12" md="6">
        <v-card outlined color="grey-lighten-2">
          <v-card-subtitle class="pb-0">
            <v-icon small left>mdi-package-variant</v-icon>
            کالا و خدمات
          </v-card-subtitle>
          <v-list dense color="grey-lighten-2">
            <v-list-item to="/acc/reports/commodity/buysell">
              <v-list-item-content>خرید و فروش به تفکیک کالا</v-list-item-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <!-- حسابداری (conditional) -->
      <v-col v-if="isPluginActive('accpro')" cols="12" md="6">
        <v-card outlined color="grey-lighten-2">
          <v-card-subtitle class="pb-0">
            <v-icon small left>mdi-format-list-bulleted</v-icon>
            حسابداری
          </v-card-subtitle>
          <v-list dense color="grey-lighten-2">
            <v-list-item v-for="item in accountingReports" :key="item.to" :to="item.to">
              <v-list-item-content>{{ item.text }}</v-list-item-content>
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
        { text: 'کارت حساب', to: '/acc/persons/card/view/' },
        { text: 'بدهکاران', to: '/acc/reports/persons/debtors' },
        { text: 'بستانکاران', to: '/acc/reports/persons/depositors' },
        { text: 'خرید و فروش های اشخاص', to: '/acc/reports/persons/buysell' }
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
.v-card {
  transition: all 0.2s;
}
.v-card:hover {
  filter: brightness(95%);
}
</style>