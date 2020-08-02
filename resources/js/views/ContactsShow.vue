<template>
    <div>
        <div v-if="loading">Loading...</div>
        <div v-else>
            <div class="flex justify-between">
            <a href="#" class="text-blue-400" @click="$router.back()">
                --Back
            </a>
            <div class="relative">
                <router-link :to="'/contacts/' + contact.contact_id + '/edit'" class="px-4 py-2 rounded mr-2 text-green-500 border border-green-500 font-bold">Edit</router-link>
                <a href="#" @click="modal =! modal"
                class="px-4 py-2 border border-red-500 rounded  font-bold text-red-500">Delete</a>
                <div v-if="modal" class="absolute bg-blue-900 text-white rounded-lg z-20 p-8 w-64 right-0 mt-4 mr-6">
                    <p>Are yuo sure to delete</p>
                    <div class="flex items-center justify-end mt-6">
                        <button class="text-white pr-4" @click="modal =! modal">Cancel</button>
                        <button class="px-4 py-2 bg-red-500 rounded text-sm font-bold text-white" @click="destroy">Delete</button>
                    </div>
                </div>
            </div>
            <div v-if="modal" @click="modal =! modal" class="bg-black opacity-25 absolute right-0 left-0 top-0 bottom-0 z-10"></div>
        </div>
        <div class="flex items-center pt-6">
            <UserCircle :name="contact.name"/>
            <p class="pl-5 text-xl">{{contact.name}}</p>
        </div>

        <p class="pt-6 text-gray-600 font-bold uppercase text-xl">Contact</p>
        <p class="pt-2 text-blue-400">{{ contact.email }}</p>

        <p class="pt-6 text-gray-600 font-bold uppercase text-xl">Company</p>
        <p class="pt-2 text-blue-400">{{ contact.company }}</p>

        <p class="pt-6 text-gray-600 font-bold uppercase text-xl">Birthday</p>
        <p class="pt-2 text-blue-400">{{ contact.birthday }}</p>
        </div>

    </div>

</template>

<script>
    import UserCircle from '../components/UserCircle';
    export default {
        name: "ContactsShow",
        components: {
            UserCircle
        },

        mounted(){
            axios.get('/api/contacts/' + this.$route.params.id)
            .then(response=>{
                this.contact = response.data.data;
                this.loading = false;
            })
            .catch(error=>{
                this.loading = false;
                if(error.response.status === 404){
                    this.$router.push('/contacts');
                }
            });
        },

        data: function(){
            return {
                loading: true,
                modal: false,
                contact: null,
            }
        },
        methods: {
            destroy: function(){
                axios.delete('/api/contacts/' + this.$route.params.id)
                .then(response=>{
                    this.$router.push('/contacts');
                })
                .catch(error=>{
                    alert('Internal Error. Unable to delete');
                });
            }
        }

    }
</script>

<style lang="stylus" scoped>

</style>
