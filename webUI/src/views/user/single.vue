<script lang="ts">
import Change_lang from "@/components/application/buttons/change_lang.vue";
import { getBasePath } from "@/hesabixConfig";
import axios from "axios";

export default {
  name: 'single',
  components: { Change_lang },
  data() {
    return {

    }
  },
  methods: {
    getbase() {
      return getBasePath();
    },
  },
  created() {
    axios.post('/api/user/check/login').then((response) => {
      if (response.data.Success == true) {
        this.$router.push('/profile/business')
      }
    });
  }
}
</script>

<template>
  <v-app id="">
    <v-app-bar class="px-2" color="indigo-darken-2" flat>
      <v-avatar size="32" :image="getbase() + 'img/favw.png'"></v-avatar>

      <v-spacer></v-spacer>

      <v-tabs centered color="">
        <v-tab prepend-icon="mdi-login" :text="$t('user.login')" to="/user/login"></v-tab>
        <v-tab prepend-icon="mdi-account-plus" :text="$t('user.register')" to="/user/register"></v-tab>
        <v-tab prepend-icon="mdi-lock" :text="$t('user.forget_password')" to="/user/forget-password"></v-tab>
      </v-tabs>
      <v-spacer></v-spacer>

    </v-app-bar>

    <v-main class="bg-grey-lighten-3">
      <RouterView />
    </v-main>

  </v-app>
</template>

<style scoped></style>