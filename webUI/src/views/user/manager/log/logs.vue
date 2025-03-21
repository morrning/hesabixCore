<template>
  <v-toolbar color="toolbar" :title="$t('user.history')">
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text class="pa-0">
        <v-row>
          <v-col>
            <v-text-field color="info" hide-details="auto" rounded="0" variant="outlined" density="compact"
              :placeholder="$t('dialog.search_txt')" v-model="searchValue" type="text" clearable>
              <template v-slot:prepend-inner>
                <v-tooltip location="bottom" :text="$t('dialog.search')">
                  <template v-slot:activator="{ props }">
                    <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
                  </template>
                </v-tooltip>
              </template>
            </v-text-field>
            <EasyDataTable table-class-name="customize-table" :headers="headers" :items="items" alternating
              :search-value="searchValue" theme-color="#1d90ff" header-text-direction="center"
              body-text-direction="center" rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از" :loading="loading">
              <template #pagination="{ prevPage, nextPage, isFirstPage, isLastPage }">
                <v-btn size="small" color="success" class="me-1" :disabled="isFirstPage" @click="prevPage"
                  prepend-icon="mdi-skip-next">
                  {{ $t('dialog.prev_page') }}
                </v-btn>
                <v-btn size="small" color="success" class="me-1" :disabled="isLastPage" @click="nextPage"
                  append-icon="mdi-skip-previous">
                  {{ $t('dialog.next_page') }}
                </v-btn>
              </template>
            </EasyDataTable>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>

import axios from "axios";
import { ref } from "vue";

export default {
  name: "logs",
  data: () => {
    return {
      searchValue: '',
      loading: ref(true),
      items: [],
      headers: [
        { text: "کسب و کار", value: "bid" },
        { text: "تاریخ", value: "date" },
        { text: "کاربر", value: "user" },
        { text: "توضیحات", value: "des" },
        { text: "بخش", value: "part" },
        { text: "آی پی آدرس", value: "ipaddress" },
      ]
    }
  },
  methods: {
    loadData() {
      axios.post('/api/admin/logs/last')
        .then((response) => {
          this.items = response.data.data;
          this.loading = false;
        })
    }
  },
  beforeMount() {
    this.loadData();
  }
}
</script>
<style scoped></style>