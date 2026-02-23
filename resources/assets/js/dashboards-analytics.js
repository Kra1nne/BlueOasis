'use strict';

(function () {
  const dataElement = document.getElementById('monthly-data');
  if (!dataElement) return;

  const monthlyData = JSON.parse(dataElement.textContent);

  const chartOptions = {
    chart: {
      height: 400,
      type: 'line',
      toolbar: { show: false }
    },
    series: [{
      name: 'Monthly Payments',
      data: monthlyData
    }],
    xaxis: {
      categories: [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
      title: { text: 'Month' }
    },
    yaxis: {
      title: { text: 'Amount (₱)' }
    },
    stroke: {
      width: 3,
      curve: 'smooth'
    },
    markers: {
      size: 5,
      colors: ['#7367f0'],
      strokeColors: '#ffffff',
      strokeWidth: 3,
      hover: { size: 7 }
    },
    tooltip: {
      y: {
        formatter: value => `₱${value.toLocaleString()}`
      }
    }
  };

  const chartEl = document.querySelector('#totalProfitLineChart');
  if (chartEl) {
    const chart = new ApexCharts(chartEl, chartOptions);
    chart.render();
  }
})();

(function () {
  const dataElement = document.getElementById('monthly-partial-data');
  if (!dataElement) return;

  const monthlyPartialData = JSON.parse(dataElement.textContent);
  console.log(monthlyPartialData);
  const chartOptions = {
    chart: {
      height: 400,
      type: 'line',
      toolbar: { show: false }
    },
    series: [{
      name: 'Monthly Payments',
      data: monthlyPartialData
    }],
    xaxis: {
      categories: [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
      title: { text: 'Month' }
    },
    yaxis: {
      title: { text: 'Amount (₱)' }
    },
    stroke: {
      width: 3,
      curve: 'smooth'
    },
    markers: {
      size: 5,
      colors: ['#7367f0'],
      strokeColors: '#ffffff',
      strokeWidth: 3,
      hover: { size: 7 }
    },
    tooltip: {
      y: {
        formatter: value => `₱${value.toLocaleString()}`
      }
    }
  };

  const chartEl = document.querySelector('#totalProfitLineChartPartial');
  if (chartEl) {
    const chart = new ApexCharts(chartEl, chartOptions);
    chart.render();
  }
})();
  // ==========================
  // Customer Satisfaction (Rating Counts)
  // ==========================
  const satisfactionEl = document.querySelector('#customerSatisfactionGauge');
  if (satisfactionEl) {
    const ratingCounts = JSON.parse(document.getElementById('rating-counts-data').textContent);
    
    // Prepare data for the chart (5 stars down to 1 star)
    const categories = ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'];
    const values = [
      ratingCounts[5] || 0,
      ratingCounts[4] || 0,
      ratingCounts[3] || 0,
      ratingCounts[2] || 0,
      ratingCounts[1] || 0
    ];
    
    const ratingChartOptions = {
      chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false }
      },
      series: [{
        name: 'Number of Ratings',
        data: values
      }],
      xaxis: { categories },
      colors: ['#FFB400'],
      plotOptions: {
        bar: {
          horizontal: true,
          columnWidth: '50%',
          borderRadius: 6
        }
      },
      dataLabels: {
        enabled: true,
        formatter: val => val.toString(),
        style: { fontSize: '13px', colors: ['#333'] }
      },
      tooltip: {
        y: { formatter: val => `${val} ratings` }
      },
      grid: {
        borderColor: '#e7e7e7'
      }
    };
    
    new ApexCharts(satisfactionEl, ratingChartOptions).render();
  }

  // ==========================
  // Revenue by Category (Bar)
  // ==========================
    // ==========================
  // Revenue by Category (Rooms vs Cottages)
  // ==========================
