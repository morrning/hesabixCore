/*
 *  Document   : be_pages_dashboard.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Default Dashboard Page
 */

// Chart.js Charts, for more examples you can check out http://www.chartjs.org/docs
class pageDashboard {
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
          backgroundColor: 'rgba(6, 101, 208, .6)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, 1)',
          data: [73, 68, 69, 53, 60, 72, 82]
        },
        {
          label: 'هفته گذشته',
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .2)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, .2)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, .2)',
          data: [62, 32, 59, 55, 52, 56, 73]
        }
      ]
    };

    // Init Chart
    if (chartBarsCon !== null) {
      chartBars = new Chart(chartBarsCon, {
        type: 'bar',
        data: chartLinesBarsData,
        options: {
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  return context.dataset.label + ': ' + context.parsed.y + ' مشتری';
                }
              }
            }
          }
        }
      });
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
Dashmix.onLoad(() => pageDashboard.init());
