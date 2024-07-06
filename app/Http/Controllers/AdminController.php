<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    protected $encryptionKey;

    public function __construct()
    {
        $this->encryptionKey = env('JWT_KEY');
    }

    public function allAdmins(Request $request)
    {
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->profile_pic = $admin->profile_pic ? 'https://bloomx.live/' . $admin->profile_pic : null;
        }

        return response(['data' => $admins], 200);
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

        $admin = Admin::whereEmail($request->email)->first();

        // Find admin
        if (!$admin) {
            return response(['message' => 'Please register'], 400);
        }

        // Validate password
        if (!password_verify($request->password, $admin->password)) {
            return response(['message' => 'Incorrect Credentials'], 400);
        }

        return response(['data' => $admin, 'token' => $this->generateToken($admin->id)], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required',
            'email' => 'required|email|unique:admins',
            'gender' => 'required',
            'password' => 'required|min:8'
        ], [
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'password.min' => 'The password must be at least 8 characters.'
        ]);

        if (Admin::whereEmail($request->email)->exists()) {
            return response(['message' => 'admin exists!, Please login to continue'], 400);
        }

        $verification_code = md5(time()) . md5(uniqid());

        $filePath =null;
     
        if ($request->hasFile('profile_pic')) {
            $filePath = $request->file('profile_pic')->store('avatar', env('DEFAULT_DISK'));
        }


        $admin = new Admin([
            'title' => $request->input('title'),
            'name' => $request->input('name'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
            'gender' => $request->input('gender'),
            'department' => $request->input('department'),
            'zone' => $request->input('zone'),
            'staff' => $request->input('staff'),
            'role' => $request->input('role'),
            'password' => bcrypt($request->input('password')),
            'profile_pic' => $filePath,
            'token' => $verification_code,
        ]);
        $admin->save();

        return response(['message' => 'Thank you for registering. Please login to continue!'], 200);
    }

    public function AdminsByEmail(Request $request, $email)
    {
        $admin = Admin::where('email', $email)->first();
        if ($admin) {
            return response(['data' => $admin], 200);
        } else {
            return response(['message' => 'admin does not exist!'], 400);
        }
    }

    public function AdminsById(Request $request, $id)
    {
        $admin = Admin::where('id', $id)->first();
        if ($admin) {
            return response(['data' => $admin], 200);
        } else {
            return response(['message' => 'admin does not exist!'], 400);
        }
    }

    public function AdminsByDepartment(Request $request, $department)
    {
        $admin = Admin::where('department', $department)->get();
        if ($admin->isNotEmpty()) {
            return response(['data' => $admin], 200);
        } else {
            return response(['message' => 'admin does not exist!'], 400);
        }
    }

    public function AdminsByZone(Request $request, $zone)
    {
        $admin = Admin::where('zone', $zone)->get();
        if ($admin->isNotEmpty()) {
            return response(['data' => $admin], 200);
        } else {
            return response(['message' => 'admin does not exist!'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response(['message' => 'admin not found'], 404);
        }

        $data = $request->only([
            'title', 'name', 'phone_number', 'email', 'gender',
            'department', 'zone', 'staff', 'password', 'profile_pic'
        ]);

        if ($request->hasFile('profile_pic')) {
            // Delete the old profile picture if it exists
            if ($admin->profile_pic) {
                Storage::disk(env('DEFAULT_DISK'))->delete($admin->profile_pic);
            }
            $data['profile_pic'] = $request->file('profile_pic')->store('avatar', env('DEFAULT_DISK'));
        }

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $admin->update(array_filter($data, fn($value) => !is_null($value)));

        return response(['message' => 'admin updated successfully', 'data' => $admin], 200);
    }
}