const categoryEl = document.querySelector('#revenueByCategoryChart');
if (categoryEl) {
    const categoryData = JSON.parse(document.getElementById('revenue-category-data').textContent);
    const categories = Object.keys(categoryData);
    const values = Object.values(categoryData);

    const categoryChartOptions = {
        chart: { type: 'pie', height: 320 },
        series: values,
        labels: categories,
        colors: ['#28C76F', '#00CFE8'], // green and blue
        dataLabels: {
            enabled: true,
            position: 'center',
            formatter: function (val, opts) {
                const rawValue = opts.w.globals.series[opts.seriesIndex];
                return `₱${rawValue.toLocaleString()}`;
            },
            style: { fontSize: '13px', colors: ['#333'] }
        },
        tooltip: {
            y: {
                formatter: val => `₱${val.toLocaleString()}`
            }
        },
        legend: {
            position: 'bottom',
            labels: { colors: '#333' }
        }
    };

    new ApexCharts(categoryEl, categoryChartOptions).render();
}


  const facilityBar = document.querySelector('#facilitiesChart');
  if (facilityBar) {
      const categoryData = JSON.parse(document.getElementById('facility-revenue-data').textContent);

      // Map names and totals from the array of objects
      const categories = categoryData.map(item => item.name);
      const values = categoryData.map(item => item.total);

      const categoryChartOptions = {
          chart: { type: 'bar', height: 320 },
          series: [{ name: 'Revenue', data: values }],
          xaxis: { categories },
          colors: ['#28C76F'],
          plotOptions: {
              bar: {
                  horizontal: false,
                  columnWidth: '50%',
                  borderRadius: 6,
              }
          },
          dataLabels: {
              enabled: true,
              formatter: val => `${val.toLocaleString()}`,
              style: { fontSize: '13px', colors: ['#333'] }
          },
          tooltip: {
              y: { formatter: val => `₱${val.toLocaleString()}` }
          },
          grid: {
              borderColor: '#e7e7e7',
              row: { colors: ['#f9f9f9', 'transparent'], opacity: 0.5 },
          }
      };

      new ApexCharts(facilityBar, categoryChartOptions).render();
  }


  document.addEventListener('DOMContentLoaded', function() {
    const foodsBar = document.querySelector('#foodsChart');
    if (!foodsBar) return;

    // Get chart data from hidden span
    const categoryData = JSON.parse(document.getElementById('foods-revenue-data').textContent);

    // Extract names and totals
    const categories = categoryData.map(item => item.name);
    const values = categoryData.map(item => item.total);

    // Chart configuration
    const categoryChartOptions = {
        chart: { type: 'bar', height: 320 },
        series: [{ name: 'Revenue', data: values }],
        xaxis: { categories },
        colors: ['#28C76F'],
        plotOptions: {
            bar: { horizontal: false, columnWidth: '50%', borderRadius: 6 }
        },
        dataLabels: {
            enabled: true,
            formatter: val => `₱${val.toLocaleString()}`,
            style: { fontSize: '13px', colors: ['#333'] }
        },
        tooltip: { y: { formatter: val => `₱${val.toLocaleString()}` } },
        grid: {
            borderColor: '#e7e7e7',
            row: { colors: ['#f9f9f9', 'transparent'], opacity: 0.5 }
        }
    };

    // Render chart
    new ApexCharts(foodsBar, categoryChartOptions).render();
});

  

  // ==========================
  // Refunds / Cancellations Chart
  // ==========================
  const refundsEl = document.querySelector('#refundsChart');
  if (refundsEl) {
    const refundsData = JSON.parse(document.getElementById('refunds-data').textContent);
    const refundsChartOptions = {
      chart: { type: 'area', height: 300, toolbar: { show: false } },
      series: [{ name: 'Refunds / Cancellations', data: refundsData }],
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      },
      colors: ['#EA5455'],
      fill: { type: 'gradient', gradient: { shadeIntensity: 0.6, opacityFrom: 0.7, opacityTo: 0.3 } },
      tooltip: {
        y: { formatter: val => `₱${val.toLocaleString()}` }
      },
      grid: {
        borderColor: '#e7e7e7',
        row: { colors: ['#f9f9f9', 'transparent'], opacity: 0.5 }
      }
    };
    new ApexCharts(refundsEl, refundsChartOptions).render();
  }

    // ==========================
  // Average Revenue per Reservation Sparkline
  // ==========================
  const avgEl = document.querySelector('#avgRevenueSparkline');
  if (avgEl) {
    const avgData = JSON.parse(document.getElementById('avg-revenue-data').textContent);
    const sparkOptions = {
      chart: {
        type: 'area',
        height: 120,
        sparkline: { enabled: true }
      },
      series: [{ data: avgData }],
      colors: ['#28C76F'],
      stroke: { width: 2.5, curve: 'smooth' },
      fill: { opacity: 0.3 },
      tooltip: {
        y: { formatter: val => `₱${val.toLocaleString()}` }
      }
    };
    new ApexCharts(avgEl, sparkOptions).render();
  }

  