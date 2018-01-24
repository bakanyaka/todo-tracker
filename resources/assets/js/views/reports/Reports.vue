<template>
    <b-card>
        <b-row>
            <b-col sm="5" class="mb-3">
                <h4 class="card-title mb-0">Отчет за период</h4>
                <div class="small text-muted">16.01.2018-23.01.2018</div>
            </b-col>
            <b-col sm="7" class="d-none d-md-block">
                <b-button-toolbar class="float-right" aria-label="Toolbar with buttons group">
                    <b-form-radio-group class="mr-3" id="radiosBtn" buttons button-variant="outline-secondary"
                                        v-model="periodFilter.selected" :options="periodFilter.options" name="radiosBtn">
                    </b-form-radio-group>
                </b-button-toolbar>
            </b-col>
        </b-row>
        <issues-chart  :chart-data="datacollection" class="chart-wrapper" style="height:300px;margin-top:40px;" :height="300"></issues-chart>

        <div slot="footer">
            <ul>
                <li class="d-none d-md-table-cell">
                    <div class="text-muted">Поступило задач</div>
                    <strong>{{issueSummary.created}}</strong>
                </li>
                <li class="d-none d-md-table-cell">
                    <div class="text-muted">Выполнено задач</div>
                    <strong>{{issueSummary.closed}}</strong>
                </li>
                <li>
                    <div class="text-muted">Выполнено на первой линии</div>
                    <strong>{{issueSummary.closed_first_line}}</strong>
                </li>
                <li class="d-none d-md-table-cell">
                    <div class="text-muted">Выполнено не в срок</div>
                    <strong>{{issueSummary.closed_overdue}}</strong>
                </li>
            </ul>
        </div>
    </b-card>
</template>

<script>
    import IssuesChart from "./IssuesChart";

    const brandSuccess = '#4dbd74';
    const brandInfo = '#63c2de';
    const brandDanger = '#f86c6b';
    function convertHex (hex, opacity) {
        hex = hex.replace('#', '');
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + opacity / 100 + ')'
    }

    export default {
        components: {IssuesChart},
        name: "reports",
        data () {
            return {
                periodFilter: {
                    selected: 7,
                    options: [
                        { text: '7 дней', value: 7},
                        { text: '14 дней', value: 14},
                        { text: '30 дней', value: 30},
                        { text: '90 дней', value: 90}
                    ]
                },
                datacollection: null,
                issueSummary: {
                    created: 0,
                    closed: 0,
                    closed_first_line: 0,
                    closed_overdue: 0,
                }
            }
        },
        mounted() {
            this.getReport(this.periodFilter.selected);
        },
        watch: {
            'periodFilter.selected'() {
                this.getReport(this.periodFilter.selected);
            }
        },
        methods: {
            getReport($days = 30) {
                return axios.get(route('api.issues.reports'), {params: {period: $days}}).then((response)=> {
                    this.issueSummary.created = response.data.data.created.total;
                    this.issueSummary.closed = response.data.data.closed.total;
                    this.issueSummary.closed_overdue = response.data.data.closed_overdue.total;
                    this.fillChartData(
                        response.data.data.created.data,
                        response.data.data.closed.data,
                        response.data.data.closed_overdue.data
                    );
                });
            },
            fillChartData(createdIssues,closedIssues,closedOverdueIssues) {
                this.datacollection = {
                    datasets: [
                        {
                            label: 'Поступило задач',
                            backgroundColor: convertHex(brandInfo, 10),
                            borderColor: brandInfo,
                            pointHoverBackgroundColor: '#fff',
                            borderWidth: 2,
                            data: createdIssues
                        },
                        {
                            label: 'Выполнено задач',
                            backgroundColor: convertHex(brandSuccess, 20),
                            borderColor: brandSuccess,
                            pointHoverBackgroundColor: '#fff',
                            borderWidth: 2,
                            data: closedIssues
                        },
                        {
                            label: 'Выполнено не в срок',
                            backgroundColor: convertHex(brandDanger, 50),
                            borderColor: brandDanger,
                            pointHoverBackgroundColor: '#fff',
                            borderWidth: 2,
                            data: closedOverdueIssues
                        }
                    ]
                }
            }
        }

    }
</script>

<style scoped>

</style>