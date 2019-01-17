<template>
    <div>
        <issues-summary></issues-summary>
        <issues-by-project></issues-by-project>
        <issues-by-assignee></issues-by-assignee>
    </div>
</template>

<script>


    import IssuesSummary from "./IssuesSummary";
    import IssuesByProject from "./IssuesByProject";
    import IssuesByAssignee from "./IssuesByAssignee";

    export default {
        components: {
            IssuesByAssignee,
            IssuesSummary,
            IssuesByProject
        },
        name: "reports",
    }

</script>

<style scoped>

</style>