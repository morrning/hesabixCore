<script>
import axios from "axios";

export default {
  name: "archive_modal",
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
  <div>
    <router-link class="btn btn-link btn-sm me-1" to="/acc/archive/order/new">
          <i class="fa fa-plus"></i>
    </router-link>
    <label class=""> فضا: <span class="text-primary-dark">{{ $filters.formatNumber(this.info.size) }}</span> مگابایت
    </label>
    <div class="progress mt-1 mx-1" role="progressbar" aria-label="فضای استفاده شده" :aria-valuenow="this.info.remain"
      aria-valuemin="0" :aria-valuemax="this.info.size">
      <div class="progress-bar progress-bar-striped progress-bar-animated"
        :style="'width: ' + ((parseInt(this.info.used) * 100) / parseInt(this.info.size)) + '%'"></div>
    </div>
  </div>

</template>

<style scoped></style>