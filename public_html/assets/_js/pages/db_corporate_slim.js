/*
 *  Document   : db_corporate_slim.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Corporate Slim Dashboard Page
 */

// Chart.js Charts, for more examples you can check out http://www.chartjs.org/docs
class DbCorporateSlim {
  /*
   * Init Charts
   *
   */
  static initCorporateChartJS() {
    // Set Global Chart.js configuration
    Chart.defaults.color = '#7c7c7c';
    Chart.defaults.scale.grid.color = "transparent";
    Chart.defaults.scale.grid.zeroLineColor = "transparent";
    Chart.defaults.scale.display = false;
    Chart.defaults.scale.beginAtZero = true;
    Chart.defaults.elements.line.borderWidth = 2;
    Chart.defaults.elements.point.radius = 6;
    Chart.defaults.elements.point.hoverRadius = 10;
    Chart.defaults.plugins.tooltip.radius = 3;
    Chart.defaults.plugins.legend.display = false;
    
    // add Custom Font familly
    Chart.defaults.font.family = "Vazir"

    // Chart Containers
    let chartCorporateSlimProjectsCon = document.getElementById('js-chartjs-corporate-slim-projects');
    let chartCorporateSlimTicketsCon = document.getElementById('js-chartjs-corporate-slim-tickets');

    // Chart Variables
    let chartCorporateSlimProjects, chartCorporateSlimTickets;

    // Lines Charts Data
    let chartCorporateSlimProjectsData = {
      labels: ['شنبه', 'یک شنبه', 'دو شنبه', 'سه شنبه', 'چهار شنبه', 'پنج شنبه', 'جمعه'], 
      datasets: [
        {
          label: 'این هفته',
          fill: true,
          backgroundColor: 'rgba(73, 80, 87, .1)',
          borderColor: 'rgba(73, 80, 87, .35)',
          pointBackgroundColor: 'rgba(73, 80, 87, .5)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(73, 80, 87, .5)',
          data: [14, 16, 6, 14, 10, 19, 12]
        }
      ]
    };

    let chartCorporateSlimProjectsOptions = {
      tension: .4,
      scales: {
        y: {
          suggestedMin: 0,
          suggestedMax: 22
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

    let chartCorporateSlimTicketsData = {
      labels: ['شنبه', 'یک شنبه', 'دو شنبه', 'سه شنبه', 'چهار شنبه', 'پنج شنبه', 'جمعه'], 
      datasets: [
        {
          label: 'این هفته',
          fill: true,
          backgroundColor: 'rgba(130, 181, 75, .1)',
          borderColor: 'rgba(130, 181, 75, .35)',
          pointBackgroundColor: 'rgba(130, 181, 75, .5)',
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: 'rgba(130, 181, 75, .5)',
          data: [35, 20, 29, 20, 40, 34, 45]
        }
      ]
    };

    let chartCorporateSlimTicketsOptions = {
      tension: .4,
      scales: {
        y: {
          suggestedMin: 0,
          suggestedMax: 50
        }
      },
      interaction: {
        intersect: false,
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function (context) {
              return ' ' + context.parsed.y + ' تیکت';
            }
          }
        }
      }
    };

    // Init Charts
    if (chartCorporateSlimProjectsCon !== null) {
      chartCorporateSlimProjects = new Chart(chartCorporateSlimProjectsCon, {
        type: 'line',
        data: chartCorporateSlimProjectsData,
        options: chartCorporateSlimProjectsOptions
      });
    }

    if (chartCorporateSlimTicketsCon !== null) {
      chartCorporateSlimTickets = new Chart(chartCorporateSlimTicketsCon, {
        type: 'line',
        data: chartCorporateSlimTicketsData,
        options: chartCorporateSlimTicketsOptions
      });
    }
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.initCorporateChartJS();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => DbCorporateSlim.init());
