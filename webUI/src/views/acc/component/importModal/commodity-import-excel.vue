<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

export default defineComponent({
  name: 'CommodityImportExcel',
  props: {
    windowsState: {
      type: Object,
      required: true
    }
  },
  setup(props, { emit }) {
    const loading = ref(false)
    const file = ref<File | null>(null)
    const fileInput = ref<HTMLInputElement | null>(null)
    const dialog = ref(false)

    const addFile = (e: Event) => {
      const target = e.target as HTMLInputElement
      if (target.files) {
        file.value = target.files[0]
      }
    }

    const submit = async () => {
      if (!file.value) {
        await Swal.fire({
          text: 'فایل انتخاب نشده است',
          icon: 'error',
          confirmButtonText: 'قبول'
        })
        return
      }

      loading.value = true
      const formData = new FormData()
      formData.append('file', file.value)

      try {
        await axios.post('/api/commodity/import/excel', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        const result = await Swal.fire({
          text: 'فایل با موفقیت ثبت شد.',
          icon: 'success',
          confirmButtonText: 'قبول'
        })

        if (result.isConfirmed) {
          props.windowsState.submited = true
          dialog.value = false
          file.value = null
          if (fileInput.value) fileInput.value.value = ''
        }
      } catch (error) {
        await Swal.fire({
          text: 'متاسفانه خطایی به وجود آمد.',
          icon: 'error',
          confirmButtonText: 'قبول'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      loading,
      file,
      fileInput,
      dialog,
      addFile,
      submit
    }
  }
})
</script>

<template>
  <div>
    <v-tooltip bottom text="وارد کردن از اکسل">
      <template #activator="{ props }">
        <v-btn
          v-bind="props"
          icon="mdi-table-arrow-left"
          color="primary"
          @click="dialog = true"
        />
      </template>
    </v-tooltip>

    <v-dialog v-model="dialog" max-width="600" persistent>
      <v-toolbar title="درون ریزی از اکسل">
         <v-spacer />
          <v-btn icon="mdi-close" variant="text" color="gray" @click="dialog = false" />
      </v-toolbar>
      <v-card rounded="0">
        <v-card-text class="pt-4">
          <v-list>
            <v-list-item>
              برای وارد کردن لیست کالا و خدمات در اکسل ابتدا فایل نمونه را دریافت نمایید سپس مطابق الگو اطلاعات را تکمیل کنید در مرحله بعدی با انتخاب فایل نسبت به ورود از لیست اقدام کنید
            </v-list-item>
            <v-list-item>
              <a
                :href="`${$filters.getApiUrl()}/imports/commodities-import.xlsx`"
                target="_blank"
                class="text-decoration-none"
              >
                دریافت فایل نمونه
              </a>
            </v-list-item>
          </v-list>

          <form @submit.prevent="submit">
            <v-file-input
              ref="fileInput"
              label="انتخاب فایل"
              accept=".xls,.xlsx,.xlsm"
              @change="addFile"
              variant="outlined"
              class="mb-4"
              :disabled="loading"
            />

            <v-btn
              type="submit"
              color="primary"
              block
              :loading="loading"
              :disabled="loading"
            >
              وارد کردن
            </v-btn>
          </form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<style scoped>
.bg-primary {
  background-color: #1976d2 !important;
}
</style>