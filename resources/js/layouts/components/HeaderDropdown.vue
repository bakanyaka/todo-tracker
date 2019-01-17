<template>
    <b-nav-item-dropdown right>
        <template slot="button-content">
            <em>{{user.name}}</em>
        </template>
        <b-dropdown-header tag="div" class="text-center"><strong>Настройки</strong></b-dropdown-header>
        <b-dropdown-item><i class="fa fa-user"></i> Профиль</b-dropdown-item>
        <b-dropdown-item><i class="fa fa-wrench"></i> Настройки</b-dropdown-item>
        <b-dropdown-divider></b-dropdown-divider>
        <b-dropdown-item @click="logout"><i class="fa fa-lock"></i> Выход</b-dropdown-item>
    </b-nav-item-dropdown>
</template>
<script>
    export default {
        name: 'header-dropdown',
        data () {
            return {
                user: config.user,
            }
        },
        methods: {
            async logout () {
                await axios.post(route('logout'));
                window.location = route('login');
            }
        }
    }
</script>
