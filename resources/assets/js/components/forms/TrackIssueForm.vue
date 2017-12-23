<template>
    <b-form inline  @submit.prevent="addIssue">
        <b-input class="form-control-sm mr-2" type="text" placeholder="# Задачи" v-model="issueId" required></b-input>
        <b-button class="btn-sm my-2" type="submit">Отслеживать</b-button>
    </b-form>
</template>

<script>
    export default {
        name: "track-issue-form",
        data () {
            return {
                issueId: null
            }
        },
        methods: {
            addIssue () {
                axios.post(route('issues.track'),{issue_id: this.issueId}).then((response)=>{
                    console.log(response);
                });
            }
        }
    }
</script>

<style scoped>

</style>