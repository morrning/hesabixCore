<template>
  <v-app id="">
    <v-main class="bg-grey-lighten-3 d-flex flex-column align-center justify-center">
      <v-container class="d-flex flex-column align-center justify-center">
        <v-row class="d-flex flex-column align-center justify-center">
          <img src="/img/logo-blue.png" width="120 " class="mt-2 mb-5 p-1" alt="" />
          <h4 class="text-center mt-5 px-8">
            نسخه وب اپلیکیشن (PWA) حسابیکس را به صفحه اصلی اضافه کنید.
          </h4>
          <p class="text-center mt-5 px-5">با این کار، می‌توانید برای همیشه و بدون نیاز به بروزرسانی از خدمات اپلیکیشن استفاده کنید.</p>
          <p class="text-center mt-5 px-5" v-if="chromeBanner">ابتدا از دکمه <span class="font-weight-bold text-indigo-darken-3">نصب</span> استفاده کنید و سپس <span class="font-weight-bold text-indigo-darken-3">Add</span> را بزنید. اگر برنامه نصب نشد مراحل بعدی را انجام دهید.</p>
        </v-row>
        <v-row class="d-flex flex-column align-center justify-center w-100">
          <v-col md="4">
            <!-- pwa install banner -->
            <v-card v-if="chromeBanner" class="d-flex justify-center rounded-lg p-2 mt-2">
              <v-row class="">
                <v-col md="8" class="d-flex pr-5">
                  <img
                    src="/img/logo-blue.png"
                    width="38"
                    class="mt-2 mb-2"
                    alt=""
                  />
                  <h4 class="text-primary pr-2 pt-4">{{ $t("app.name") }}</h4>
                </v-col>
                <v-col md="4" class="pl-5">
                  <v-btn
                    block
                    class="text-none mt-2 mb-2"
                    color="indigo-darken-3"
                    variant="flat"
                    @click="callInstallbtn"
                  >
                    نصب
                  </v-btn>
                </v-col>
              </v-row>
            </v-card>
            <v-card v-if="iosChecker" class="rounded-lg d-flex flex-column ga-4 pa-4 mt-2">
                <span>۱. دکمه <span class="font-weight-bold text-indigo-darken-3">Share</span> (<v-icon icon="mdi-export-variant" color="indigo-darken-3"></v-icon>) در نوار پایین را انتخاب کنید.</span>
                <span>۲. گزینه <span class="font-weight-bold text-indigo-darken-3">Add to Home Screen</span> (<v-icon icon="mdi-plus-box-outline" color="indigo-darken-3"></v-icon>)  را انتخاب کنید.</span>
                <span>۳. در قسمت بالا سمت راست <span class="font-weight-bold text-indigo-darken-3">Add</span> را انتخاب کنید.</span>
            </v-card>
            <v-card v-else class="rounded-lg d-flex flex-column ga-4 pa-4 mt-2">
                <span>۱. دکمه <span class="font-weight-bold text-indigo-darken-3">More</span> (<v-icon icon="mdi-dots-vertical" color="indigo-darken-3"></v-icon>) در نوار بالا را انتخاب کنید.</span>
                <span>۲. گزینه <span class="font-weight-bold text-indigo-darken-3">Add to Home Screen</span> (<v-icon icon="mdi-cellphone-arrow-down-variant" color="indigo-darken-3"></v-icon>)  را انتخاب کنید.</span>
                <span>۳. در پنجره‌ی باز شده، <span class="font-weight-bold text-indigo-darken-3">Add</span> را انتخاب کنید.</span>
            </v-card>
          </v-col>
        </v-row>

        <v-row>
          <v-btn
            block
            class="text-none my-4"
            color="indigo-darken-3"
            variant="flat"
            :to="{ name: 'user_login' }"
            >
              باز گشت به صفحه ورود
          </v-btn>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>
  
<script setup lang="ts">
import axios from "axios";
import { onMounted, ref } from "vue";

const installPromptEvent = ref<Event | null>(null);
const browserName = ref<string>("");
const chromeBanner = ref<boolean>(false);
const iosChecker = ref<boolean>(false);

// Call button method for install banner
const callInstallbtn = () => {
  if (installPromptEvent.value) {
    // Cast as BeforeInstallPromptEvent if needed
    const event = installPromptEvent.value as BeforeInstallPromptEvent;
    event.prompt();
    

    event.userChoice.then((choiceResult: { outcome: string }) => {
      if (choiceResult.outcome === "accepted") {
        console.log("User Accepted");
      } else {
        console.log("User dismissed");
      }

      installPromptEvent.value = null;
    });
  }
};

const getBrowserName = (): any => {
  const userAgent = navigator.userAgent;

  if (userAgent.includes("Chrome") && !userAgent.includes("Edg")) {
    if (userAgent.includes("Mac OS X")) {
      iosChecker.value = true
    } else{
      chromeBanner.value = true;
    }
    return "Google Chrome";
  } else if (userAgent.includes("Firefox")) {
    if (userAgent.includes("Mac OS X")) {
      iosChecker.value = true
    } 
    return "Mozilla Firefox";
  } else if (userAgent.includes("Safari") && !userAgent.includes("Chrome")) {
    iosChecker.value = true;
    chromeBanner.value = false;
    return "Apple Safari";
  } else if (userAgent.includes("SamsungBrowser")) {
    if (userAgent.includes("Mac OS X")) {
      iosChecker.value = true
    } else{
      chromeBanner.value = true;
    }
    return "Samsung Browser";
  } else if (userAgent.includes("Edg")) {
    if (userAgent.includes("Mac OS X")) {
      iosChecker.value = true
    } else{
      chromeBanner.value = true;
    }
    return "Microsoft Edge";
  } else if (userAgent.includes("Opera") || userAgent.includes("OPR")) {
    if (userAgent.includes("Mac OS X")) {
      iosChecker.value = true
    } else{
      chromeBanner.value = true;
    }
    return "Opera";
  } else {
    return "Unknown Browser";
  }
};

onMounted(() => {
  browserName.value = getBrowserName();

  window.addEventListener("beforeinstallprompt", (e: Event) => {
    e.preventDefault();
    installPromptEvent.value = e;
    console.log(installPromptEvent.value);
  });

  // setTimeout(() => {
  //   if (installPromptEvent.value) {
  //     installBanner.value = true;
  //   }
  // }, 1000);
});
</script>