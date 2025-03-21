<script>
import axios from "axios";

export default {
  name: "icon",
  data: () => {
    return {
      items: undefined,
      timeoutId: null, // برای ذخیره ID تایمر
    };
  },
  created() {
    this.loadData();
  },
  beforeDestroy() { // یا destroyed بسته به نسخه Vue
    // پاک کردن تایمر هنگام تخریب کامپوننت
    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
    }
  },
  methods: {
    jump(item) {
      axios.post('/api/notifications/read/' + item.id).then((response) => {
        if (item.url.startsWith('http')) {
          window.location.href = item.url;
        } else {
          this.$router.push(item.url);
        }
      });
    },
    loadData() {
      axios.post('/api/notifications/list/new').then((response) => {
        if (response.data.length !== 0) {
          this.items = response.data;
        } else {
          this.items = undefined;
        }
      }).finally(() => {
        // تنظیم تایمر جدید و ذخیره ID آن
        this.timeoutId = setTimeout(this.loadData, 10000);
      });
    },
  },
};
</script>

<template>
  <div class="dropdown d-inline-block">
    <button aria-expanded="false" aria-haspopup="true" class="btn text-warning me-2 btn-sm btn-alt-secondary rounded-circle" data-bs-toggle="dropdown" id="page-header-notifications-dropdown" type="button">
      <i class="fa fa-bell"></i>
    </button>
    <div aria-labelledby="page-header-notifications-dropdown" class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" style="">
      <div class="bg-primary-dark rounded-top fw-semibold text-white text-center py-2 px-3"> اعلان ها </div>
      <ul v-if="items === undefined" class="nav-items my-2">
        <li>
          <div class="d-flex text-dark py-2 text-center">
            <div class="flex-grow-1 fs-sm pe-2">
              <div class="fw-semibold">اعلانی ندارید ...</div>
            </div>
          </div>
        </li>
      </ul>
      <ul v-else class="nav-items my-2">
        <li v-for="item in items">
          <a class="d-flex text-dark py-2" @click.prevent="jump(item)">
            <div class="flex-shrink-0 mx-3">
              <i :class="'text-' + item.icon" class="fa fa-fw fa-check-circle"></i>
            </div>
            <div class="flex-grow-1 fs-sm pe-2">
              <div class="fw-semibold">{{item.message}}</div>
              <div class="text-muted">{{item.date}} قبل</div>
            </div>
          </a>
        </li>
      </ul>
      <div class="p-2 border-top">
        <RouterLink class="btn btn-alt-primary w-100 text-center" to="/acc/notifications/list">
          <i class="fa fa-fw fa-eye opacity-50 me-1"></i> مشاهده همه </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>