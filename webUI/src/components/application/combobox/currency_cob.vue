<template>
  <v-select prepend-inner-icon="mdi-cash" :loading="loading" :label="$t('dialog.currency')" :items="moneys"
    v-model="activeMoney" item-title="label" persistent-hint return-object>
  </v-select>
</template>

<script>
import axios from "axios";

export default {
  name: "currency_cob",
  data: () => {
    return {
      first: true,
      loading: false,
      activeMoney: {
        id: '',
        name: '',
        symbol: '',
        shortName: '',
        label: ''
      },
      moneys: []
    }
  },
  watch: {
    'activeMoney.name': function (newVal, oldVal) {
      if (this.first == false) {
        this.changemoney(newVal);
      }
      else {
        this.first = false;
      }
    },
  },
  mounted() {
    this.loading = true;
    axios.post('/api/business/get/info/' + localStorage.getItem('activeBid')).then((response) => {
      this.loading = false;
      this.moneys = response.data.moneys;
      if (localStorage.getItem('activeMoney') == '' && localStorage.getItem('activeMoney') == 'undefined') {
        this.activeMoney = response.data.arzmain;
        localStorage.setItem('activeMoney', this.activeMoney.name);
        localStorage.setItem('activeMoneySymbol', this.activeMoney.symbol);
        localStorage.setItem('activeMoneyShortName', this.activeMoney.shortName);
        localStorage.setItem('activeMoneyLabel', this.activeMoney.label);
      }
      else {
        this.moneys.forEach((item) => {
          if (item.name == localStorage.getItem('activeMoney')) {
            this.activeMoney = item;
            localStorage.setItem('activeMoney', item.name);
            localStorage.setItem('activeMoneySymbol', item.symbol);
            localStorage.setItem('activeMoneyShortName', item.shortName);
            localStorage.setItem('activeMoneyLabel', item.label);
          }
        });
      }
    })
  },
  methods: {
    async changemoney(id) {
      localStorage.setItem('activeMoney', id);
      await axios.post('/api/money/get/info', { 'name': localStorage.getItem('activeMoney') }).then((response) => {
        localStorage.setItem('activeMoneySymbol', response.data.data.symbol);
        localStorage.setItem('activeMoneyShortName', response.data.data.shortName);
        localStorage.setItem('activeMoneyLabel', response.data.data.label);
      })
      window.location.reload();
    }
  }
}
</script>

<style scoped></style>