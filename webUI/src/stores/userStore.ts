import { defineStore } from 'pinia'
import axios from "axios";

export const useUserStore = defineStore('user', {
    state() {return{
        synced: false,
        userData:{
            login:false,
            name:'',
            mobile:'',
            email:'',
            id:''
        }
    }},
    // could also be defined as
    // state: () => ({ count: 0 })
    actions: {
        refresh(){
            axios.post('/api2/user/current/info').then((response:any)=>{
                if(response.data.Success == true){
                    this.userData = response.data.data;
                }
            }).catch((result)=>{
                this.userData.login = false;
            });
            this.synced = true;
        },
        isLogin(){
            return this.userData.login;
        }
    },
})