<template>
  <b-card>
    <div class="d-flex">
      <b-form-group label="Группировка:" v-slot="{ ariaDescribedby }" class="d-flex flex-column justify-content-end">
        <b-form-radio-group
          id="group-by-radio"
          v-model="filters.groupBy"
          :aria-describedby="ariaDescribedby"
        >
          <b-form-radio value="assigned_to">Назначена</b-form-radio>
          <b-form-radio value="project">Проект</b-form-radio>
          <b-form-radio value="category">Категория</b-form-radio>
        </b-form-radio-group>
      </b-form-group>
      <div class="ml-2 flex-grow-1">
        <label>Сотрудник:</label>
        <multiselect v-model="filters.selectedAssignees"
                     :options="assignees"
                     :multiple="true"
                     track-by="id"
                     label="login"
                     :custom-label="customLabel"
                     placeholder="Выберите сотрудника для фильтрации или введите ФИО для поиска"
        >
        </multiselect>
      </div>
    </div>
    <gantt-chart class="mt-2" :tasks="tasks" style="height: 70vh" :config="config" />
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
      filters: {
        selectedAssignees: [],
        groupBy: 'assigned_to',
      },
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
  watch: {
    filters: {
      deep: true,
      handler() {
        this.fetchTasks();
      }
    }
  },
  methods: {
    async fetchTasks() {
      const assignees = this.filters.selectedAssignees.map(({ id }) => id);
      const { data } = await axios.get(route('api.issues.gantt'), {
        params: {
          assignees,
          group_by: this.filters.groupBy
        }
      });
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

