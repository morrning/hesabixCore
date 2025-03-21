<template>
  <v-select prepend-inner-icon="mdi-calendar" :loading="loading" :label="$t('dialog.fiscal_year')" :items="years" v-model="activeYear" item-title="label"
    persistent-hint return-object>
  </v-select>
</template>

<script>
import axios from "axios";

export default {
  name: "year_cob",
  data: () => {
    return {
      first: true,
      loading: false,
      activeYear: {
        id: '',
        label: ''
      },
      years: []
    }
  },
  watch: {
    'activeYear.id': function (newVal, oldVal) {
      if (this.first == false) {
        this.changeyear(newVal);
      }
      else {
        this.first = false;
      }
    },
  },
  mounted() {
    this.loading = true;
    axios.post('/api/year/list').then((response) => {
      this.loading = false;
      this.years = response.data;

      if (localStorage.getItem('activeYear') != '') {
        this.years.forEach((item) => {
          if (item.id == localStorage.getItem('activeYear')) {
            this.activeYear = item;
          }
        });
      }
      else {
        this.years.forEach((item) => {
          if (item.head == '1') {
            this.activeYear = item;
            localStorage.setItem('activeYear', item.id);
          }
        });
      }
    })
  },
  methods: {
    async changeyear(id) {
      this.loading = true;
      await localStorage.setItem('activeYear', id);
      window.location.reload();
    }
  }
}
</script>

<style scoped></style>