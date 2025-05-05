<script>
import axios from "axios";

export default {
  name: "archive_list",
  data: () => {
    return {
      info: {
        size: 0,
        remain: 0
      }
    }
  },
  created() {
    this.loadData()
  },
  methods: {
    loadData() {
      axios.post('/api/archive/info').then((response) => {
        this.info = response.data;
      });
      setTimeout(this.loadData, 60000);
    }
  }
}
</script>

<template>
  <v-list>
    <v-list-item to="/acc/archive/order/new" class="my-0 py-0">
      <v-list-item-title>
        <v-progress-linear class="my-2"color="primary" :model-value="((parseInt(this.info.used) * 100) / parseInt(this.info.size))" :height="10"></v-progress-linear>
        <span class="text-danger">{{ $filters.formatNumber(this.info.size) }}</span>
        {{ $t('units.mb') }}
      </v-list-item-title>
      <template v-slot:prepend>
            <v-icon color="primary" icon="mdi-cloud-tags"></v-icon>
          </template>
    </v-list-item>
  </v-list>
</template>

<style scoped></style>