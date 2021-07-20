<style scoped>
.widget-user-header{
   background-position: center center;
   background-size: cover;
   height: 250px;
}
</style>

<template>
   <div class="container">
      <div class="row justify-content-center">
         <div class="col-md-12 mt-3">
            <div class="card card-widget widget-user">
              <!-- Add the bg color to the header using any of the bg-* classes -->
              <div class="widget-user-header text-white" style="background-image:url('./img/user-cover.jpg')">
                <h3 class="widget-user-username text-left">{{this.form.name}}</h3>
                <!-- <h5 class="widget-user-desc text-left">{{ this.form.type }}</h5> -->
              </div>
              <div class="widget-user-image">
                <img class="img-circle" :src="getProfilePhoto()" alt="User Avatar">
              </div>
              <div class="card-footer py-0">
                <div class="row">
                  <div class="col-sm-4 border-right">
                    <div class="description-block">
                      <h5 class="description-header">3,200</h5>
                      <span class="description-text">SALES</span>
                    </div>
                    <!-- /.description-block -->
                  </div>
                  <!-- /.col -->
                  <div class="col-sm-4 border-right">
                    <div class="description-block">
                      <h5 class="description-header">13,000</h5>
                      <span class="description-text">FOLLOWERS</span>
                    </div>
                    <!-- /.description-block -->
                  </div>
                  <!-- /.col -->
                  <div class="col-sm-4">
                    <div class="description-block">
                      <h5 class="description-header">35</h5>
                      <span class="description-text">PRODUCTS</span>
                    </div>
                    <!-- /.description-block -->
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
            </div>

            <div class="col-md-12">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a></li>
                  <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="tab-pane active" id="activity">
                    <h3 class="text-center">Display User Activity</h3>
                  </div>
                  <!-- /.tab-pane -->
                  
                  <!-- /.tab-pane -->

                  <div class="tab-pane" id="settings">
                    <form class="form-horizontal">
                      <div class="form-group row">
                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                          <input type="name" class="form-control" :class="{ 'is-invalid': form.errors.has('name') }" v-model="form.name" id="inputName" placeholder="Name">
                          <HasError :form="form" field="name" />
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                          <input type="email" class="form-control" :class="{ 'is-invalid': form.errors.has('email') }" v-model="form.email" id="inputEmail" placeholder="Email">
                          <HasError :form="form" field="email" />
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputExperience" class="col-sm-2 col-form-label">Experience</label>
                        <div class="col-sm-10">
                          <textarea type="name" class="form-control" :class="{ 'is-invalid': form.errors.has('name') }" v-model="form.bio" id="inputExperience" placeholder="Experience"></textarea>
                          <HasError :form="form" field="name" />
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="inputSkills" class="col-sm-2 col-form-label">Profile Photo</label>
                        <div class="col-sm-10">
                          <input type="file" name="photo" @change="updateProfile" class="form-input"  id="inputSkills">
                        </div>
                      </div>
                       <div class="form-group row">
                        <label for="inputExperience" class="col-sm-2 col-form-label">Password (Optional)</label>
                        <div class="col-sm-10">
                          <input type="password" class="form-control" :class="{ 'is-invalid': form.errors.has('password') }" v-model="form.password" id="inputExperience" placeholder="Password">
                          <HasError :form="form" field="password" />
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-2 col-sm-10">
                          <button type="submit" @click.prevent="updateInfo" class="btn btn-success">Update</button>
                        </div>
                      </div>
                    </form>
                  </div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
         </div>
      </div>
   </div>
</template> 
 
<script>
   export default {
      data: () => ({
    form: new Form({
            id:       '',
            name:     '',
            email:    '',
            password: '',
            // type:     '', user type should not be display here for security purpose bcoz it can be change using Vue devtool.
            bio:      '',
            photo:    ''
    })
  }),

      mounted() {
         console.log('Component mounted.')
      },

      methods: {
        // getting new image uploaded using tanury operator
        getProfilePhoto(){
          let photo = (this.form.photo.length > 200) ? this.form.photo : "/img/profile/"+ this.form.photo;
          return photo;
        },
         updateInfo(){
            this.$Progress.start()
            // setting photo to undefined if it's null
            if (this.form.password == '') {
              this.form.password = undefined;
            }
            this.form.put('api/profile')
            .then(() => {
              this.$Progress.finish()
              Fire.$emit('ReloadUserData');
            })
            .catch(() => {
              this.$Progress.fail()
            });
         },

        //  method for getting image uploaded 
         updateProfile(e){
            let file = e.target.files[0];
            //console.log(file.size);
            let reader = new FileReader();
            // condition for checking file size
            if(file ['size'] <  1048576){

               //converting the image to base64
               reader.onloadend = (file) => {
                 //console.log('RESULT', reader.result)
                 this.form.photo = reader.result;
               }
               reader.readAsDataURL(file);
            }else{
               swal.fire({
                  type:  'error',
                  title: 'Oops...',
                  text:  'You are uploading a large file Image should less than 1MB',
               }
                  )
            }
         }
         
      },
      created(){
          axios.get("/api/profile").then(({ data }) => (this.form.fill(data)))
         Fire.$on('ReloadUserData',() => {
          axios.get("/api/profile");
        })
      }
   }
</script>