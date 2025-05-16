<script setup>
import { ref, onMounted } from 'vue'
import axios from "axios"

const plugins = ref([])

const loadData = () => {
  axios.post('/api/plugin/get/all').then((response) => {
    plugins.value = response.data
  })
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <v-toolbar title="فهرست افزونه‌ها" flat color="toolbar">
        <template v-slot:prepend>
            <v-btn icon @click="$router.back()" class="me-2 d-none d-md-flex" variant="text">
          <v-icon>mdi-arrow-right</v-icon>
        </v-btn>
        </template>
      </v-toolbar>
  <v-container fluid>
    <v-row>
      <v-col
        v-for="plugin in plugins"
        :key="plugin.code"
        cols="12"
        md="6"
        lg="4"
        xl="4"
      >
        <v-card class="mb-6 elevation-3" rounded="lg">
          <v-img
            :src="'/u/img/plugins/' + plugin.icon"
            height="200"
            class="rounded-t-lg"
            cover
          ></v-img>
          <v-card-title class="d-flex align-center justify-space-between">
            <div>
              <v-icon class="me-2" color="primary">mdi-power-plug</v-icon>
              <span class="font-weight-bold">{{ plugin.name }}</span>
            </div>
          </v-card-title>
          <v-card-subtitle class="d-flex align-center justify-space-between mb-2">
            <v-chip color="success" text-color="white" size="small">
              {{ Intl.NumberFormat('en-US').format(plugin.price) }} تومان
            </v-chip>
            <v-chip color="info" text-color="white" size="small">
              <v-icon size="small" class="me-1">mdi-clock-outline</v-icon>
              {{ plugin.timelabel }}
            </v-chip>
          </v-card-subtitle>
          <v-divider></v-divider>
          <v-row class="text-center py-2 bg-grey-lighten-4">
            <v-col cols="6" class="border-e">
              <v-icon class="me-1" color="grey">mdi-ticket</v-icon>
              پشتیبانی دارد
            </v-col>
            <v-col cols="6">
              <v-icon class="me-1" color="grey">mdi-account-group</v-icon>
              کاربر نامحدود
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-card-actions class="justify-center">
            <RouterLink :to="'/acc/plugin-center/view-end/' + plugin.code">
              <v-btn
                color="success"
                class="mx-2 px-4"
                elevation="1"
                variant="flat"
              >
                <v-icon start class="ms-1">mdi-cart</v-icon>
                خرید
              </v-btn>
            </RouterLink>
            <RouterLink :to="'/acc/plugins/' + plugin.code + '/intro'">
              <v-btn
                color="info"
                class="mx-2 px-4"
                elevation="1"
                variant="outlined"
              >
                <v-icon start class="ms-1">mdi-book-open-page-variant</v-icon>
                کاتالوگ
              </v-btn>
            </RouterLink>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>

</style>