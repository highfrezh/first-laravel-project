<template>
   <div class="container">
      <div class="row mt-4" v-if="$gate.isAdminOrAuthor()">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users Table</h3>

                <div class="card-tools">
                  <button class="btn btn-success" @click="newModal">Add New <i class="fas fa-user-plus fa-fw"></i></button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>   
                      <th>Type</th>
                      <th>Registered at</th>
                      <th>Modify</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- vform for looping through the user from the database "v-for="user in users.data" :key="user.id" -->
                    <tr v-for="user in users.data" :key="user.id">
                      <td>{{user.id}}</td>
                      <td>{{user.name}}</td>
                      <td>{{user.email}}</td>
                      <!-- the upText use here is vue filter for text upperCase -->
                      <td>{{user.type | upText}}</td>
                      <!-- the myDate use here is vue filter Date formating -->
                      <td>{{user.created_at | myDate}}</td>
                      <td>
                       <a href="#" @click="editModal(user)">
                        <i class="fa fa-edit blue mr-3"></i>
                       </a>

                       <a href="#" @click="deleteUser(user.id)">
                        <i class="fa fa-trash red"></i>
                       </a>

                       </td>
                    </tr>
                   
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <pagination :data="users" @pagination-change-page="getResults"></pagination>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>

        <div v-if="!$gate.isAdminOrAuthor()">
          <not-found></not-found>
        </div>
                                 <!-- Modal -->
           <div class="modal fade" id="addNew" tabindex="-1" aria-labelledby="addNewModalLabel" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content">
                <div class="modal-header">
                  <h5 v-show="editMode" class="modal-title" id="addNewModalLabel">Update User's Info</h5>
                  <h5 v-show="!editMode" class="modal-title" id="addNewModalLabel">Add New</h5>
                  <button type="button" class="btn-close red" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <form @submit.prevent="editMode ? updateUser() : createUser()">
               <div class="modal-body">

                 <div class="form-group">
                   <input v-model="form.name" type="text" name="name" placeholder="Name"
                     class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                   <HasError :form="form" field="name" />
                 </div>

                 <div class="form-group">
                   <input v-model="form.email" type="email" name="email" placeholder="Email"
                     class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                   <HasError :form="form" field="email" />
                 </div>

                 <div class="form-group">
                   <textarea v-model="form.bio" id="bio" name="bio" placeholder="Short bio for user (Optional)"
                     class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }">
                   </textarea>
                   <HasError :form="form" field="bio" />
                 </div>

                 <div class="form-group">
                   <select name="type" v-model="form.type" id="type" 
                     class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                     <option disabled value="">Select User Role</option>
                     <option value="admin">Admin</option>
                     <option value="user">Standard User</option>
                     <option value="author">Author</option>
                   </select >
                     <HasError :form="form" field="type" />
                 </div>

                 <div class="form-group" v-show="!editMode">
                   <input v-model="form.password" type="password" name="password" placeholder="Password"
                     class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                   <HasError :form="form" field="password" />
                 </div>

                 

                </div>
              <div class="modal-footer">
                 <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                 <button v-show="editMode" type="submit" class="btn btn-success">Update</button>
                 <button v-show="!editMode" type="submit" class="btn btn-primary">Create</button>
              </div>
              </form>
            </div>
          </div>
        </div>
        <not-found :items="users.data" />
   </div>
</template> 
 
<script>
//import Form from 'vform'
import { Button, HasError, AlertError } from 'vform/src/components/tailwind'
import NotFound from './NotFound.vue';
export default {
  data: () => ({
    editMode: false,
    users : {}, //The users object Store the infomation coming from the suer form.
    form: new Form({
            id:       '',
            name:     '',
            email:    '',
            password: '',
            type:     '',
            bio:      '',
            photo:    ''
    })
  }),

//vform component error message here and component registration
  components: {
    Button,
    HasError,
    AlertError,
    NotFound
  },

// All the method or function goes here
  methods: {
    getResults(page = 1) {
			axios.get('api/user?page=' + page)
				.then(response => {
					this.users = response.data;
				});
		}, 

    // Method for Updating user to database after Edited from Edit Model
    updateUser(){
      this.$Progress.start()
      this.form.put('api/user/'+this.form.id)
      .then(() => {
        //success
        $('#addNew').modal('hide');
        swal.fire(
                    'Updated!',
                    'Your file has been updated.',
                    'success'
                  )
                  this.$Progress.finish();
                  Fire.$emit('ReloadUserData');

      })
      .catch(() => {
        this.$Progress.fail()
      });
    },  
    //Showing Editing User Modal Method
    editModal(user){
      this.editMode = true;
      this.form.reset();
      $('#addNew').modal('show') // Showing Modal
      this.form.fill(user); //filling the user to the Modal
    },

    // opening Modal for new  user
    newModal(){
      this.editMode = false;
      this.form.reset();
      $('#addNew').modal('show') // Showing Modal
    },


    deleteUser(id){
      swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
          }).then((result) => {

            // sending Request to the server
            if (result.isConfirmed) {
              this.form.delete('api/users/'+id).then(() => {
                  swal.fire(
                    'Deleted!',
                    'Your file has been deleted.',
                    'success'
                  )
              Fire.$emit('ReloadUserData');
                  }).catch(() => {
                    swal.fire("Failed", "There is something went wrong!.", "warning");
              });

            }

          })
    },
    loadUsers(){
      // Checking if the User is authorized before sendin HTTP Request
      if (this.$gate.isAdminOrAuthor()) {
        // getting the data  from the controller using the route name (/api/user) then fetch the data to the user (this.users) object 
        axios.get("/api/user").then(({ data }) => (this.users = data)) 
      }
    },
     createUser () {
      this.$Progress.start() //progress bar start here

      this.form.post('/api/user')
      /*
        .THEN()
        .CATCH() 
        Are ES6 promise that IF the condition is true the statement from the .THEN() with call back function will executed
        ELSE .CATCH() with call back function will executed.
      */
      .then(()=>{
        Fire.$emit('ReloadUserData'); // Event listener for  sending HTTP Request to the serve
        $('#addNew').modal('hide') // hiding Modal after User Created
        // taost sweetalert2 begin here
        toast.fire({
          icon: 'success',
            title: 'User created successfully'
          })
          // taost sweetalert2 finish here
        this.$Progress.finish();  //progress bar finish here

      })
      
      .catch(()=>{

      })
    }
  },
   created() {
     Fire.$on('searching',() => {
      //  $parent is refrenecing the parent of this file which is app.js
          let query = this.$parent.search;
          axios.get('api/findUser?q=' + query)
          .then((data) => {
            this.users = data.data
          })
          .catch(() =>{
            
          })
    })

        this.loadUsers();
        // Fire.$on send another Http request to reload the user if another user is created
        Fire.$on('ReloadUserData',() => {
          this.loadUsers();
        })
      }
}
   
</script>