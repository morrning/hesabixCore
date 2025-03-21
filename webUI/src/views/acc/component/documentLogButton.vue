<script lang="ts">
import axios from 'axios';
import {defineComponent,ref} from 'vue'

export default defineComponent({
  name: "documentLogButton",
  props:{
    docCode: String
  },
  data: ()=>{return {
    logPermision : ref(false),
    itemsSelected: [],
    searchValue: '',
    loading: ref(true),
    items:[],
    headers: [
      { text: "تاریخ", value: "date"},
      { text: "کاربر", value: "user"},
      { text: "شرح", value: "des"},
    ]
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
  <!-- Button trigger modal -->
  <button v-show="this.logPermision" title="تاریخچه سند" type="button" class="btn btn-sm btn-info mx-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop" >
    <i class="fa fa-history me-2" aria-hidden="true"></i>
    <span class="">تاریخچه</span>
  </button>

  <!-- Modal -->
  <div class="modal modal-lg fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-info-light text-dark">
          <h1 class="modal-title fs-5" id="staticBackdropLabel">
            <i class="fa fa-history me-2" aria-hidden="true"></i>
            تاریخچه تغییرات سند
          </h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <EasyDataTable table-class-name="customize-table"
              show-index
              alternating
              :search-value="searchValue"
              :headers="headers"
              :items="items"
              theme-color="#1d90ff"
              header-text-direction="center"
              body-text-direction="center"
              rowsPerPageMessage="تعداد سطر"
              emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از"
              :loading = "loading"
          >
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>