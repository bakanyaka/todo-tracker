<template>
    <div class="container-fluid">
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <tr>
                    <th>№</th>
                    <th>Название</th>
                    <th>Подразделение</th>
                    <th>Назначена</th>
                    <th>Приоритет</th>
                    <th>Сервис</th>
                    <th>Расчетное время</th>
                    <th>Оставшееся время</th>
                    <th>Дата создания</th>
                    <th>Плановая дата завершения</th>
                    <th>Фактическая дата завершения</th>
                    <th>&nbsp;</th>
                </tr>
                <tr is="issue" v-for="issue in issues" :issue="issue" :key="issue.id"></tr>
            </table>
        </div>
    </div>
</template>

<script>
    import issue from './issue'
    export default {
        props: ['endpoint'],
        data () {
            return {
                issues: []
            }
        },
        components: {
          issue
        },
        mounted() {
            this.getIssues()
        },
        methods: {
            getIssues() {
                return axios.get(`${this.endpoint}`).then((response) => {
                    this.issues = response.data.data
                })
            }
        }
    }
</script>
