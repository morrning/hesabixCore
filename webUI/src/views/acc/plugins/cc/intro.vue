<script lang="ts">
import {defineComponent} from 'vue'
import {getApiUrl, getSiteName} from '@/hesabixConfig'
export default defineComponent({
  name: "intro",
  data:()=>{return{
    siteName:''
  }},
  created(){
    this.siteName = getSiteName();
  }
})
</script>

<template>
  <main id="main-container pt-0 mt-o">
    <!-- Hero -->
    <div class="bg-image" style="background-image: url('/img/plugins/club.webp');">
      <div class="bg-black-75">
        <div class="content content-top content-full text-center">
          <h1 class="text-white"><i class="fa fa-bowl-food"></i></h1>
          <h1 class="fw-bold text-white mt-5 mb-3"> افزونه باشگاه مشتریان </h1>
          <h2 class="h3 fw-normal text-white-75 mb-5">با افزونه باشگاه مشتریان هیچ گاه مشتریان وفادار خود را از دست نخواهید داد.</h2>
          <RouterLink to="/acc/plugin-center/view-end/noghre">
                <span class="badge rounded-pill bg-primary fs-base px-3 py-2 me-2 m-1">
                  <i class="fa fa-user-circle me-1"></i> خرید </span>
          </RouterLink>
          <br>
          <i class="fa fa-arrow-down text-white"></i>
        </div>
      </div>
    </div>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content content-full">
      <div class="row justify-content-center">
        <div class="col-sm-11 py-2">
          <!-- Story -->
          <article class="story justify-content-between">
            <p class=" justify-content-between">
              نرم افزار حسابداری رستوران از نرم افزارهای است که مدیریت رستوران و محاسبات مالی مربوط به آن را ساده‌تر می‌کند. از ویژگی‌های بهترین برنامه حسابداری رستوران استفاده آسان کاربری است. این نرم افزار طوری طراحی شده است که کاربر با هر سطح سوادی می‌تواند از آن استفاده کند و همه آیتم‌ها مثل تنظیم ستون‌ها برای کاربران قابل تنظیم است. برای مدیریت یک رستوران و صندوق فروشگاهی آن، می‌توان در نرم افزار شماره میز را وارد کرد و سفارش مشتری را ثبت کرد.
            </p>
            <p>
              برنامه حسابداری رستوران نوعی نرم افزار است که بسیاری از دغدغه‌های موجود در این صنف را از بین برده و باعث پیشرفت کسب و کارها در این صنف شده است. با استفاده از نرم افزار حسابداری مخصوص رستوران کارهای مربوط به ثبت سفارش مشتریان و صدور فاکتور با سرعت بیشتری انجام خواهد گرفت. با جلب رضایت بالای مشتریان امکان مراجعه مجدد مشتریان و سوددهی بیشتر فراهم خواهد شد.
            </p>
            <p>
              از دیگر دغدغه‌های موجود در این صنف دسته بندی کالاها بر اساس مشخصات آنها در انبارهاست. کاربران با مشخصاتی که مانند تاریخ انقضای کالاها می‌توانند آنها را دسته بندی کرده و به این ترتیب کالاهایی مانند لبنیات را که زودتر فاسد می‌شوند را به ترتیب اولویت زودتر به مصرف رسانده و از فاسد شدن و دور ریختن کالاها جلوگیری می‌شود. این مشخصه برای صاحبان کسب و کارهایی که با مواد غذایی سرو کار دارند و در این صنف هستند، قابلیت بسیار مهمی به‌شمار می‌رود. به‌این ترتیب از هدر رفت بسیاری از مواد غذایی و هزینه‌ای که صرف تامین آنها شده است، جلوگیری خواهد شد.
            </p>
            <p>
              نرم افزار حسابداری رستوران حرفه‌ای بایستی قابلیت اتصال به سیستم کالر آیدی و نرم افزار CRM را داشته باشد. از طریق اتصال به نرم افزار CRM یا نرم افزرا ارتباط با مشتریان به‌راحتی می‌توان مشتریان را از روند کاری رستوران، تخفیفات و جشنواره‌ها آگاه نمود. همچنین از داشتن رضایت یا انتقادات آنها نیز می‌توان آگاه شد، تا درجهت بهبود کسب و کار رستوران تلاش‌های بیشتری نمود. با استفاده از این نرم افزار می‌توان اطلاعات تماسی مشتریان را می‌توان ثبت کرده و با ارسال پیامک و ایمیل ایجاد تعامل نمود. با اتصال سیستم کالر آیدی به نرم افزار می‌توان برای مشتریان عزیز، شماره اشتراک تعریف کرد و در زمان تماس و ثبت سفارش دیگر نیازی به گرفتن اطلاعات تماس و آدرس از مشتریان نخواهید بود. به‌این ترتیب روند ثبت سفارش با سرعت بیشتری انجام شده و رضایت خاطر مشتریان نیز فراهم خواهد شد.
            </p>
            <p>
              حسابداری ثبت هر واقعه‌ای است که در فرایند کسب و کار اتفاق می‌افتد و تقریبا همه فرایندهای کسب و کار با پول درگیر است؛ پس باید در زمینه امور مالی و حسابداری کسب و کاری مثل رستوران از نرم افزاری استفاده کرد که به‌طور تخصصی برای این صنف طراحی شده باشد تا بتوان از بروز خطاهای انسانی در وارد کردن ناصحیح اطلاعات جلوگیری کرد. امروزه نرم افزارهای حسابداری رستوران زیادی در بازار وجود دارند که بعضا قیمت‌های پایینی دارند اما تجربه نشان داده است که این نرم افزارها ممکن است ایراداتی همچون کیفیت و کارایی پایین، عدم پشتیبانی طولانی مدت، عدم بروز رسانی و امنیت پایین را داشته باشند، که نرم افزار حسابداری رستوران رستامپ این مشکلات را از بین برده و مدیران رستوران‌ها می‌توانند نسبت به خرید آن با اطمینان خاطر اقدام کنند.
            </p>
            <p>
              در این نرم افزار که به صورت افزونه بر روی نرم افزار تحت وب و کاملا رایگان {{ siteName }} منتشر شده است لازم نیست هیچ گونه نرم افزاری را نصب کنید و تمام اطلاعات بر روی سرورهای ابری ذخیره می شود. شما در هر لحظه و هر مکان به اطلاعات خود دسترسی خواهید داشت. همچنین هزینه های نگهداری نرم افزارهای لوکال را نیز نخواهید داشت.
            </p>
            <div class="block block-bordered js-classic-nav d-none d-sm-block">
              <div class="block-content block-content-full">
                <div class="row g-0 border">
                  <div class="col-sm-6 col-xl-3">
                    <a class="block block-bordered block-link-pop text-center mb-0" href="javascript:void(0)">
                      <div class="block-content block-content-full text-center">
                        <div class="py-2">
                          <i class="far fa-2x fa-compass text-primary d-none d-sm-inline-block mb-3"></i>
                          <div> تعداد نصب فعال </div>
                          <div class="text-primary">712</div>
                        </div>
                      </div>
                    </a>
                  </div>
                  <div class="col-sm-6 col-xl-3">
                    <RouterLink to="/acc/plugin-center/view-end/cc" class="block block-bordered block-link-pop text-center mb-0">
                      <div class="block-content block-content-full text-center">
                        <div class="py-2">
                          <i class="fa fa-2x fa-shopping-basket text-primary d-none d-sm-inline-block mb-3"></i>
                          <div>سفارش</div>
                          <div class="text-primary">فعال سازی آنی</div>
                        </div>
                      </div>
                    </RouterLink>
                  </div>
                  <div class="col-sm-6 col-xl-3">
                    <a class="block block-bordered block-link-pop text-center mb-0" href="https://app.hesabix.ir/profile/support-new">
                      <div class="block-content block-content-full text-center">
                        <div class="py-2">
                          <i class="far fa-2x fa-user-circle text-primary d-none d-sm-inline-block mb-3"></i>
                          <div> مشاوره </div>
                          <div class="text-primary">تیکت پشتیبانی</div>
                        </div>
                      </div>
                    </a>
                  </div>
                  <div class="col-sm-6 col-xl-3">
                    <a class="block block-bordered block-link-pop text-center mb-0" href="javascript:void(0)">
                      <div class="block-content block-content-full text-center">
                        <div class="py-2">
                          <i class="far fa-2x fa-edit text-primary d-none d-sm-inline-block mb-3"></i>
                          <div> پشتیبانی تلفنی </div>
                          <div class="text-primary">0918 328 2405</div>

                        </div>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </article>
          <!-- END Story -->
        </div>
      </div>
    </div>
    <!-- END Page Content -->
  </main>
</template>

<style scoped>

</style>