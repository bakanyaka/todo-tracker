<template>
    <div>
        <navbar></navbar>
        <router-view></router-view>
    </div>
</template>

<script>
    import navbar from './Navbar'
    export default {
        name: "full",
        components: {
            navbar
        }
    }
</script>

<style scoped>

</style>