<template>
  <v-card class="top-commodities-chart" elevation="0" variant="outlined">
    <v-card-title class="d-flex align-center justify-space-between pa-4">
      <span class="text-h6">{{ $t('dashboard.topCommodities.title') }}</span>
      <div class="d-flex align-center gap-2">
        <v-select
          v-model="period"
          :items="periodOptions"
          :label="$t('dashboard.topCommodities.period')"
          density="compact"
          variant="outlined"
          hide-details
          class="period-select"
          @update:modelValue="fetchData"
        ></v-select>
        <v-select
          v-model="limit"
          :items="limitOptions"
          :label="$t('dashboard.topCommodities.limit')"
          density="compact"
          variant="outlined"
          hide-details
          class="limit-select"
          @update:modelValue="fetchData"
        ></v-select>
      </div>
    </v-card-title>

    <v-card-text class="pa-4">
      <div class="d-flex justify-end mb-4">
        <v-btn-group>
          <v-btn
            :color="activeTab === 'quantity' ? 'primary' : 'grey'"
            variant="text"
            @click="activeTab = 'quantity'"
          >
            {{ $t('dashboard.topCommodities.byQuantity') }}
          </v-btn>
          <v-btn
            :color="activeTab === 'price' ? 'primary' : 'grey'"
            variant="text"
            @click="activeTab = 'price'"
          >
            {{ $t('dashboard.topCommodities.byPrice') }}
          </v-btn>
        </v-btn-group>
      </div>

      <div class="chart-container">
        <apexchart
          v-if="showBarChart"
          ref="barChart"
          type="bar"
          height="300"
          :options="barOptions"
          :series="[{ name: activeTab === 'quantity' ? $t('dashboard.topCommodities.salesCount') : $t('dashboard.topCommodities.salesPrice'), data: series }]"
        ></apexchart>
        <apexchart
          v-else
          ref="pieChart"
          type="pie"
          height="300"
          :options="pieOptions"
          :series="series"
        ></apexchart>
      </div>

      <div class="d-flex justify-end mt-4">
        <v-switch
          v-model="showBarChart"
          :label="$t('dashboard.topCommodities.chartToggle')"
          color="primary"
          density="compact"
          hide-details
        ></v-switch>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts';
import axios from 'axios';

