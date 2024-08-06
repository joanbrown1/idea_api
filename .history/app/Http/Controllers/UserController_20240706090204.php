<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    protected $encryptionKey;

    public function __construct()
    {
        $this->encryptionKey = env('JWT_KEY');
    }



    public function index(){
        return 'welcome';
    }

    public function allUsers(Request $request)
    {
        $users = User::all();
        foreach ($users as $user) {
            $user->profile_pic = $user->profile_pic ? 'https://bloomx.live/' . $user->profile_pic : null;
        }

        return response(['data' => $users], 200);
    }

    public function generateToken($id)
    {
        $payload = [
            'id' => $id,
            'time' => time()
        ];
        return JWT::encode($payload, $this->encryptionKey, 'HS256');
    }

    public function decodeToken($token)
    {
        $decoded = JWT::decode($token, new Key($this->encryptionKey, 'HS256'));
        return $decoded;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::whereEmail($request->email)->first();

        // Find user
        if (!$user) {
            return response(['message' => 'Please register'], 400);
        }

        // Validate password
        if (!password_verify($request->password, $user->password)) {
            return response(['message' => 'Incorrect Credentials'], 400);
        }

        return response(['data' => $user, 'token' => $this->generateToken($user->id)], 200);
    }

    public function register(Request $request)
    {
   
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
           
        ], [
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'password.min' => 'The password must be at least 8 characters.',
       
        ]);
         

        if (User::whereEmail($request->email)->exists()) {
            return response(['message' => 'User exists!, Please login to continue'], 400);
        }

        $verification_code = md5(time()) . md5(uniqid());

        $filePath =null;
     
        if ($request->hasFile('profile_pic')) {
            $filePath = $request->file('profile_pic')->store('avatar', env('DEFAULT_DISK'));
        }

  

        $user = new User([
            'title' => $request->input('title'),
            'name' => $request->input('name'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'gender' => $request->input('gender'),
            'department' => $request->input('department'),
            'zone' => $request->input('zone'),
            'staff' => $request->input('staff'),
            'password' => bcrypt($request->input('password')),
            'profile_pic' => $filePath,
            'token' => $verification_code,
        ]);
        $user->save();

        return response(['message' => 'Thank you for registering. Please login to continue!'], 200);
    }

    public function UsersByEmail(Request $request, $email)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            return response(['data' => $user], 200);
        } else {
            return response(['message' => 'User does not exist!'], 400);
        }
    }

    public function UsersById(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        if ($user) {
            return response(['data' => $user], 200);
        } else {
            return response(['message' => 'User does not exist!'], 400);
        }
    }

    public function UsersByDepartment(Request $request, $department)
    {
        $user = User::where('department', $department)->get();
        if ($user->isNotEmpty()) {
            return response(['data' => $user], 200);
        } else {
            return response(['message' => 'User does not exist!'], 400);
        }
    }

    public function UsersByZone(Request $request, $zone)
    {
        $user = User::where('zone', $zone)->get();
        if ($user->isNotEmpty()) {
            return response(['data' => $user], 200);
        } else {
            return response(['message' => 'User does not exist!'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response(['message' => 'User not found'], 404);
        }

        $data = $request->only([
            'title', 'name', 'phone_number', 'email', 'gender',
            'department', 'zone', 'staff', 'password', 'profile_pic'
        ]);

        if ($request->hasFile('profile_pic')) {
            // Delete the old profile picture if it exists
            if ($user->profile_pic) {
                Storage::disk(env('DEFAULT_DISK'))->delete($user->profile_pic);
            }
            $data['profile_pic'] = $request->file('profile_pic')->store('avatar', env('DEFAULT_DISK'));
        }

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update(array_filter($data, fn($value) => !is_null($value)));

        return response(['message' => 'User updated successfully', 'data' => $user], 200);
    }
}
