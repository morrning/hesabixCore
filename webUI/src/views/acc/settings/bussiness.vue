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
        <v-tab value="3">
          {{ $t('dialog.gate_pay') }}
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
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating required">
                    <input class="form-control" type="text" v-model="content.name">
                    <label class="form-label">نام کسب و کار</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating required">
                    <input class="form-control" type="text" v-model="content.legal_name">
                    <label class="form-label">نام قانونی کسب و کار</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <input class="form-control" type="text" v-model="content.field">
                    <label>زمینه فعالیت</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <select v-model="content.type" class="form-select">
                      <option value="شرکت">شرکت</option>
                      <option value="مغازه">مغازه</option>
                      <option value="فروشگاه">فروشگاه</option>
                      <option value="اتحادیه">اتحادیه</option>
                      <option value="باشگاه">باشگاه</option>
                      <option value="موسسه">موسسه</option>
                      <option value="شخصی">شخصی</option>
                    </select>
                    <label>نوع فعالیت</label>
                  </div>
                </div>
              </div>
              <h3 class="text-primary">اطلاعات اقتصادی</h3>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <input v-model="content.shenasemeli" type="text" class="form-control">
                    <label class="form-label">شناسه ملی</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <input v-model="content.codeeqtesadi" type="text" class="form-control">
                    <label class="form-label">کد اقتصادی</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <input v-model="content.shomaresabt" type="text" class="form-control">
                    <label class="form-label">شماره ثبت</label>
                  </div>
                </div>
              </div>
              <h3 class="text-primary">اطلاعات تماس</h3>
              <div class="row">
                <div class="col-sm-12 col-md-4 mb-2">
                  <div class="form-floating">
                    <input v-model="content.country" type="text" class="form-control">
                    <label class="form-label">کشور</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-2">
                  <div class="form-floating">
                    <input v-model="content.ostan" type="text" id="business_new_ostan" name="business_new[ostan]"
                      maxlength="50" class="form-control form-control-sm">
                    <label class="form-label">استان</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-2">
                  <div class="form-floating">
                    <input v-model="content.shahrestan" type="text" id="business_new_shahr" name="business_new[shahr]"
                      maxlength="50" class="form-control form-control-sm">
                    <label class="form-label">شهر</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-2">
                  <div class="form-floating">
                    <input v-model="content.postalcode" type="text" id="business_new_codeposti"
                      name="business_new[codeposti]" maxlength="10" class="form-control form-control-sm">
                    <label class="form-label">کد پستی</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-2">
                  <div class="form-floating">
                    <input v-model="content.tel" type="text" id="business_new_tel" name="business_new[tel]"
                      maxlength="15" class="form-control form-control-sm">
                    <label class="form-label">تلفن</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-2">
                  <div class="form-floating">
                    <input v-model="content.mobile" type="tel" id="business_new_fax" name="business_new[fax]"
                      maxlength="15" class="form-control form-control-sm">
                    <label class="form-label">موبایل</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-12 mb-2">
                  <div class="form-floating">
                    <input type="text" v-model="content.address" id="business_new_address" name="business_new[address]"
                      maxlength="255" class="form-control form-control-sm">
                    <label class="form-label">آدرس</label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <input v-model="content.website" type="url" id="business_new_website" name="business_new[website]"
                      inputmode="url" class="form-control form-control-sm">
                    <label class="form-label">وب‌سایت</label>
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating">
                    <input v-model="content.email" type="email" id="business_new_email" name="business_new[email]"
                      maxlength="255" class="form-control form-control-sm">
                    <label class="form-label">پست الکترونیکی</label>
                  </div>
                </div>
              </div>
              <h3 class="text-primary">اطلاعات مالی</h3>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-floating required">
                    <input v-model="content.maliyatafzode" type="number" id="business_new_maliyatafzode"
                      name="business_new[maliyatafzode]" required="required" class="form-control form-control-sm">
                    <label class="form-label">مالیات بر ارزش افزوده</label>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="1">
          <v-card>
            <v-card-text>
              <h3 class="text-primary">سال مالی</h3>
              <div class="row">
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-control">
                    <label class="form-label">
                      <span class="text-danger">*</span>
                      شروع سال مالی
                    </label>
                    <date-picker class="" v-model="content.year.startShamsi" format="jYYYY/jMM/jDD"
                      display-format="jYYYY/jMM/jDD" />
                  </div>
                </div>
                <div class="col-sm-12 col-md-6 mb-2">
                  <div class="form-control">
                    <label class="form-label">
                      <span class="text-danger">*</span>
                      اتمام سال مالی
                    </label>
                    <date-picker class="" v-model="content.year.endShamsi" format="jYYYY/jMM/jDD"
                      display-format="jYYYY/jMM/jDD" :min="content.year.startShamsi" />
                  </div>
                </div>
                <div class="col-sm-12 col-md-12">
                  <div class="form-control mb-2">
                    <label class="form-label">
                      <span class="text-danger">*</span>
                      عنوان سال مالی
                    </label>
                    <input v-model="content.year.label" class="form-control" type="text">
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="2">
          <v-card>
            <v-card-text>
              <h3 class="text-primary">نمایش پیوند یکتا</h3>
              <div class="row">
                <div class="col-sm-12 col-md-8 mb-2">
                  <div class="space-y-2">
                    <div class="form-check form-switch">
                      <input v-model="content.shortlinks" class="form-check-input" type="checkbox">
                      <label class="form-check-label">فعال‌سازی پیوند‌های یکتا</label>
                      <br>
                      <label class="text-muted">این قابلیت برای تولید پیوند‌های یکتا برای ارسال به مشتری جهت مشاهده
                        فاکتورها است.</label>
                    </div>
                  </div>
                </div>
              </div>
              <h3 class="text-primary">دریافت مبلغ فاکتور از طریق کیف پول</h3>
              <div class="row">
                <div class="col-sm-12 col-md-12 mb-2">
                  <div class="space-y-2">
                    <div class="form-check form-switch">
                      <input @change="checkBanksExist()" v-model="content.walletEnabled" class="form-check-input"
                        type="checkbox">
                      <label class="form-check-label">فعال‌سازی دریافت آنلاین از طریق کیف پول</label>
                      <br>
                      <label class="text-muted">با فعال سازی این قابلیت قادر خواهید بود مبالغ فاکتورهای ثبت شده را
                        به صورت آنلاین از مشتریان خود دریافت کنید.</label>
                    </div>
                  </div>
                  <div class="row" v-show="content.walletEnabled">
                    <div class="col-sm-12 col-md-6">
                      <label class="mb-2">حساب بانکی متصل به کیف پول</label>
                      <div class="col">
                        <v-cob dir="rtl" :options="listBanks" label="name" v-model="content.walletMatchBank"
                          @option:deselecting="" @search:focus="" @option:selecting="">
                          <template #no-options="{ search, searching, loading }">
                            نتیجه‌ای یافت نشد!
                          </template>
                        </v-cob>
                      </div>
                    </div>
                    <label class="text-muted">برای تسویه اتوماتیک به حساب انتخاب شده حتما باید تمام موارد از جمله
                      شماره شبا و شماره کارت و
                      ... به درستی تکمیل شده باشد در غیر این صورت تراکنش با خطا مواجه خواهد شد.</label>

                  </div>
                </div>
              </div>
              <h3 class="text-primary">کالا و خدمات</h3>
              <div class="row">
                <div class="col-sm-12 col-md-8 mb-2">
                  <div class="space-y-2">
                    <div class="form-check form-switch">
                      <input v-model="content.updateBuyPrice" class="form-check-input" type="checkbox">
                      <label class="form-check-label">به روز رسانی قیمت خرید هنگام صدور فاکتور</label>
                      <br>
                      <label class="text-muted">با صدور فاکتور خرید یا برگشت از خرید قیمت خرید کالا و خدمات به
                        روزرسانی خواهد
                        شد.</label>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-8 mb-2">
                  <div class="space-y-2">
                    <div class="form-check form-switch">
                      <input v-model="content.updateSellPrice" class="form-check-input" type="checkbox">
                      <label class="form-check-label">به روز رسانی قیمت فروش هنگام صدور فاکتور</label>
                      <br>
                      <label class="text-muted">با صدور فاکتور فروش یا برگشت از فروش قیمت خرید کالا و خدمات به
                        روزرسانی خواهد
                        شد.</label>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-8 mb-2">
                  <div class="space-y-2">
                    <div class="form-floating">
                      <select class="form-select" v-model="content.profitCalcType">
                        <option value="simple">بر اساس اختلاف قیمت خرید و فروش</option>
                        <option value="lis">بر اساس آخرین قیمت ورود به انبار</option>
                        <option value="avgis">بر اساس میانگین قیمت ورود به انبار</option>
                      </select>
                      <label for="floatingSelect">نحوه محاسبه سود فاکتور</label>
                    </div>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-tabs-window-item>
        <v-tabs-window-item value="3">
          <v-card>
            <v-card-text>
              <h3 class="text-primary">درگاه پرداخت زرین پال</h3>
              <div class="row">
                <div class="col-sm-12 col-md-8 mb-2">
                  <div class="form-floating required">
                    <input class="form-control" type="text" v-model="content.zarinpal">
                    <label class="form-label">کد شناسایی: مثال a1104652-18b9-4b63-911c-0a5046e61be1</label>
                  </div>
                  <label class="text-muted mt-2">برای غیر فعال کردن درگاه پرداخت آن را خالی بگذارید. در صورت اشتباه
                    بودن کد وارد
                    شده × مشتری در هنگام تسویه فاکتورها با خطا مواجه خواهد شد.</label>
                </div>
              </div>
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

