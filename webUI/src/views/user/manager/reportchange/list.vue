<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "list",
  data: () => {
    return {
      searchValue: '',
      loading: ref(true),
      items: [],
      headers: [
        { text: "عملیات", value: "operation" },
        { text: "تاریخ", value: "dateSubmit", sortable: true },
        { text: "نسخه", value: "version", sortable: true },
      ]
    }
  },
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/admin/reportchange/lists').then((response) => {
        this.items = response.data;
        this.loading = false;
      })
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف این مورد مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
        icon: 'warning'
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/admin/reportchange/delete/' + code
          ).then((response) => {
            if (response.data.result == 1) {
              let index = 0;
              for (let z = 0; z < this.items.length; z++) {
                index++;
                if (this.items[z]['id'] == code) {
                  this.items.splice(index - 1, 1);
                }
              }
              Swal.fire({
                text: 'با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
            else if (response.data.result == 2) {
              Swal.fire({
                text: response.data.message,
                icon: 'warning',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    }
  },
  mounted() {
    this.loadData();
  }
})
</script>

<template>
  <v-toolbar color="toolbar" :title="$t('user.history')">
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('dialog.new')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="mdi-plus" color="primary" to="/profile/manager/changes/mod/0"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <v-card-text class="pa-0">
        <v-row>
          <v-col>
            <EasyDataTable table-class-name="customize-table" show-index alternating :search-value="searchValue"
              :headers="headers" :items="items" theme-color="#1d90ff" header-text-direction="center"
              body-text-direction="center" rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از" :loading="loading">
              <template #item-operation="{ id }">
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-btn variant="text" color="error" icon="mdi-menu" v-bind="props" />
                  </template>
                  <v-list>
                    <v-list-item class="text-dark" :title="$t('dialog.edit')"
                      :to="'/profile/manager/changes/mod/' + id">
                      <template v-slot:prepend>
                        <v-icon icon="mdi-file-edit"></v-icon>
                      </template>
                    </v-list-item>
                    <v-list-item class="text-dark" :title="$t('dialog.delete')" @click="deleteItem(id)">
                      <template v-slot:prepend>
                        <v-icon color="error" icon="mdi-trash-can"></v-icon>
                      </template>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
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

<style scoped></style>