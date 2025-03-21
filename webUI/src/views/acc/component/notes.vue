<script lang="ts">
import axios from 'axios';
import { defineComponent } from 'vue'
import Swal from "sweetalert2";
import { ref } from 'vue';
import Loading from 'vue-loading-overlay';

export default defineComponent({
  name: "notes",
  components:{
    Loading
  },
  props: {
    stat: Object,
    code: String,
    typeNote: String
  },
  data: () => {
    return {
      loading: ref(true),
      items: [],
      des: '',
    }
  },
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/notes/list', {
        type: this.$props.typeNote,
        code: this.$props.code
      }).then((response) => {
        this.items = response.data;
        this.$props.stat.count = response.data.length;
        this.loading = false;
      });
    },
    remove(id) {
      this.loading = true;
      axios.post('/api/notes/remove/' + id).then((response) => {
        this.loading = false;
        Swal.fire({
          text: ' با موفقیت حذف شد.',
          icon: 'success',
          confirmButtonText: 'قبول'
        }).then((result) => {
          this.loadData();
        });
      })
    },
    save() {
      if (this.des.trim() == '') {
        Swal.fire({
          text: 'شرح الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        })
      }
      else {
        this.loading = true;
        axios.post('/api/notes/add', {
          des: this.des,
          type: this.$props.typeNote,
          code: this.$route.params.id
        }).then((response) => {
          this.loading = false;
          Swal.fire({
            text: ' با موفقیت ثبت شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then((result) => {
            this.loadData();
            this.des = '';
          });
        })
      }

    }
  },
  mounted() {
    this.loadData();
  }
})
</script>

<template>
  <!-- Modal -->
  <div class="modal modal-lg fade" id="notesModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-warning text-white">
          <h1 class="modal-title fs-5" id="notesModalLabel">
            <i class="fa-regular fa-note-sticky me-1"></i>
            یاداشت‌ها
          </h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="input-group mb-3">
            <input v-model="des" type="text" class="form-control" placeholder="شرح" aria-label="افزودن یاداشت"
              aria-describedby="button-addon1">
            <button :disabled="this.loading" @click="save()" class="btn btn-outline-success" type="button"
              id="button-addon1">ثبت</button>
          </div>
          <Loading color="blue" loader="dots" v-model:active="loading" :is-full-page="false"/>
          <ul :disabled="this.loading" class="list-group">
            <li v-for="item in items" class="list-group-item d-flex justify-content-between align-items-center">
              <span class="">
                {{ item.des }}
              </span>
              <a title="حذف" @click="remove(item.id)" class="text-danger rounded-pill float-start">
                <i class="fa fa-trash"></i>
              </a>
            </li>
          </ul>
          <div v-if="items.length == 0">
            نتیجه‌ای یافت نشد
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>