<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Thesis;
//https://www.dbestech.com/tutorials/laravel-sanctum-install-and-login-and-register
class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function createUser(Request $request)
    {
        try {
            
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'mail' => 'required|email|unique:users,mail',
                'password' => 'required',
                'role' => 'nullable'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'mail' => $request->mail,
                'role'=> $request->role,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

/**
 * Login The User
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */


 public function loginUser(Request $request)
 {
     try {
         // Validate the request
         $request->validate([
             'mail' => 'required|email',
             'password' => 'required'
         ]);
 
         // Try to find the user in students table
         $user = Student::where('mail', $request->mail)->first();
         $role = 'student';
 
         // If not found, check the supervisors table
         if (!$user) {
             $user = Teacher::where('mail', $request->mail)->first();
             $role = 'supervisor';
         }

         if (!$user || $request->password !== 'password123') {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }
        
 
        //  if (!$user || !Hash::check($request->password, $user->password)) {
        //      return response()->json([
        //          'status' => false,
        //          'message' => 'Invalid credentials.'
        //      ], 401);
        //  }
 
         // Generate Laravel Sanctum authentication token
         $token = $user->createToken('auth_token')->plainTextToken;
 
         // Fetch thesis information (if exists)

 
         return response()->json([
             'status' => true,
             'message' => 'User Logged In Successfully',
             'token' => $token,
             'user' => [
                 'mail' => $user->mail,
                 'role' => $role,
                 'name' => $user->firstname . ' ' . $user->lastname,
            
             ]
         ], 200);
 
     } catch (\Throwable $th) {
         return response()->json([
             'status' => false,
             'message' => 'Something went za ystoi emdkue.',
             'error' => $th->getMessage()
         ], 500);
     }
 }
 






}