<script lang="ts">
import {ref, watch, computed, defineComponent} from 'vue'
  export default defineComponent({
    name:'Hdatepicker',
    inheritAttrs: false,
    data:()=>{
      const self = this;
      return{
        isMenuOpen: ref(false),
        selectedDate: ref(),
        output:''
      }
    },
    watch:{
      selectedDate(){
        this.isMenuOpen = false
        this.output = this.selectedDate.toLocaleString(
            localStorage.getItem('UI_LANG'),
            {year: 'numeric', month: 'numeric', day: 'numeric'}
        )
      }
    },
    setup(props, ctx) {


    },
  })
</script>

<template>
  <v-menu v-model="isMenuOpen" :close-on-content-click="false">
    <template v-slot:activator="{ props }">
      <v-text-field
          v-model="output"
          readonly
          v-bind="props"
          :value="output"
          outlined
          color="primary"
          @input="(v: any) => $emit('input', v)"
      ></v-text-field>
    </template>
    <!-- !!! hide-actions prop too !!! -->
    <v-date-picker  color="indigo" v-model="selectedDate">
      <template v-slot:header></template>
    </v-date-picker>

  </v-menu>
</template>

<style scoped>

</style>