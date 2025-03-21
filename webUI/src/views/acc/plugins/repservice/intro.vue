<script lang="ts">
import { defineComponent } from 'vue'
import { getApiUrl, getSiteName } from '@/hesabixConfig'
export default defineComponent({
  name: "intro",
  data: () => {
    return {
      siteName: ''
    }
  },
  created() {
    this.siteName = getSiteName();
  }
})
</script>

<template>
  <main id="main-container p-0 m-0">
    <!-- Hero -->
    <div class="bg-image" style="background-image: url('/img/plugins/repservice.png');">
      <div class="bg-black-75">
        <div class="content content-top content-full text-center">
          <h1 class="text-white"><i class="fa fa-shop"></i></h1>
          <h1 class="fw-bold text-white mt-5 mb-3">افزونه مدیریت تعمیرگاه</h1>
          <h2 class="h3 fw-normal text-white-75 mb-5">
            افزونه تعمیرکاران یکپارچه طراحی شد تا تمام نیازهای شما را در مدیریت تعمیرگاه و فروشگاه تان را بصورت کامل رفع
            کند.
          </h2>
          <RouterLink to="/acc/plugin-center/view-end/repservice">
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
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-sm-11 py-2">
          <!-- Story -->
          <article class="story justify-content-between">
            <div class="rounded-3 bg-white p-3 mb-3">
              <div class="row">
                <div class="col-sm-12 col-md-6">
                  <h3 class="text-primary mt-0">
                    افزونه مدیریت تعمیرگاه(تعمیرکاران)
                  </h3>
                  <p>
                    افزونه تعمیرکاران بهترین انتخاب برای مدیریت انواع مراکز تعمیر و خدمات و همچنین فروشگاه های در حال توسعه است؛ چرا که افزونه تعمیرکاران نیازی به نصب ندارد, همیشه با موبایل و کامپیوتر در دسترس است و رابط کاربری بسیار آسانی دارد و از همه مهمتر تمام نیازهای تعمیرگاه و فروشگاه را در قالب یک نرم افزار پشتیبانی می‌کند.
                  </p>


                </div>
                <div class="col-sm-12 col-md-6">
                  <img class="img-fluid" src="/img/plugins/repservice.jpg" />
                </div>
              </div>
              <h2 class="text-primary">امکانات:</h2>
              <ul>
                <li>
                  صدور قبض های پذیرش تعمیرگاه قابل چاپ و پیامکی
                </li>
                <li>
                  بدون نیاز به خرید سرشماره پیامک خدماتی
                </li>
                <li>
                  گزارشات کامل از دستگاه های ورودی و خروجی تعمیرگاه
                </li>
                <li>
                  ثبت قبض های پذیرش بصورت آنلاین با گوشی
                </li>
                <li>
                  ارسال پیامک خودکار به تعمیرکار موقع ثبت پذیرش دستگاه تعمیری
                </li>
                <li>
                  مشاهده لیست دستگاه های تعمیری
                </li>
                <li>
                  درج سریال/پلاک دستگاه های تعمیری درقبض پذیرش
                </li>
                <li>
                  ارسال پیامک خودکار هنگام تغییر وضعیت دستگاه به آماده تحویل
                </li>
                <li>
                  مشاهده فاکتور تعمیرگاه بصورت آنلاین بصورت پیامکی
                </li>
                <li>
                  قابلیت چاپ قبض تعمیرگاه
                </li>
                <li>
                  قابلیت ارایه خروجی اکسل از تمام گزارشات تعمیرگاه
                </li>
                <li>
                  یکپارچه با
                  {{ siteName }}
                  و بسته حسابداری پیشرفته
                </li>
              </ul>
            </div>
          </article>
          <!-- END Story -->
        </div>
      </div>
    </div>
    <!-- END Page Content -->
  </main>
</template>

<style scoped></style>