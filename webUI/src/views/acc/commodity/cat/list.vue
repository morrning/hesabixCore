<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button" class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        <i class="mx-2 fa fa-tree"></i>
        دسته‌بندی‌های کالا و خدمات </h3>
      <div class="block-options">
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <loading color="blue" loader="dots" v-model:active="isLoading" :is-full-page="false"/>
      <div class="alert alert-info">
        <div class="alert-heading">
          <i class="fa fa-warning"></i>
          هشدار</div>
        <div class="">در انتخاب نوع دسته بندی دقت کنید . تا انتشار به روز رسانی بعدی حذف دسته بندی تنها از طریق تیکت پشتیبانی قابل انجام است.</div>
      </div>
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 py-2 px-1 border border-1 rounded-2">
          <Tree
              :nodes="tree"
              :config="config"
          >
            <template #after-input="props">
              <button class="btn btn-link" type="button" @click="addChild(props.node.id)">
                <i class="fa fa-plus-circle"></i>
              </button>
              <button class="btn btn-link text-warning" type="button" @click="editeNode(props.node)">
                <i class="fa fa-edit"></i>
              </button>
            </template>
          </Tree>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal add child -->
  <div class="modal fade" id="ModalAdd" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="staticBackdropLabel">افزودن دسته‌بندی</h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="form-floating mb-4">
            <input v-model="addCatText" class="form-control" type="text">
            <label class="form-label"><span class="text-danger">(لازم)</span> نام دسته بندی</label>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="this.submitChild" type="button" class="btn btn-primary">
            <i class="fa fa-save"></i>
            ثبت</button>
          <button id="modalAddClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa fa-close"></i>
            انصراف</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal edit node -->
  <div class="modal fade" id="ModalEdit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="staticBackdropLabel">ویرایش دسته‌بندی</h1>
          <div class="block-options">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <div class="modal-body">
          <div class="form-floating mb-4">
            <input v-model="editCatText" class="form-control" type="text">
            <label class="form-label"><span class="text-danger">(لازم)</span> نام دسته بندی</label>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="this.submitEditChild" type="button" class="btn btn-primary">
            <i class="fa fa-save"></i>
            ثبت</button>
          <button id="modalEditClose" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa fa-close"></i>
            انصراف</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import {ref} from "vue";
import Loading from "vue-loading-overlay";
import treeview from "vue3-treeview";
import "vue3-treeview/dist/style.css";

export default {
  name: "list",
  components:{
    Tree : treeview,
    Loading
  },
  data: ()=>{return {
    isLoading: true,
    config: {
      roots: [],
      opened: true,
      openedIcon: {
        type: "shape",
        stroke: "black",
        strokeWidth: 3,
        viewBox: "0 0 24 24",
        draw: "M 2 12 L 22 12",
      },
      closedIcon: {
        type: "shape",
        stroke: "black",
        strokeWidth: 3,
        viewBox: "0 0 24 24",
        draw: `M 12 2 L 12 22 M 2 12 L 22 12`,
      },
    },
    tree: [],
    selectedNode:0,
    addCatText:'',
    editCatText:''
  }},
  mounted() {
    this.loadData();
  },
  methods:{
    loadData(){
      axios.post('/api/commodity/cat/get').then((response)=>{
        this.tree = response.data.items;
        this.config.roots.push(response.data.root.toString());
        this.isLoading =false;
      })
    },
    addChild(id){
      this.selectedNode = id;
      const myModalAlternative = new bootstrap.Modal('#ModalAdd', {})
      myModalAlternative.show();
    },
    submitChild(node){
      if(this.addCatText.trim().length === 0 ){
        Swal.fire({
          text: 'نام دسته‌بندی الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else {
        axios.post('/api/commodity/cat/insert',{
          upper:this.selectedNode,
          text:this.addCatText
        }).then((response)=>{
          Swal.fire({
            text: 'دسته بندی افزوده شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then((res)=>{
            document.getElementById('modalAddClose').click();
            window.location.reload();
          } );
        })
      }
    },
    editeNode(node){
      this.selectedNode = node.id;
      this.editCatText = node.text;
      const myModalAlternative = new bootstrap.Modal('#ModalEdit', {})
      myModalAlternative.show();
    },
    submitEditChild(node){
      if(this.editCatText.trim().length === 0 ){
        Swal.fire({
          text: 'نام دسته‌بندی الزامی است.',
          icon: 'error',
          confirmButtonText: 'قبول'
        });
      }
      else {
        axios.post('/api/commodity/cat/edit',{
          id:this.selectedNode,
          text:this.editCatText
        }).then((response)=>{
          Swal.fire({
            text: 'دسته بندی ویرایش شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          }).then((res)=>{
            document.getElementById('modalEditClose').click();
            window.location.reload();
          } );
        })
      }
    },

  }
}
</script>

<style scoped>
.node-input, .node-text, .tree{
  font-family: 'vazir', sans-serif;
}
</style>