<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        تراکنش های تنخواه گردان
      </h3>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="col-sm-12 col-md-6 mb-1">
            <div class="card push">
              <div class="card-header border-bottom-0 bg-primary-dark text-light">
                <h3 class="block-title"> گردش حساب <small class="text-info-light">{{ selectedObjectItem.name }}</small>
                </h3>
              </div>
              <div class="card-body">
                <small class="mb-2">تنخواه گردان</small>
                <v-cob dir="rtl" :options="objectItems" label="name" v-model="selectedObjectItem"
                  @option:selected="updateRoute(selectedObjectItem.code)">
                  <template #no-options="{ search, searching, loading }">
                    نتیجه‌ای یافت نشد!
                  </template>
                </v-cob>
                <hr />
                <div class="fw-bold mb-2">کد حسابداری: <small class="text-primary">{{ selectedObjectItem.code }}</small>
                </div>
                <div class="fw-bold mb-2">نام : <small class="text-primary">{{ selectedObjectItem.name }}</small></div>
                <div class="fw-bold mb-2">شرح: <small class="text-primary">{{ selectedObjectItem.des }}</small></div>
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 col-md-12">
          <h3>تراکنش ها:</h3>
          <div class="mb-1">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
            </div>
          </div>
          <EasyDataTable table-class-name="customize-table" show-index alternating :search-value="searchValue" :headers="headers" :items="items"
            theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
            rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از"
            :loading="loading">
            <template #item-operation="{ code }">
              <router-link class="text-success" :to="'/acc/accounting/view/' + code">
                <i class="fa fa-eye px-1"></i>
              </router-link>
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { ref } from "vue";

export default {
  name: "card",
  data: () => {
    return {
      searchValue: '',
      objectItems: [{
        name: ''
      }],
      selectedObjectItem: {},
      items: [],
      loading: ref(true),
      headers: [
        { text: "عملیات", value: "operation" },
        { text: "تاریخ", value: "date", 'sortable': true },
        { text: "شرح", value: "des" },
        { text: "تفضیل", value: "ref", 'sortable': true },
        { text: "بدهکار", value: "bd", 'sortable': true },
        { text: "بستانکار", value: "bs", 'sortable': true },
      ]
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    updateRoute(id) {
      this.$router.push(id);
      this.loadData();
    },
    loadData() {
      axios.post('/api/salary/list').then((response) => {
        this.objectItems = response.data;
        if (this.$route.params.id != '') {
          this.loadObject(this.$route.params.id);
          this.objectItems.forEach((item) => {
            if (item.code == this.$route.params.id) {
              this.selectedObjectItem = item;
            }
          });
        } else {
          this.selectedObjectItem = response.data[0];
          this.loadObject(this.selectedObjectItem.code);
        }
      });
    },
    loadObject(id) {
      this.loading = true;
      axios.post('/api/accounting/rows/search',
        {
          type: 'salary',
          id: id
        }
      ).then((response) => {
        this.items = response.data;
        this.items.forEach((item) => {
          item.bs = this.$filters.formatNumber(item.bs)
          item.bd = this.$filters.formatNumber(item.bd)
        })
        this.loading = false;
      });
    }
  }
}
</script>

<style scoped></style>