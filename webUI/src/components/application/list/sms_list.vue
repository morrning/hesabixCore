<script>
import axios from "axios";

export default {
  name: "sms_list",
  data: () => {
    return {
      smsCharge: 0
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      axios.post('/api/business/get/info/' + localStorage.getItem('activeBid')).then((response) => {
        this.smsCharge = response.data.smsCharge;
      });
      setTimeout(this.loadData, 10000);
    }
  }
}
</script>

<template>
  <v-list>
    <v-list-item to="/acc/sms/panel" class="my-0 py-0">
      <v-list-item-title>
        <span class="text-danger">{{ $filters.formatNumber(smsCharge) }}</span>
        ریال
      </v-list-item-title>
      <template v-slot:prepend>
        <v-icon color=success icon="mdi-message-fast"></v-icon>
      </template>
    </v-list-item>
  </v-list>
</template>

<style scoped></style>