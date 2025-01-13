<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login()
    {
        $title = "Kelola Akses";
        $webTitle = "SKYREFF - DASHBOARD";
        $config = WebConfig::first();
        $description = "Web Reseller Skyreff";
        if ($config) {
            $description = $config->web_description;
            $title = $config->web_title;
        }
        return view("pages.auth.index", compact("title", 'description'));
    }

    // API
    public function validateLogin(Request $request)
    {
        try {
            $rules = [
                "username" => "required|string",
                "password" => "required|string",
            ];

            $messages = [
                "username.required" => "Username harus diisi",
                "password.required" => "Password harus diisi"
            ];

            $validate = Validator::make($request->all(), $rules, $messages);
            if ($validate->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validate->errors()->first(),
                ], 400);
            }

            $user = User::where('username', $request->username)->first();

            if ($user && $user->is_active != "Y") {
                return response()->json([
                    "status" => "error",
                    "message" => "Akun tidak aktif, silahkan hubungi admin"
                ], 400);
            }

            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                return response()->json([
                    "status" => "success",
                    "message" => "Login Sukses",
                ]);
            }

            return response()->json([
                "status" => "error",
                "message" => "Username / Password salah"
            ], 400);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->route('login');
    }

    public function register(Request $request)
    {
        try {
            $rules = [
                "name" => "required|string",
                "username" => "required|string|unique:users",
                "password" => "required|string|min:5",
                "passwordConfirm" => "required|string|same:password"
            ];

            $messages = [
                "name.required" => "Nama harus diisi",
                "username.required" => "Username harus diisi",
                "username.unique" => "Username sudah digunakan",
                "password.required" => "Password harus diisi",
                "password.min" => "Password minimal 5 karakter",
                "passwordConfirm.required" => "Password harus diisi",
                "passwordConfirm.same" => "Password Confirm tidak sesuai"
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = new User();
            $user->code = "RES" . strtoupper(Str::random(7));
            $user->name = $request->name;
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->is_active = "N";
            $user->role = "RESELLER";
            $user->save();

            return response()->json([
                "status" => "success",
                "message" => "Registrasi berhasil, silahkan hubungi admin untuk aktivasi akun"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }
}
