<template>
  <div class="d-flex flex-column">
    <b-form-group label="Масштаб:" v-slot="{ ariaDescribedby }">
      <b-form-radio-group
        id="radio-group-2"
        v-model="zoom"
        :aria-describedby="ariaDescribedby"
        name="radio-sub-component"
      >
        <b-form-radio value="day">Месяц/День</b-form-radio>
        <b-form-radio value="month">Месяц/Неделя</b-form-radio>
        <b-form-radio value="year">Год/Месяц</b-form-radio>
      </b-form-radio-group>
    </b-form-group>
    <div ref="gantt" class="flex-grow-1"></div>
  </div>
</template>
<script>
import { gantt } from 'dhtmlx-gantt';

const scales = {
  'day': {
    min_column_width: 25,
    scales: [
      { unit: 'month', step: 1, format: '%Y %M' },
      { unit: 'day', step: 1, format: '%d' }
    ],
  },
  'month': {
    min_column_width: 80,
    scales: [
      { unit: 'month', step: 1, format: '%F, %Y' },
      { unit: 'week', step: 1, format: '№%W' }
    ],
  },
  'year': {
    min_column_width: 80,
    scales: [
      { unit: 'year', step: 1, format: '%Y' },
      { unit: 'month', step: 1, format: '%F' },
    ]
  }
}

const defaultConfig = {
  date_format: '%Y-%m-%d %H:%i:%s',
  grid_width: 500,
  columns: [
    {
      name: 'text',
      label: 'Задача',
      width: '*',
      tree: true,
      template: (task) => isNaN(task.id) ? task.text : `<a href="${config.redmineUri}/issues/${task.id}" target="_blank">${task.text}</a>`
    },
    { name: 'start_date', label: 'Дата начала', width: 100, align: 'center' },
    { name: 'end_date', label: 'Срок завершения', width: 100, align: 'center' },
  ],
}

export default {
  props: {
    tasks: {
      type: Object,
      default: () => ({})
    },
    config: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      zoom: 'day',
    }
  },
  mounted() {
    const config = { ...defaultConfig, ...this.config };
    for (const [key, value] of Object.entries(config)) {
      gantt.config[key] = value;
    }
    gantt.i18n.setLocale('ru');
    gantt.init(this.$refs.gantt);
    console.log(gantt.config.layout);
  },
  watch: {
    tasks: {
      immediate: true,
      deep: true,
      handler(value) {
        gantt.clearAll();
        gantt.parse(value);
      }
    },
    zoom: {
      immediate: true,
      handler(value) {
        gantt.config.scales = scales[value].scales
        gantt.config.min_column_width = scales[value].min_column_width
        gantt.render();
      }
    }
  }
}
</script>

<style>
@import "~dhtmlx-gantt/codebase/dhtmlxgantt.css";

.gantt_cell {
  font-size: 11px;
}

</style>
