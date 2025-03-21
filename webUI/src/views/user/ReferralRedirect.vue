<template>
    <div>
    </div>
  </template>
  
  <script>
  export default {
    name: "ReferralRedirect",
    beforeRouteEnter(to, from, next) {
      const referralCode = to.params.param;
      if (referralCode) {
        localStorage.setItem('inviteCode', referralCode);
      }
      next('/user/register');
    }
  };
  </script>
  
  <style scoped></style>