export default {
  name: "bussiness",
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
        zarinpalCode: '',
        shortlinks: false,
        walletEnabled: false,
        walletMatchBank: '',
        year: {},
        updateSellPrice: false,
        updateBuyPrice: false,
        profitCalcType: 'lis'
      },
      listBanks: [],
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
      }
      if (this.content.walletEnabled && (this.content.walletMatchBank === undefined || this.content.walletMatchBank === null)) {
        Swal.fire({
          text: 'حساب بانکی متصل به کیف پول انتخاب نشده است',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else {
        //submit data
        this.loading = true;
        let data = axios.post('/api/business/insert', {
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
          'zarinpalCode': this.content.zarinpalCode,
          'shortlinks': this.content.shortlinks,
          'walletEnabled': this.content.walletEnabled,
          'walletMatchBank': this.content.walletMatchBank,
          'year': this.content.year,
          'commodityUpdateBuyPriceAuto': this.content.updateBuyPrice,
          'commodityUpdateSellPriceAuto': this.content.updateSellPrice,
          'profitCalcType': this.content.profitCalcType
        })
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
      }
    }
  },
  async beforeMount() {
    this.loading = true
    //get all money types
    axios.post("/api/money/get/all").then((response) => {
      this.moneys = response.data;
      this.content.arzmain = this.moneys[0];
    })

    //get business info
    let data = axios.post('/api/business/get/info/' + localStorage.getItem('activeBid'))
      .then((response) => {
        this.content = response.data;
        this.loading = false;
      });
    //get list of banks
    axios.post('/api/bank/list').then((response) => {
      this.listBanks = response.data;
    })

  }
}
</script>

<style scoped>
.required label:before {
  content: "*";
  color: red;
}
</style>