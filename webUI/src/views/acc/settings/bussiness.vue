<template>
  <v-toolbar color="toolbar" :title="$t('dialog.bid_info')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text"
            icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-btn :loading="loading" @click="submit()" icon="" color="green">
      <v-tooltip activator="parent" :text="$t('dialog.save')" location="bottom" />
      <v-icon icon="mdi-content-save"></v-icon>
    </v-btn>
    <template v-slot:extension>
         <v-tabs color="primary" class="bg-light" grow v-model="tabs">
        <v-tab value="0">
          {{ $t('dialog.basic_info') }}
        </v-tab>
        <v-tab value="1">
          {{ $t('dialog.year_label') }}
        </v-tab>
        <v-tab value="2">
          {{ $t('dialog.global_settings') }}
        </v-tab>
      </v-tabs>
    </template>
  </v-toolbar>
  <v-row class="pa-1">
    <v-col>
      <v-tabs-window v-model="tabs">
        <v-tabs-window-item value="0">
          <v-card>
            <v-card-text>
              <h3 class="text-primary">اطلاعات کسب و کار</h3>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.name"
                    label="نام کسب و کار"
                    variant="outlined"
                    required
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.legal_name"
                    label="نام قانونی کسب و کار"
                    variant="outlined"
                    required
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.field"
                    label="زمینه فعالیت"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    v-model="content.type"
                    :items="['شرکت', 'مغازه', 'فروشگاه', 'اتحادیه', 'باشگاه', 'موسسه', 'شخصی']"
                    label="نوع فعالیت"
                    variant="outlined"
                    density="compact"
                  ></v-select>
                </v-col>
              </v-row>

              <h3 class="text-primary mt-4">اطلاعات اقتصادی</h3>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.shenasemeli"
                    label="شناسه ملی"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.codeeqtesadi"
                    label="کد اقتصادی"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.shomaresabt"
                    label="شماره ثبت"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
              </v-row>

              <h3 class="text-primary mt-4">اطلاعات تماس</h3>
              <v-row>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="content.country"
                    label="کشور"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="content.ostan"
                    label="استان"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="content.shahrestan"
                    label="شهر"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="content.postalcode"
                    label="کد پستی"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="content.tel"
                    label="تلفن"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="content.mobile"
                    label="موبایل"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="content.address"
                    label="آدرس"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.website"
                    label="وب‌سایت"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.email"
                    label="پست الکترونیکی"
                    variant="outlined"
                    density="compact"
                  ></v-text-field>
                </v-col>
              </v-row>

              <h3 class="text-primary mt-4">اطلاعات مالی</h3>
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="content.maliyatafzode"
                    label="مالیات بر ارزش افزوده"
                    type="number"
                    variant="outlined"
                    required
                    density="compact"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="1">
          <v-card>
            <v-card-text>
              <h3 class="text-primary">سال مالی</h3>
              <v-row>
                <v-col cols="12" md="6">
                  <Hdatepicker
                    v-model="content.year.startShamsi"
                    label="شروع سال مالی"
                    :rules="[v => !!v || 'این فیلد الزامی است']"
                    :ignore-year-range="true"
                  />
                </v-col>
                <v-col cols="12" md="6">
                  <Hdatepicker
                    v-model="content.year.endShamsi"
                    label="اتمام سال مالی"
                    :rules="[v => !!v || 'این فیلد الزامی است']"
                    :ignore-year-range="true"
                    :min="content.year.startShamsi"
                  />
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="content.year.label"
                    label="عنوان سال مالی"
                    variant="outlined"
                    required
                    density="compact"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="2">
          <v-card>
            <v-card-text>
              <h3 class="text-primary">نمایش پیوند یکتا</h3>
              <v-row>
                <v-col cols="12" md="8">
                  <v-switch
                    v-model="content.shortlinks"
                    label="فعال‌سازی پیوند‌های یکتا"
                    color="primary"
                    hide-details
                  ></v-switch>
                  <div class="text-caption text-medium-emphasis mt-1">
                    این قابلیت برای تولید پیوند‌های یکتا برای ارسال به مشتری جهت مشاهده فاکتورها است.
                  </div>
                </v-col>
              </v-row>

              <h3 class="text-primary mt-4">دریافت مبلغ فاکتور از طریق کیف پول</h3>
              <v-row>
                <v-col cols="12">
                  <v-switch
                    v-model="content.walletEnabled"
                    label="فعال‌سازی دریافت آنلاین از طریق کیف پول"
                    color="primary"
                    @change="checkBanksExist"
                    hide-details
                  ></v-switch>
                  <div class="text-caption text-medium-emphasis mt-1">
                    با فعال سازی این قابلیت قادر خواهید بود مبالغ فاکتورهای ثبت شده را به صورت آنلاین از مشتریان خود دریافت کنید.
                  </div>
                  
                  <v-row v-if="content.walletEnabled" class="mt-4">
                    <v-col cols="12" md="6">
                      <v-select
                        v-model="content.walletMatchBank"
                        :items="listBanks"
                        item-title="name"
                        item-value="id"
                        label="حساب بانکی متصل به کیف پول"
                        variant="outlined"
                        density="compact"
                      ></v-select>
                    </v-col>
                    <v-col cols="12">
                      <div class="text-caption text-medium-emphasis">
                        برای تسویه اتوماتیک به حساب انتخاب شده حتما باید تمام موارد از جمله شماره شبا و شماره کارت و ... به درستی تکمیل شده باشد در غیر این صورت تراکنش با خطا مواجه خواهد شد.
                      </div>
                    </v-col>
                  </v-row>
                </v-col>
              </v-row>

              <h3 class="text-primary mt-4">کالا و خدمات</h3>
              <v-row>
                <v-col cols="12" md="8">
                  <v-switch
                    v-model="content.updateBuyPrice"
                    label="به روز رسانی قیمت خرید هنگام صدور فاکتور"
                    color="primary"
                    hide-details
                  ></v-switch>
                  <div class="text-caption text-medium-emphasis mt-1">
                    با صدور فاکتور خرید یا برگشت از خرید قیمت خرید کالا و خدمات به روزرسانی خواهد شد.
                  </div>
                </v-col>
                <v-col cols="12" md="8">
                  <v-switch
                    v-model="content.updateSellPrice"
                    label="به روز رسانی قیمت فروش هنگام صدور فاکتور"
                    color="primary"
                    hide-details
                  ></v-switch>
                  <div class="text-caption text-medium-emphasis mt-1">
                    با صدور فاکتور فروش یا برگشت از فروش قیمت خرید کالا و خدمات به روزرسانی خواهد شد.
                  </div>
                </v-col>
                <v-col cols="12" md="8">
                  <v-select
                    v-model="content.profitCalcType"
                    :items="[
                      { title: 'بر اساس اختلاف قیمت خرید و فروش', value: 'simple' },
                      { title: 'بر اساس آخرین قیمت ورود به انبار', value: 'lis' },
                      { title: 'بر اساس میانگین قیمت ورود به انبار', value: 'avgis' }
                    ]"
                    item-title="title"
                    item-value="value"
                    label="نحوه محاسبه سود فاکتور"
                    variant="outlined"
                    density="compact"
                  ></v-select>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
      </v-tabs-window>
    </v-col>
  </v-row>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Hdatepicker from "@/components/forms/Hdatepicker.vue";

