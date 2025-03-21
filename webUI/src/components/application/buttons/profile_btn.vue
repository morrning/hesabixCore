<script lang="ts">
import { defineComponent } from 'vue'
import Archive_list from '../list/archive_list.vue';
import Sms_list from '../list/sms_list.vue';
import axios from "axios";

export default defineComponent({
  name: "profile_btn",
  data: () => ({

  }),
  components: {
    Archive_list: Archive_list,
    Sms_list: Sms_list
  },
  methods: {
    logout() {
      axios.post('/api/user/logout')
        .then((response) => {
          localStorage.removeItem('X-AUTH-TOKEN');
          delete axios.defaults.headers.common['X-AUTH-TOKEN'];
          this.$router.push('/user/login')
        });
    },
  }
})
</script>

<template>
  <v-menu location="bottom">
    <template v-slot:activator="{ props }">
      <v-btn stacked v-bind="props">
        <v-icon>mdi-dots-vertical</v-icon>
      </v-btn>
    </template>
    <v-card>
      <v-list>
        <Sms_list />
        <v-divider class="my-0 py-0"></v-divider>
        <Archive_list />
        <v-divider class="my-0 py-0"></v-divider>
        <v-list-item to="/profile/dashboard">
          <v-list-item-title>{{ $t('dialog.account') }}</v-list-item-title>
          <template v-slot:prepend>
            <v-icon color="primary" icon="mdi-face-man-profile"></v-icon>
          </template>
        </v-list-item>
        <v-list-item @click="logout()">
          <v-list-item-title>{{ $t('dialog.logout') }}</v-list-item-title>
          <template v-slot:prepend>
            <v-icon color="danger" icon="mdi-logout"></v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-card>

  </v-menu>
</template>

<style scoped></style>