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
                    <tr v-for="user in users.data" :key="user.id">
                      <td>{{user.id}}</td>
                      <td>{{user.name}}</td>
                      <td>{{user.email}}</td>
                      <td>{{user.type | upText}}</td>
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
   </div>
</template> 
 
<script>
//import Form from 'vform'
import { Button, HasError, AlertError } from 'vform/src/components/tailwind'
export default {
  data: () => ({
    editMode: false,
    users : {},
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

//vform component error message here 
  components: {
    Button, HasError, AlertError
  },

  methods: {
    getResults(page = 1) {
			axios.get('api/user?page=' + page)
				.then(response => {
					this.users = response.data;
				});
		}, 

    //Edit Mode Method
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
                  Fire.$emit('AfterCreate');

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

    // opening Modal Method
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
              this.form.delete('api/user/'+id).then(() => {
                  swal.fire(
                    'Deleted!',
                    'Your file has been deleted.',
                    'success'
                  )
              Fire.$emit('AfterCreate');
                  }).catch(() => {
                    swal.fire("Failed", "There is something went wrong!.", "warning");
              });

            }

          })
    },
    loadUsers(){
      // Checking if the User is authorized before sendin HTTP Request
      if (this.$gate.isAdminOrAuthor()) {
        axios.get("/api/user").then(({ data }) => (this.users = data)) 
      }
    },
     createUser () {
      this.$Progress.start()

      this.form.post('/api/user')
      .then(()=>{
        Fire.$emit('AfterCreate');
        $('#addNew').modal('hide') // hiding Modal after User Created
  
        toast.fire({
            icon: 'success',
            title: 'User created successfully'
          })
        this.$Progress.finish();

      })
      
      .catch(()=>{

      })
    }
  },
   created() {
     Fire.$on('searching',() => {
          let query = this.$parent.search;
          axios.get('api/findUser?q=' + query)
          .then((data) => {
            this.users = data.data
          })
          .catch(() =>{
            //  axios.get("/api/user").then(({ data }) => (this.users = data));
          })
    })

        this.loadUsers();
        Fire.$on('AfterCreate',() => {
          this.loadUsers();
        })
      }
}
   
</script>