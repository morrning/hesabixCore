<template>
  <v-container>
    <v-card>
      <v-card-title class="d-flex align-center">
        {{ $t('drawer.hrm_docs') }}
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          prepend-icon="mdi-pencil"
          :to="'/acc/hrm/docs/mod/' + $route.params.id"
        >
          {{ $t('dialog.edit') }}
        </v-btn>
      </v-card-title>
      <v-card-text>
        <v-row v-if="!loading">
          <v-col cols="12" md="6">
            <v-list>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon icon="mdi-account"></v-icon>
                </template>
                <v-list-item-title>{{ $t('field.employee') }}</v-list-item-title>
                <v-list-item-subtitle>{{ item.employee }}</v-list-item-subtitle>
              </v-list-item>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon icon="mdi-currency-usd"></v-icon>
                </template>
                <v-list-item-title>{{ $t('field.amount') }}</v-list-item-title>
                <v-list-item-subtitle>{{ item.amount }}</v-list-item-subtitle>
              </v-list-item>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon icon="mdi-calendar"></v-icon>
                </template>
                <v-list-item-title>{{ $t('field.date') }}</v-list-item-title>
                <v-list-item-subtitle>{{ item.date }}</v-list-item-subtitle>
              </v-list-item>
              <v-list-item>
                <template v-slot:prepend>
                  <v-icon icon="mdi-check-circle"></v-icon>
                </template>
                <v-list-item-title>{{ $t('field.status') }}</v-list-item-title>
                <v-list-item-subtitle>{{ item.status }}</v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
        <v-progress-circular
          v-else
          indeterminate
          color="primary"
        ></v-progress-circular>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="$router.back()">
          {{ $t('dialog.back') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      loading: true,
      item: {}
    }
  },
  mounted() {
    this.loadData()
  },
  methods: {
    async loadData() {
      try {
        const response = await this.$axios.post('/api/hrm/docs/get/' + this.$route.params.id)
        this.item = response.data
      } catch (error) {
        console.error('Error loading data:', error)
      }
      this.loading = false
    }
  }
}
</script> 