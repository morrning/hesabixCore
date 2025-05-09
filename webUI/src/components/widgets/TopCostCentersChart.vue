<!-- src/components/widgets/TopCostCentersChart.vue -->
<template>
    <v-container fluid class="pt-0">
      <v-row justify="end">
        <v-col cols="12" sm="6" md="6">
          <v-select
            v-model="period"
            :items="periodOptions"
            label="بازه زمانی"
            density="compact"
            variant="outlined"
            @update:modelValue="fetchData"
          />
        </v-col>
        <v-col cols="12" sm="6" md="6">
          <v-select
            v-model="limit"
            :items="limitOptions"
            label="تعداد"
            density="compact"
            variant="outlined"
            @update:modelValue="fetchData"
          />
        </v-col>
      </v-row>
      <div v-if="isLoading" class="d-flex justify-center align-center empty-state">
        <v-progress-circular
          indeterminate
          color="primary"
        ></v-progress-circular>
      </div>
      <div v-else-if="!hasData" class="d-flex justify-center align-center empty-state">
        <v-icon icon="mdi-chart-pie" size="large" color="grey-lighten-1" class="mb-2"></v-icon>
        <div class="text-grey-darken-1">اطلاعاتی برای نمایش یافت نشد</div>
      </div>
      <apexchart v-else type="pie" :options="chartOptions" :series="series" />
    </v-container>
  </template>
  
  <script>
  import VueApexCharts from 'vue3-apexcharts';
  import axios from 'axios';
  
  export default {
    name: 'TopCostCentersChart',
    components: {
      apexchart: VueApexCharts,
    },
    data() {
      return {
        period: 'month',
        limit: 10,
        isLoading: false,
        series: [],
        chartOptions: {
          chart: {
            type: 'pie',
            id: 'top-commodities-pie',
            fontFamily: "'Vazirmatn FD', Arial, sans-serif",
          },
          labels: [],
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
        periodOptions: [
          { title: 'امروز', value: 'today' },
          { title: 'این ماه', value: 'month' },
          { title: 'سال مالی', value: 'year' },
        ],
        limitOptions: [
          { title: '۵', value: 5 },
          { title: '۱۰', value: 10 },
          { title: '۲۰', value: 20 },
          { title: 'همه', value: 0 },
        ],
      };
    },
    computed: {
      hasData() {
        return this.series && this.series.length > 0;
      }
    },
    methods: {
      async fetchData() {
        this.isLoading = true;
        try {
          const response = await axios.get('/api/cost/top-centers', {
            params: { period: this.period, limit: this.limit },
          });
          if (response.data.series && response.data.series.length > 0) {
            this.series = response.data.series;
            this.chartOptions = { ...this.chartOptions, labels: response.data.labels };
          } else {
            this.series = [];
            this.chartOptions.labels = [];
          }
        } catch (error) {
          console.error('Error fetching top cost centers:', error);
          this.series = [];
          this.chartOptions.labels = [];
        } finally {
          this.isLoading = false;
        }
      },
    },
    mounted() {
      this.fetchData();
    },
  };
  </script>

<style scoped>
.empty-state {
  height: 200px;
  flex-direction: column;
  text-align: center;
}
</style>