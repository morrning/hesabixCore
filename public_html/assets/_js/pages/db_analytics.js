/*
 *  Document   : db_analytics.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Analytics Dashboard Page
 */

// Chart.js Charts, for more examples you can check out http://www.chartjs.org/docs
class pageDashboardAnalytics {
  /*
   * Init Charts
   *
   */
  static initChartsBars() {
    // Set Global Chart.js configuration
    Chart.defaults.color = '#818d96';
    Chart.defaults.scale.grid.color = 'transparent';
    Chart.defaults.scale.grid.zeroLineColor = 'transparent';
    Chart.defaults.scale.beginAtZero = true;
    Chart.defaults.elements.line.borderWidth = 1;
    Chart.defaults.plugins.legend.labels.boxWidth = 12;
    
    // add Custom Font familly
    Chart.defaults.font.family = "Vazir"

    // Get Chart Containers
    let chartBarsCon = document.getElementById('js-chartjs-analytics-bars');

    // Set Chart and Chart Data variables
    let chartBars, chartLinesBarsData;

    // Bars Chart Data
    chartLinesBarsData = {
      labels: ['شنبه', 'یک شنبه', 'دو شنبه', 'سه شنبه', 'چهار شنبه', 'پنج شنبه', 'جمعه'],
      datasets: [
        {
          label: 'این هفته',
          fill: true,
          backgroundColor: 'rgba(103, 114, 229, .75)',
          borderColor: 'rgba(103, 114, 229, 1)',
          pointBackgroundColor: 'rgba(103, 114, 229, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(103, 114, 229, 1)',
          data: [500, 750, 650, 570, 582, 480, 680]
        },
        {
          label: 'هفته گذشته',
          fill: true,
          backgroundColor: 'rgba(108, 117, 125, .15)',
          borderColor: 'rgba(108, 117, 125, .75)',
          pointBackgroundColor: 'rgba(108, 117, 125, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(108, 117, 125, 1)',
          data: [352, 530, 541, 521, 410, 395, 460]
        }
      ]
    };

    // Init Chart
    if (chartBarsCon !== null) {
      chartBars = new Chart(chartBarsCon, {type: 'bar', data: chartLinesBarsData});
    }
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initChartsBars();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageDashboardAnalytics.init());
