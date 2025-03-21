<template>
  <v-toolbar color="toolbar" :title="$t('drawer.open_balance')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
  </v-toolbar>
  <v-container>
    <v-row class="pa-1">
      <v-col v-if="sums.degSum != sums.shareSum" cols="12" sm="12" md="12">
        <v-alert :title="$t('dialog.error')" :text="$t('dialog.openbalance_notvalid')" type="warning"></v-alert>
      </v-col>
      <v-col cols="12" sm="12" md="6">
        <v-card :title="$t('dialog.deg')" :subtitle="$t('dialog.deg_info')" color="success">
          <v-card-text class="px-0 pb-0">
            <v-list class="py-0">
              <v-list-item @click="sheet.banks = !sheet.banks" value="banks" :title="$t('drawer.banks')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.banks, true) }}
                </template>
              </v-list-item>
              <v-list-item @click="sheet.cashdesks = !sheet.cashdesks" value="cashdesks"
                :title="$t('drawer.cashdesks')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.cashdesks, true) }}
                </template>
              </v-list-item>
              <v-list-item @click="sheet.salarys = !sheet.salarys" value="salarys" :title="$t('drawer.salarys')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.salarys, true) }}
                </template>
              </v-list-item>
              <v-list-item :title="$t('dialog.sum')" class="bg-light">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.degSum, true) }}
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" sm="12" md="6">
        <v-card :title="$t('dialog.shareloders_equity')" :subtitle="$t('dialog.shareloders_equity_info')" color="info">
          <v-card-text class="px-0 pb-0">
            <v-list class="py-0">
              <v-list-item @click="sheet.shareholders = !sheet.shareholders" value="shareholders"
                :title="$t('drawer.shareholders')">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.shareholders, true) }}
                </template>
              </v-list-item>
              <v-list-item :title="$t('dialog.sum')" class="bg-light">
                <template v-slot:append>
                  {{ $filters.formatNumber(sums.shareSum, true) }}
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

  <v-bottom-sheet inset v-model="sheet.banks">
    <v-toolbar color="toolbar" :title="$t('drawer.banks')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.banks = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveBanks()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.banks" key="item.info.id">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.name }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
  <v-bottom-sheet inset v-model="sheet.cashdesks">
    <v-toolbar color="toolbar" :title="$t('drawer.cashdesks')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.cashdesks = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveCashdesks()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.cashdesks" key="item.info.id">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.name }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
  <v-bottom-sheet inset v-model="sheet.salarys">
    <v-toolbar color="toolbar" :title="$t('drawer.salarys')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.salarys = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveSalarys()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.salarys" key="item.info.id">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.name }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
  <v-bottom-sheet inset v-model="sheet.shareholders">
    <v-toolbar color="toolbar" :title="$t('drawer.shareholders')">
      <template v-slot:prepend>
        <v-tooltip :text="$t('dialog.close')" location="bottom">
          <template v-slot:activator="{ props }">
            <v-btn v-bind="props" @click="sheet.shareholders = false" class="d-none d-sm-flex" variant="text"
              icon="mdi-close" />
          </template>
        </v-tooltip>
      </template>
      <v-spacer></v-spacer>
      <v-btn :loading="loading" @click="saveShareholders()" icon="" color="green">
        <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
        <v-icon icon="mdi-content-save"></v-icon>
      </v-btn>
    </v-toolbar>
    <v-card>
      <v-card-text>
        <v-row>
          <v-col v-for="item in data.shareholders" key="item.info.id">
            <div class="form-floating mb-3">
              <money3 v-bind="currencyConfig" min=0 class="form-control" v-model="item.openbalance" />
              <label for="floatingInput">{{ item.info.nikename }} ({{ $filters.getActiveMoney().symbol }}) </label>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
</template>

<script>
import axios from "axios";
import Loading from 'vue-loading-overlay';

export default {
  name: "table",
  components: {
    Loading
  },
  data: () => {
    return {
      loading: false,
      currencyConfig: {
        masked: false,
        prefix: '',
        suffix: '',
        thousands: ',',
        decimal: '.',
        precision: 0,
        disableNegative: true,
        disabled: false,
        min: 0,
        max: null,
        allowBlank: false,
        minimumNumberOfCharacters: 1,
        shouldRound: false,
        focusOnRight: true,
      },
      sheet: {
        banks: false,
        cashdesks: false,
        salarys: false,
        shareholders: false,
      },
      data: {
        banks: [],
        cashdesks: [],
        salarys: [],
        shareholders: [],
      },
      sums: {
        banks: 0,
        cashdesks: 0,
        salarys: 0,
        shareholders: 0,
        degSum: 0,
        shareSum: 0,
      }
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      this.sums.banks = 0;
      this.sums.cashdesks = 0;
      this.sums.salarys = 0;
      this.sums.shareholders = 0;

      axios.post('/api/openbalance/get').then((Response) => {
        this.data = Response.data.data;
        this.data.banks.forEach((item) => {
          if (item.openbalance != undefined) {
            this.sums.banks += parseFloat(item.openbalance);
          }
          else {
            item.openbalance = 0;
          }
        });
        this.data.cashdesks.forEach((item) => {
          if (item.openbalance != undefined) {
            this.sums.cashdesks += parseFloat(item.openbalance);
          }
          else {
            item.openbalance = 0;
          }
        });
        this.data.salarys.forEach((item) => {
          if (item.openbalance != undefined) {
            this.sums.salarys += parseFloat(item.openbalance);
          }
          else {
            item.openbalance = 0;
          }
        });
        this.data.shareholders.forEach((item) => {
          if (item.openbalance != undefined) {
            this.sums.shareholders += parseFloat(item.openbalance);
          }
          else {
            item.openbalance = 0;
          }
        });
        this.sums.degSum = parseFloat(this.sums.banks) + parseFloat(this.sums.cashdesks) + parseFloat(this.sums.salarys);
        this.sums.shareSum = parseFloat(this.sums.shareholders);

      });

    },
    saveBanks() {
      this.loading = true;
      axios.post('/api/openbalance/save/banks', this.data.banks).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.banks = false;
      })
    },
    saveCashdesks() {
      this.loading = true;
      axios.post('/api/openbalance/save/cashdesks', this.data.cashdesks).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.cashdesks = false;
      })
    },
    saveSalarys() {
      this.loading = true;
      axios.post('/api/openbalance/save/salarys', this.data.salarys).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.salarys = false;
      })
    },
    saveShareholders() {
      this.loading = true;
      axios.post('/api/openbalance/save/shareholders', this.data.shareholders).then((Response) => {
        this.loading = false;
        this.loadData();
        this.sheet.shareholders = false;
      })
    },
  }

}
</script>

<style scoped></style>