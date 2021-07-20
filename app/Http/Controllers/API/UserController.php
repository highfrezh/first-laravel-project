<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');

         // giving authority to delete user by only Admin.
         // adding this code Here willtake effect on the all CRUD operation.
        //$this->authorize('isAdmin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // giving authority to Display user by only Admin.
       // $this->authorize('isAdmin');
          
    // To grant authority to more than (for both Admin and author) one user to display User Info.
    if (\Gate::allows('isAdmin') || \Gate::allows('isAuthor')) {
        return User::latest()->paginate(5);
    } 


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 
    
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'type' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);


        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'type' => $request['type'],
            'bio' => $request['bio'],
            // 'photo' => $request['photo'],
            'password' => Hash::make($request['password']),
        ]);
    }
    
    //Profile Method to Update user info in user profile
   public function updateProfile(request $request)
   {
       $user = auth('api')->user();

       $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|min:6',
            ]);
            
       $currentPhoto = $user->photo;
          //form photo        DB photo
       if($request->photo != $currentPhoto){
        // converting the image string  and saving it.

        $name = time().'.'.explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
        // Image Intervention package (image.intervention.io) for  converting base64 image
        \Image::make($request->photo)->save(public_path('img/profile/').$name);

        //setting the image name to be upload to the database
        //$request->photo =$name;
        $request->merge(['photo' => $name]);

        // deleting the previous Photo from the profile.
        $userPhoto = public_path('img/profile/').$currentPhoto;
        if(file_exists($userPhoto)){
            @unlink($userPhoto);
        }


       } 

       //Changing password
       if(!empty($request->password)){
           $request->merge(['password' => Hash::make($request
           ['password'])]);
       }

       $user->update($request->all());

       return['message' => "Success"];
   }

     // This Profile Method is to get authenticated login user info
    public function profile() 
    {
        // user() is a buildin laravel function for Authentication
        return auth('api')->user();
    }    


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

         $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id, // if the Email is the same or different it will fill it to database.
            'password' => 'sometimes|string|min:6',
            ]);
 
        $user->update($request->all());

        return['message' => 'Updated the user info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // giving authority to delete user by only Admin.
        $this->authorize('isAdmin');

        $user = User::findOrFail($id);

        // delete the user

        $user->delete();

        // This is just additional Message Alert whch is optional.
        return['message' => 'user Deleted Successfully..'];
        
    }

    // Controller class to search user
    
    public function search()
    {
        // if the search is not empty the query will be set else it will return the normal users
       if ($search = \Request::get('q')) {
           $users = User::where(function($query) use ($search){
               $query->where('name','LIKE',"%$search%")->orWhere('email','LIKE',"%$search%")->orWhere('type','LIKE',"%$search%");
           })->paginate(20);
       }else {

        $users = User::latest()->paginate(5);  
    
    }
        return $users;
        
    }
}
