<template>
  <b-card>
    <gantt-chart :tasks="tasks" style="height: 80vh" :config="{readonly: true, open_tree_initially: false}" />
  </b-card>
</template>

<script>

import GanttChart from '@/Components/Gantt/GanttChart';

export default {
  components: { GanttChart },
  data() {
    return {
      tasks: {
        data: [],
      },
    }
  },
  created() {
    this.fetchTasks();
  },
  methods: {
    async fetchTasks() {
      const { data } = await axios.get(route('api.issues.gantt'));
      console.log(data.data);
      this.tasks.data = data.data;
    }
  }
}
</script>

