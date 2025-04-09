<template>
  <v-toolbar color="toolbar" title="آرشیو فایل‌ها">
    <template v-slot:prepend>
      <v-btn icon @click="$router.back()">
        <v-icon>mdi-arrow-right</v-icon>
      </v-btn>
    </template>
  </v-toolbar>
  <v-container fluid>
    <v-row>
      <v-col cols="12" md="3">
        <v-expansion-panels class="mb-1" :model-value="isMobile ? [] : [0]">
          <v-expansion-panel>
            <v-expansion-panel-title class="bg-primary text-white">
              <v-icon start>mdi-folder-tree</v-icon>
              دسته بندی فایل‌ها
            </v-expansion-panel-title>
            <v-expansion-panel-text>
              <v-list density="compact" class="py-0">
                <v-list-item v-for="(category, index) in categories" :key="index" :title="category.title"
                  :value="category.value" @click="loadData(category.value)" class="py-1"></v-list-item>
              </v-list>
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-col>

      <v-col cols="12" md="9">
        <v-text-field v-model="searchValue" prepend-inner-icon="mdi-magnify" label="جست و جو ..." variant="outlined"
          density="compact" :rounded="false"></v-text-field>

        <v-data-table :headers="headers" :items="items" :search="searchValue" :loading="loading"
          loading-text="در حال بارگذاری..." no-data-text="اطلاعاتی برای نمایش وجود ندارد"
          items-per-page-text="تعداد سطر" :items-per-page-options="[10, 20, 50, 100]" class="elevation-1 text-center"
          :header-props="{ class: 'custom-header' }">
          <template v-slot:item.operation="{ item }">
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn variant="text" size="small" color="error" icon="mdi-menu" v-bind="props" />
              </template>
              <v-list>
                <v-list-item class="text-dark" :title="$t('dialog.download')"
                  @click="downloadFile(item.id, item.filename, item.fileType)">
                  <template v-slot:prepend>
                    <v-icon color="primary" icon="mdi-download" />
                  </template>
                </v-list-item>
                <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="deleteItem(item.id)">
                  <template v-slot:prepend>
                    <v-icon color="error" icon="mdi-delete" />
                  </template>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>

          <template v-slot:item.cat="{ item }">
            {{ getCategoryTitle(item.cat) }}
          </template>

          <template v-slot:item.filePublic="{ item }">
            <v-icon :color="item.filePublic ? 'success' : 'error'">
              {{ item.filePublic ? 'mdi-check' : 'mdi-close' }}
            </v-icon>
          </template>
        </v-data-table>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "ArchiveFiles",
  data: () => ({
    searchValue: '',
    loading: true,
    cat: 'all',
    items: [],
    categories: [
      { title: 'همه فایل‌ها', value: 'all' },
      { title: 'اسناد حسابداری', value: 'accounting' },
      { title: 'اشخاص', value: 'persons' },
      { title: 'کالا', value: 'commodity' },
      { title: 'بانک', value: 'bank' },
      { title: 'صندوق', value: 'cashdesk' },
      { title: 'تنخواه', value: 'salary' },
      { title: 'انبار', value: 'storeroom' },
      { title: 'انتقال', value: 'transfer' },
      { title: 'دریافت', value: 'persons_recive' },
      { title: 'پرداخت', value: 'persns_send' },
      { title: 'درآمد', value: 'income' },
      { title: 'هزینه', value: 'cost' },
      { title: 'خرید', value: 'buy' },
      { title: 'فروش', value: 'sell' },
      { title: 'فروشگاه آنلاین', value: 'onlinestore' }
    ],
    headers: [
      { title: "عملیات", key: "operation", width: "130", align: "center" },
      { title: "نام فایل", key: "filename", width: "100", align: "center" },
      { title: "نوع فایل", key: "fileType", width: "120", align: "center" },
      { title: "حجم (مگابایت)", key: "filesize", width: "100", align: "center" },
      { title: "تاریخ ایجاد", key: "dateSubmit", width: "140", align: "center" },
      { title: "ایجاد کننده", key: "submitter", width: "140", align: "center" },
      { title: "دسترسی عمومی", key: "filePublic", width: "120", align: "center" },
      { title: "دسته بندی", key: "cat", width: "120", align: "center" }
    ]
  }),
  computed: {
    isMobile() {
      return this.$vuetify.display.mobile;
    }
  },
  methods: {
    loadData(cat) {
      this.loading = true;
      axios.post('/api/archive/list/' + cat)
        .then((response) => {
          this.items = response.data;
          this.cat = cat;
          this.loading = false;
        })
        .catch(() => {
          this.loading = false;
        });
    },
    deleteItem(id) {
      Swal.fire({
        text: 'آیا برای حذف فایل مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'خیر',
      }).then((result) => {
        if (result.isConfirmed) {
          axios.post('api/archive/file/remove/' + id).then((response) => {
            if (response.data.result == 1) {
              this.loadData(this.cat);
              Swal.fire({
                text: 'فایل با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          });
        }
      });
    },
    downloadFile(id, filename, fileType) {
      axios.get(this.$filters.getApiUrl() + '/api/archive/file/get/' + id, {
        responseType: 'blob'
      }).then(response => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      });
    },
    getCategoryTitle(cat) {
      const category = this.categories.find(c => c.value === cat);
      return category ? category.title : cat;
    }
  },
  mounted() {
    this.loadData(this.cat);
  }
}
</script>

<style scoped>
.v-list-item--active {
  background-color: rgba(var(--v-theme-primary), 0.1);
}
</style>