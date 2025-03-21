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
  <main id="main-container p-0 m-0">
    <!-- Hero -->
    <div class="bg-image" style="background-image: url('/img/plugins/apartemanma/drawer.jpg');">
      <div class="bg-black-75">
        <div class="content content-top content-full text-center">
          <h1 class="text-white"><i class="fa fa-shop"></i></h1>
          <h1 class="fw-bold text-white mt-5 mb-3"> افزونه مدیریت و جمع آوری شارژ مجتمع های مسکونی و تجاری </h1>
          <h2 class="h3 fw-normal text-white-75 mb-5">مدیریت خود بر هزینه های ساختمان را چند برابر کنید</h2>
          <RouterLink to="/acc/plugin-center/view-end/apartemanma">
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
                    افزونه مدیریت و جمع آوری شارژ مجتمع های مسکونی و تجاری 
                  </h3>
                  <p>
                    اگر در محاسبه مبلغ شارژ و هزینه‌های ساختمان و مجتمع دچار مشکل هستید کافی است انجام این کار را به افزونه آپارتمان من بسپارید.
                  </p>
                  <p>
                     
                    با این افزونه میتوانید علاوه بر محاسبه اتوماتیک مبلغ شارژ کلیه صورت حساب ها را از طریق پیامک و شبکه های اجتماعی به مالکین و مستاجران ارسال کنید و هزینه را نیز به صورت آنلاین دریافت کنید.
                     
                  </p>
                  <p>در تمام مسیر تیم {{ siteName }} با پیشتیبانی دائمی و مشاوره‌ حرفه‌ای در کنار شما خواهند بود تا بتوانید به بهترین شکل ممکن هزینه‌های آپارتمان و مجتمع خود را مدیریت کنید.</p>
            
                </div>
                <div class="col-sm-12 col-md-6">
                  <img class="img-fluid" src="/img/plugins/apartemanma/intro.jpg" />
                </div>
              </div>
              <h2 class="text-primary">امکانات:</h2>
              <ul>
                <li>قابلیت تفکیک واحدها بر اساس بلوک و نام واحد</li>
                <li>قابلیت تفکیک مالکین و ساکنین و اعلام هزینه بر اساس هر کدام</li>
                <li>ارسال پیامک و اعلام شارژ به افراد</li>
                <li>قابلیت پرداخت آنلاین و وصول مبلغ شارژ</li>
                <li>یکپارچه با حسابیکس</li>
                <li>قابلیت تفکیک هزینه‌ها برای هر گروه به صورت مجزا</li>
                <li>ارسال پیامک هشدار عدم پرداخت شارژ به صورت اتوماتیک</li>
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

<style scoped>

</style>