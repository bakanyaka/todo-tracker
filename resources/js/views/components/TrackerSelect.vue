<template>
    <v-select :options="trackers" v-model="localValue" @input="onInput" multiple label="name"></v-select>
    <!--    <b-form-select v-model="localValue"-->
    <!--                   @input="$emit('input', $event)"-->
    <!--                   @change="$emit('change', $event)"-->
    <!--                   :size="size">-->
    <!--        <option :value="null">Все трекеры</option>-->
    <!--        <option v-for="tracker in trackers" :value="tracker.id">{{tracker.name}}</option>-->
    <!--    </b-form-select>-->
</template>

<script>
  export default {
    name: 'tracker-select',
    props: {
      value: [Array],
      size: {
        type: String,
        default: null,
      },
    },
    data() {
      return {
        trackersRaw: [],
        localValue: this.value,
      };
    },
    computed: {
      trackers() {
        return this.trackersRaw.filter((tracker) => !tracker.name.startsWith('-'));
      },
    },
    watch: {
      //  When v-model is changed set local value to new value
      value(newValue) {
        this.localValue = this.trackers.filter((tracker) => {
          return newValue.includes(tracker.id);
        });
      },
    },
    created() {
      this.getProjects();
    },
    methods: {
      async getProjects() {
        const response = await axios.get(route('api.trackers'));
        this.trackersRaw = response.data.data;
      },
      onInput(value) {
        this.$emit('input', value.map(item => item.id));
      },
    },
  };
</script>

<style scoped>

</style>
