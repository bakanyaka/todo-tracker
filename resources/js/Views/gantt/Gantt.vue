<template>
  <b-card>
    <div class="row">
      <b-form-group label="Группировка:" class="col">
        <b-form-select id="group-by-select" v-model="filters.groupBy">
          <b-form-select-option value="assigned_to">Назначена (Сотрудник)</b-form-select-option>
          <b-form-select-option value="project">Проект</b-form-select-option>
          <b-form-select-option value="category">Категория</b-form-select-option>
        </b-form-select>
      </b-form-group>
      <div class="ml-2 col">
        <label>Сотрудник:</label>
        <multiselect v-model="filters.assignees"
                     :options="assignees"
                     :multiple="true"
                     track-by="id"
                     label="name"
                     placeholder="Выберите сотрудника"
        >
        </multiselect>
      </div>
      <div class="ml-2 col">
        <label>Проект</label>
        <multiselect v-model="filters.projects"
                     :options="projects"
                     :multiple="true"
                     track-by="id"
                     label="name"
                     placeholder="Выберите проект"
        >
        </multiselect>
      </div>
      <div class="ml-2 col">
        <label>Категория</label>
        <multiselect v-model="filters.categories"
                     :options="categories"
                     :multiple="true"
                     track-by="id"
                     label="name"
                     placeholder="Выберите категорию"
        >
        </multiselect>
      </div>
    </div>
    <gantt-chart class="mt-1" :tasks="tasks" style="height: 70vh" :config="config" />
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
      projects: [],
      categories: [],
      filters: {
        assignees: [],
        projects: [],
        categories: [],
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
    this.fetchProjects();
    this.fetchCategories();
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
      const { data } = await axios.get(route('api.issues.gantt'), {
        params: {
          assignees: this.filters.assignees.map(({ id }) => id),
          projects: this.filters.projects.map(({ id }) => id),
          categories: this.filters.categories.map(({ id }) => id),
          group_by: this.filters.groupBy
        }
      });
      this.tasks.data = data.data;
    },
    async fetchAssignees() {
      const { data } = await axios.get(route('api.assignees'));
      this.assignees = data.data;
    },
    async fetchProjects() {
      const { data } = await axios.get(route('api.projects'));
      this.projects = data.data;
    },
    async fetchCategories() {
      const { data } = await axios.get(route('api.categories.index'));
      this.categories = data.data;
    },
  }
}
</script>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

