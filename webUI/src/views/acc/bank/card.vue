<template>
  <v-toolbar color="toolbar" :title="$t('drawer.bankaccounts_transactions')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="" color="red">
          <v-tooltip activator="parent" :text="$t('dialog.export_pdf')" location="bottom" />
          <v-icon icon="mdi-file-pdf-box"></v-icon>
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_pdf') }}</v-list-subheader>
        <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="print(false)">
          <template v-slot:prepend>
            <v-icon color="green-darken-4" icon="mdi-check"></v-icon>
          </template>
        </v-list-item>
        <v-list-item class="text-dark" :title="$t('dialog.selected_all')" @click="print(true)">
          <template v-slot:prepend>
            <v-icon color="indigo-darken-4" icon="mdi-expand-all"></v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-menu>
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" icon="" color="green">
          <v-tooltip activator="parent" :text="$t('dialog.export_excel')" location="bottom" />
          <v-icon icon="mdi-file-excel-box"></v-icon>
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader color="primary">{{ $t('dialog.export_excel') }}</v-list-subheader>
        <v-list-item class="text-dark" :title="$t('dialog.selected')" @click="excellOutput(false)">
          <template v-slot:prepend>
            <v-icon color="green-darken-4" icon="mdi-check"></v-icon>
          </template>
        </v-list-item>
        <v-list-item class="text-dark" :title="$t('dialog.selected_all')" @click="excellOutput(true)">
          <template v-slot:prepend>
            <v-icon color="indigo-darken-4" icon="mdi-expand-all"></v-icon>
          </template>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-toolbar>
  <v-row class="pa-1">
    <v-col cols="12" sm="12" md="12">
      <v-card :loading="loading">
        <v-card-text>
          <v-row class="">
            <v-col cols="12" sm="12" md="12">
              <small class="mb-2">بانک</small>
              <v-cob dir="rtl" :options="objectItems" label="name" v-model="selectedObjectItem"
                @option:selected="updateRoute(selectedObjectItem.code)">
                <template #no-options="{ search, searching, loading }">
                  نتیجه‌ای یافت نشد!
                </template>
              </v-cob>
            </v-col>
            <v-col cols="12" sm="12" md="4">
              <div class="fw-bold mb-2">کد حسابداری: <small class="text-primary">{{ selectedObjectItem.code }}</small>
              </div>
              <div class="fw-bold mb-2">نام : <small class="text-primary">{{ selectedObjectItem.name }}</small></div>
              <div class="fw-bold mb-2">شماره کارت: <small class="text-primary">{{ selectedObjectItem.cardNum
                  }}</small>
              </div>
            </v-col>
            <v-col cols="12" sm="12" md="4">
              <div class="fw-bold mb-2">شبا: <small class="text-primary">{{ selectedObjectItem.shaba }}</small></div>
              <div class="fw-bold mb-2">صاحب حساب: <small class="text-primary">{{ selectedObjectItem.owner }}</small>
              </div>
              <div class="fw-bold mb-2">تلفن اینترنت بانک: <small class="text-primary">{{
                selectedObjectItem.mobileInternetBank }}</small></div>
            </v-col>
            <v-col cols="12" sm="12" md="4">
              <div class="fw-bold mb-2">شماره دستگاه پوز: <small class="text-primary">{{ selectedObjectItem.posNum
                  }}</small></div>
              <div class="fw-bold mb-2">شعبه: <small class="text-primary">{{ selectedObjectItem.shobe }}</small></div>
              <div class="fw-bold mb-2">توضیحات: <small class="text-primary">{{ selectedObjectItem.des }}</small>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-col>
    <v-col cols="12" sm="12" md="12">
      <v-text-field :loading="loading" color="green" class="mb-0 pt-0 rounded-0" hide-details="auto" density="compact"
        :placeholder="$t('dialog.search_txt')" v-model="searchValue" type="text" clearable>
        <template v-slot:prepend-inner>
          <v-tooltip location="bottom" :text="$t('dialog.search')">
            <template v-slot:activator="{ props }">
              <v-icon v-bind="props" color="danger" icon="mdi-magnify"></v-icon>
            </template>
          </v-tooltip>
        </template>
      </v-text-field>
      <EasyDataTable table-class-name="customize-table" show-index alternating v-model:items-selected="itemsSelected"
        :search-value="searchValue" :headers="headers" :items="items" theme-color="#1d90ff"
        header-text-direction="center" body-text-direction="center" rowsPerPageMessage="تعداد سطر"
        emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از" :loading="loading">
        <template #item-operation="{ code }">
          <router-link class="text-success" :to="'/acc/accounting/view/' + code">
            <i class="fa fa-eye px-1"></i>
          </router-link>
        </template>
      </EasyDataTable>
    </v-col>
  </v-row>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

