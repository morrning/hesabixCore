<template>
  <div v-if="this.active" class="container-fluid rounded-2 border border-secondary p-3 my-2">
    <div class="row">
      <span class="text-end">
        <button @click="changeView()" class="btn btn-sm text-danger" to="/">
          <i class="fa fa-trash"></i>
        </button>
      </span>
    </div>
    <div class="row my-2">
      <div class="col-sm-12 col-md-8">
        <label class="fw-bold mb-2">شخص</label>
        <v-cob
            dir="rtl"
            :options="persons"
            label="labelName"
        >
          <template #option="{ nikename, code,mobile }">
            <div class="row">
              <img src="/img/avatar.png" class="col-2 img-fluid" />
              <div class="col-10">
                <div>{{ nikename }}</div>
                <em>({{ code }})</em>
                <span v-if="mobile != ''" class=""><i class="fa fa-mobile"></i> {{ mobile }}</span>
              </div>
            </div>
          </template>
          <template #no-options="{ search, searching, loading }">
           نتیجه‌ای یافت نشد!
          </template>
          <div slot="vs__no-options">No Options Her33333e!</div>
        </v-cob>
      </div>
      <div class="col-sm-12 col-md-4">
        <label class="fw-bold mb-2">مبلغ</label>
        <money3 v-model="amount" v-bind="currencyConfig"></money3> {{ amount }}
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12 col-md-12">
        <label class="fw-bold mb-2">شرح</label>
        <input class="form-control" />
      </div>
    </div>
  </div>
</template>

<script>

import axios from "axios";
export default {
  name: "person",
  components: {  },
  props: {
    initialPerson : String
  },
  data: ()=>{return {
    persons: [],
    amount: 0,
    des: '',
    active: true,
    currencyConfig:{
      masked: false,
      prefix: '',
      suffix: 'ریال',
      thousands: ',',
      decimal: '.',
      precision: 0,
      disableNegative: false,
      disabled: false,
      min: 0,
      max: null,
      allowBlank: false,
      minimumNumberOfCharacters: 0,
      shouldRound: true,
      focusOnRight: true,
    }
  }},
  created() {
    this.getData();
  },
  methods:{
    changeView(){
      alert(this.person)
    },
    getData(){
      axios.post('/api/person/list/limit').then((response)=>{
        this.persons = response.data;
        this.persons.forEach((item)=>{
          item.labelName = '(' + item.code + ')=>  ' +  item.nikename;
        })
      })
    }
  }
}
</script>

<style scoped>

</style>