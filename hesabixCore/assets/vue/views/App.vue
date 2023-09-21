<script>

export default {
  data(){
    return {

    }
  },
  beforeMount() {
    alert();
  },
  created() {

  },
  methods:{

  },
  async mounted() {

  },
  components:{

  }
}
</script>

<template>
  <div>
    This is From App view
  </div>
  <RouterView />
</template>

<style>

.customize-table {
  --easy-table-header-font-color: #e1e1e1;
  --easy-table-header-background-color: #055bbb;
}
.form-control ,.form-select{
  font-family: 'vazir', sans-serif;
}
</style>
