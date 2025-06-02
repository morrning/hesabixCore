<template>
  <v-container>
    <v-card>
      <v-card-title class="d-flex align-center">
        {{ $t('drawer.hrm_docs') }}
        <v-spacer></v-spacer>
        <v-btn color="primary" prepend-icon="mdi-plus" to="/acc/hrm/docs/mod/">
          {{ $t('dialog.add_new') }}
        </v-btn>
      </v-card-title>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="items"
          :loading="loading"
          class="elevation-1"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn
              icon="mdi-eye"
              variant="text"
              size="small"
              :to="'/acc/hrm/docs/view/' + item.id"
            ></v-btn>
            <v-btn
              icon="mdi-pencil"
              variant="text"
              size="small"
              :to="'/acc/hrm/docs/mod/' + item.id"
            ></v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      loading: false,
      headers: [
        { title: this.$t('dialog.field.id'), key: 'id' },
        { title: this.$t('dialog.field.date'), key: 'date' },
        { title: this.$t('dialog.field.employee'), key: 'employee' },
        { title: this.$t('dialog.field.amount'), key: 'amount' },
        { title: this.$t('dialog.field.status'), key: 'status' },
        { title: this.$t('dialog.field.actions'), key: 'actions', sortable: false }
      ],
      items: []
    }
  },
  mounted() {
    this.loadData()
  },
  methods: {
    async loadData() {
      this.loading = true
      try {
        const response = await axios.post('/api/hrm/docs/list')
        this.items = response.data
      } catch (error) {
        console.error('Error loading data:', error)
      }
      this.loading = false
    }
  }
}
</script> 