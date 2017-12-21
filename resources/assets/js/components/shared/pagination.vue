<template>
    <ul class="pagination">
        <li class="page-item" :class="{'disabled' : meta.current_page === 1 }">
            <a class="page-link" href="#" rel="prev" @click.prevent="switched(meta.current_page - 1)">&laquo;</a>
        </li>

        <li v-for="page in meta.last_page" class="page-item" :class="{'active' : meta.current_page === page}">
            <a class="page-link" href="" @click.prevent="switched(page)">{{page}}</a>
        </li>

        <li class="page-item" :class="{'disabled' : meta.current_page === meta.last_page}">
            <a class="page-link" href="#" rel="next" @click.prevent="switched(meta.current_page + 1)">&raquo;</a>
        </li>
    </ul>
</template>

<script>
    export default {
        name: "pagination",
        props: ['meta'],
        methods: {
            switched (page) {
                this.$emit('pagination:changed', page)
            }
        }
    }
</script>

<style scoped>

</style>