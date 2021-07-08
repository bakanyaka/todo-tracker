<script>
  import * as moment from 'moment';
  import 'moment/locale/ru';
  import { Line, mixins } from 'vue-chartjs';

  const { reactiveProp } = mixins;

  moment.locale('ru');

  export default {
    name: 'issues-chart',
    mixins: [reactiveProp],
    extends: Line,
    data() {
      return {
        options: {
          maintainAspectRatio: false,
          scales: {
            xAxes: [
              {
                type: 'time',
                time: {
                  unit: 'day',
                  displayFormats: {
                    day: 'X',
                  },
                },
                ticks: {
                  callback: function(value) {
                    return moment(value, 'X').format('D MMM');
                  },
                },
              }],
            yAxes: [
              {
                ticks: {
                  beginAtZero: true,
                  suggestedMax: 70,
                },
              }],
          },
        },
      };
    },
    mounted() {
      this.renderChart(this.chartData, this.options);
    },
  };
</script>

<style scoped>

</style>
