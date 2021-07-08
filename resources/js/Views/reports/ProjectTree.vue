<template>
    <ul>
        <li v-for="project in projects">
            {{project.project}}
            <ul>
                <li class="pr-2 d-sm-table-cell">
                    <div>Поступило задач:</div>
                    {{project.created}}
                </li>
                <li class="pr-2 d-sm-table-cell">
                    <div>Выполнено задач:</div>
                    {{project.closed}}
                </li>
                <li class="pr-2 d-sm-table-cell">
                    <div>Выполнено в срок</div>
                    {{project.closed_in_time}}
                </li>
                <li class="pr-2 d-sm-table-cell">
                    <div>Выполнено не в срок</div>
                    {{project.closed_overdue}}
                </li>
            </ul>
            <project-tree :projects="project.children"></project-tree>
        </li>
    </ul>
</template>

<script>
    export default {
        name: "project-tree",
        props: ['projects']
    }
</script>

<style scoped>

</style>