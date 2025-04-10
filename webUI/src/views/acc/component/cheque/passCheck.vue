<script lang="ts">
import { defineComponent, ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'
import Hdatepicker from '@/components/forms/Hdatepicker.vue'

export default defineComponent({
  name: 'PassCheck',
  components: {
    Hdatepicker
  },
  props: {
    id: {
      type: Number,
      required: true
    },
    windowsState: {
      type: Object,
      required: false,
      default: () => ({})
    }
  },
  setup(props) {
    const dialog = ref(false)
    const loading = ref(false)
    const banks = ref([])
    const bankSelected = ref(null)
    const des = ref('')
    const passDate = ref('')
    const year = ref({
      start: '',
      end: '',
      now: ''
    })

    const loadData = async () => {
      try {
        loading.value = true
        const [banksResponse, yearResponse] = await Promise.all([
          axios.post('/api/bank/list'),
          axios.post('/api/year/get')
        ])
        
        banks.value = banksResponse.data
        year.value = yearResponse.data
        passDate.value = yearResponse.data.now
      } catch (error) {
        console.error('خطا در بارگذاری اطلاعات:', error)
      } finally {
        loading.value = false
      }
    }

    const openDialog = () => {
      dialog.value = true
      loadData()
    }

    const closeDialog = () => {
      dialog.value = false
      bankSelected.value = null
      des.value = ''
    }

    const save = async () => {
      if (!bankSelected.value) {
        await Swal.fire({
          text: 'بانک انتخاب نشده است',
          icon: 'error',
          confirmButtonText: 'قبول'
        })
        return
      }

      try {
        loading.value = true
        await axios.post(`/api/cheque/pass/${props.id}`, {
          bank: bankSelected.value,
          date: passDate.value,
          des: des.value
        })

        await Swal.fire({
          text: 'ثبت وصول چک با موفقیت ثبت شد.',
          icon: 'success',
          confirmButtonText: 'قبول'
        })

        if (props.windowsState) {
          props.windowsState.submited = true
        }
        closeDialog()
      } catch (error) {
        console.error('خطا در ثبت اطلاعات:', error)
        await Swal.fire({
          text: 'خطا در ثبت اطلاعات',
          icon: 'error',
          confirmButtonText: 'قبول'
        })
      } finally {
        loading.value = false
      }
    }

    return {
      dialog,
      loading,
      banks,
      bankSelected,
      des,
      passDate,
      year,
      save,
      openDialog,
      closeDialog
    }
  },
  methods: {
    openDialog() {
      this.dialog = true
      this.loadData()
    },
    closeDialog() {
      this.dialog = false
      this.bankSelected = null
      this.des = ''
    }
  }
})
</script>

<template>
  <v-dialog v-model="dialog" max-width="500px" @click:outside="closeDialog">
    <v-card>
      <v-toolbar color="toolbar" title="پاس کردن چک">
        <template v-slot:append>
          <v-tooltip text="ثبت" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" color="success" :loading="loading" @click="save" icon="mdi-content-save" />
            </template>
          </v-tooltip>
          <v-tooltip text="بستن" location="bottom">
            <template v-slot:activator="{ props }">
              <v-btn v-bind="props" icon="mdi-close" variant="text" @click="closeDialog" />
            </template>
          </v-tooltip>
        </template>
      </v-toolbar>

      <v-card-text class="pt-4">
        <v-form @submit.prevent="save">
          <v-row>
            <v-col cols="12">
              <Hdatepicker
                v-model="passDate"
                label="تاریخ"
                :min="year.start"
                :max="year.end"
                required
              />
            </v-col>

            <v-col cols="12">
              <v-select
                v-model="bankSelected"
                :items="banks"
                item-title="name"
                item-value="id"
                label="بانک"
                :rules="[v => !!v || 'انتخاب بانک الزامی است']"
                required
              />
            </v-col>

            <v-col cols="12">
              <v-text-field
                v-model="des"
                label="توضیحات"
                variant="outlined"
              />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.bg-primary-light {
  background-color: var(--v-primary-lighten1);
}
</style>