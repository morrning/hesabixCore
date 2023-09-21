/*
 *  Document   : be_pages_dashboard.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in default Dashboard v1 Page
 */

// Chart.js Charts, for more examples you can check out http://www.chartjs.org/docs
class pageDashboardv1 {
  /*
   * Init Charts
   *
   */
  static initChartsMain() {
    // Set Global Chart.js configuration
    Chart.defaults.color = '#818d96';
    Chart.defaults.scale.grid.color = 'transparent';
    Chart.defaults.scale.grid.zeroLineColor = 'transparent';
    Chart.defaults.scale.display = false;
    Chart.defaults.scale.beginAtZero = true;
    Chart.defaults.elements.point.radius = 0;
    Chart.defaults.elements.point.hoverRadius = 0;
    Chart.defaults.plugins.tooltip.radius = 3;
    Chart.defaults.plugins.legend.labels.boxWidth = 12;
    
    // add Custom Font familly
    Chart.defaults.font.family = "Vazir"
    // Get Chart Containers
    let chartMainCon = document.getElementById('js-chartjs-dashboard-earnings');

    // Set Main Chart variables
    let chartMain, chartMainOptions, chartMainData, chartMainDataYear, chartMainDataMonth, chartMainDataWeek;

    // Main Chart Options
    chartMainOptions = {
      maintainAspectRatio: false,
      tension: .4,
      scales: {
        y: {
          suggestedMin: 0,
          suggestedMax: 260
        }
      },
      interaction: {
        intersect: false,
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function (context) {
              return ' ' + context.parsed.y + ' فروش';
            }
          }
        }
      }
    };

    // Main Chart Default Data
    chartMainData = {
      labels: ['دی', 'بهمن', 'اسفند', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر'],
      datasets: [
        {
          label: 'امسال',
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .5)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, 1)',
          data: [50, 210, 110, 90, 230, 130, 190, 75, 155, 120, 140, 230]
        },
        {
          label: 'سال گذشته',
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .2)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, .2)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, .2)',
          data: [210, 150, 90, 220, 150, 216, 143, 150, 240, 230, 136, 150]
        }
      ]
    };

    // Main Chart for Year
    chartMainDataYear = {
      labels: ['دی', 'بهمن', 'اسفند', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر'],
      datasets: [
        {
          label: 'امسال',
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .5)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, 1)',
          data: [50, 210, 110, 90, 230, 130, 190, 75, 155, 120, 140, 230]
        },
        {
          label: 'سال گذشته', 
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .2)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, .2)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, .2)',
          data: [210, 150, 90, 220, 150, 216, 143, 150, 240, 230, 136, 150]
        }
      ]
    };

    // Set up month labels
    let chartMainDataMonthLabels = [];

    for (let i = 0; i < 30; i++) {
      chartMainDataMonthLabels[i] = (i === 29) ? '1 روز قبل' : (30 - i) + ' روز قبل';
    }

    // Main Chart Data for Month
    chartMainDataMonth = {
      labels: chartMainDataMonthLabels,
      datasets: [
        {
          label: 'این ماه',
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .5)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, 1)',
          data: [50, 210, 110, 90, 230, 130, 190, 75, 155, 120, 140, 230, 50, 210, 110, 90, 230, 130, 155, 120, 140, 230, 50, 210, 110, 90, 230, 130, 190, 75]
        },
        {
          label: 'ماه گذشته',
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .2)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, .2)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, .2)',
          data: [210, 150, 90, 220, 150, 216, 143, 150, 136, 150, 210, 150, 90, 220, 150, 216, 240, 230, 136, 150, 210, 150, 90, 220, 150, 216, 143, 150, 240, 230]
        }
      ]
    };

    // Main Chart Data for Week
    chartMainDataWeek = {
      labels: ['شنبه', 'یک شنبه', 'دو شنبه', 'سه شنبه', 'چهار شنبه', 'پنج شنبه', 'جمعه'],
      datasets: [
        {
          label: 'این هفته',
          fill: true,
          backgroundColor: 'rgba(6, 101, 208, .5)',
          borderColor: 'transparent',
          pointBackgroundColor: 'rgba(6, 101, 208, 1)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(6, 101, 208, 1)',
          data: [34, 42, 62, 78, 39, 83, 98]
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
          data: [130, 95, 125, 160, 187, 110, 143]
        }
      ]
    };

    // Init Main Chart
    if (chartMainCon !== null) {
      chartMain = new Chart(chartMainCon, {
        type: 'line',
        data: chartMainData,
        options: chartMainOptions
      });
    }

    // Toggle to Week data
    document.querySelector('[data-toggle="dashboard-chart-set-week"]').addEventListener('click', () => {
      chartMain.data.labels = chartMainDataWeek.labels;
      chartMain.data.datasets[0] = chartMainDataWeek.datasets[0];
      chartMain.data.datasets[1] = chartMainDataWeek.datasets[1];
      chartMain.update();
    });

    // Toggle to Month data
    document.querySelector('[data-toggle="dashboard-chart-set-month"]').addEventListener('click', () => {
      chartMain.data.labels = chartMainDataMonth.labels;
      chartMain.data.datasets[0] = chartMainDataMonth.datasets[0];
      chartMain.data.datasets[1] = chartMainDataMonth.datasets[1];
      chartMain.update();
    });

    // Toggle to Year data
    document.querySelector('[data-toggle="dashboard-chart-set-year"]').addEventListener('click', () => {
      chartMain.data.labels = chartMainDataYear.labels;
      chartMain.data.datasets[0] = chartMainDataYear.datasets[0];
      chartMain.data.datasets[1] = chartMainDataYear.datasets[1];
      chartMain.update();
    });
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initChartsMain();
  }
}

// Initialize when page loads
jQuery(() => {
  pageDashboardv1.init();
});