export default {
  name: 'TopCommoditiesChart',
  components: {
    apexchart: VueApexCharts,
  },
  data() {
    const self = this;
    return {
      activeTab: 'quantity',
      showBarChart: true,
      period: 'year',
      limit: 5,
      series: [],
      commodities: [],
      periodOptions: [
        { title: this.$t('dashboard.period.today'), value: 'today' },
        { title: this.$t('dashboard.period.week'), value: 'week' },
        { title: this.$t('dashboard.period.month'), value: 'month' },
        { title: this.$t('dashboard.period.year'), value: 'year' },
      ],
      limitOptions: [
        { title: '۳', value: 3 },
        { title: '۵', value: 5 },
        { title: '۷', value: 7 },
        { title: '۱۰', value: 10 },
      ],
      pieOptions: {
        chart: {
          id: 'top-commodities-pie',
          fontFamily: "'Vazirmatn FD', Arial, sans-serif",
          toolbar: { show: false }
        },
        labels: [],
        colors: ['#2196F3', '#4CAF50', '#FFC107', '#F44336', '#9C27B0', '#00BCD4', '#FF9800', '#795548', '#607D8B', '#E91E63'],
        legend: {
          position: 'bottom',
          fontSize: '14px',
          fontFamily: "'Vazirmatn FD', Arial, sans-serif",
          markers: {
            width: 12,
            height: 12,
            radius: 6
          }
        },
        tooltip: {
          y: {
            formatter: function(value, { dataPointIndex }) {
              const isPrice = self.activeTab === 'price';
              if (isPrice) {
                return `${self.$filters.formatNumber(value)} ${self.$t('currency.irr.short')}`;
              }
              return `${self.$filters.formatNumber(value)} ${self.commodities[dataPointIndex]?.unit || ''}`;
            },
          },
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: { width: '100%' },
            legend: { position: 'bottom' }
          }
        }]
      },
      barOptions: {
        chart: {
          id: 'top-commodities-bar',
          fontFamily: "'Vazirmatn FD', Arial, sans-serif",
          toolbar: { show: false }
        },
        xaxis: {
          categories: [],
          labels: {
            style: {
              fontSize: '12px',
              fontFamily: "'Vazirmatn FD', Arial, sans-serif"
            }
          }
        },
        yaxis: {
          labels: {
            formatter: function(value) {
              const isPrice = self.activeTab === 'price';
              if (isPrice) {
                return `${self.$filters.formatNumber(value)} ${self.$t('currency.irr.short')}`;
              }
              return self.$filters.formatNumber(value);
            }
          }
        },
        colors: ['#2196F3'],
        dataLabels: {
          enabled: true,
          formatter: function(value, { dataPointIndex }) {
            const isPrice = self.activeTab === 'price';
            if (isPrice) {
              return `${self.$filters.formatNumber(value)} ${self.$t('currency.irr.short')}`;
            }
            return `${self.$filters.formatNumber(value)} ${self.commodities[dataPointIndex]?.unit || ''}`;
          },
          style: {
            fontSize: '12px',
            fontFamily: "'Vazirmatn FD', Arial, sans-serif"
          }
        },
        tooltip: {
          y: {
            formatter: function(value, { dataPointIndex }) {
              const isPrice = self.activeTab === 'price';
              if (isPrice) {
                return `${self.$filters.formatNumber(value)} ${self.$t('currency.irr.short')}`;
              }
              return `${self.$filters.formatNumber(value)} ${self.commodities[dataPointIndex]?.unit || ''}`;
            },
          },
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 4
          },
        },
        grid: {
          borderColor: '#f1f1f1',
          strokeDashArray: 4
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: { width: '100%' }
          }
        }]
      }
    };
  },
  watch: {
    activeTab() {
      this.fetchData();
    }
  },
  methods: {
    async fetchData() {
      try {
        const [quantityResponse, priceResponse] = await Promise.all([
          axios.post('/api/report/top-selling-commodities', {
            period: this.period,
            limit: this.limit
          }),
          axios.post('/api/report/top-selling-commodities-by-price', {
            period: this.period,
            limit: this.limit
          })
        ]);
        
        this.updateCharts(
          this.activeTab === 'quantity' ? quantityResponse.data : priceResponse.data
        );
      } catch (error) {
        console.error('Fetch data error:', error);
        this.updateCharts([]);
      }
    },
    updateCharts(commodities) {
      if (!commodities || !Array.isArray(commodities) || commodities.length === 0) {
        this.pieOptions.labels = [];
        this.barOptions.xaxis.categories = [];
        this.series = [];
        this.commodities = [];
        return;
      }

      const validCommodities = commodities.filter(item => {
        const isValid = item && typeof item.name === 'string' && 
          (typeof item.count === 'number' || typeof item.totalPrice === 'number');
        if (!isValid) {
          console.warn('Invalid commodity item:', item);
        }
        return isValid;
      });

      if (validCommodities.length === 0) {
        this.pieOptions.labels = [];
        this.barOptions.xaxis.categories = [];
        this.series = [];
        this.commodities = [];
        return;
      }

      this.commodities = validCommodities;
      this.pieOptions.labels = validCommodities.map(item => item.name);
      this.barOptions.xaxis.categories = validCommodities.map(item => item.name);
      this.series = validCommodities.map(item => 
        this.activeTab === 'price' ? item.totalPrice : item.count
      );

      if (this.showBarChart && this.$refs.barChart) {
        this.$refs.barChart.refresh();
      } else if (!this.showBarChart && this.$refs.pieChart) {
        this.$refs.pieChart.refresh();
      }
    }
  },
  mounted() {
    this.fetchData();
  }
};
</script>

<style scoped>
.top-commodities-chart {
  height: 100%;
}

.chart-container {
  min-height: 300px;
  width: 100%;
}

.period-select,
.limit-select {
  width: 120px;
}

.gap-2 {
  gap: 8px;
}

:deep(.v-card-title) {
  border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  padding: 12px 16px;
}

:deep(.v-card-text) {
  padding: 12px 16px;
}

:deep(.v-btn-group) {
  border-radius: 8px;
  overflow: hidden;
}

:deep(.v-btn) {
  min-width: 100px;
}

@media (max-width: 600px) {
  .chart-container {
    min-height: 250px;
  }
  
  .period-select,
  .limit-select {
    width: 100px;
  }
  
  :deep(.v-btn) {
    min-width: 80px;
    font-size: 0.875rem;
  }
}
</style>