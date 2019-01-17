<template>
    <b-modal :title="title" centered class="modal-primary" v-model="displayed"
             @ok="handleOk" ok-title="Добавить" cancel-title="Отмена">
        <b-form-group>
            <label for="name">Наименование</label>
            <b-form-input type="text" id="name" v-model="service.name" required></b-form-input>
        </b-form-group>
        <b-row>
            <b-col sm="4">
                <b-form-group>
                    <label for="hours">Часов</label>
                    <b-form-input type="number" min="1" id="hours" v-model="service.hours"
                                  required></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>
    </b-modal>
</template>

<script>
    export default {
        name: "modify-service-modal",
        props: ['modalType'],
        data() {
            return {
                service: {},
                displayed: false,
            }
        },
        computed: {
            title() {
                return this.modalType === 'edit' ? 'Редактирование сервиса' : 'Добавление сервиса'
            },
            okTitle() {
            },
        },
        methods: {
            show(service = {}) {
                this.displayed = true;
                this.service = service
            },
            handleOk(e) {
                e.preventDefault();
                if (!this.service.name || !this.service.hours) {
                    this.$snotify.error('Введите данные')
                } else {
                    this.handleSubmit()
                }
            },
            handleSubmit() {
                let result;
                if (this.modalType === 'edit') {
                    result = axios.patch(route('api.services.update', this.service.id),this.service)
                } else {
                    result = axios.post(route('api.services.store'),this.service)
                }
                result.then(() => {
                    this.$snotify.success('Сервис изменен');
                    this.$emit('modified');
                }).catch((e) => {
                    console.log(e);
                    this.$snotify.error('Ошибка при изменении сервиса!');
                });
                this.displayed = false;
            }
        }
    }
</script>

<style scoped>

</style>