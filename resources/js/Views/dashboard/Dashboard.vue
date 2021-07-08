<template>
    <div class="animated fadeIn">
        <quick-issue-stats></quick-issue-stats>
    </div>
</template>

<script>
    import QuickIssueStats from "@/Components/Shared/QuickIssueStats";

    export default {
        components: {QuickIssueStats},
        name: "dashboard"
    }
</script>

<style scoped>

</style>
