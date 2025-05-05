<script lang="ts">
import {defineComponent} from 'vue'

export default defineComponent({
  name: "change_lang",
  data: () => ({
    items: [
      {name:'en',label:'English'},
      {name:'ar',label:'العربية'},
      {name:'fa',label:'فارسی'},
      {name:'ckb',label:'کوردی'},
      {name:'tr',label:'Türkçe'},
    ],
  }),
  methods:{
    changeLanguage(item:any){
      localStorage.setItem('UI_LANG',item.name);
      window.location.reload();
    }
  }
})
</script>

<template>
  <v-menu>
    <template v-slot:activator="{ props }">
      <v-btn
          class="me-2"
          color="white"
          icon="mdi-web"
          v-bind="props"
      />

    </template>
    <v-list>
      <v-list-item
          v-for="(item, index) in items"
          :key="index"
          :value="index"
          @click="changeLanguage(item)"
      >
        <v-list-item-title>{{ item.label }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<style scoped>

</style>