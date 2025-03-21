<template>
  <v-toolbar color="toolbar" :title="$t('drawer.person_info')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-btn :loading="loading" @click="save" icon color="green">
      <v-icon>mdi-content-save</v-icon>
      <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
    </v-btn>
    <template v-slot:extension>
      <v-tabs v-model="tabs" color="primary" grow class="bg-light">
        <v-tab value="0">{{ $t('pages.person.basic_info') }}</v-tab>
        <v-tab value="1">{{ $t('pages.person.eco_info') }}</v-tab>
        <v-tab value="2">{{ $t('pages.person.contact_info') }}</v-tab>
        <v-tab value="3">{{ $t('pages.person.address') }}</v-tab>
        <v-tab value="4">{{ $t('pages.person.banks_accounts') }}</v-tab>
      </v-tabs>
    </template>
  </v-toolbar>

  <v-container fluid class="pa-2">
    <v-row>
      <v-col cols="12">
        <v-tabs-window v-model="tabs">
          <!-- اطلاعات پایه -->
          <v-tabs-window-item value="0">
            <v-card flat>
              <v-card-text class="pa-2">
                <v-row dense>
                  <v-col cols="12">
                    <v-switch v-model="person.speedAccess" :label="$t('pages.person.speed_access')"
                      color="primary" inset hide-details />
                  </v-col>
                  <v-col cols="12">
                    <v-select v-model="selectedTypes" :items="person.types" item-title="label" item-value="label"
                      :label="$t('pages.person.customer_type')" multiple dense hide-details chips deletable-chips
                      prepend-inner-icon="mdi-account-group" />
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-select v-model="person.prelabel" :items="prelabels" item-title="label" item-value="label"
                      :label="$t('dialog.prelabel')" clearable dense hide-details />
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field v-model="person.nikename" :label="$t('pages.person.nickname')" required dense
                      prepend-inner-icon="mdi-account" :rules="[v => !!v || $t('validator.required')]" hide-details />
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field v-model="person.company" :label="$t('pages.person.company')" dense
                      prepend-inner-icon="mdi-domain" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.name" :label="$t('pages.person.name')" dense
                      prepend-inner-icon="mdi-account-outline" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.des" :label="$t('pages.person.description')" dense
                      prepend-inner-icon="mdi-text" hide-details />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tabs-window-item>

          <!-- اطلاعات اقتصادی -->
          <v-tabs-window-item value="1">
            <v-card flat>
              <v-card-text class="pa-2">
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.shenasemeli" :label="$t('pages.person.national_id')" dense
                      prepend-inner-icon="mdi-card-account-details" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.sabt" :label="$t('pages.person.registration_number')" dense
                      prepend-inner-icon="mdi-numeric" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.codeeghtesadi" :label="$t('pages.person.economic_code')" dense
                      prepend-inner-icon="mdi-barcode" hide-details />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tabs-window-item>

          <!-- اطلاعات تماس -->
          <v-tabs-window-item value="2">
            <v-card flat>
              <v-card-text class="pa-2">
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.mobile" :label="$t('pages.person.mobile')" dense
                      prepend-inner-icon="mdi-phone" dir="ltr" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.mobile2" :label="$t('pages.person.mobile2')" dense
                      prepend-inner-icon="mdi-phone-plus" dir="ltr" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.tel" :label="$t('pages.person.phone')" dense
                      prepend-inner-icon="mdi-phone-classic" dir="ltr" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.fax" :label="$t('pages.person.fax')" dense
                      prepend-inner-icon="mdi-fax" dir="ltr" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.email" :label="$t('pages.person.email')" dense
                      prepend-inner-icon="mdi-email" dir="ltr" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.website" :label="$t('pages.person.website')" dense
                      prepend-inner-icon="mdi-web" dir="ltr" hide-details />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tabs-window-item>

          <!-- آدرس -->
          <v-tabs-window-item value="3">
            <v-card flat>
              <v-card-text class="pa-2">
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.keshvar" :label="$t('pages.person.country')" dense
                      prepend-inner-icon="mdi-earth" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.ostan" :label="$t('pages.person.province')" dense
                      prepend-inner-icon="mdi-map-marker" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.shahr" :label="$t('pages.person.city')" dense
                      prepend-inner-icon="mdi-city" hide-details />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="person.postalcode" :label="$t('pages.person.postal_code')" dense
                      prepend-inner-icon="mdi-mailbox" hide-details />
                  </v-col>
                  <v-col cols="12">
                    <v-textarea v-model="person.address" :label="$t('pages.person.address')" dense
                      prepend-inner-icon="mdi-home" rows="2" hide-details />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tabs-window-item>

          <!-- حساب‌های بانکی -->
          <v-tabs-window-item value="4">
            <v-card flat>
              <v-card-text class="pa-2">
                <v-row justify="end" class="mb-2">
                  <v-col cols="auto">
                    <v-btn color="primary" @click="addNewCard" prepend-icon="mdi-plus" size="small">
                      {{ $t('dialog.add') }}
                    </v-btn>
                  </v-col>
                </v-row>
                <v-row v-for="(account, index) in person.accounts" :key="index" dense class="mb-2">
                  <v-col cols="12">
                    <v-card outlined elevation="2">
                      <v-toolbar flat color="grey-lighten-4" dense>
                        <v-icon left>mdi-bank</v-icon>
                        <v-toolbar-title class="text-subtitle-2">
                          {{ $t('pages.person.bank_account') }}
                        </v-toolbar-title>
                        <v-spacer />
                        <v-btn icon color="red" @click="removeCard(index)" small>
                          <v-icon>mdi-trash-can</v-icon>
                        </v-btn>
                      </v-toolbar>
                      <v-card-text class="pa-2">
                        <v-row dense>
                          <v-col cols="12" md="6">
                            <v-text-field v-model="account.bank" :label="$t('pages.person.bank_name')" dense
                              prepend-inner-icon="mdi-bank" :rules="[v => !!v || $t('validator.required')]"
                              hide-details />
                          </v-col>
                          <v-col cols="12" md="6">
                            <v-text-field v-model="account.accountNum" :label="$t('pages.person.account_number')" dense
                              prepend-inner-icon="mdi-numeric" hide-details />
                          </v-col>
                          <v-col cols="12" md="6">
                            <v-text-field v-model="account.cardNum" :label="$t('pages.person.card_number')" dense
                              prepend-inner-icon="mdi-credit-card" hide-details />
                          </v-col>
                          <v-col cols="12" md="6">
                            <v-text-field v-model="account.shabaNum" :label="$t('pages.person.shaba_number')" dense
                              prepend-inner-icon="mdi-barcode" hide-details />
                          </v-col>
                        </v-row>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-tabs-window-item>
        </v-tabs-window>
      </v-col>
    </v-row>
  </v-container>

  <v-overlay :model-value="loading" contained class="align-center justify-center">
    <v-progress-circular indeterminate size="64" />
  </v-overlay>
