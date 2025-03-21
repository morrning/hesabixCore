<template>
    <v-card :loading="loading ? 'red' : null" :disabled="loading">
      <!-- Toolbar -->
      <v-toolbar color="toolbar" :title="$t('dialog.explore_accounts')" flat>
        <v-spacer></v-spacer>
        <v-btn icon @click="loadNode('root', 'calc', true, 1)" :title="$t('button.back_to_root')">
          <v-icon>mdi-home</v-icon>
        </v-btn>
      </v-toolbar>
  
      <!-- Breadcrumbs -->
      <v-card-text>
        <v-row>
          <v-col>
            <v-breadcrumbs :items="tree" class="ma-0 pa-0">
              <template #prepend>
                <v-icon icon="mdi-family-tree" color="primary" class="me-3"></v-icon>
              </template>
              <template #title="{ item }">
                <v-btn
                  density="compact"
                  @click="loadNode(item.id, 'calc', item.hasChild, 1)"
                  color="info"
                  variant="text"
                >
                  {{ item.name }}
                </v-btn>
              </template>
            </v-breadcrumbs>
          </v-col>
        </v-row>
      </v-card-text>
  
      <!-- Table -->
      <v-card-text class="mt-0 pt-0 px-0">
        <EasyDataTable
          :headers="headers"
          :items="items"
          :loading="loading"
          table-class-name="customize-table"
          multi-sort
          show-index
          alternating
          theme-color="#1d90ff"
          header-text-direction="center"
          body-text-direction="center"
          hide-default-footer
          :rows-per-page-message="$t('table.rows_per_page')"
          :empty-message="$t('table.no_data')"
          :rows-of-page-separator-message="$t('table.of')"
        >
          <!-- Custom Slots -->
          <template #item-operation="{ id, type, isObject, upperID }">
            <DetailsBtn :node="id" :node-type="type" :is-object="isObject" :upper-id="upperID" />
          </template>
          <template #item-account="{ hasChild, type, isObject, code, account, id }">
            <v-btn
              color="primary"
              :disabled="(!hasChild && isObject) || (!hasChild && type === 'calc')"
              block
              variant="text"
              @click="loadNode(id, type, hasChild, 1)"
            >
              {{ account }}
            </v-btn>
          </template>
          <template #item-bal_bd="{ bal_bd }">
            {{ formatNumber(bal_bd) }}
          </template>
          <template #item-bal_bs="{ bal_bs }">
            {{ formatNumber(bal_bs) }}
          </template>
          <template #item-his_bd="{ his_bd }">
            {{ formatNumber(his_bd) }}
          </template>
          <template #item-his_bs="{ his_bs }">
            {{ formatNumber(his_bs) }}
          </template>
        </EasyDataTable>
        <!-- Custom Pagination -->
        <v-row class="mt-2" justify="center" align="center">
          <v-btn
            small
            icon
            :disabled="pagination.currentPage === 1"
            @click="loadNode(currentNode, currentType, currentHasChild, pagination.currentPage - 1)"
          >
            <v-icon small>mdi-chevron-right</v-icon>
          </v-btn>
          <span class="mx-2 text-caption">
            {{ $t('pagination.page') }} {{ pagination.currentPage }} {{ $t('pagination.of') }} {{ pagination.totalPages }}
          </span>
          <v-btn
            small
            icon
            :disabled="pagination.currentPage === pagination.totalPages"
            @click="loadNode(currentNode, currentType, currentHasChild, pagination.currentPage + 1)"
          >
            <v-icon small>mdi-chevron-left</v-icon>
          </v-btn>
        </v-row>
      </v-card-text>
  
      <!-- Error Dialog -->
      <v-dialog v-model="errorDialog" max-width="500">
        <v-card>
          <v-card-title class="text-error">{{ $t('error.title') }}</v-card-title>
          <v-card-text>{{ errorMessage }}</v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" @click="errorDialog = false">{{ $t('button.close') }}</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-card>
  </template>
  
  <script>
  import axios from 'axios';
  import DetailsBtn from '../component/reports/detailsBtn.vue';
  
  export default {
    name: 'ExploreAccounts',
    components: {
      DetailsBtn,
    },
    data() {
      return {
        loading: false,
        tree: [],
        items: [],
        plugins: [],
        errorDialog: false,
        errorMessage: '',
        headers: [
          { text: this.$t('table.account'), value: 'account', sortable: true },
          { text: this.$t('table.debit_turnover'), value: 'his_bd', sortable: true, width: 100 },
          { text: this.$t('table.credit_turnover'), value: 'his_bs', sortable: true, width: 100 },
          { text: this.$t('table.debit_balance'), value: 'bal_bd', sortable: true, width: 100 },
          { text: this.$t('table.credit_balance'), value: 'bal_bs', sortable: true, width: 100 },
          { text: this.$t('table.operations'), value: 'operation', width: 100, sortable: false },
        ],
        pagination: {
          totalItems: 0,
          totalPages: 0,
          currentPage: 1,
          perPage: 10,
        },
        currentNode: 'root',
        currentType: 'calc',
        currentHasChild: true,
      };
    },
    methods: {
      async loadNode(id, type, hasChild, page) {
        this.loading = true;
        this.currentNode = id;
        this.currentType = type;
        this.currentHasChild = hasChild;
        try {
          const response = await axios.post('/api/report/acc/explore_accounts_det', {
            node: id,
            type,
            hasChild,
            page: page || this.pagination.currentPage,
            perPage: this.pagination.perPage,
          });
          this.items = response.data.itemData || [];
          this.tree = response.data.tree || [];
          this.pagination = response.data.pagination || {
            totalItems: 0,
            totalPages: 0,
            currentPage: 1,
            perPage: 10,
          };
        } catch (error) {
          this.showError(this.$t('error.fetch_data') + (error.response?.data?.message || error.message));
        } finally {
          this.loading = false;
        }
      },
      async loadInitialData() {
        this.loading = true;
        try {
          const pluginResponse = await axios.post('/api/plugin/get/actives');
          this.plugins = pluginResponse.data || [];
          await this.loadNode('root', 'calc', true, 1);
        } catch (error) {
          this.showError(this.$t('error.initial_load') + (error.response?.data?.message || error.message));
        } finally {
          this.loading = false;
        }
      },
      showError(message) {
        this.errorMessage = message;
        this.errorDialog = true;
      },
      formatNumber(value) {
        return value ? Number(value).toLocaleString('fa-IR') : '0';
      },
      isPluginActive(plugName) {
        return !!this.plugins[plugName];
      },
    },
    mounted() {
      this.loadInitialData();
    },
  };
  </script>
  
  <style scoped>
  .customize-table {
    font-family: 'Vazir', sans-serif;
  }
  </style>