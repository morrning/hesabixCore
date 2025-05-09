<template>
  <div>
    <apexchart ref="chart" height="200rem" type="bar" :options="options" :series="series"></apexchart>
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: "saleChart",
  data() {
    const self = this;
    return {
      options: {        
        chart: {
          id: 'vuechart-example',
          fontFamily: "'Vazirmatn FD', Arial, sans-serif",
        },
        xaxis: {
          categories: []
        }
      },
      series: [{
        name: self.$t('drawer.sell'),
        data: []
      }]
    }
  },
  mounted() {
    this.updateChart();
  },
  methods: {
    updateChart() {
      axios.post('/api/sell/chart/data').then((response) => {
        this.options.xaxis.categories = response.data.dayNames.reverse();
        this.series[0].data = response.data.daySells.reverse();
        this.$refs.chart.refresh();
      });
    },
  }
}
</script>

<style scoped></style>