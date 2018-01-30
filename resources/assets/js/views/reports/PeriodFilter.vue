<template>
    <b-button-toolbar class="float-right" aria-label="Period filter">
        <b-form-radio-group class="mr-3" id="radiosBtn" buttons button-variant="outline-secondary"
                            v-model="periodFilter.selected" :options="periodFilter.options" name="radiosBtn">
        </b-form-radio-group>
    </b-button-toolbar>
</template>

<script>
    export default {
        name: "period-filter",
        props: ['period'],
        data () {
            return {
                periodFilter: {
                    selected: this.period,
                    options: [
                        {text: '7 дней', value: 7},
                        {text: '14 дней', value: 14},
                        {text: '30 дней', value: 30},
                        {text: '90 дней', value: 90}
                    ]
                },
            }
        }
    }
</script>

<style scoped>

</style>