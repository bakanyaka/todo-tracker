<template>
    <div>
        <div class="form-row form-inline align-items-start">
            <div class="col-auto">
                <label for="tracked-by">Кто отслеживает:</label>
                <b-form-select size="sm" id="tracked-by" v-model="filters.user.selected" :options="filters.user.options"
                               @change="onFiltersChanged">
                </b-form-select>
            </div>
            <div class="col-auto">
                <label for="status">Статус:</label>
                <b-form-select size="sm" id="status" v-model="filters.status.selected" :options="filters.status.options"
                               @change="onFiltersChanged">
                </b-form-select>
            </div>
            <div class="col-auto">
                <label for="overdue">Срок:</label>
                <b-form-select size="sm" id="overdue" v-model="filters.overdue.selected"
                               :options="filters.overdue.options"
                               @change="onFiltersChanged">
                </b-form-select>
            </div>
            <div class="col-auto">
                <label for="period">Период:</label>
                <b-form-select size="sm" id="period" v-model="filters.period.selected" :options="filters.period.options"
                               @change="onFiltersChanged">
                </b-form-select>
            </div>
            <div class="col-auto" style="min-width: 15rem">
                <label for="project">Трекер:</label>
                <tracker-select size="sm" id="tracker-select" v-model="filters.tracker.selected"
                                @input="onFiltersChanged">
                </tracker-select>
            </div>
            <div class="col-auto">
                <label for="project">Проект:</label>
                <b-form-select size="sm" id="project" v-model="filters.project.selected"
                               :options="filters.project.options"
                               @change="onFiltersChanged">
                </b-form-select>
                <b-form-checkbox :plain="true" v-model="filters.include_subprojects.selected" value="yes"
                                 :unchecked-value="null" @change="onFiltersChanged">
                    Включая подпроекты
                </b-form-checkbox>
            </div>
            <div class="col-auto">
                <label>&nbsp;</label>
                <b-button :to="{name: 'issues.index', query: {user: 'me'}}" variant="primary" size="sm" active-class=""
                          exact-active-class="">
                    Сбросить
                </b-button>
            </div>
        </div>
    </div>
</template>

<script>
  import PeriodFilter from '../components/PeriodFilter';
  import TrackerSelect from '../components/TrackerSelect';

  export default {
    components: { TrackerSelect, PeriodFilter },
    name: 'filters',
    data() {
      return {
        filters: {
          user: {
            selected: 'me',
            options: [
              { value: 'me', text: 'Только я' },
              { value: 'all', text: 'Какой-либо пользователь' },
              { value: null, text: 'Все задачи' },
            ],
          },
          status: {
            selected: null,
            options: [
              { value: null, text: 'Открытые' },
              { value: 'all', text: 'Все' },
              { value: 'paused', text: 'Обратная связь' },
              { value: 'closed', text: 'Закрытые' },
            ],
          },
          overdue: {
            selected: null,
            options: [
              { value: null, text: 'Все' },
              { value: 'yes', text: 'Просроченные' },
              { value: 'no', text: 'Не просроченные' },
              { value: 'soon', text: 'Истекает срок' },
            ],
          },
          period: {
            selected: null,
            options: [
              { value: null, text: 'Все время' },
              { value: 0, text: 'Сегодня' },
              { value: 7, text: 'За неделю' },
              { value: 14, text: 'За 14 дней' },
              { value: 30, text: 'За месяц' },
            ],
          },
          project: {
            selected: null,
            options: [
              { value: null, text: 'Все проекты' },
            ],
          },
          include_subprojects: {
            selected: null,
          },
          tracker: {
            selected: [],
          }
        },
      };
    },
    watch: {
      '$route'() {
        this.updateFromQuery();
      },
    },
    mounted() {
      this.updateFromQuery();
      this.loadFilters();
    },
    methods: {
      updateFromQuery() {
        for (let filter in this.filters) {
          if (this.$route.query.hasOwnProperty(filter)) {
            this.filters[filter].selected = this.$route.query[filter];
          } else {
            this.filters[filter].selected = null;
          }
        }
      },
      loadFilters() {
        axios.get(route('api.projects')).then((response) => {
          let projectFilterOptions = [
            { value: null, text: 'Все проекты' },
          ];
          for (let project of response.data.data) {
            projectFilterOptions.push({
              value: project.id,
              text: project.name,
            });
          }
          this.filters.project.options = projectFilterOptions;
        });
      },
      onFiltersChanged() {
        // $nextTick is workaround for event being triggered before v-model variable is updated
        // Can't use watchers because need to react only on changes being made by user
        this.$nextTick(() => {
          const selectedFilters = {};
          for (let key in this.filters) {
            if (this.filters[key].selected !== null) {
              selectedFilters[key] = this.filters[key].selected;
            }
          }
          this.$emit('filters:changed', selectedFilters);
        });
      },
    },
  };
</script>

<style scoped>
    .period-filter {
        justify-content: flex-end;
    }
</style>
