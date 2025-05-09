<template>
  <v-toolbar color="toolbar" :title="$t('title.user.business_create')">
  </v-toolbar>
  <v-container class="pa-0 ma-0">
    <v-row>
      <v-col class="">
        <v-form fast-fail ref="form" @submit.prevent>
          <v-card class="pa-3" :loading="loading ? 'red' : null" :disabled="loading" flat>
            <h3 class="ma-2 text-primary">{{ $t('pages.create_business.info') }}</h3>
            <v-row>
              <v-col cols="12" sm="12" md="6">
                <v-text-field class="" :label="$t('pages.create_business.business_name')" v-model="content.name"
                  type="text" prepend-inner-icon="mdi-domain"
                  :rules="[() => content.name.length > 0 || $t('validator.required')]"></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field class="" :label="$t('pages.create_business.business_legal_name')"
                  v-model="content.legal_name" type="text" prepend-inner-icon="mdi-domain"
                  :rules="[() => content.legal_name.length > 0 || $t('validator.required')]"></v-text-field>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-select prepend-inner-icon="mdi-focus-field" v-model="content.type"
                  :label="$t('pages.create_business.types')" variant="solo-filled" :items="types"></v-select>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-select prepend-inner-icon="mdi-call-merge" v-model="content.field"
                  :label="$t('pages.create_business.fields')" variant="solo-filled" :items="fields"></v-select>
              </v-col>
              <v-col cols="12" sm="12" md="12">
                <v-text-field class="" :label="$t('pages.create_business.address')" v-model="content.address"
                  type="text" prepend-inner-icon="mdi-map-marker"
                  :rules="[() => content.address.length > 0 || $t('validator.required')]"></v-text-field>
              </v-col>
            </v-row>
            <h3 class="ma-2 text-primary">{{ $t('pages.create_business.financial_settings') }}</h3>
            <v-row>
              <v-col cols="12" sm="12" md="6">
                <v-select class="" :label="$t('pages.dashboard.money')" :hint="$t('pages.create_business.moneys_hint')"
                  prepend-inner-icon="mdi-cash-multiple" :items="moneys" item-title="label" persistent-hint
                  return-object v-model="content.arzmain"
                  :rules="[() => moneys.length > 0 || $t('validator.required')]"></v-select>
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field prepend-inner-icon="mdi-calendar" readonly
                  :rules="[() => content.year.start.length > 0 || $t('validator.required')]"
                  :label="$t('pages.create_business.fiscal_year_start')" v-model="content.year.start"
                  class="txt_calendar1" />
                <CustomDatePicker custom-input=".txt_calendar1" append-to="body" v-model="content.year.start" />
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field prepend-inner-icon="mdi-calendar" readonly
                  :rules="[() => content.year.end.length > 0 || $t('validator.required')]"
                  :label="$t('pages.create_business.fiscal_year_end')" v-model="content.year.end"
                  class="txt_calendar2" />
                <CustomDatePicker custom-input=".txt_calendar2" append-to="body" v-model="content.year.end"
                  :min="content.dateStart" />
              </v-col>
              <v-col cols="12" sm="12" md="6">
                <v-text-field prepend-inner-icon="mdi-text-box-outline"
                  :label="$t('pages.create_business.fiscal_year_label')"
                  :rules="[() => content.year.label.length > 0 || $t('validator.required')]"
                  v-model="content.year.label" />
              </v-col>
              <v-col>
                <v-btn type="submit" @click="submit()" color="primary" prepend-icon="mdi-content-save"
                  :loading="loading" :title="$t('pages.create_business.insert_business')">
                  {{ $t('pages.create_business.insert_business') }}
                </v-btn>
              </v-col>
            </v-row>

          </v-card>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "insert",
  data: () => {
    return {
      loading: false,
      fields: ['تولیدی', 'خدماتی', 'بازرگانی', 'سایر'],
      moneys: [],
      types: ['شرکت', 'مغازه', 'فروشگاه', 'باشگاه', 'موسسه', 'اتحادیه', 'شخصی'],
      content: {
        name: '',
        legal_name: '',
        field: 'خدماتی',
        type: 'مغازه',
        shenasemeli: '',
        codeeqtesadi: '',
        shomaresabt: '',
        country: '',
        ostan: '',
        shahrestan: '',
        postalcode: '',
        tel: '',
        mobile: '',
        address: '',
        website: '',
        email: '',
        arzmain: [],
        maliyatafzode: 9,
        year: {
          start: '',
          end: '',
          label: ''
        }
      },
    }
  },
  methods: {
    async submit(event) {
      const { valid } = await this.$refs.form.validate()
      if (valid) {
        this.loading = true;
        let data = axios.post('/api/business/insert', {
          'name': this.content.name,
          'legal_name': this.content.legal_name,
          'field': this.content.field,
          'type': this.content.type,
          'shenasemeli': this.content.shenasemeli,
          'codeeqtesadi': this.content.codeeqtesadi,
          'shomaresabt': this.content.shomaresabt,
          'country': this.content.country,
          'ostan': this.content.ostan,
          'shahrestan': this.content.shahrestan,
          'postalcode': this.content.postalcode,
          'tel': this.content.tel,
          'mobile': this.content.mobile,
          'address': this.content.address,
          'website': this.content.website,
          'email': this.content.email,
          'arzmain': this.content.arzmain,
          'maliyatafzode': this.content.maliyatafzode,
          'year': this.content.year
        })
          .then((response) => {
            if (response.data.result == 1) {
              Swal.fire({
                title: 'پیام',
                text: 'با موفقیت ثبت شد.',
                icon: 'success',
                confirmButtonText: 'قبول',

              }).then((result) => {
                if (result.isConfirmed) {
                  this.$router.push('/profile/business')
                }
              });
            }
            else if (response.data.result === 0) {
              Swal.fire({
                text: 'تکمیل موارد ستاره دار الزامی است.',
                icon: 'error',
                confirmButtonText: 'قبول'
              });
            }
            else if (response.data.result === 3) {
              Swal.fire({
                text: 'در هر روز تنها قادر به ایجاد یک کسب و کار هستید.لطفا بعدا دوباره اقدام کنید.',
                icon: 'error',
                confirmButtonText: 'قبول'
              });
            }
            this.loading = false;
          })
      }

    }
  },
  beforeMount() {
    //get all money types
    this.loading = true;
    axios.post("/api/money/get/all").then((response) => {
      this.moneys = response.data.data;
      this.content.arzmain = this.moneys[0];
      this.loading = false;
    })


  },
  watch: {
    'content.year.start': {
      handler: function (val, oldVal) {
        let year = this.content.year.start.split('/');
        year[0] = parseInt(year[0]) + 1;
        this.content.year.end = year.join('/');
        year.reverse();
        this.content.year.label = "سال مالی منتهی به " + this.content.year.end;
      },
      deep: true
    },
  },
}
</script>

<style scoped>
.required label:before {
  content: "*";
  color: red;
}
</style>