</template>

<script>
import Swal from "sweetalert2";
import axios from "axios";
import { ref } from "vue";

export default {
  name: "insert",
  data() {
    return {
      tabs: '0',
      prelabels: [],
      loading: false,
      selectedTypes: [], // برای v-select نوع مشتری
      person: {
        nikename: '',
        name: '',
        des: '',
        tel: '',
        mobile: '',
        mobile2: '',
        address: '',
        company: '',
        shenasemeli: '',
        codeeghtesadi: '',
        sabt: '',
        keshvar: '',
        ostan: '',
        shahr: '',
        postalcode: '',
        email: '',
        website: '',
        fax: '',
        code: 0,
        types: [],
        accounts: [],
        prelabel: ref(null),
        speedAccess: false
      }
    };
  },
  mounted() {
    this.loadData(this.$route.params.id);
  },
  beforeRouteUpdate(to) {
    this.loadData(to.params.id);
  },
  watch: {
    selectedTypes(newVal) {
      // به‌روزرسانی person.types بر اساس انتخاب‌ها
      this.person.types.forEach(type => {
        type.checked = newVal.includes(type.label);
      });
    },
    'person.types': {
      handler(newVal) {
        // همگام‌سازی selectedTypes با person.types
        this.selectedTypes = newVal.filter(type => type.checked).map(type => type.label);
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    addNewCard() {
      this.person.accounts.push({
        bank: '',
        accountNum: '',
        cardNum: '',
        shabaNum: ''
      });
    },
    removeCard(index) {
      this.person.accounts.splice(index, 1);
    },
    loadData(id = '') {
      this.loading = true;

      axios.post('/api/person/prelabels/list').then((response) => {
        this.prelabels = response.data;
      }).catch((error) => {
        Swal.fire({
          text: this.$t('pages.person.load_error') + error.message,
          icon: 'error',
          confirmButtonText: this.$t('dialog.confirm')
        });
      });

      if (id) {
        axios.post('/api/person/info/' + id).then((response) => {
          this.person = response.data;
          this.loading = false;
        }).catch((error) => {
          this.loading = false;
          Swal.fire({
            text: this.$t('pages.person.load_error') + error.message,
            icon: 'error',
            confirmButtonText: this.$t('dialog.confirm')
          });
        });
      } else {
        axios.post('/api/person/types/get').then((response) => {
          this.person.types = response.data;
          this.loading = false;
        }).catch((error) => {
          this.loading = false;
          Swal.fire({
            text: this.$t('pages.person.load_error') + error.message,
            icon: 'error',
            confirmButtonText: this.$t('dialog.confirm')
          });
        });
        this.person.code = 0;
      }
    },
    async save() {
      let canSubmit = true;

      if (this.person.mobile && !/^(\+98|0)?9\d{9}$/.test(this.person.mobile)) {
        canSubmit = false;
        Swal.fire({
          text: this.$t('pages.person.invalid_mobile'),
          icon: 'error',
          confirmButtonText: this.$t('dialog.confirm')
        });
      }

      if (!this.person.nikename) {
        canSubmit = false;
        Swal.fire({
          text: this.$t('pages.person.nickname_required'),
          icon: 'error',
          confirmButtonText: this.$t('dialog.confirm')
        });
      }

      if (this.person.accounts.some(account => !account.bank)) {
        canSubmit = false;
        Swal.fire({
          text: this.$t('pages.person.bank_required'),
          icon: 'error',
          confirmButtonText: this.$t('dialog.confirm')
        });
      }

      if (canSubmit) {
        this.loading = true;
        try {
          const response = await axios.post('/api/person/mod/' + this.person.code, this.person);
          this.loading = false;
          if (response.data.result === 2) {
            Swal.fire({
              text: this.$t('pages.person.already_exists'),
              icon: 'error',
              confirmButtonText: this.$t('dialog.confirm')
            });
          } else {
            Swal.fire({
              text: this.$t('pages.person.saved'),
              icon: 'success',
              confirmButtonText: this.$t('dialog.confirm')
            }).then(() => {
              this.$router.push('/acc/persons/list');
            });
          }
        } catch (error) {
          this.loading = false;
          Swal.fire({
            text: this.$t('pages.person.save_error') + error.message,
            icon: 'error',
            confirmButtonText: this.$t('dialog.confirm')
          });
        }
      }
    }
  }
};
</script>

<style scoped>
.bg-light {
  background-color: #f5f5f5;
}
</style>