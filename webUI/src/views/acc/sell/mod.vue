<template>
  <v-toolbar color="toolbar" :title="$t('dialog.sell_invoice')">
    <template v-slot:prepend>
      <v-tooltip :text="$t('dialog.back')" location="bottom">
        <template v-slot:activator="{ props }">
          <v-btn v-bind="props" @click="$router.back()" class="d-none d-sm-flex" variant="text" icon="mdi-arrow-right" />
        </template>
      </v-tooltip>
    </template>
    <v-spacer></v-spacer>
    <v-tooltip text="ثبت فاکتور" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-content-save" color="success" @click="saveInvoice" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
    <v-tooltip v-if="$route.params.id" text="حذف فاکتور" location="bottom">
      <template v-slot:activator="{ props }">
        <v-btn v-bind="props" variant="text" icon="mdi-delete" color="error" @click="deleteInvoice" :loading="loading"></v-btn>
      </template>
    </v-tooltip>
  </v-toolbar>
  <v-container fluid class="pa-0">
    <v-row>
      <v-col cols="12">
        <v-tabs v-model="activeTab" color="primary" class="w-100 tabs-container">
          <v-tab value="invoice" class="flex-grow-1">فاکتور</v-tab>
          <v-tab value="payments" class="flex-grow-1">اسناد پرداخت</v-tab>
          <v-tab value="settings" class="flex-grow-1">تنظیمات جانبی</v-tab>
        </v-tabs>

        <v-window v-model="activeTab" class="mt-3">
          <!-- تب فاکتور -->
          <v-window-item value="invoice">
            <v-row class="ma-1">
              <v-col cols="12" sm="12">
                <v-row class="mb-2">
                  <v-col cols="12" sm="6">
                    <Hdatepicker v-model="invoiceDate" label="تاریخ فاکتور" density="compact"></Hdatepicker>
                  </v-col>
                  <v-col cols="12" sm="6">
                    <Hpersonsearch v-model="customer" label="خریدار" :rules="[v => !!v || 'خریدار الزامی است']" required></Hpersonsearch>
                  </v-col>
                </v-row>
                <v-text-field v-model="invoiceDescription" label="توضیحات فاکتور" density="compact" hide-details class="mb-4">
                  <template v-slot:prepend-inner>
                    <mostdes v-model="invoiceDescription" :submitData="{ id: null, des: invoiceDescription }" type="sell" label=""></mostdes>
                  </template>
                </v-text-field>
                <v-table class="border rounded d-none d-sm-table" style="width: 100%;">
                  <thead>
                    <tr style="background-color: #0D47A1; color: white;">
                      <th class="text-center">نام کالا</th>
                      <th class="text-center">تعداد</th>
                      <th class="text-center">قیمت</th>
                      <th class="text-center">تخفیف</th>
                      <th class="text-center" style="width: 150px;">جمع کل</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template v-for="(item, index) in items" :key="index">
                      <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '64px' }">
                        <td class="text-center" style="min-width: 200px;">
                          <Hcommoditysearch v-model="item.name" density="compact" hide-details class="my-0" style="font-size: 0.8rem;" return-object @update:modelValue="handleCommodityChange(item)"></Hcommoditysearch>
                        </td>
                        <td class="text-center" style="width: 100px;">
                          <Hnumberinput v-model="item.count" density="compact" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                        </td>
                        <td class="text-center" style="width: 120px;">
                          <Hnumberinput v-model="item.price" density="compact" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;"></Hnumberinput>
                        </td>
                        <td class="text-center" style="width: 150px;">
                          <div class="d-flex align-center">
                            <Hnumberinput v-if="item.showPercentDiscount" v-model="item.discountPercent" density="compact" suffix="%" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;">
                              <template v-slot:prepend>
                                <v-tooltip text="تخفیف درصدی" location="bottom">
                                  <template v-slot:activator="{ props }">
                                    <v-checkbox v-bind="props" v-model="item.showPercentDiscount" hide-details density="compact" color="primary" class="mt-0" @update:modelValue="handleDiscountTypeChange(item)"></v-checkbox>
                                  </template>
                                </v-tooltip>
                              </template>
                            </Hnumberinput>
                            <Hnumberinput v-else v-model="item.discountAmount" density="compact" @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;">
                              <template v-slot:prepend>
                                <v-tooltip text="تخفیف مبلغی" location="bottom">
                                  <template v-slot:activator="{ props }">
                                    <v-checkbox v-bind="props" v-model="item.showPercentDiscount" hide-details density="compact" color="primary" class="mt-0" @update:modelValue="handleDiscountTypeChange(item)"></v-checkbox>
                                  </template>
                                </v-tooltip>
                              </template>
                            </Hnumberinput>
                          </div>
                        </td>
                        <td class="text-center font-weight-bold" style="width: 120px;">
                          {{ item.total.toLocaleString('fa-IR') }}
                        </td>
                      </tr>
                      <tr :style="{ backgroundColor: index % 2 === 0 ? '#f8f9fa' : 'white', height: '64px' }">
                        <td colspan="4">
                          <v-text-field v-model="item.description" density="compact" hide-details placeholder="شرح" class="my-0" style="font-size: 0.8rem;">
                            <template v-slot:prepend-inner>
                              <mostdes v-model="item.description" :submitData="{ id: null, des: item.description }" type="sell" label=""></mostdes>
                            </template>
                          </v-text-field>
                        </td>
                        <td class="text-center" style="width: 120px;">
                          <v-tooltip text="حذف" location="bottom">
                            <template v-slot:activator="{ props }">
                              <v-btn v-bind="props" icon="mdi-delete" variant="text" size="small" color="error" @click="removeItem(index)"></v-btn>
                            </template>
                          </v-tooltip>
                        </td>
                      </tr>
                    </template>
                    <tr>
                      <td colspan="5" class="text-center pa-2" style="height: 64px;">
                        <v-btn color="primary" prepend-icon="mdi-plus" size="small" @click="addItem">افزودن سطر جدید</v-btn>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </v-col>
            </v-row>

            <!-- جدول موبایل -->
            <div class="d-sm-none">
              <v-card v-for="(item, index) in items" :key="index" class="mb-4" variant="outlined">
                <v-card-text>
                  <div class="d-flex justify-space-between align-center mb-2">
                    <span class="text-subtitle-2 font-weight-bold">ردیف:</span>
                    <span>{{ index + 1 }}</span>
                  </div>
                  <div class="mb-2">
                    <Hcommoditysearch v-model="item.name" density="compact" label="نام کالا" hide-details class="my-0" style="font-size: 0.8rem;" return-object @update:modelValue="handleCommodityChange(item)"></Hcommoditysearch>
                  </div>
                  <div class="d-flex justify-space-between mb-2">
                    <div style="width: 48%;">
                      <Hnumberinput v-model="item.count" density="compact" label="تعداد" hide-details class="my-0" style="font-size: 0.8rem;" @update:modelValue="recalculateTotals"></Hnumberinput>
                    </div>
                    <div style="width: 48%;">
                      <Hnumberinput v-model="item.price" density="compact" label="قیمت" hide-details class="my-0" style="font-size: 0.8rem;" @update:modelValue="recalculateTotals"></Hnumberinput>
                    </div>
                  </div>
                  <div class="mb-2">
                    <div class="d-flex align-center">
                      <Hnumberinput v-if="item.showPercentDiscount" v-model="item.discountPercent" density="compact" label="تخفیف" suffix="%" hide-details @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;">
                        <template v-slot:prepend>
                          <v-tooltip text="تخفیف درصدی" location="bottom">
                            <template v-slot:activator="{ props }">
                              <v-checkbox v-bind="props" v-model="item.showPercentDiscount" hide-details density="compact" color="primary" class="mt-0" @update:modelValue="handleDiscountTypeChange(item)"></v-checkbox>
                            </template>
                          </v-tooltip>
                        </template>
                      </Hnumberinput>
                      <Hnumberinput v-else v-model="item.discountAmount" density="compact" label="تخفیف" hide-details @update:modelValue="recalculateTotals" class="my-0" style="font-size: 0.8rem;">
                        <template v-slot:prepend>
                          <v-tooltip text="تخفیف مبلغی" location="bottom">
                            <template v-slot:activator="{ props }">
                              <v-checkbox v-bind="props" v-model="item.showPercentDiscount" hide-details density="compact" color="primary" class="mt-0" @update:modelValue="handleDiscountTypeChange(item)"></v-checkbox>
                            </template>
                          </v-tooltip>
                        </template>
                      </Hnumberinput>
                    </div>
                  </div>
                  <div class="mb-2">
                    <v-text-field v-model="item.description" density="compact" label="شرح" hide-details class="my-0" style="font-size: 0.8rem;">
                      <template v-slot:prepend-inner>
                        <mostdes v-model="item.description" :submitData="{ id: null, des: item.description }" type="sell" label=""></mostdes>
                      </template>
                    </v-text-field>
                  </div>
                  <div class="d-flex justify-space-between align-center">
                    <span class="text-subtitle-2 font-weight-bold">جمع کل:</span>
                    <span class="text-subtitle-2 font-weight-bold">{{ item.total.toLocaleString('fa-IR') }}</span>
                  </div>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn icon="mdi-delete" variant="text" color="error" @click="removeItem(index)"></v-btn>
                </v-card-actions>
              </v-card>
              <v-btn color="primary" prepend-icon="mdi-plus" block class="mb-4" @click="addItem">افزودن کالای جدید</v-btn>
            </div>
            <v-card class="mt-4 mx-2 border pa-4">
              <v-row>
                <v-col cols="12" sm="6">
                  <v-row>
                    <v-col cols="12">
                      <Hnumberinput v-model="taxPercent" label="مالیات بر ارزش افزوده" density="compact" hide-details suffix="%" @update:modelValue="recalculateTotals"></Hnumberinput>
                    </v-col>
                    <v-col cols="12">
                      <div class="d-flex align-center">
                        <Hnumberinput v-if="showTotalPercentDiscount" v-model="totalDiscountPercent" density="compact" label="تخفیف کلی" hide-details suffix="%" @update:modelValue="recalculateTotals">
                          <template v-slot:prepend>
                            <v-tooltip text="تخفیف درصدی" location="bottom">
                              <template v-slot:activator="{ props }">
                                <v-checkbox v-bind="props" v-model="showTotalPercentDiscount" hide-details density="compact" color="primary" class="mt-0"></v-checkbox>
                              </template>
                            </v-tooltip>
                          </template>
                        </Hnumberinput>
                        <Hnumberinput v-else v-model="totalDiscount" density="compact" label="تخفیف کلی" hide-details @update:modelValue="recalculateTotals">
                          <template v-slot:prepend>
                            <v-tooltip text="تخفیف مبلغی" location="bottom">
                              <template v-slot:activator="{ props }">
                                <v-checkbox v-bind="props" v-model="showTotalPercentDiscount" hide-details density="compact" color="primary" class="mt-0"></v-checkbox>
                              </template>
                            </v-tooltip>
                          </template>
                        </Hnumberinput>
                      </div>
                    </v-col>
                    <v-col cols="12">
                      <Hnumberinput v-model="shippingCost" density="compact" label="هزینه حمل" hide-details @update:modelValue="recalculateTotals"></Hnumberinput>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-card class="pa-4" color="grey-lighten-4">
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-subtitle-2 font-weight-bold">جمع کل فاکتور:</span>
                      <span class="text-subtitle-2 font-weight-bold">{{ totalInvoice.toLocaleString('fa-IR') }}</span>
                    </div>
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-subtitle-2 font-weight-bold">تخفیف کلی:</span>
                      <span class="text-subtitle-2 font-weight-bold">{{ (showTotalPercentDiscount ? Math.round((totalInvoice * totalDiscountPercent) / 100) : totalDiscount).toLocaleString('fa-IR') }}</span>
                    </div>
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-subtitle-2 font-weight-bold">هزینه حمل:</span>
                      <span class="text-subtitle-2 font-weight-bold">{{ shippingCost.toLocaleString('fa-IR') }}</span>
                    </div>
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-subtitle-2 font-weight-bold">جمع کل با تخفیف و حمل:</span>
                      <span class="text-subtitle-2 font-weight-bold">{{ (totalInvoice - (showTotalPercentDiscount ? Math.round((totalInvoice * totalDiscountPercent) / 100) : totalDiscount) + shippingCost).toLocaleString('fa-IR') }}</span>
                    </div>
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-subtitle-2 font-weight-bold">مبلغ مالیات:</span>
                      <span class="text-subtitle-2 font-weight-bold">{{ taxAmount.toLocaleString('fa-IR') }}</span>
                    </div>
                    <div class="d-flex align-center justify-space-between mb-2">
                      <span class="text-subtitle-2 font-weight-bold">جمع کل نهایی:</span>
                      <span class="text-subtitle-2 font-weight-bold">{{ finalTotal.toLocaleString('fa-IR') }}</span>
                    </div>
                  </v-card>
                </v-col>
              </v-row>
            </v-card>
          </v-window-item>

          <!-- تب اسناد پرداخت -->
          <v-window-item value="payments">
            <v-card class="mt-4">
              <v-card-text>
                <v-container class="pa-0">
                  <!-- بخش خلاصه مبالغ -->
                  <v-row class="mb-4">
                    <v-col cols="12" md="4">
                      <v-text-field v-model="totalPayments" label="مجموع پرداخت‌ها" readonly density="compact" hide-details variant="outlined" bg-color="grey-lighten-4"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">
                      <v-text-field v-model="remainingAmount" label="باقی مانده" readonly density="compact" hide-details variant="outlined" bg-color="grey-lighten-4"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="4">
                      <v-text-field v-model="totalAmount" label="مبلغ کل فاکتور" readonly density="compact" hide-details variant="outlined" bg-color="grey-lighten-4"></v-text-field>
                    </v-col>
                  </v-row>

                  <!-- بخش لیست پرداخت‌ها -->
                  <div v-if="paymentItems.length === 0" class="text-center pa-8">
                    <v-icon size="large" color="grey" class="mb-2">mdi-cash-remove</v-icon>
                    <div class="text-subtitle-1 text-grey">هیچ پرداختی ثبت نشده است.</div>
                  </div>

                  <v-row v-else>
                    <v-col v-for="(item, index) in paymentItems" :key="index" cols="12">
                      <v-card :class="{
                        'bank-card': item.type === 'bank',
                        'cashdesk-card': item.type === 'cashdesk',
                        'salary-card': item.type === 'salary',
                        'cheque-card': item.type === 'cheque'
                      }" border="sm" class="mb-0 payment-card">
                        <v-card-item class="py-0">
                          <template v-slot:prepend>
                            <v-icon :color="item.type === 'bank' ? 'blue' : item.type === 'cashdesk' ? 'green' : item.type === 'salary' ? 'orange' : 'purple'" size="small">
                              {{ item.type === 'bank' ? 'mdi-bank' : item.type === 'cashdesk' ? 'mdi-cash-register' : item.type === 'salary' ? 'mdi-wallet' : 'mdi-checkbook' }}
                            </v-icon>
                          </template>

                          <v-card-title class="text-subtitle-2 py-0">
                            {{ item.type === 'bank' ? 'حساب بانکی' : item.type === 'cashdesk' ? 'صندوق' : item.type === 'salary' ? 'تنخواه گردان' : 'چک' }}
                          </v-card-title>

                          <template v-slot:append>
                            <v-btn-group density="compact">
                              <v-btn variant="text" color="primary" size="small" @click="fillWithTotal(item)">
                                <v-icon size="small">mdi-cash-100</v-icon>
                                <v-tooltip activator="parent" location="bottom">کل فاکتور</v-tooltip>
                              </v-btn>
                              <v-btn variant="text" color="error" size="small" @click="deletePaymentItem(index)">
                                <v-icon size="small">mdi-trash-can</v-icon>
                                <v-tooltip activator="parent" location="bottom">حذف</v-tooltip>
                              </v-btn>
                            </v-btn-group>
                          </template>
                        </v-card-item>

                        <v-divider class="my-0"></v-divider>

                        <v-card-text class="py-2">
                          <v-row class="mt-0">
                            <v-col cols="12" sm="6">
                              <Hbankaccountsearch v-if="item.type === 'bank'" v-model="item.bank" label="بانک" density="compact" variant="outlined"></Hbankaccountsearch>
                              <Hcashdesksearch v-if="item.type === 'cashdesk'" v-model="item.cashdesk" label="صندوق" density="compact" variant="outlined"></Hcashdesksearch>
                              <Hsalarysearch v-if="item.type === 'salary'" v-model="item.salary" label="تنخواه گردان" density="compact" variant="outlined"></Hsalarysearch>
                            </v-col>
                            <v-col cols="12" sm="6">
                              <Hnumberinput v-model="item.amount" label="مبلغ" placeholder="0" @update:modelValue="calculatePayments" density="compact" variant="outlined" />
                            </v-col>
                            <v-col cols="12" sm="6">
                              <v-text-field v-model="item.reference" label="ارجاع" density="compact" variant="outlined"></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6">
                              <v-text-field v-model="item.description" label="شرح" density="compact" variant="outlined"></v-text-field>
                            </v-col>
                          </v-row>
                        </v-card-text>
                      </v-card>
                    </v-col>
                  </v-row>

                  <!-- دکمه افزودن پرداخت -->
                  <v-btn color="primary" prepend-icon="mdi-plus" class="mt-4" @click="showPaymentMenu = true">
                    افزودن پرداخت
                  </v-btn>

                  <!-- دیالوگ افزودن پرداخت -->
                  <v-dialog v-model="showPaymentMenu" max-width="300">
                    <v-card>
                      <v-card-title class="text-h6 pa-4">
                        <v-icon icon="mdi-plus-circle" class="ml-2"></v-icon>
                        افزودن پرداخت
                      </v-card-title>
                      <v-divider class="my-0"></v-divider>
                      <v-list>
                        <v-list-item @click="addPaymentItem('bank')">
                          <template v-slot:prepend>
                            <v-icon color="blue">mdi-bank</v-icon>
                          </template>
                          <v-list-item-title>حساب بانکی</v-list-item-title>
                        </v-list-item>
                        <v-list-item @click="addPaymentItem('cashdesk')">
                          <template v-slot:prepend>
                            <v-icon color="green">mdi-cash-register</v-icon>
                          </template>
                          <v-list-item-title>صندوق</v-list-item-title>
                        </v-list-item>
                        <v-list-item @click="addPaymentItem('salary')">
                          <template v-slot:prepend>
                            <v-icon color="orange">mdi-wallet</v-icon>
                          </template>
                          <v-list-item-title>تنخواه گردان</v-list-item-title>
                        </v-list-item>
                      </v-list>
                    </v-card>
                  </v-dialog>
                </v-container>
              </v-card-text>
            </v-card>
          </v-window-item>

          <!-- تب تنظیمات جانبی -->
          <v-window-item value="settings">
            <v-card class="settings-card">
              <v-card-text>
                <v-row>
                  <v-col cols="12" md="6">
                    <v-card class="settings-section-card" variant="outlined">
                      <v-card-title class="settings-section-title">
                        <v-icon icon="mdi-message-text" class="ml-2" color="primary"></v-icon>
                        <span class="text-subtitle-1 font-weight-bold">تنظیمات پیامک</span>
                      </v-card-title>
                      <v-card-text class="settings-section-content">
                        <v-switch
                          v-model="sendSmsToCustomer"
                          color="primary"
                          label="ارسال پیامک به مشتری"
                          hide-details
                          class="mb-0"
                        ></v-switch>
                      </v-card-text>
                    </v-card>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-card class="settings-section-card" variant="outlined">
                      <v-card-title class="settings-section-title">
                        <v-icon icon="mdi-printer" class="ml-2" color="primary"></v-icon>
                        <span class="text-subtitle-1 font-weight-bold">تنظیمات چاپ</span>
                      </v-card-title>
                      <v-card-text class="settings-section-content">
                        <v-switch
                          v-model="cloudPrint"
                          color="primary"
                          label="ارسال به پرینتر ابری"
                          hide-details
                          class="mb-2"
                        ></v-switch>
                        <v-select
                          v-if="cloudPrint"
                          v-model="selectedPrinter"
                          :items="printers"
                          item-title="name"
                          item-value="id"
                          label="انتخاب پرینتر ابری"
                          density="compact"
                          variant="outlined"
                          hide-details
                          class="mt-2 mb-4"
                        ></v-select>
                        <v-divider v-if="cloudPrint" class="mb-4"></v-divider>
                        <v-switch
                          v-model="printInvoice"
                          color="primary"
                          label="چاپ صورت حساب"
                          hide-details
                          class="mb-2"
                          :disabled="!cloudPrint"
                        ></v-switch>
                        <v-switch
                          v-model="printCashier"
                          color="primary"
                          label="چاپ قبض صندوق"
                          hide-details
                          class="mb-2"
                          :disabled="!cloudPrint"
                        ></v-switch>
                        <v-switch
                          v-model="generatePdf"
                          color="primary"
                          label="خروجی PDF فاکتور"
                          hide-details
                          class="mb-2"
                        ></v-switch>
                      </v-card-text>
                    </v-card>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-window-item>
        </v-window>
      </v-col>
    </v-row>
  </v-container>
  <v-snackbar v-model="showError" color="error" timeout="3000">
    مبلغ تخفیف نمی‌تواند از قیمت پایه بیشتر باشد
  </v-snackbar>
  <v-snackbar v-model="showDiscountError" color="error" timeout="3000">
    مبلغ تخفیف کلی نمی‌تواند از جمع کل فاکتور بیشتر باشد
  </v-snackbar>
  <v-snackbar v-model="showError" color="error" timeout="3000">
    {{ error }}
  </v-snackbar>
  <v-snackbar v-model="showSuccess" color="success" timeout="3000">
    {{ successMessage }}
  </v-snackbar>
  <v-snackbar v-model="showValidationErrors" color="error" timeout="3000">
    <ul class="mb-0">
      <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
    </ul>
  </v-snackbar>
  <v-overlay :model-value="loading" class="align-center justify-center">
    <v-progress-circular color="primary" indeterminate></v-progress-circular>
  </v-overlay>

  <!-- دیالوگ تأیید حذف -->
  <v-dialog v-model="deleteDialog" max-width="400">
    <v-card>
      <v-card-title class="text-h5">
        حذف فاکتور
      </v-card-title>
      <v-card-text>
        آیا مطمئن هستید که می‌خواهید این فاکتور را حذف کنید؟
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="grey-darken-1" variant="text" @click="deleteDialog = false">
          انصراف
        </v-btn>
        <v-btn color="error" variant="text" @click="confirmDelete" :loading="loading">
          حذف
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- دیالوگ تأیید بارگذاری پیش‌نویس -->
  <v-dialog v-model="showDraftDialog" max-width="500" persistent>
    <v-card class="draft-dialog">
      <v-card-title class="draft-dialog-title">
        <v-icon icon="mdi-file-document-outline" color="primary" class="ml-2"></v-icon>
        پیش‌نویس فاکتور فروش
      </v-card-title>
      
      <v-divider></v-divider>
      
      <v-card-text class="draft-dialog-content">
        <div class="draft-message">
          <v-icon icon="mdi-information-outline" color="info" size="large" class="mb-2"></v-icon>
          <p class="text-body-1">یک پیش‌نویس از فاکتور فروش قبلی موجود است.</p>
          <p class="text-body-2 text-medium-emphasis">آیا می‌خواهید این پیش‌نویس را بارگذاری کنید؟</p>
        </div>
      </v-card-text>
      
      <v-divider></v-divider>
      
      <v-card-actions class="draft-dialog-actions">
        <v-btn
          color="error"
          variant="outlined"
          prepend-icon="mdi-delete"
          @click="discardDraft"
          class="ml-2"
        >
          حذف پیش‌نویس
        </v-btn>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          variant="elevated"
          prepend-icon="mdi-file-restore"
          @click="loadDraft"
        >
          بارگذاری پیش‌نویس
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from "axios";
import Hnumberinput from "@/components/forms/Hnumberinput.vue";
import Hdatepicker from "@/components/forms/Hdatepicker.vue";
import Hpersonsearch from "@/components/forms/Hpersonsearch.vue";
import mostdes from "../component/mostdes.vue";
import Hcommoditysearch from "@/components/forms/Hcommoditysearch.vue";
import Hbankaccountsearch from "@/components/forms/Hbankaccountsearch.vue";
import Hcashdesksearch from "@/components/forms/Hcashdesksearch.vue";
import Hsalarysearch from "@/components/forms/Hsalarysearch.vue";
import { ref, watch } from 'vue';

