/*
 *  Document   : db_crypto.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Crypto Dashboard Page
 */

// Chart.js Charts, for more examples you can check out http://www.chartjs.org/docs
class pageDashboardCrypto {
  /*
   * Init Charts
   *
   */
  static initChartsBitcoin() {
    // Set Global Chart.js configuration
    Chart.defaults.color = '#818d96';
    Chart.defaults.scale.grid.color = 'transparent';
    Chart.defaults.scale.grid.zeroLineColor = 'transparent';
    Chart.defaults.scale.beginAtZero = true;
    Chart.defaults.elements.point.radius = 0;
    Chart.defaults.elements.point.hoverRadius = 0;
    Chart.defaults.plugins.tooltip.radius = 3;
    Chart.defaults.plugins.legend.display = false;
    // add Custom Font familly
    Chart.defaults.font.family = "Vazir"

    // Get Chart Container
    let chartBitcoinCon = document.getElementById('js-chartjs-bitcoin');

    // Set up labels
    let chartBitcoinlabels = [];
    for (let i = 0; i < 30; i++) {
      chartBitcoinlabels[i] = (i === 29) ? '1 روز قبل' : (30 - i) + ' روز قبل';
    }

    // Crypto Data
    let chartBitcoinData = [10500, 10400, 9500, 8268, 10218, 8250, 8707, 9284, 9718, 9950, 9879, 10147, 10883, 11071, 11332, 11584, 11878, 13540, 16501, 16007, 15142, 14869, 16762, 17276, 16808, 16678, 16771, 12900, 13100, 14000];

    // Init Chart
    if (chartBitcoinCon !== null) {
      let chartBitcoin = new Chart(chartBitcoinCon, {
        type: 'line',
        data: {
          labels: chartBitcoinlabels,
          datasets: [
            {
              label: 'قیمت بیت کوین',
              fill: true,
              backgroundColor: 'rgba(255,193,7,.25)',
              borderColor: 'rgba(255,193,7,1)',
              pointBackgroundColor: 'rgba(255,193,7,1)',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: 'rgba(255,193,7,1)',
              data: chartBitcoinData
            }
          ]
        },
        options: {
          maintainAspectRatio: false,
          tension: .4,
          scales: {
            y: {
              suggestedMin: 6000,
              suggestedMax: 20000
            }
          },
          interaction: {
            intersect: false,
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  return ' ریال ' + context.parsed.y;
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
    this.initChartsBitcoin();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageDashboardCrypto.init());
