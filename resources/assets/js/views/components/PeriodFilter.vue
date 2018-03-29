<template>
    <div class="form-inline pull-right">
        <b-button-toolbar class="float-right" aria-label="Period filter">
            <b-form-radio-group size="sm" class="mr-3" id="radiosBtn" buttons button-variant="outline-secondary"
                                v-model="selected" :options="options" name="radiosBtn"
                                @change="onDaysChange">
            </b-form-radio-group>
        </b-button-toolbar>
        <b-form-input size="sm" v-model="startDate" type="date" @input="onPeriodInput" @change="selected = 'other'"></b-form-input>
        <span class="mx-1">-</span>
        <b-form-input size="sm" v-model="endDate" type="date" @input="onPeriodInput" @change="selected = 'other'"></b-form-input>
    </div>
</template>

<script>
    import moment from 'moment';

    export default {
        name: "period-filter",
        props: ['period'],
        data() {
            return {
                selected: this.period,
                startDate: moment().subtract(this.period,'days').format('YYYY-MM-DD'),
                endDate: moment().subtract(1,'days').format('YYYY-MM-DD'),
                options: [
                    {text: '7 дней', value: 7},
                    {text: '14 дней', value: 14},
                    {text: '30 дней', value: 30},
                    {text: '90 дней', value: 90},
                    {text: 'Другой', value: 'other'}
                ]
            }
        },
        methods: {
            onDaysChange() {
                this.$nextTick(() => {
                    if (this.selected !== 'other') {
                        this.startDate = moment().subtract(this.selected,'days').format('YYYY-MM-DD');
                        this.endDate = moment().subtract(1,'days').format('YYYY-MM-DD');
                    }
                });
            },
            onPeriodInput() {
                this.$emit('change', {
                    startDate: this.startDate,
                    endDate: this.endDate
                });
            }
        }
    }
</script>

<style scoped>

</style>