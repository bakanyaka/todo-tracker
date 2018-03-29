<template>
    <div class="animated fadeIn">
        {{ $route.params.id }}
    </div>

</template>

<script>
    export default {
        name: "assignee-report",
        data() {
            return {
                issues: {
                    participated: [],
                    spent: []
                }
            }
        },
        created() {
            this.getUserReport()
        },
        methods: {
            getUserReport() {
                axios.get(route('api.assignees.report.show', this.$route.params.id)).then((response) => {
                    this.issues = response.data.data.issues
                })
            }
        }

    }
</script>

<style scoped>

</style>