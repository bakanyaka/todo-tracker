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
        <pagination :meta="meta" v-on:pagination:changed="getIssues"></pagination>
    </div>
</template>

<script>
    import issue from './issue'
    import pagination from '../shared/pagination'
    export default {
        props: ['endpoint'],
        data () {
            return {
                issues: [],
                meta: {}
            }
        },
        components: {
            issue,
            pagination
        },
        mounted() {
            this.getIssues()
        },
        methods: {
            getIssues(page = 1) {
                return axios.get(this.endpoint, {
                    params: {
                        page
                    }
                }).then((response) => {
                    this.issues = response.data.data;
                    this.meta = response.data.meta;
                })
            }
        }
    }
</script>
