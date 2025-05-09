<script lang="ts">
import axios from 'axios';
import {defineComponent,ref} from 'vue'

export default defineComponent({
  name: "documentLogButton",
  props:{
    docCode: String
  },
  data: ()=>{return {
    dialog: false,
    loading: ref(true),
    items:[],
    headers: [
      { title: "تاریخ", key: "date", align: "center" as const},
      { title: "کاربر", key: "user", align: "center" as const},
      { title: "شرح", key: "des", align: "center" as const},
    ],
    searchValue: '',
    logPermision: ref(false)
  }},
  methods:{
    loadData(){
      this.loading = true;
      axios.post('/api/business/my/permission/state',{'permission':'log',}).then((response)=>{
          if(response.data.state == true){
            this.logPermision = true;
            axios.post('/api/business/logs/doc/' + this.$props.docCode).then((response)=>{
              this.items = response.data;
              this.loading = false;
            });
          }
      });
    }
  },
  mounted(){
    this.loadData();
  }
})
</script>

<template>
  <!-- دکمه در تولبار -->
  <v-btn v-show="logPermision" icon color="info" class="ml-2" @click="dialog = true">
    <v-icon>mdi-history</v-icon>
    <v-tooltip activator="parent" location="bottom">تاریخچه سند</v-tooltip>
  </v-btn>

  <!-- دیالوگ تاریخچه -->
  <v-dialog v-model="dialog" max-width="800" persistent>
    <v-card>
      <v-toolbar color="toolbar" flat dark>
        <v-toolbar-title>
          <v-icon color="info" left>mdi-history</v-icon>
          تاریخچه تغییرات سند
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon @click="dialog = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text class="pa-0">
        <v-data-table
          :headers="headers"
          :items="items"
          :loading="loading"
          :search="searchValue"
          density="compact"
          hover
          class="elevation-1"
        >
          <template v-slot:loading>
            <v-progress-circular
              indeterminate
              color="primary"
            ></v-progress-circular>
          </template>
          <template v-slot:no-data>
            <div class="text-center py-4">
              اطلاعاتی برای نمایش وجود ندارد
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.v-data-table {
  --v-table-header-height: 40px;
  --v-table-row-height: 40px;
}
</style>