export default {
  name: "bussiness",
  components: {
    Hdatepicker
  },
  data: () => {
    return {
      tabs: '',
      loading : false,
      moneys: [],
      content: {
        name: '',
        legal_name: '',
        field: '',
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
        shortlinks: false,
        walletEnabled: false,
        walletMatchBank: null,
        year: {
          startShamsi: '',
          endShamsi: '',
          label: ''
        },
        updateSellPrice: false,
        updateBuyPrice: false,
        profitCalcType: 'lis'
      },
      listBanks: [],
    }
  },
  watch: {
    'content.year.endShamsi': {
      handler(newVal) {
        if (newVal) {
          this.content.year.label = `سال مالی منتهی به ${newVal}`;
        }
      }
    }
  },
  methods: {
    checkBanksExist() {
      if (this.listBanks.length === 0) {
        Swal.fire({
          text: 'هنوز هیچ حساب بانکی تعریف نشده است.',
          icon: 'error',
          confirmButtonText: 'تعریف حساب جدید',
          cancelButtonText: 'بازگشت',
          showCancelButton: true
        }).then((res) => {
          if (res.isConfirmed) {
            this.$router.push('/acc/banks/mod/');
          }
          else {
            this.content.walletEnabled = false;
          }
        });
      }
    },
    submit() {
      if (this.content.year.label === '' || this.content.name === '' || this.content.legal_name === '' || this.content.maliyatafzode === '') {
        Swal.fire({
          text: 'تکمیل موارد ستاره دار الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        return;
      }
      if (this.content.walletEnabled && (this.content.walletMatchBank === undefined || this.content.walletMatchBank === null)) {
        Swal.fire({
          text: 'حساب بانکی متصل به کیف پول انتخاب نشده است',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
        return;
      }
      
      //submit data
      this.loading = true;
      let data = {
        'bid': localStorage.getItem('activeBid'),
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
        'shortlinks': this.content.shortlinks,
        'walletEnabled': this.content.walletEnabled,
        'walletMatchBank': this.content.walletMatchBank,
        'year': this.content.year,
        'commodityUpdateBuyPriceAuto': this.content.updateBuyPrice,
        'commodityUpdateSellPriceAuto': this.content.updateSellPrice,
        'profitCalcType': this.content.profitCalcType
      };

      axios.post('/api/business/insert', data)
        .then((response) => {
          this.loading = false;
          if (response.data.result == 1) {
            Swal.fire({
              text: 'با موفقیت ثبت شد.',
              icon: 'success',
              confirmButtonText: 'قبول',
            })
          }
          else if (response.data.result === 0) {
            Swal.fire({
              text: 'تکمیل موارد ستاره دار الزامی است.',
              icon: 'error',
              confirmButtonText: 'قبول'
            });
          }
        })
        .catch((error) => {
          this.loading = false;
          Swal.fire({
            text: 'خطا در ثبت اطلاعات',
            icon: 'error',
            confirmButtonText: 'قبول'
          });
        });
    }
  },
  async beforeMount() {
    this.loading = true
    //get all money types
    axios.post("/api/money/get/all").then((response) => {
      this.moneys = response.data;
      this.content.arzmain = this.moneys[0];
    })

    //get list of banks
    await axios.post('/api/bank/list').then((response) => {
      this.listBanks = response.data;
    });

    //get business info
    let data = await axios.post('/api/business/get/info/' + localStorage.getItem('activeBid'))
      .then((response) => {
        this.content = response.data;
        // اگر walletMatchBank یک آبجکت است، فقط id آن را نگه می‌داریم
        if (this.content.walletMatchBank && typeof this.content.walletMatchBank === 'object') {
          this.content.walletMatchBank = this.content.walletMatchBank.id;
        }
        this.loading = false;
      });
  }
}
</script>

<style scoped>
.required label:before {
  content: "*";
  color: red;
}
</style>