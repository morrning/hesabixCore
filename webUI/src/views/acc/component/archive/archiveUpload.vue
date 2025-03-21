<script lang="ts">
import {defineComponent} from 'vue'
import axios from "axios";
import Swal from "sweetalert2";

export default defineComponent({
  name: "archiveUpload",
  props:{
    doctype: String,
    docid: [String ,Number],
    cat: String
  },
  data() {
    return {
      fileStack:[],
      des:'',
      media: {
        saved: [],
        added: [],
        removed: []
      }
    }
  },
  mounted() {
    this.getFilesList();
  },
  methods:{
    changeMedia(media){
      this.media = media
    },
    addMedia(addedImage, addedMedia){
      this.media.added = addedMedia
    },
    removeMedia(removedImage, removedMedia){
      this.media.removed = removedMedia
    },
    getFilesList(){
      axios.post('api/archive/files/list',{
        id:this.$props.docid,
        type:this.$props.doctype
      }).then((resp)=>{
        this.media.added =[];
        this.fileStack = resp.data;
        this.fileStack.forEach((item)=>{
          axios.post(this.$filters.getApiUrl() + '/api/archive/file/get/' + item.id, { responseType: "arraybuffer" })
              .then((response)=>{
                const b64 = btoa(String.fromCharCode(...new Uint8Array(response.data)));
                item.fileBin = "data:" + response.headers['content-type'] + ";base64," + b64;
              });
        })
      })
    },
    deleteItem(item){
      Swal.fire({
        text: 'آیا برای حذف فایل مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('api/archive/file/remove/' + item.id).then((response)=>{
            if(response.data.result == 1){
              this.getFilesList();
              Swal.fire({
                text: 'فایل با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    },
    downloadFile(item){
      axios.post(this.$filters.getApiUrl() + '/api/archive/file/get/' + item.id, { responseType: "arraybuffer" })
          .then(response => {
            const blob = new Blob([response.data], {type: item.fileType});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = item.filename;
            link.click();
            URL.revokeObjectURL(link.href);
          });
    },
    submitArchive(){
      let formData = new FormData(document.getElementById('archive-file-upload'))
      axios.post('api/archive/file/save',formData,{
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then((resp)=>{
        if(resp.data.result === 'nem'){
          Swal.fire({
            text: 'فضای کافی وجود ندارد لطفا حساب کاربری خود را شارژ نمایید.',
            icon: 'success',
            confirmButtonText: 'قبول'
          });
        }else{
          Swal.fire({
            text: 'فایل‌های انتخابی ذخیره شدند.',
            icon: 'success',
            confirmButtonText: 'قبول'
          });
          this.getFilesList();
        }
      });
    }
  },
})
</script>

<template>
  <!-- Button trigger modal -->
  <button type="button" class="btn btn-sm btn-outline-success mx-2" data-bs-toggle="modal" data-bs-target="#archiveModal">
    <span class="badge text-bg-dark me-2">{{fileStack.length}}</span>
    <i class="fa fa-file me-1"></i>
    <span class="d-none d-sm-inline-block">آرشیو</span>
  </button>
  <!-- Modal -->
  <div class="modal modal-lg fade" data-bs-backdrop="static" data-bs-keyboard="false" id="archiveModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">آرشیو فایل</h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <h5 class="text-primary-dark mt-3 my-0 py-0">افزودن فایل جدید</h5>
          <div>
            <form id="archive-file-upload" @submit.prevent="submitArchive()">
              <input type="hidden" name="doctype" :value="$props.doctype">
              <input type="hidden" name="docid" :value="$props.docid">
              <input type="hidden" name="cat" :value="$props.cat">
              <Uploader
                  :server="$filters.getApiUrl() + '/api/archive/file/upload'"
                  :media="media.saved"
                  path="/storage/media"
                  @add="addMedia"
                  @remove="removeMedia"
                  @change="changeMedia"
                  :maxFilesize="5"
              />
              <div class="container-fluid mt-2">
                <div class="row">
                  <div class="col-9">
                    <input class="form-control" type="text" name="des" v-model="des" placeholder="توضیحات" aria-label="توضیحات">
                  </div>
                  <div class="col-3">
                    <button type="submit" class="btn btn-success d-flex">
                      <i class="fa fa-save me-2"></i>
                      بارگذاری فایل‌ها
                    </button>
                  </div>
                </div>
              </div>

            </form>
          </div>
          <hr>
          <h5 class="text-primary-dark mt-3 mb-0 pb-0">آرشیو فایل‌ها</h5>
          <table class="table table-striped table-hover table-borderless table-vcenter fs-sm text-center">
            <thead>
            <tr class="text-uppercase">
              <th>پیش نمایش</th>
              <th>نام فایل</th>
              <th class="">سایز فایل(مگابایت)</th>
              <th class="">تاریخ</th>
              <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in fileStack">
              <td>
                <img class="img-fluid" :src="item.fileBin" alt="پیش نمایش">
              </td>
              <td>
                <span class="fw-semibold">{{item.filename}}</span>
              </td>
              <td>
                <span>{{item.filesize}}</span>
              </td>
              <td>
                <span>{{item.dateSubmit}}</span>
              </td>
              <td class="text-center text-nowrap fw-medium">
                <a class="btn btn-sm btn-link" href="/" @click.prevent="downloadFile(item)">
                  <i class="fa fa-download"></i>
                </a>
                <button @click="deleteItem(item)" class="btn btn-sm ms-2 btn-link text-danger">
                  <i class="fa fa-trash"></i>
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بازگشت</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>