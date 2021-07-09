<template>
  <div ref="gantt"></div>
</template>
<script>
import { gantt } from 'dhtmlx-gantt';

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
  mounted() {
    const config = { date_format: '%Y-%m-%d %H:%i:%s', ...this.config };
    for (const [key, value] of Object.entries(config)) {
      gantt.config[key] = value;
    }
    gantt.init(this.$refs.gantt);
  },
  watch: {
    tasks: {
      immediate: true,
      deep: true,
      handler(value) {
        gantt.clearAll();
        gantt.parse(value);
      }
    }
  }
}
</script>

<style>
@import "~dhtmlx-gantt/codebase/dhtmlxgantt.css";

</style>
