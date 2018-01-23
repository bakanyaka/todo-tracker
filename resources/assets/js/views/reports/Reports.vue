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
        <b-row>
            <b-col sm="12">
                <uL class="list-unstyled">
                    <li>Поступило задач: <a href="#">200</a></li>
                    <li>Выполнено задач: <a href="#">150</a></li>
                    <li>Выполнено задач на первой линии: <a href="#">50</a></li>
                    <li>Выполнено не в срок: <a href="#">10</a></li>
                </uL>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        name: "reports",
        data () {
            return {
                periodFilter: {
                    selected: 7,
                    options: [
                        { text: 'Сегодня', value: 1},
                        { text: '7 дней', value: 7},
                        { text: '14 дней', value: 14},
                        { text: '30 дней', value: 30}
                    ]
                }
            }
        }

    }
</script>

<style scoped>

</style>