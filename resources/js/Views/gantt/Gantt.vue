<template>
  <b-card>
    <label>Сотрудник:</label>
    <multiselect v-model="selectedAssignees"
                 :options="assignees"
                 :multiple="true"
                 track-by="id"
                 label="login"
                 :custom-label="customLabel"
                 @input="fetchTasks"
                 placeholder="Выберите сотрудника для фильтрации или введите ФИО для поиска"
    >
    </multiselect>

    <gantt-chart class="mt-4" :tasks="tasks" style="height: 70vh" :config="config" />
  </b-card>
</template>

<script>
import Multiselect from 'vue-multiselect'
import GanttChart from '@/Components/Gantt/GanttChart';

export default {
  components: { GanttChart, Multiselect },
  data() {
    return {
      assignees: [],
      selectedAssignees: [],
      tasks: {
        data: [],
      },
      config: {
        readonly: true,
        open_tree_initially: true,
      }
    }
  },
  created() {
    this.fetchAssignees();
    this.fetchTasks();
  },
  methods: {
    async fetchTasks() {
      const assignees = this.selectedAssignees.map(({ id }) => id);
      const { data } = await axios.get(route('api.issues.gantt'), { params: { assignees } });
      this.tasks.data = data.data;
    },
    async fetchAssignees() {
      const { data } = await axios.get(route('api.assignees'));
      this.assignees = data.data;
    },
    customLabel({ firstname, lastname }) {
      return `${lastname} ${firstname}`
    }
  }
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

