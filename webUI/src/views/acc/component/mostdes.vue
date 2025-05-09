<script lang="ts">
import axios from 'axios';
import { defineComponent } from 'vue'
import Swal from "sweetalert2";
import { ref } from 'vue';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';

interface MostDesItem {
  id: number;
  des: string;
}

interface SubmitData {
  id: number | null;
  des: string;
}

export default defineComponent({
  name: "mostdes",
  components: {
    Loading
  },
  props: {
    submitData: {
      type: Object as () => SubmitData,
      required: true
    },
    type: {
      type: String,
      required: true
    },
    modelValue: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue'],
  watch: {
    search: {
      handler: function (val: string, oldVal: string) {
        if (val == '') {
          this.items = this.orgItems;
        }
        else {
          const temp: MostDesItem[] = [];
          this.orgItems.forEach((item: MostDesItem) => {
            if (item.des.includes(val)) {
              temp.push(item);
            }
          });
          this.items = temp.slice();
        }
      },
      deep: false
    },
  },
  data: () => {
    return {
      loading: ref(false),
      items: [] as MostDesItem[],
      orgItems: [] as MostDesItem[],
      des: '',
      selected: null as MostDesItem | null,
      search: '',
      dialog: false,
    }
  },
  methods: {
    loadData() {
      this.loading = true;
      axios.post('/api/mostdes/list', {
        type: this.$props.type
      }).then((response) => {
        this.items = response.data.slice();
        this.orgItems = response.data.slice();
        this.search = '';
        this.loading = false;
      });
    },
    remove(id: number) {
      this.loading = true;
      axios.post('/api/mostdes/remove/' + id).then((response) => {
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
        axios.post('/api/mostdes/add', {
          des: this.des,
          type: this.$props.type
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
    },
    selectItem(item: MostDesItem) {
      this.selected = item;
      if (this.$props.submitData) {
        this.$props.submitData.id = item.id;
        this.$props.submitData.des = item.des;
        this.$emit('update:modelValue', item.des);
      }
      this.dialog = false;
    }
  },
  mounted() {
    this.loadData();
  }
})
</script>

<template>
  <v-tooltip :text="$t('dialog.most_des')" location="bottom">
    <template v-slot:activator="{ props }">
      <v-btn @click="dialog = true;" v-bind="props" size="small" icon="mdi-list-box" variant="plain"></v-btn>
    </template>
  </v-tooltip>
  <v-dialog v-model="dialog">
    <v-card>
      <v-card-title>
        <v-toolbar class="fixed-top" color="toolbar" :title="$t('dialog.most_des')">
          <template v-slot:append>
            <v-tooltip :text="$t('dialog.close')" location="bottom">
              <template v-slot:activator="{ props }">
                <v-btn v-bind="props" @click="dialog = false" class="d-none d-sm-flex" variant="text"
                  icon="mdi-close" />
              </template>
            </v-tooltip>
          </template>
        </v-toolbar>
      </v-card-title>
      <v-card-text class="mt-5">
        <v-text-field class="mb-2" v-model="search" :loading="loading" prepend-inner-icon="mdi-magnify"
          :label="$t('dialog.search')" variant="outlined" hide-details single-line></v-text-field>

        <ul :disabled="loading" class="list-group">
          <li v-for="item in items" class="list-group-item d-flex justify-content-between align-items-center">
            <a title="حذف" @click="remove(item.id)" class="text-danger rounded-pill float-start">
              <i class="fa fa-trash"></i>
            </a>
            <span class="">
              {{ item.des }}
            </span>
            <span @click="selectItem(item)" class="badge text-bg-primary rounded-pill float-start">
              <i class="fa fa-arrow-left"></i>
            </span>

          </li>
        </ul>
        <div v-if="items.length == 0 && loading == false">
          نتیجه‌ای یافت نشد
        </div>
        <v-text-field class="mt-2" v-model="des" :loading="loading" append-inner-icon="mdi-content-save" block
          :label="$t('dialog.insert_mostdes')" variant="outlined" :placeholder="$t('dialog.input_text')" hide-details
          single-line @click:append-inner="save"></v-text-field>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped></style>