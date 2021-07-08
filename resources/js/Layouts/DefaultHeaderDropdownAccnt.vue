<template>
  <AppHeaderDropdown right>
    <template slot="header">
      <em>{{user.name}}</em>
    </template>\
    <template slot="dropdown">
      <b-dropdown-header
        tag="div"
        class="text-center">
        <strong>Настройки</strong>
      </b-dropdown-header>
      <b-dropdown-item @click="logout"><i class="fa fa-lock" /> Выход</b-dropdown-item>
    </template>
  </AppHeaderDropdown>
</template>

<script>
import { HeaderDropdown as AppHeaderDropdown } from '@coreui/vue'
export default {
  name: 'DefaultHeaderDropdownAccnt',
  components: {
    AppHeaderDropdown
  },
  data: () => {
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
