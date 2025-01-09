<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    function validateCaptcha($captchaResponse)
    {
        $secretKey = env('recaptcha2.secret');
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $captchaResponse);
        $responseKeys = json_decode($response, true);
        return $responseKeys;
        return isset($responseKeys["success"]) && $responseKeys["success"] === true;
    }


    public function validateLogin(Request $request)
    {
        try {
            $captchaResponse = $request->input('g-recaptcha-response');

            if (!$this->validateCaptcha($captchaResponse)) {
                return response()->json(['message' => 'Captcha tidak valid.'], 422);
            }

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
                    "message" => "Akun tidak aktif"
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
}