export default {
  name: "mod",
  components: {
    Hnumberinput,
    Hdatepicker,
    Hpersonsearch,
    mostdes,
    Hcommoditysearch,
    Hbankaccountsearch,
    Hcashdesksearch,
    Hsalarysearch
  },
  setup() {
    const activeTab = ref('invoice');
    const items = ref([]);
    const totalInvoice = ref(0);
    const showError = ref(false);
    const showDiscountError = ref(false);
    const showPercentDiscount = ref(true);
    const showTotalPercentDiscount = ref(true);
    const invoiceDescription = ref('');
    const invoiceDate = ref(null);
    const customer = ref(null);
    const taxPercent = ref(0);
    const totalDiscount = ref(0);
    const totalDiscountPercent = ref(0);
    const shippingCost = ref(0);
    const finalTotal = ref(0);
    const totalWithoutTax = ref(0);
    const taxAmount = ref(0);
    const loading = ref(false);
    const error = ref(null);
    const validationErrors = ref([]);
    const deleteDialog = ref(false);
    const showSuccess = ref(false);
    const successMessage = ref('');
    const showValidationErrors = ref(false);
    const paymentDate = ref(null);
    const paymentDescription = ref('');
    const totalPayments = ref(0);
    const remainingAmount = ref(0);
    const totalAmount = ref(0);
    const paymentItems = ref([]);
    const showPaymentMenu = ref(false);
    const addPaymentBtn = ref(null);
    const sendSmsToCustomer = ref(false);
    const printInvoice = ref(false);
    const printCashier = ref(false);
    const generatePdf = ref(false);
    const cloudPrint = ref(false);
    const printers = ref([]);
    const selectedPrinter = ref(null);
    const showDraftDialog = ref(false);
    const hasChanges = ref(false);
    const isNewInvoice = ref(true);
    const isInitializing = ref(true);

    // بارگذاری تنظیمات از لوکال استوریج
    const loadSettings = () => {
      try {
        const settings = localStorage.getItem('sellInvoiceSettings');
        if (settings) {
          const parsedSettings = JSON.parse(settings);
          sendSmsToCustomer.value = parsedSettings.sendSmsToCustomer || false;
          printInvoice.value = parsedSettings.printInvoice || false;
          printCashier.value = parsedSettings.printCashier || false;
          generatePdf.value = parsedSettings.generatePdf || false;
          cloudPrint.value = parsedSettings.cloudPrint || false;
          selectedPrinter.value = parsedSettings.selectedPrinter || null;
        }
      } catch (error) {
        console.error('خطا در بارگذاری تنظیمات:', error);
      }
    };

    // ذخیره تنظیمات در لوکال استوریج
    const saveSettings = () => {
      try {
        const settings = {
          sendSmsToCustomer: sendSmsToCustomer.value,
          printInvoice: printInvoice.value,
          printCashier: printCashier.value,
          generatePdf: generatePdf.value,
          cloudPrint: cloudPrint.value,
          selectedPrinter: selectedPrinter.value
        };
        localStorage.setItem('sellInvoiceSettings', JSON.stringify(settings));
      } catch (error) {
        console.error('خطا در ذخیره تنظیمات:', error);
      }
    };

    // تماشای تغییرات تنظیمات
    watch([sendSmsToCustomer, printInvoice, printCashier, generatePdf, cloudPrint, selectedPrinter], () => {
      saveSettings();
    });

    return {
      activeTab,
      items,
      totalInvoice,
      showError,
      showDiscountError,
      showPercentDiscount,
      showTotalPercentDiscount,
      invoiceDescription,
      invoiceDate,
      customer,
      taxPercent,
      totalDiscount,
      totalDiscountPercent,
      shippingCost,
      finalTotal,
      totalWithoutTax,
      taxAmount,
      loading,
      error,
      validationErrors,
      deleteDialog,
      showSuccess,
      successMessage,
      showValidationErrors,
      paymentDate,
      paymentDescription,
      totalPayments,
      remainingAmount,
      totalAmount,
      paymentItems,
      showPaymentMenu,
      addPaymentBtn,
      sendSmsToCustomer,
      printInvoice,
      printCashier,
      generatePdf,
      cloudPrint,
      printers,
      selectedPrinter,
      loadSettings,
      saveSettings,
      showDraftDialog,
      hasChanges,
      isNewInvoice,
      isInitializing
    };
  },
  watch: {
    items: {
      handler() {
        if (this.isNewInvoice && !this.isInitializing) {
          this.hasChanges = true;
          this.saveDraft();
        }
      },
      deep: true
    },
    'items.*.name': {
      handler(newVal) {
        if (newVal && newVal.priceSell !== undefined) {
          const index = this.items.findIndex(item => item.name && item.name.id === newVal.id);
          if (index !== -1) {
            this.items[index].price = Number(newVal.priceSell);
            this.recalculateTotals();
          }
        }
      },
      deep: true
    },
    customer() {
      if (this.isNewInvoice && !this.isInitializing) {
        this.hasChanges = true;
        this.saveDraft();
      }
    },
    invoiceDate() {
      if (this.isNewInvoice && !this.isInitializing) {
        this.hasChanges = true;
        this.saveDraft();
      }
    },
    invoiceDescription() {
      if (this.isNewInvoice && !this.isInitializing) {
        this.hasChanges = true;
        this.saveDraft();
      }
    },
    taxPercent() {
      if (this.isNewInvoice && !this.isInitializing) {
        this.hasChanges = true;
        this.saveDraft();
      }
    },
    totalDiscount() {
      if (this.isNewInvoice && !this.isInitializing) {
        this.hasChanges = true;
        this.saveDraft();
      }
    },
    totalDiscountPercent() {
      if (this.isNewInvoice && !this.isInitializing) {
        this.hasChanges = true;
        this.saveDraft();
      }
    },
    shippingCost() {
      if (this.isNewInvoice && !this.isInitializing) {
        this.hasChanges = true;
        this.saveDraft();
      }
    },
    paymentItems: {
      handler() {
        if (this.isNewInvoice && !this.isInitializing) {
          this.hasChanges = true;
          this.saveDraft();
        }
      },
      deep: true
    }
  },
  async mounted() {
    try {
      // اول تنظیمات را لود می‌کنیم
      this.loadSettings();
      
      // بررسی وضعیت پیش‌نویس
      this.isNewInvoice = !this.$route.params.id;
      
      // تاریخ را تنظیم می‌کنیم
      const response = await axios.get('/api/year/get');
      this.date = response.data.now;
      this.paymentDate = response.data.now;

      if (this.isNewInvoice) {
        const draft = localStorage.getItem('sellInvoiceDraft');
        if (draft) {
          try {
            const parsedDraft = JSON.parse(draft);
            // بررسی اینکه آیا پیش‌نویس شامل داده‌های معتبر است
            const hasValidData = parsedDraft.items && 
                               parsedDraft.items.length > 0 && 
                               parsedDraft.items.some(item => item.name || item.count > 0 || item.price > 0);
            
            if (hasValidData) {
              this.showDraftDialog = true;
            } else {
              // اگر پیش‌نویس خالی است، آن را حذف می‌کنیم
              localStorage.removeItem('sellInvoiceDraft');
              this.addItem();
            }
          } catch (error) {
            // اگر پیش‌نویس معتبر نیست، آن را حذف می‌کنیم
            localStorage.removeItem('sellInvoiceDraft');
            this.addItem();
          }
        } else {
          this.addItem();
        }
      } else {
        await this.loadInvoice(this.$route.params.id);
      }
      
      // در نهایت پرینترها را لود می‌کنیم
      await this.loadPrinters();

      // اضافه کردن event listener برای تشخیص خروج از صفحه
      window.addEventListener('beforeunload', this.handleBeforeUnload);

      // غیرفعال کردن حالت اولیه
      this.$nextTick(() => {
        this.isInitializing = false;
      });
    } catch (error) {
      console.error('خطا در بارگذاری اولیه:', error);
      this.error = 'خطا در بارگذاری اطلاعات';
      this.showError = true;
    }
  },
  beforeUnmount() {
    // حذف event listener هنگام خروج از کامپوننت
    window.removeEventListener('beforeunload', this.handleBeforeUnload);
  },
  methods: {
    validateForm() {
      this.validationErrors = [];
      this.showValidationErrors = false;

      if (!this.customer) {
        this.validationErrors.push('لطفا خریدار را انتخاب کنید');
      }

      if (!this.invoiceDate) {
        this.validationErrors.push('لطفا تاریخ را انتخاب کنید');
      }

      if (this.items.length === 0) {
        this.validationErrors.push('لطفا حداقل یک کالا اضافه کنید');
      } else {
        this.items.forEach((item, index) => {
          if (!item.name) {
            this.validationErrors.push(`لطفا کالای ردیف ${index + 1} را انتخاب کنید`);
          }
          if (!item.count || item.count <= 0) {
            this.validationErrors.push(`لطفا تعداد کالای ردیف ${index + 1} را وارد کنید`);
          }
          if (!item.price || item.price <= 0) {
            this.validationErrors.push(`لطفا قیمت کالای ردیف ${index + 1} را وارد کنید`);
          }
        });
      }

      if (this.validationErrors.length > 0) {
        this.showValidationErrors = true;
      }

      return this.validationErrors.length === 0;
    },
    async loadInvoice(id) {
      try {
        this.loading = true;
        const response = await axios.get(`/api/sell/v2/get/${id}`);
        
        if (response.data.result !== 1) {
          throw new Error(response.data.message || 'خطا در بارگذاری فاکتور');
        }

        const data = response.data.data;

        this.invoiceDate = data.date;
        this.customer = data.person.id;
        this.invoiceDescription = data.des;
        this.taxPercent = data.taxPercent;
        this.totalDiscount = Number(data.totalDiscount);
        this.totalDiscountPercent = Number(data.discountPercent);
        this.showTotalPercentDiscount = data.discountType === 'percent';
        this.shippingCost = Number(data.shippingCost);
        this.totalInvoice = Number(data.totalInvoice);
        this.finalTotal = Number(data.finalTotal);

        this.items = data.items.map(item => ({
          name: {
            id: item.name.id,
            name: item.name.name,
            code: item.name.code
          },
          count: Number(item.count),
          price: Number(item.price),
          discountPercent: Number(item.discountPercent),
          discountAmount: Number(item.discountAmount),
          total: Number(item.total),
          description: item.description,
          showPercentDiscount: item.showPercentDiscount,
          tax: Number(item.tax)
        }));

        // بارگذاری اسناد پرداخت
        this.paymentItems = data.payments.map(payment => ({
          type: payment.type,
          amount: Number(payment.amount),
          reference: payment.reference,
          description: payment.description,
          bank: payment.bank,
          cashdesk: payment.cashdesk,
          salary: payment.salary
        }));

        this.recalculateTotals();
        this.calculatePayments();
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'خطا در بارگذاری فاکتور';
        this.showError = true;
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async saveInvoice() {
      if (!this.validateForm()) {
        return;
      }

      try {
        this.loading = true;
        const data = {
          id: this.$route.params.id || '',
          invoiceDate: this.invoiceDate,
          customer: this.customer,
          invoiceDescription: this.invoiceDescription,
          totalInvoice: this.totalInvoice,
          taxPercent: this.taxPercent,
          discountType: this.showTotalPercentDiscount ? 'percent' : 'fixed',
          discountPercent: this.showTotalPercentDiscount ? this.totalDiscountPercent : null,
          totalDiscount: this.showTotalPercentDiscount ? 0 : this.totalDiscount,
          shippingCost: this.shippingCost,
          showTotalPercentDiscount: this.showTotalPercentDiscount,
          items: this.items.map(item => ({
            name: { id: item.name.id },
            count: item.count,
            price: item.price,
            discountType: item.showPercentDiscount ? 'percent' : 'fixed',
            discountPercent: item.showPercentDiscount ? item.discountPercent : null,
            discountAmount: item.showPercentDiscount ? 0 : item.discountAmount,
            description: item.description,
            showPercentDiscount: item.showPercentDiscount,
            total: item.total,
            tax: item.tax || 0
          })),
          sendSmsToCustomer: this.sendSmsToCustomer,
          payments: this.paymentItems.map(payment => ({
            type: payment.type,
            amount: payment.amount,
            reference: payment.reference,
            description: payment.description,
            bank: payment.bank,
            cashdesk: payment.cashdesk,
            salary: payment.salary
          }))
        };

        const response = await axios.post('/api/sell/v2/mod', data);
        
        if (response.data.result === 1) {
          // حذف پیش‌نویس پس از ذخیره موفق
          localStorage.removeItem('sellInvoiceDraft');
          this.hasChanges = false;
          
          this.successMessage = this.$route.params.id ? 'فاکتور با موفقیت ویرایش شد' : 'فاکتور با موفقیت ثبت شد';
          this.showSuccess = true;

          // اگر گزینه خروجی PDF فعال باشد
          if (this.generatePdf) {
            try {
              const printResponse = await axios.post('/api/sell/print/invoice', {
                code: response.data.data.code,
                pdf: true,
                printers: this.cloudPrint,
              });
              
              if (printResponse.data && printResponse.data.id) {
                // باز کردن PDF در پنجره جدید
                window.open(this.$API_URL + '/front/print/' + printResponse.data.id, '_blank', 'noreferrer');
              } else {
                throw new Error('خطا در دریافت شناسه چاپ');
              }
            } catch (printError) {
              console.error('خطا در چاپ فاکتور:', printError);
              this.error = 'خطا در ایجاد نسخه PDF';
              this.showError = true;
            }
          }

          setTimeout(() => {
            this.$router.push('/acc/sell/list');
          }, 1500);
        } else {
          this.error = response.data.message || 'خطا در ذخیره فاکتور';
          this.showError = true;
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'خطا در ذخیره فاکتور';
        this.showError = true;
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async deleteInvoice() {
      this.deleteDialog = true;
    },
    async confirmDelete() {
      try {
        this.loading = true;
        await axios.delete(`/api/invoice/delete/${this.$route.params.id}`);
        this.$router.push('/acc/sell/list');
      } catch (error) {
        this.error = 'خطا در حذف فاکتور';
        console.error(error);
      } finally {
        this.loading = false;
        this.deleteDialog = false;
      }
    },
    addItem() {
      this.items.push({
        name: null,
        count: 0,
        price: 0,
        discountPercent: 0,
        discountAmount: 0,
        total: 0,
        description: '',
        showPercentDiscount: this.showPercentDiscount
      });
      this.recalculateTotals();
    },
    handleCommodityChange(item) {
      if (item.name && item.name.priceSell !== undefined) {
        item.price = Number(item.name.priceSell);
        this.recalculateTotals();
      }
    },
    removeItem(index) {
      this.items.splice(index, 1);
      this.recalculateTotals();
    },
    recalculateTotals() {
      let total = 0;
      this.items.forEach(item => {
        const basePrice = (item.count || 0) * (item.price || 0);
        let totalDiscount = 0;

        if (item.showPercentDiscount) {
          totalDiscount = Math.round((basePrice * (item.discountPercent || 0)) / 100);
        } else {
          totalDiscount = item.discountAmount || 0;
        }

        if (totalDiscount > basePrice) {
          item.discountPercent = 0;
          item.discountAmount = 0;
          this.showError = true;
        }

        const itemTotal = basePrice - totalDiscount;
        item.total = itemTotal;
        total += itemTotal;
      });
      this.totalInvoice = total;

      // محاسبه مالیات برای هر آیتم
      this.items.forEach(item => {
        item.tax = Math.round((item.total * this.taxPercent) / 100);
      });

      // محاسبه کل مالیات
      this.taxAmount = this.items.reduce((sum, item) => sum + (item.tax || 0), 0);
      this.totalWithoutTax = total;

      let calculatedTotalDiscount = 0;
      if (this.showTotalPercentDiscount) {
        calculatedTotalDiscount = Math.round((total * this.totalDiscountPercent) / 100);
      } else {
        calculatedTotalDiscount = this.totalDiscount;
      }

      if (calculatedTotalDiscount > total) {
        this.totalDiscountPercent = 0;
        this.totalDiscount = 0;
        this.showDiscountError = true;
      }

      this.finalTotal = total + this.taxAmount - calculatedTotalDiscount + this.shippingCost;
      this.totalAmount = this.finalTotal;
      this.calculatePayments();
    },
    handleDiscountTypeChange(item) {
      if (item.showPercentDiscount) {
        item.discountAmount = 0;
      } else {
        item.discountPercent = 0;
      }
      this.recalculateTotals();
    },
    calculatePayments() {
      const total = this.paymentItems.reduce((sum, item) => {
        const amount = item.amount !== null && item.amount !== undefined ? Number(item.amount) : 0;
        return sum + amount;
      }, 0);

      this.totalPayments = total;
      this.remainingAmount = this.totalAmount - total;
    },
    fillWithTotal(item) {
      const total = this.paymentItems.reduce((sum, i) => {
        if (i !== item) {
          const amount = i.amount !== null && i.amount !== undefined ? Number(i.amount) : 0;
          return sum + amount;
        }
        return sum;
      }, 0);

      item.amount = this.totalAmount - total;
      this.calculatePayments();
    },
    deletePaymentItem(index) {
      this.paymentItems.splice(index, 1);
      this.calculatePayments();
    },
    addPaymentItem(type) {
      let item = {
        type,
        amount: 0,
        reference: '',
        description: '',
        bank: null,
        cashdesk: null,
        salary: null
      };

      this.paymentItems.push(item);
      this.showPaymentMenu = false;
      this.calculatePayments();
    },
    async loadPrinters() {
      try {
        const response = await axios.get('/api/printers/list');
        this.printers = response.data || [];
      } catch (error) {
        console.error('خطا در دریافت لیست پرینترها:', error);
      }
    },
    saveDraft() {
      if (!this.isNewInvoice) return;
      
      // بررسی اینکه آیا داده‌های معتبری برای ذخیره وجود دارد
      const hasValidData = this.items && 
                          this.items.length > 0 && 
                          this.items.some(item => item.name || item.count > 0 || item.price > 0);
      
      if (hasValidData) {
        const draft = {
          invoiceDate: this.invoiceDate,
          customer: this.customer,
          invoiceDescription: this.invoiceDescription,
          taxPercent: this.taxPercent,
          totalDiscount: this.totalDiscount,
          totalDiscountPercent: this.totalDiscountPercent,
          showTotalPercentDiscount: this.showTotalPercentDiscount,
          shippingCost: this.shippingCost,
          items: this.items,
          paymentItems: this.paymentItems
        };
        
        localStorage.setItem('sellInvoiceDraft', JSON.stringify(draft));
      } else {
        // اگر داده‌های معتبری وجود ندارد، پیش‌نویس را حذف می‌کنیم
        localStorage.removeItem('sellInvoiceDraft');
      }
    },
    loadDraft() {
      try {
        this.isInitializing = true;
        const draft = JSON.parse(localStorage.getItem('sellInvoiceDraft'));
        if (draft) {
          // تنظیم مقادیر اصلی
          this.invoiceDate = draft.invoiceDate;
          this.customer = draft.customer;
          this.invoiceDescription = draft.invoiceDescription;
          this.taxPercent = draft.taxPercent;
          this.totalDiscount = draft.totalDiscount;
          this.totalDiscountPercent = draft.totalDiscountPercent;
          this.showTotalPercentDiscount = draft.showTotalPercentDiscount;
          this.shippingCost = draft.shippingCost;

          // تنظیم آیتم‌های فاکتور
          this.items = draft.items.map(item => ({
            ...item,
            name: item.name ? {
              id: item.name.id,
              name: item.name.name,
              code: item.name.code,
              priceSell: item.name.priceSell
            } : null,
            count: Number(item.count) || 0,
            price: Number(item.price) || 0,
            discountPercent: Number(item.discountPercent) || 0,
            discountAmount: Number(item.discountAmount) || 0,
            total: Number(item.total) || 0,
            description: item.description || '',
            showPercentDiscount: item.showPercentDiscount || false
          }));

          // تنظیم آیتم‌های پرداخت
          this.paymentItems = draft.paymentItems.map(payment => ({
            type: payment.type,
            amount: Number(payment.amount) || 0,
            reference: payment.reference || '',
            description: payment.description || '',
            bank: payment.bank || null,
            cashdesk: payment.cashdesk || null,
            salary: payment.salary || null
          }));

          // محاسبه مجدد مبالغ
          this.$nextTick(() => {
            this.recalculateTotals();
            this.calculatePayments();
            this.isInitializing = false;
          });
        }
      } catch (error) {
        console.error('خطا در بارگذاری پیش‌نویس:', error);
        this.error = 'خطا در بارگذاری پیش‌نویس';
        this.showError = true;
        this.isInitializing = false;
      }
      this.showDraftDialog = false;
    },
    discardDraft() {
      localStorage.removeItem('sellInvoiceDraft');
      this.showDraftDialog = false;
      this.addItem();
    },
    handleBeforeUnload(event) {
      if (this.isNewInvoice && this.hasChanges) {
        event.preventDefault();
        event.returnValue = '';
      }
    }
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