<template>
    <b-form-select v-model="localValue" @input="$emit('input', $event)" :size="size">
        <option :value="null">Все проекты</option>
        <option v-for="project in projects" :value="project.id">{{project.name}}</option>
    </b-form-select>
</template>

<script>
  export default {
    name: 'project-select',
    props: {
      value: [Number, String],
      size: {
        type: String,
        default: null,
      }
    },
    data() {
      return {
        projects: [],
        localValue: this.value,
      };
    },
    watch: {
      //  When v-model is changed set local value to new value
      value(newValue) {
        this.localValue = newValue;
      },
    },
    created() {
      this.getProjects();
    },
    methods: {
      async getProjects() {
        const response = await axios.get(route('api.projects'));
        this.projects = response.data.data;
      },
    },
  };
</script>

<style scoped>

</style>
