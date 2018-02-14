<template>
    <b-button-toolbar class="float-right" aria-label="Period filter">
        <b-form-radio-group class="mr-3" id="radiosBtn" buttons button-variant="outline-secondary"
                            v-model="selected" :options="options" name="radiosBtn"
                            @change="onChange">
        </b-form-radio-group>
    </b-button-toolbar>
</template>

<script>
    export default {
        name: "period-filter",
        props: ['period'],
        data() {
            return {
                selected: this.period,
                options: [
                    {text: '7 дней', value: 7},
                    {text: '14 дней', value: 14},
                    {text: '30 дней', value: 30},
                    {text: '90 дней', value: 90}
                ]
            }
        },
        methods: {
            onChange() {
                this.$nextTick(() => {
                    this.$emit('change', this.selected);
                });
            }
        }
    }
</script>

<style scoped>

</style>