export default {
  name: "card",
  data: () => {
    return {
      searchValue: '',
      itemsSelected: [],
      items: [],
      selectedObjectItem: {
        id: '',
        code: 0,
        name: '',
      },
      items: [],
      objectItems: [],
      loading: true,
      headers: [
        { text: "عملیات", value: "operation" },
        { text: "تاریخ", value: "date", 'sortable': true },
        { text: "شرح", value: "des" },
        { text: "تفضیل", value: "ref", 'sortable': true },
        { text: "واریز", value: "bd", 'sortable': true },
        { text: "برداشت", value: "bs", 'sortable': true },
      ]
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    updateRoute(id) {
      this.$router.push(id);
      this.loadData();
    },
    loadData() {
      this.loading = true;
      axios.post('/api/bank/list').then((response) => {
        this.loading = false;
        this.objectItems = response.data;
        if (this.$route.params.id != '') {
          this.loadObject(this.$route.params.id);
          this.objectItems.forEach((item) => {
            if (item.code == this.$route.params.id) {
              this.selectedObjectItem = item;
            }
          });
        } else {
          this.selectedObjectItem = response.data[0];
          this.loadObject(this.selectedObjectItem.code);
        }
      });
    },
    loadObject(id) {
      this.loading = true;
      axios.post('/api/accounting/rows/search',
        {
          type: 'bank',
          id: id
        }
      ).then((response) => {
        this.items = response.data;
        this.items.forEach((item) => {
          item.bs = this.$filters.formatNumber(item.bs)
          item.bd = this.$filters.formatNumber(item.bd)
        })
        this.loading = false;
      });
    },
    excellOutput(AllItems = true) {
      if (AllItems) {
        this.loading = true;
        axios({
          method: 'post',
          url: '/api/bank/card/list/excel',
          data: { 'code': this.selectedObjectItem.code },
          responseType: 'arraybuffer',
        }).then((response) => {
          this.loading = false;
          var FILE = window.URL.createObjectURL(new Blob([response.data]));
          var fileURL = window.URL.createObjectURL(new Blob([response.data]));
          var fileLink = document.createElement('a');

          fileLink.href = fileURL;
          fileLink.setAttribute('download', 'bank-card-view.xlsx');
          document.body.appendChild(fileLink);
          fileLink.click();
        })
      }
      else {
        if (this.itemsSelected.length === 0) {
          Swal.fire({
            text: 'هیچ آیتمی انتخاب نشده است.',
            icon: 'info',
            confirmButtonText: 'قبول'
          });
        }
        else {
          this.loading = true;
          axios({
            method: 'post',
            url: '/api/bank/card/list/excel',
            responseType: 'arraybuffer',
            data: {
              'code': this.selectedObjectItem.code,
              'items': this.itemsSelected
            }
          }).then((response) => {
            this.loading = false;
            var FILE = window.URL.createObjectURL(new Blob([response.data]));
            var fileURL = window.URL.createObjectURL(new Blob([response.data]));
            var fileLink = document.createElement('a');

            fileLink.href = fileURL;
            fileLink.setAttribute('download', 'persons-list.xlsx');
            document.body.appendChild(fileLink);
            fileLink.click();
          })
        }
      }
    },
    print(AllItems = true) {
      if (this.selectedObjectItem == null) {
        Swal.fire({
          text: 'هیچ آیتمی انتخاب نشده است.',
          icon: 'info',
          confirmButtonText: 'قبول'
        });
      }
      else {
        if (AllItems) {
          this.loading = true;
          axios.post('/api/bank/card/list/print', { 'code': this.selectedObjectItem.code }).then((response) => {
            this.printID = response.data.id;
            this.loading = false;
            window.open(this.$API_URL + '/front/print/' + this.printID, '_blank', 'noreferrer');
          })
        }
        else {
          if (this.itemsSelected.length === 0) {
            Swal.fire({
              text: 'هیچ آیتمی انتخاب نشده است.',
              icon: 'info',
              confirmButtonText: 'قبول'
            });
          }
          else {
            this.loading = true;
            axios.post('/api/bank/card/list/print', {
              'code': this.selectedObjectItem.code,
              'items': this.itemsSelected
            }).then((response) => {
              this.loading = false;
              this.printID = response.data.id;
              window.open(this.$API_URL + '/front/print/' + this.printID, '_blank', 'noreferrer');
            })
          }
        }
      }

    },
  }
}
</script>

<style scoped></style>