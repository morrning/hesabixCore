<template>
  <div class="block block-content-full ">
    <div id="fixed-header" class="block-header block-header-default bg-gray-light pt-2 pb-1">
      <h3 class="block-title text-primary-dark">
        <button @click="$router.back()" type="button"
          class="float-start d-none d-sm-none d-md-block btn btn-sm btn-link text-warning">
          <i class="fa fw-bold fa-arrow-right"></i>
        </button>
        درخواست‌ها
      </h3>
      <div class="block-options">
        <div class="dropdown-center">
          <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link" data-bs-toggle="dropdown"
            id="dropdown-align-center-alt-primary" type="button">
            <i class="fa-solid fa-ellipsis"></i>
          </button>
          <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end" style="">
            <router-link class="dropdown-item" to="/acc/plugin/repservice/order/mod/">
              <i class="fa fa-plus text-success pe-2"></i>
              درخواست جدید
            </router-link>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="changeSingleStateModal" tabindex="-1" aria-labelledby="changeSingleStateModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="changeSingleStateModalLabel">تغییر وضعیت درخواست</h1>
            <div class="block-options">
              <button type="button" class="btn-close btn-close-change-state" data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-sm-12 col-md-12 mb-2">
                <span class="form-check form-switch  form-check-inline">
                  <input :disabled="this.singleChangeStateSelected.person.mobile == ''"
                    v-model="singleChangeStateSelected.sms" class="form-check-input" type="checkbox">
                  <label class="form-check-label">ارسال پیامک</label>
                </span>
              </div>
              <div class="col-sm-12 col-md-6 mb-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">مشتری</span>
                  <input type="text" readonly="readonly" class="form-control"
                    v-model="singleChangeStateSelected.person.nikename">
                </div>
              </div>
              <div class="col-sm-12 col-md-6 mb-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text">کالا</span>
                  <input type="text" readonly="readonly" class="form-control"
                    v-model="singleChangeStateSelected.commodity.name">
                </div>
              </div>
              <div class="col-sm-12 col-md-12 mb-2">
                <div class="input-group input-group-sm">
                  <label class="input-group-text bg-success text-light">وضعیت</label>
                  <select v-model="singleChangeStateSelected.state" class="form-select">
                    <option v-for="item in orderStates" :value="item">{{ item.label }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بازگشت</button>
            <button @click="changeStateSingle()" type="button" data-bs-dismiss="modal"
              class="btn btn-primary">ثبت</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="historyModal" tabindex="-1" aria-labelledby="historyModalModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="historyModalModalLabel">تاریخچه</h1>
            <div class="block-options">
              <button type="button" class="btn-close btn-close-change-state" data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
          </div>
          <div class="modal-body">
            <EasyDataTable table-class-name="customize-table" multi-sort show-index alternating :headers="logHeaders"
              :items="logItems" theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
              rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد"
              rowsOfPageSeparatorMessage="از" :loading="loading">
            </EasyDataTable>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بازگشت</button>
          </div>
        </div>
      </div>
    </div>
    <div class="block-content pt-1 pb-3">
      <div class="row">
        <div class="col-sm-12 col-md-12 m-0 p-0">
          <div class="mb-1">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input v-model="searchValue" class="form-control" type="text" placeholder="جست و جو ...">
            </div>
          </div>
          <div class="col-sm-12 col-md-12 border rounded mb-2 px-2 py-1">
            <div v-for="(item, index) in orderStates" class="form-check form-check-inline">
              <input @change="filterTable()" v-model="orderStates[index].checked" checked="" class="form-check-input"
                type="checkbox">
              <label class="form-check-label">{{ item.label }}</label>
            </div>
          </div>
          <EasyDataTable table-class-name="customize-table" multi-sort show-index alternating :headers="headers"
            :items="items" theme-color="#1d90ff" header-text-direction="center" body-text-direction="center"
            rowsPerPageMessage="تعداد سطر" emptyMessage="اطلاعاتی برای نمایش وجود ندارد" rowsOfPageSeparatorMessage="از"
            :loading="loading">
            <template #item-operation="{ code }">
              <div class="dropdown-center" style="z-index: 1000;">
                <button aria-expanded="false" aria-haspopup="true" class="btn btn-sm btn-link" data-bs-toggle="dropdown"
                  id="dropdown-align-center-alt-primary" type="button">
                  <i class="fa-solid fa-ellipsis"></i>
                </button>
                <div aria-labelledby="dropdown-align-center-outline-primary" class="dropdown-menu dropdown-menu-end"
                  style="">
                  <router-link class="dropdown-item" :to="'/acc/plugin/repservice/order/mod/' + code">
                    <i class="fa fa-edit pe-2"></i>
                    ویرایش
                  </router-link>
                  <button type="button" class="dropdown-item" data-bs-toggle="modal"
                    data-bs-target="#changeSingleStateModal" :data-bs-whatever="code">
                    <i class="fa-solid fa-bolt pe-2"></i>
                    تغییر وضعیت
                  </button>
                  <button type="button" class="dropdown-item" data-bs-toggle="modal"
                    @click="printInvoice(code)">
                    <i class="fa-solid fa-print pe-2"></i>
                    چاپ قبض رسید
                  </button>
                  <button type="button" class="dropdown-item" data-bs-toggle="modal"
                    data-bs-target="#changeSingleStateModal" :data-bs-whatever="code">
                    <i class="fa-solid fa-bolt pe-2"></i>
                    تغییر وضعیت
                  </button>
                  <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#historyModal"
                    @click="changeItem(code)" :data-bs-whatever="code">
                    <i class="fa fa-list pe-2"></i>
                    تاریخچه
                  </button>
                  <button type="button" class="dropdown-item" @click="deleteItem(code)">
                    <i class="fa fa-trash text-danger pe-2"></i>
                    حذف
                  </button>
                </div>
              </div>
            </template>
            <template #item-person="{ person }">
              <router-link :to="'/acc/persons/card/view/' + person.code">
                {{ person.nikename }}
              </router-link>
            </template>
            <template #item-commodity="{ commodity }">
              {{ commodity.name }}
            </template>
            <template #item-state="{ state }">
              {{ state.label }}
            </template>
            <template #expand="{ des, motaleghat, serial, pelak, model, color }">
              <div class="container text-start">
                <div class="row my-1">
                  <div class="col">
                    <strong>شرح: </strong>
                    {{ des }}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col">
                    <strong>متعلقات: </strong>
                    {{ motaleghat }}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col">
                    <strong>پلاک: </strong>
                    {{ pelak }}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col">
                    <strong>سریال: </strong>
                    {{ serial }}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col">
                    <strong>مدل: </strong>
                    {{ model }}
                  </div>
                </div>
                <div class="row mb-1">
                  <div class="col">
                    <strong>رنگ: </strong>
                    {{ color }}
                  </div>
                </div>
              </div>
            </template>
          </EasyDataTable>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import { ref } from "vue";

export default {
  name: "list",
  components: {
    history
  },
  watch: {
    'singleChangeStateSelected.code'(newValue, oldValue) {
      this.items.forEach((item) => {
        if (item.code == newValue) {
          this.singleChangeStateSelected = item;
          if (this.singleChangeStateSelected.person.mobile == '') {
            this.singleChangeStateSelected.sms = false;
          }
        }
      });
    },
    'searchValue'(newValue, oldValue) {
      this.searchTable();
    }
  },
  data: () => {
    return {
      orderStates: [],
      singleChangeStateSelected: {
        code: 0,
        person: {
          nikename: ''
        },
        commodity: {
          name: ''
        }
      },
      searchValue: '',
      loading: ref(true),
      items: [],
      orgItems: [],
      headers: [
        { text: "عملیات", value: "operation" },
        { text: "کد", value: "code" },
        { text: "مشتری", value: "person", sortable: true, width: 150 },
        { text: "کالا", value: "commodity", sortable: true, width: 150 },
        { text: "تاریخ", value: "date", sortable: true, width: 100 },
        { text: "وضعیت", value: "state", sortable: true, width: 150 },
        { text: "تاریخ تحویل", value: "dateOut", sortable: true, width: 100 },
      ],
      logItems: [],
      logHeaders: [
        { text: "تاریخ", value: "date" },
        { text: "شرح", value: "des" },
        { text: "ثبت کننده", value: "user" },
      ]
    }
  },
  methods: {
    changeItem(code) {
      this.loading = true;
      axios.post('/api/plug/repservice/order/logs/' + code).then((response) => {
        this.loading = false;
        this.logItems = response.data;
      });
    },
    filterTable() {
      this.loading = true;
      let calcItems = [];
      let isAll = true;
      let selectedTypes = [];
      this.orderStates.forEach((item) => {
        if (item.checked == true) {
          isAll = false;
          selectedTypes.push(item);
        }
      });
      if (isAll) {
        this.items = this.orgItems;
      }
      else {
        this.orgItems.forEach((item) => {
          selectedTypes.forEach((st) => {
            if (st.code == item.state.code) {
              let existBefore = false;
              calcItems.forEach((ri) => {
                if (item.state.code == ri.code) {
                  existBefore = true;
                }
              })
              if (existBefore == false) {
                calcItems.push(item);
              }
            }
          });
        });
        this.items = calcItems;
      }

      this.loading = false;
    },
    searchTable() {
      this.loading = true;
      let calcItems = [];
      let isAll = false;
      if(this.searchValue == ''){isAll = true;}
      if (isAll) {
        this.items = this.orgItems;
      }
      else {
        this.orgItems.forEach((item) => {
          let addItem = false;
          if(item.commodity.name.includes(this.searchValue)){
            addItem = true
          }
          else if(item.person.nikename.includes(this.searchValue)){
            addItem = true
          }
          else if(item.des.includes(this.searchValue)){
            addItem = true
          }
          else if(item.state.label.includes(this.searchValue)){
            addItem = true
          }
          else if(item.date.includes(this.searchValue)){
            addItem = true
          }
          if(addItem){
            calcItems.push(item);
          }
        });
        this.items = calcItems;
      }
      this.loading = false;
    },
    loadData() {
      axios.post('/api/plug/repservice/order/list')
        .then((response) => {
          this.items = response.data;
          this.orgItems = response.data;
          this.loading = false;
        });
      axios.post('/api/plug/repservice/order/state/list')
        .then((response) => {
          this.orderStates = response.data;
        });
    },
    changeStateSingle() {
      this.loading = true;
      axios.post('/api/plug/repservice/order/state/change', this.singleChangeStateSelected).then((response) => {
        this.loading = false;
        if (response.data.code == '0') {
          Swal.fire({
            text: 'وضعیت درخواست به روز شد.',
            icon: 'success',
            confirmButtonText: 'قبول'
          });
        }
        this.loadData();
      });
    },
    deleteItem(code) {
      Swal.fire({
        text: 'آیا برای حذف درخواست مطمئن هستید؟',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: `خیر`,
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          axios.post('/api/repservice/order/remove/' + code).then((response) => {
            if (response.data.result == 1) {
              let index = 0;
              for (let z = 0; z < this.items.length; z++) {
                index++;
                if (this.items[z]['code'] == code) {
                  this.items.splice(index - 1, 1);
                }
              }
              Swal.fire({
                text: 'درخواست با موفقیت حذف شد.',
                icon: 'success',
                confirmButtonText: 'قبول'
              });
            }
          })
        }
      })
    },
    printInvoice(code) {
      this.loading = true;
      axios.post('/api/repservice/print/invoice', { 
        'code': code
       }).then((response) => {
        this.loading = false;
        window.open(this.$API_URL + '/front/print/' + response.data.id, '_blank', 'noreferrer');
      });
    },
  },
  mounted() {
    this.loadData();
    const changeStateSingleModal = document.getElementById('changeSingleStateModal');
    if (changeStateSingleModal) {
      changeStateSingleModal.addEventListener('show.bs.modal', event => {
        // Button that triggered the modal
        const button = event.relatedTarget
        // Extract info from data-bs-* attributes
        this.singleChangeStateSelected.code = button.getAttribute('data-bs-whatever');
        // If necessary, you could initiate an Ajax request here
        // and then do the updating in a callback.

        // Update the modal's content.
        const modalTitle = changeStateSingleModal.querySelector('.modal-title')
        modalTitle.textContent = `تغییر وضعیت درخواست  ${this.singleChangeStateSelected.code}`

      })
    }
  }
}
</script>

<style scoped></style>