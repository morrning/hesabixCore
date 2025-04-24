<template>
  <v-container fluid>
    <!-- سوئیچ برای انتخاب نوع نمودار -->
    <v-row dense justify="end">
      <v-col cols="auto">
        <v-switch
          v-model="showBarChart"
          :label="$t('dashboard.topCommodities.chartToggle')"
          color="primary"
          density="compact"
          hide-details
        ></v-switch>
      </v-col>
    </v-row>

    <!-- نمایش نمودار انتخاب‌شده -->
    <v-row dense>
      <v-col cols="12" sm="12" md="12" v-if="showBarChart">
        <apexchart ref="barChart" type="bar" height="200rem" :options="barOptions" :series="[{ name: $t('dashboard.topCommodities.salesCount'), data: series }]" ></apexchart>
      </v-col>
      <v-col cols="12" sm="12" md="12" v-else>
        <apexchart ref="pieChart" type="pie" height="200rem" :options="pieOptions" :series="series"></apexchart>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts';

export default {
  name: 'TopCommoditiesChart',
  components: {
    apexchart: VueApexCharts,
  },
  props: {
    commodities: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    const self = this;
    return {
      showBarChart: true, // پیش‌فرض: نمودار میله‌ای
      series: [],
      pieOptions: {
        chart: {
          id: 'top-commodities-pie',
          fontFamily: "'Vazirmatn FD', Arial, sans-serif",
        },
        labels: [],
        colors: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#7BC225', '#FF5733', '#C70039'],
        legend: {
          position: 'top',
        },
        tooltip: {
          y: {
            formatter: function(value, { dataPointIndex }) {
              const unit = self.commodities[dataPointIndex]?.unit || '';
              return `${value} ${unit}`;
            },
          },
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200,
            },
            legend: {
              position: 'bottom',
            },
          },
        }],
      },
      barOptions: {
        chart: {
          id: 'top-commodities-bar',
          fontFamily: "'Vazirmatn FD', Arial, sans-serif",
        },
        xaxis: {
          categories: [],
        },
        colors: ['#36A2EB'],
        dataLabels: {
          enabled: true,
          formatter: function(value, { dataPointIndex }) {
            const unit = self.commodities[dataPointIndex]?.unit || '';
            return `${value} ${unit}`;
          },
        },
        tooltip: {
          y: {
            formatter: function(value, { dataPointIndex }) {
              const unit = self.commodities[dataPointIndex]?.unit || '';
              return `${value} ${unit}`;
            },
          },
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
          },
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200,
            },
          },
        }],
      },
    };
  },
  watch: {
    commodities: {
      immediate: true,
      handler(newVal) {
        this.updateCharts(newVal);
      },
    },
  },
  methods: {
    updateCharts(commodities) {
      if (!commodities || !Array.isArray(commodities) || commodities.length === 0) {
        this.pieOptions.labels = [];
        this.barOptions.xaxis.categories = [];
        this.series = [];
        return;
      }

      // فیلتر کردن داده‌های معتبر
      const validCommodities = commodities.filter(item => {
        const isValid = item && typeof item.name === 'string' && typeof item.count === 'number';
        if (!isValid) {
          console.warn('Invalid commodity item:', item);
        }
        return isValid;
      });

      if (validCommodities.length === 0) {
        this.pieOptions.labels = [];
        this.barOptions.xaxis.categories = [];
        this.series = [];
        return;
      }

      // به‌روزرسانی داده‌ها
      this.pieOptions.labels = validCommodities.map(item => item.name);
      this.barOptions.xaxis.categories = validCommodities.map(item => item.name);
      this.series = validCommodities.map(item => item.count);

      // رفرش کردن نمودار فعلی
      if (this.showBarChart && this.$refs.barChart) {
        this.$refs.barChart.refresh();
      } else if (!this.showBarChart && this.$refs.pieChart) {
        this.$refs.pieChart.refresh();
      }
    },
  },
};
</script>

<style scoped>
.v-container {
  padding: 0 !important;
}

@media (max-width: 959px) {
  .v-col {
    flex: 0 0 100%;
    max-width: 100%;
  }
}

@media (min-width: 960px) {
  .v-col {
    flex: 0 0 50%;
    max-width: 50%;
  }
}
</style>