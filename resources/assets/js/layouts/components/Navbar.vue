<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <router-link to="/" exact class="navbar-brand">TODO-Tracker</router-link>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <router-link :to="{ name: 'issues.index'}">Задачи</router-link>
                </li>
            </ul>
            <track-issue-form class="ml-5"/>
            <button type="button" class="btn btn-secondary btn-sm ml-sm-2" @click="syncDataWithRedmine">Синхронизироваь данные</button>
        </div>
        <ul class="navbar-nav ml-auto">
            <div>
                <b-dropdown id="ddown1" :text="user.name" variant="primary" size="sm" class="nav-link">
                    <b-dropdown-item>First Action</b-dropdown-item>
                    <b-dropdown-divider></b-dropdown-divider>
                    <b-dropdown-item>Something else here...</b-dropdown-item>
                    <b-dropdown-item disabled>Disabled action</b-dropdown-item>
                    <a class="dropdown-item" @click.prevent="logout" href="#">Выйти</a>
                </b-dropdown>
            </div>
        </ul>
    </nav>
</template>

<script>
    import TrackIssueForm from '../../views/components/forms/TrackIssueForm';
    export default {
        name: "Nav",
        components: {
            TrackIssueForm
        },
        data () {
            return {
                user: config.user,
            }
        },
        methods: {
            syncDataWithRedmine () {
                axios.get(route('issues.update'));
            },
            async logout () {
                await axios.post(route('logout'));
                window.location = route('login');
            }
        }
    }
</script>

<style scoped>

</style>