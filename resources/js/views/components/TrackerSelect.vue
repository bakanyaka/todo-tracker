<template>
    <b-form-select v-model="localValue" @input="$emit('input', $event)" @change="$emit('change', $event)" :size="size">
        <option :value="null">Все трекеры</option>
        <option v-for="tracker in trackers" :value="tracker.id">{{tracker.name}}</option>
    </b-form-select>
</template>

<script>
  export default {
    name: 'tracker-select',
    props: {
      value: [Number, String],
      size: {
        type: String,
        default: null,
      }
    },
    data() {
      return {
        trackers: [],
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
        const response = await axios.get(route('api.trackers'));
        this.trackers = response.data.data;
      },
    },
  };
</script>

<style scoped>

</style>
