<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Twilio\Rest\Client;

class AuthOtpController extends Controller
{
    // Generate OTP API
    public function generateOtp($user_phone)
    {
        // Validate Data
        // $request->validate([
        //     'type' => 'required|in:send_login_otp',
        //     'user_phone' => 'required|exists:users,user_phone'
        // ]);

        // Generate an OTP for the user phone number
        $verificationCode = $this->generateOtpForUser($user_phone);

        // Return success response with OTP
        return response()->json([
            'status' => 'true',
            'message' => 'OTP generated successfully',
            'otp' => $verificationCode->otp,
            'user_phone' => $verificationCode->user_phone
        ]);
    }

    // Generate OTP for User
    private function generateOtpForUser($user_phone)
    {
        // Retrieve user based on user phone number
        $user = User::where('user_phone', $user_phone)->first();

        // Check if user exists
        if (!$user) {
            abort(404, 'User not found');
        }

        // Delete any existing OTPs for the user phone number
        VerificationCode::where('user_phone', $user_phone)->delete();

        // Generate a new OTP
        $newVerificationCode = VerificationCode::create([
            'user_phone' => $user_phone,
            'otp' => rand(1000, 9999), // Generate a 4-digit OTP
            'expire_at' => Carbon::now()->addMinutes(60)
        ]);

        // Send OTP via SMS
        // $this->sendOtpSms($user_phone, $newVerificationCode->otp);

        return $newVerificationCode;
    }


    // Send OTP to mobile number via SMS
//     private function sendOtpSms($mobile_no, $otp)
// {
//     $sid = env('TWILIO_SID');
//     $token = env('TWILIO_AUTH_TOKEN');
//     $from = env('TWILIO_PHONE_NUMBER');

    //     // Format the phone number to include the country code for Nepal (+977)
//     $formatted_mobile_no = '+977' . $mobile_no;

    //     $client = new Client($sid, $token);

    //     // Send SMS using Twilio
//     $client->messages->create(
//         $formatted_mobile_no,
//         [
//             'from' => $from,
//             'body' => "Your OTP for the expense manager is: $otp"
//         ]
//     );
// }

    // Verify OTP and Login API
    // Verify OTP and Login API
    public function loginWithOtp(Request $request)
    {
        // Validation
        $request->validate([
            'type' => 'required|in:send_login_otp,login_otp,viewprofile',
            'otp' => ($request->type == 'send_login_otp' || $request->type == 'viewprofile') ? 'nullable' : 'required',
            'user_phone' => ($request->type == 'send_login_otp' || $request->type == 'login_otp') ? 'required' : 'nullable'
        ]);

        // Check the value of 'type' and handle accordingly
        if ($request->type == 'send_login_otp') {
            // Call generateOtp method
            return $this->generateOtp($request->user_phone);
        } elseif ($request->type == 'login_otp') {
            // Verify OTP
            $verificationCode = VerificationCode::where('user_phone', $request->user_phone)
                ->where('otp', $request->otp)
                ->first();

            $now = Carbon::now();

            if (!$verificationCode) {
                return response()->json(['error' => 'Invalid OTP'], 401);
            } elseif ($now->isAfter($verificationCode->expire_at)) {
                return response()->json(['error' => 'OTP expired'], 401);
            }

            // Check if the OTP is within the validity period
            $validityPeriod = Carbon::parse($verificationCode->expire_at);
            if ($now->diffInMinutes($validityPeriod) < 60) {
                // Extend the validity period by 60 minutes
                $verificationCode->update([
                    'expire_at' => $validityPeriod->addMinutes(60)
                ]);
            }

            // Expire the OTP
            $verificationCode->update([
                'expire_at' => $now
            ]);

            // Login the user
            $user = User::where('user_phone', $request->user_phone)->first();
            if ($user) {
                // Update login status and last login time
                $user->update([
                    'login_status' => '1',
                    'last_login' => Carbon::now()
                ]);
                
                $request->session()->put('user', $user);
                return response()->json(['status' => 'true', 'message' => 'Login successful', 'user' => $user]);
            }
        } elseif ($request->type == 'viewprofile') {
            // Retrieve the user from the session
            $user = $request->session()->get('user');
            if ($user) {
                return response()->json(['status' => 'true', 'message' => 'User profile retrieved successfully', 'user' => $user]);
            } else {
                return response()->json(['error' => 'User profile not found'], 404);
            }
        }
    }

    public function logout(Request $request)
    {
        // Check if the type parameter is 'logout'
        if ($request->type == 'logout') {
            $user = Auth::user();
            if ($user) {
                $user->update(['login_status' => '0']);
            }
            Auth::logout();
            $request->session()->invalidate();
            return response()->json(['status'=> 'true','message'=> 'User logged out successfully.','user'=>$user]);
        } else {
            // If type parameter is not 'logout', return error response
            return response()->json(['status'=> 'false','message'=> 'Invalid request.']);
        }
    }
    

}