<template>
    <div>
        <issues-summary></issues-summary>
        <issues-by-project></issues-by-project>
    </div>
</template>

<script>


    import IssuesSummary from "./IssuesSummary";
    import IssuesByProject from "./IssuesByProject";

    export default {
        components: {IssuesSummary, IssuesByProject},
        name: "reports",
    }

</script>

<style scoped>

</style>