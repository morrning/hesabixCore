<script lang="ts">
import axios from 'axios';
import { defineComponent } from 'vue'
import Loading from "vue-loading-overlay";
import 'vue-loading-overlay/dist/css/index.css';

export default defineComponent({
  name: "quickView",
  props: {
    code: {
      type: String
    },
    btn: {
      default: true,
      type: Boolean
    }
  },
  components: {
    Loading
  },
  data: () => {
    return {
      isLoading: true,
      dialog: false,
      selectedPerson: {
        accounts: []
      }
    }
  },
  methods: {
    loadData() {
      this.isLoading = true;
      if (this.$props.code != undefined) {
        axios.post('/api/person/info/' + this.$props.code).then((response) => {
          this.isLoading = false;
          this.selectedPerson = response.data;
        });
      }

    }
  },
  mounted() {
    this.loadData();
  }
})
</script>

<template>
  <v-btn v-if="$props.btn == true" :disabled="this.$props.code == undefined" @click="loadData(); dialog = true"
    icon="mdi-eye" class="text-primary" variant="plain" density="compact" size="small" :title="$t('dialog.user_info')">
  </v-btn>

  <v-dialog v-model="dialog" transition="dialog-bottom-transition" fullscreen>
    <v-card class="bg-white">
      <v-toolbar>
        <v-btn icon="mdi-close" @click="dialog = false"></v-btn>

        <v-toolbar-title>{{ $t('dialog.user_info') }}</v-toolbar-title>

        <v-spacer></v-spacer>

        <v-toolbar-items>
        </v-toolbar-items>
      </v-toolbar>
      <v-row class="pa-3">
        <v-col>
          <Loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false" />
          <div class="row">
            <div class="col-sm-12 col-md-6 mb-2">
              <div class="fw-bold mb-2">کد حسابداری: <small class="text-primary">{{ selectedPerson.code }}</small>
              </div>
              <div class="fw-bold mb-2">نام مستعار: <small class="text-primary">{{ selectedPerson.nikename
                  }}</small>
              </div>
              <div class="fw-bold mb-2">نام و نام خانوادگی: <small class="text-primary">{{ selectedPerson.name
                  }}</small></div>
              <div class="fw-bold mb-2">تلفن: <small class="text-primary">{{ selectedPerson.tel }}</small></div>
              <div class="fw-bold mb-2">موبایل: <small class="text-primary">{{ selectedPerson.mobile }}</small></div>
              <div class="fw-bold mb-2">موبایل دوم: <small class="text-primary">{{ selectedPerson.mobile2 }}</small>
              </div>
              <div class="fw-bold mb-2">آدرس: <small class="text-primary">{{ selectedPerson.keshvar + ' ' +
                selectedPerson.ostan + ' ' + selectedPerson.shahr + ' ' + selectedPerson.address }}</small></div>
              <div class="fw-bold mb-2">توضیحات: <small class="text-primary">{{ selectedPerson.des }}</small></div>

            </div>
            <div class="col-sm-12 col-md-6 mb-2">
              <div class="card-body">
                <div class="fw-bold mb-2">
                  وضعیت حسابداری:
                  <b v-if="selectedPerson.balance > 0" class="text-success">بستانکار</b>
                  <b v-if="selectedPerson.balance < 0" class="text-danger">بدهکار</b>
                  <b v-if="selectedPerson.balance == 0" class="text-dark">تسویه شده</b>

                </div>
                <div class="fw-bold mb-2">بستانکار: <small class="text-primary">{{
                  this.$filters.formatNumber(selectedPerson.bs)
                    }}</small></div>
                <div class="fw-bold mb-2">بدهکار: <small class="text-primary">{{
                  this.$filters.formatNumber(selectedPerson.bd)
                    }}</small></div>
                <div class="fw-bold mb-2">تراز حسابداری: <small class="text-primary">{{
                  this.$filters.formatNumber(selectedPerson.balance) }}</small></div>
              </div>

            </div>
          </div>
          <div v-if="this.selectedPerson.accounts.length != 0" class="row">
            <div class="col-12">
              <h4 class="text-primary">حساب‌های بانکی</h4>
              <div v-for="item in selectedPerson.accounts" class="block block-rounded bg-light mb-1">
                <div class="block-header">
                  <h3 class="block-title"> {{ item.bank }} <small> {{ item.accountNum }}</small></h3>
                </div>
                <div class="block-content pt-0">
                  <ul class="list-group mb-1">
                    <li class="list-group-item">
                      <span class="text-muted">شماره کارت:</span>
                      {{ item.cardNum }}
                    </li>
                    <li class="list-group-item">
                      <span class="text-muted">شماره شبا:</span>
                      {{ item.shabaNum }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </v-col>
      </v-row>
    </v-card>
  </v-dialog>
</template>

<style scoped></style>