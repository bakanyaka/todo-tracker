<template>
    <b-card>
        <b-row>
            <b-col sm="4" class="mb-3">
                <h4 class="card-title mb-0">Общий отчет за период</h4>
                <div class="small text-muted">{{period.startDate}} - {{period.endDate}}</div>
            </b-col>
            <b-col sm="8">
                <period-filter :period="7" @input="onDateRangeChanged "></period-filter>
                <div class="d-flex justify-content-end mt-2">
                    <project-select v-model="projectId" @input="getReport" size="sm" class="w-25 mr-3"></project-select>
                    <tracker-select v-model="trackerId" @input="getReport" size="sm" class="w-25"></tracker-select>
                </div>
            </b-col>
        </b-row>
        <issues-chart :chart-data="datacollection" class="chart-wrapper" style="height:300px;margin-top:40px;"
                      :height="300"></issues-chart>

        <div slot="footer">
            <b-row class="text-center">
                <b-col class="mb-sm-2 mb-0">
                    <div class="text-muted">Поступило задач</div>
                    <router-link
                            :to="{
                                 name: 'issues.index',
                                 query: {
                                     status: 'all',
                                     period_from_date: period.startDate,
                                     period_to_date: period.endDate,
                                     project: projectId,
                                     include_subprojects: 'yes',
                                     tracker: trackerId,
                                 }
                             }">
                        <strong>{{issueSummary.created}}</strong>
                    </router-link>
                </b-col>
                <b-col class="mb-sm-2 mb-0 d-md-down-none">
                    <div class="text-muted">Выполнено задач</div>
                    <router-link
                            :to="{
                                name: 'issues.index',
                                query: {
                                    status: 'closed',
                                    period_from_date: period.startDate,
                                    period_to_date: period.endDate,
                                    project: projectId,
                                    include_subprojects: 'yes',
                                    tracker: trackerId,
                                }
                            }">
                        <strong>{{issueSummary.closed}}</strong>
                    </router-link>
                </b-col>
                <b-col class="mb-sm-2 mb-0">
                    <div class="text-muted">Выполнено на первой линии</div>
                    <strong>{{issueSummary.closed_first_line}}</strong>
                </b-col>
                <b-col class="mb-sm-2 mb-0">
                    <div class="text-muted">Выполнено в срок</div>
                    <router-link
                            :to="{
                                name: 'issues.index',
                                query: {
                                    status: 'closed',
                                    overdue: 'no',
                                    period_from_date: period.startDate,
                                    period_to_date: period.endDate,
                                    project: projectId,
                                    include_subprojects: 'yes',
                                    tracker: trackerId,
                                }
                            }">
                        <strong>{{issueSummary.closed_in_time}}</strong>
                    </router-link>
                </b-col>
                <b-col class="mb-sm-2 mb-0 d-md-down-none">
                    <div class="text-muted">Выполнено не в срок</div>
                    <router-link
                            :to="{
                                name: 'issues.index',
                                query: {
                                    status: 'closed',
                                    overdue: 'yes',
                                    period_from_date: period.startDate,
                                    period_to_date: period.endDate,
                                    project: projectId,
                                    include_subprojects: 'yes',
                                    tracker: trackerId,
                                }
                            }">
                        <strong>{{issueSummary.closed_overdue}}</strong>
                    </router-link>
                </b-col>
            </b-row>
        </div>
    </b-card>
</template>

<script>
  import * as moment from 'moment';
  import PeriodFilter from '../components/PeriodFilter';
  import ProjectSelect from '../components/ProjectSelect';
  import TrackerSelect from '../components/TrackerSelect';
  import IssuesChart from './IssuesChart';

  const brandSuccess = '#4dbd74';
  const brandInfo = '#63c2de';
  const brandDanger = '#f86c6b';
  const brandGray = '#c2cfd6';
  const brandTeal = '#42f4bf';
  const brandPurple = '#ba69e5';

  function convertHex(hex, opacity) {
    hex = hex.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + opacity / 100 + ')';
  }

  export default {
    components: {
      TrackerSelect,
      ProjectSelect,
      PeriodFilter,
      IssuesChart,
    },
    name: 'issues-summary',
    data() {
      return {
        period: {
          startDate: moment().subtract(7, 'days').format('YYYY-MM-DD'),
          endDate: moment().subtract(1, 'days').format('YYYY-MM-DD'),
        },
        datacollection: null,
        issueSummary: {
          created: 0,
          closed: 0,
          closed_first_line: 0,
          closed_overdue: 0,
          closed_in_time: 0,
        },
        projectId: null,
        trackerId: null,
      };
    },
    mounted() {
      this.getReport();
    },
    methods: {
      onDateRangeChanged(range) {
        this.period = range;
        this.getReport();
      },
      getReport() {
        return axios.get(route('api.issues.reports'), {
          params: {
            period_from_date: this.period.startDate,
            period_to_date: this.period.endDate,
            project_id: this.projectId,
            tracker_id: this.trackerId,
          },
        }).then((response) => {
          this.issueSummary.created = response.data.data.created.total;
          this.issueSummary.closed = response.data.data.closed.total;
          this.issueSummary.closed_first_line = response.data.data.closed_first_line.total;
          this.issueSummary.closed_overdue = response.data.data.closed_overdue.total;
          this.issueSummary.closed_in_time = response.data.data.closed_in_time.total;
          this.fillChartData(
            response.data.data.created.data,
            response.data.data.closed.data,
            response.data.data.closed_overdue.data,
            response.data.data.closed_first_line.data,
            response.data.data.closed_in_time.data,
          );
        });
      },
      fillChartData(createdIssues, closedIssues, closedOverdueIssues, closedFirstLineIssues, closedInTimeIssues) {
        this.datacollection = {
          datasets: [
            {
              label: 'Поступило задач',
              backgroundColor: convertHex(brandInfo, 10),
              borderColor: brandInfo,
              pointHoverBackgroundColor: '#fff',
              borderWidth: 2,
              data: createdIssues,
            },
            {
              label: 'Выполнено задач',
              backgroundColor: convertHex(brandSuccess, 20),
              borderColor: brandSuccess,
              pointHoverBackgroundColor: '#fff',
              borderWidth: 2,
              data: closedIssues,
            },
            {
              label: 'Выполнено не в срок',
              backgroundColor: convertHex(brandDanger, 50),
              borderColor: brandDanger,
              pointHoverBackgroundColor: '#fff',
              borderWidth: 2,
              data: closedOverdueIssues,
            },
            {
              label: 'Выполнено на первой линии',
              backgroundColor: convertHex(brandPurple, 20),
              borderColor: brandPurple,
              pointHoverBackgroundColor: '#fff',
              borderWidth: 1,
              hidden: true,
              data: closedFirstLineIssues,
            },
            {
              label: 'Выполнено в срок',
              backgroundColor: convertHex(brandTeal, 20),
              borderColor: brandTeal,
              pointHoverBackgroundColor: '#fff',
              borderWidth: 1,
              hidden: true,
              data: closedInTimeIssues,
            },
          ],
        };
      },
    },

  };
</script>

<style scoped>

</style>
