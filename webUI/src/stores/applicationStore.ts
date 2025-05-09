import {defineStore} from "pinia";

export const applicationStore = defineStore('application',{
    state () {
        return {
            title: 'app.name'
        }
    },
    actions: {
        setTitle (state:any, title:String) {
            state.title = title;
        },
        getTitle () {
            return this.$state.title;
        }
    }
});
export default applicationStore;