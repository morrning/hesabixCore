<template>
  <v-toolbar color="toolbar" flat class="rounded-t mb-4">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-toolbar-title class="text-h6">{{ isEdit ? $t('dialog.edit') : $t('dialog.add_new') }}</v-toolbar-title>
    <v-spacer></v-spacer>
    <v-tooltip :text="$t('dialog.save')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-content-save" color="success" @click="validateAndSave"></v-btn>
      </template>
    </v-tooltip>
    <v-tooltip v-if="isEdit" :text="$t('dialog.delete')" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-delete" color="error" @click="deleteDialog = true"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container>
    <v-form ref="form" v-model="valid">
      <v-row>
        <v-col cols="12" sm="6" md="6">
          <Hdatepicker v-model="form.date" :label="$t('dialog.hrm.date')"
            :rules="[v => !!v || $t('dialog.hrm.required_fields.date')]" required />
        </v-col>
        <v-col cols="12" sm="6" md="6">
          <v-text-field v-model="form.description" :label="$t('dialog.hrm.description')" 
            :rules="[v => !!v || $t('dialog.hrm.required_fields.description')]" required></v-text-field>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <v-table class="border rounded d-none d-sm-table" style="width: 100%;">
            <thead>
              <tr style="background-color: #0D47A1; color: white;">
                <th class="text-center pa-1" style="width: 100px;">شخص</th>
                <th class="text-center pa-1">حقوق پایه</th>
                <th class="text-center pa-1">اضافه کار</th>
                <th class="text-center pa-1">حق شیفت</th>
                <th class="text-center pa-1">شب کاری</th>
                <th class="text-center pa-1" style="width: 150px;">جمع کل</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(row, index) in tableItems" :key="index">
                <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '48px' }">
                  <td class="text-center pa-1" style="width: 170px;">
                    <Hpersonsearch v-model="row.person" label="شخص" density="compact" hide-details class="my-0" style="font-size: 0.8rem;"></Hpersonsearch>
                  </td>
                  <td class="text-center pa-1" style="width: 120px;">
                    <Hnumberinput v-model="row.baseSalary" density="compact" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                  </td>
                  <td class="text-center pa-1" style="width: 120px;">
                    <Hnumberinput v-model="row.overtime" density="compact" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                  </td>
                  <td class="text-center pa-1" style="width: 120px;">
                    <Hnumberinput v-model="row.shift" density="compact" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                  </td>
                  <td class="text-center pa-1" style="width: 120px;">
                    <Hnumberinput v-model="row.night" density="compact" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                  </td>
                  <td class="text-center font-weight-bold pa-1" style="width: 120px;">
                    {{ calculateTotal(row).toLocaleString('fa-IR') }}
                  </td>
                </tr>
                <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '48px' }">
                  <td colspan="4" class="pa-1">
                    <v-text-field v-model="row.description" density="compact" hide-details 
                      :placeholder="$t('dialog.hrm.row_description')" class="my-0" style="font-size: 0.8rem;"></v-text-field>
                  </td>
                  <td class="text-center pa-1" style="width: 120px;">
                    <v-tooltip text="حذف" location="bottom">
                      <template v-slot:activator="{ props }">
                        <v-btn v-bind="props" icon="mdi-delete" variant="text" size="small" color="error" @click="removeRow(index)"></v-btn>
                      </template>
                    </v-tooltip>
                    <v-tooltip text="کپی" location="bottom">
                      <template v-slot:activator="{ props }">
                        <v-btn v-bind="props" icon="mdi-content-copy" variant="text" size="small" color="primary" @click="copyRow(index)"></v-btn>
                      </template>
                    </v-tooltip>
                  </td>
                </tr>
              </template>
              <tr v-if="tableItems.length === 0">
                <td colspan="6" class="text-center text-grey pa-1">{{ $t('dialog.hrm.no_data') }}</td>
              </tr>
              <tr>
                <td colspan="6" class="text-center pa-1" style="height: 48px;">
                  <v-btn color="primary" prepend-icon="mdi-plus" size="small" @click="addRow">{{ $t('dialog.hrm.add_new_row') }}</v-btn>
                </td>
              </tr>
              <tr v-if="tableItems.length > 0" style="background-color: #E3F2FD;">
                <td class="text-center pa-1 font-weight-bold">جمع کل</td>
                <td class="text-center pa-1 font-weight-bold">{{ calculateColumnTotal('baseSalary').toLocaleString('fa-IR') }}</td>
                <td class="text-center pa-1 font-weight-bold">{{ calculateColumnTotal('overtime').toLocaleString('fa-IR') }}</td>
                <td class="text-center pa-1 font-weight-bold">{{ calculateColumnTotal('shift').toLocaleString('fa-IR') }}</td>
                <td class="text-center pa-1 font-weight-bold">{{ calculateColumnTotal('night').toLocaleString('fa-IR') }}</td>
                <td class="text-center pa-1 font-weight-bold">{{ calculateColumnTotal('total').toLocaleString('fa-IR') }}</td>
              </tr>
            </tbody>
          </v-table>
        </v-col>
      </v-row>
    </v-form>
    <v-snackbar v-model="showSuccess" color="success" timeout="3000">
      {{ successMessage }}
    </v-snackbar>
    <v-snackbar v-model="showError" color="error" timeout="3000">
      {{ errorMessage }}
    </v-snackbar>
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title class="text-h5">{{ $t('dialog.hrm.title') }}</v-card-title>
        <v-card-text>{{ $t('dialog.hrm.delete_confirm') }}</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="grey-darken-1" variant="text" @click="deleteDialog = false">{{ $t('dialog.cancel') }}</v-btn>
          <v-btn color="error" variant="text" @click="confirmDelete" :loading="loading">{{ $t('dialog.delete') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import Hdatepicker from '@/components/forms/Hdatepicker.vue';
import Hpersonsearch from '@/components/forms/Hpersonsearch.vue';
import Hnumberinput from '@/components/forms/Hnumberinput.vue';
export default {
  components: { Hdatepicker, Hpersonsearch, Hnumberinput },
  data() {
    return {
      valid: false,
      isEdit: false,
      loading: false,
      deleteDialog: false,
      showSuccess: false,
      showError: false,
      successMessage: '',
      errorMessage: '',
      form: {
        date: '',
        description: ''
      },
      tableItems: [],
    }
  },
  mounted() {
    const id = this.$route.params.id
    if (id) {
      this.isEdit = true
      this.loadData(id)
    }
  },
  methods: {
    async loadData(id) {
      try {
        const response = await this.$axios.post('/api/hrm/docs/get/' + id)
        this.form = response.data
      } catch (error) {
        this.errorMessage = 'خطا در دریافت اطلاعات';
        this.showError = true;
      }
    },
    validateAndSave() {
      if (!this.$refs.form.validate()) {
        this.errorMessage = this.$t('validator.form_invalid');
        this.showError = true;
        return;
      }

      // بررسی وجود حداقل یک سطر در جدول
      if (this.tableItems.length === 0) {
        this.errorMessage = this.$t('dialog.hrm.no_data');
        this.showError = true;
        return;
      }

      // بررسی انتخاب شخص در تمام سطرها
      const hasInvalidPerson = this.tableItems.some(row => !row.person);
      if (hasInvalidPerson) {
        this.errorMessage = this.$t('dialog.hrm.required_fields.person');
        this.showError = true;
        return;
      }

      this.save();
    },
    async save() {
      try {
        this.loading = true;
        const url = this.isEdit ? '/api/hrm/docs/update' : '/api/hrm/docs/insert'
        await this.$axios.post(url, this.form)
        this.successMessage = this.isEdit ? this.$t('dialog.hrm.edit_success') : this.$t('dialog.hrm.save_success');
        this.showSuccess = true;
        setTimeout(() => {
          this.$router.push('/acc/hrm/docs/list')
        }, 1200)
      } catch (error) {
        this.errorMessage = this.$t('dialog.hrm.save_error');
        this.showError = true;
      } finally {
        this.loading = false;
      }
    },
    async confirmDelete() {
      try {
        this.loading = true;
        await this.$axios.post('/api/hrm/docs/delete', { id: this.$route.params.id })
        this.successMessage = 'سند با موفقیت حذف شد';
        this.showSuccess = true;
        setTimeout(() => {
          this.$router.push('/acc/hrm/docs/list')
        }, 1200)
      } catch (error) {
        this.errorMessage = 'خطا در حذف سند';
        this.showError = true;
      } finally {
        this.loading = false;
        this.deleteDialog = false;
      }
    },
    addRow() {
      this.tableItems.push({
        person: null,
        description: '',
        baseSalary: 0,
        overtime: 0,
        shift: 0,
        night: 0
      });
    },
    removeRow(index) {
      this.tableItems.splice(index, 1);
    },
    calculateTotal(row) {
      const base = Number(row.baseSalary) || 0;
      const overtime = Number(row.overtime) || 0;
      const shift = Number(row.shift) || 0;
      const night = Number(row.night) || 0;
      return base + overtime + shift + night;
    },
    recalculateTotals() {
      // Implementation of recalculateTotals method
    },
    calculateColumnTotal(column) {
      return this.tableItems.reduce((sum, row) => {
        if (column === 'total') {
          return sum + this.calculateTotal(row);
        }
        return sum + (Number(row[column]) || 0);
      }, 0);
    },
    copyRow(index) {
      const rowToCopy = this.tableItems[index];
      this.tableItems.push({
        person: null,
        description: rowToCopy.description,
        baseSalary: rowToCopy.baseSalary,
        overtime: rowToCopy.overtime,
        shift: rowToCopy.shift,
        night: rowToCopy.night
      });
    },
  }
}
</script>

<style scoped>
.bank-card {
  border-right: 4px solid #1976D2 !important;
}

.cashdesk-card {
  border-right: 4px solid #4CAF50 !important;
}

.salary-card {
  border-right: 4px solid #FF9800 !important;
}

.tabs-container {
  background-color: #f5f5f5;
}

.payment-card :deep(.v-card-item) {
  min-height: 40px;
}

.payment-card :deep(.v-card-title) {
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.payment-card :deep(.v-card-text) {
  padding-top: 8px;
  padding-bottom: 8px;
}

:deep(.v-overlay__content) {
  z-index: 9999 !important;
}

:deep(.v-menu__content) {
  z-index: 9999 !important;
}

:deep(.v-dialog) {
  z-index: 9999 !important;
}

:deep(.v-date-picker) {
  z-index: 9999 !important;
}

.settings-section-card {
  height: 100%;
  transition: all 0.3s ease;
}

.settings-section-card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.settings-section-title {
  background-color: #f5f5f5;
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
}

.settings-section-content {
  padding: 20px;
}

.settings-section-card :deep(.v-switch) {
  margin-bottom: 8px;
}

.settings-section-card :deep(.v-switch .v-label) {
  font-size: 0.9rem;
  color: #424242;
}

.settings-section-card :deep(.v-switch.v-input--disabled) {
  opacity: 0.6;
}

.settings-section-card :deep(.v-select) {
  margin-top: 8px;
}

.settings-section-card :deep(.v-divider) {
  margin: 16px 0;
}

.draft-dialog {
  border-radius: 12px;
  overflow: hidden;
}

.draft-dialog-title {
  background-color: #f5f5f5;
  padding: 16px;
  font-size: 1.25rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.draft-dialog-content {
  padding: 24px;
}

.draft-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 16px 0;
}

.draft-message p {
  margin: 8px 0;
}

.draft-dialog-actions {
  padding: 16px;
  background-color: #f5f5f5;
}

:deep(.v-btn) {
  text-transform: none;
  letter-spacing: 0;
  font-weight: 500;
}

:deep(.v-btn--elevated) {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

:deep(.v-btn--outlined) {
  border-width: 1px;
}
</style>