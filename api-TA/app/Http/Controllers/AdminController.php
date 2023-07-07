<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\Investasi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

setlocale(LC_TIME, 'id_ID');

class AdminController extends Controller
{
    public function adminregister()
    {
       
       $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:admins',
            'phone'     => 'required|numeric|digits_between:10,12',
            'password'  => 'required|min:8|confirmed',
            
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create user
        $user = Admin::create([
            'name'      => request('name'),
            'email'     => request('email'),
            'phone'     => request('phone'),
            'tipeAkun'  => 'Admin',
            'password'  => bcrypt(request('password')),
            
        ]);
        


        //return response JSON user is created
        if ($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 200);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    public function adminlogin()
    {
        $validator = Validator::make(request()->all(), [

            'email'     => 'required|email',
            'password'  => 'required',
            
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->guard('admin-api')->attempt($credentials)) {
            return response()->json(['error' => 'Email atau Password Salah'], 401);
        }

        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('admin-api')->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }

    public function adminme()
    {
        return response()->json(auth()->guard('admin-api')->user());
    }

    public function adminlogout()
    {
        auth()->guard('admin-api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function adminrefresh()
    {
        return $this->respondWithToken(auth()->guard('admin-api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('admin-api')->factory()->getTTL() * 60,
        ]);
    }

    public function getInvestor()
    {
        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $investors = User::where('tipeAkun', 'Investor')->get();

        return response()->json([
            'investor' => $investors
        ]);
    }



    public function totalInvestor()
    {
        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $totalInvestors = User::where('tipeAkun', 'Investor')->count();

        return response()->json([
            'total_investor' => $totalInvestors
        ]);
    }

    public function getPetani()
    {
        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $petani = User::where('tipeAkun', 'Petani')->get();

        return response()->json([
            'petani' => $petani
        ]);
    }

    public function totalPetani()
    {
        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $totalPetani = User::where('tipeAkun', 'Petani')->count();

        return response()->json([
            'total_petani' => $totalPetani
        ]);
    }

    public function totalDana(){
        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $totalDana = Investasi::sum('amount');

        return response()->json(['total_dana' => $totalDana]);
    }

    public function deleteAkun($id)
    {
        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userToDelete = User::find($id);

        if (!$userToDelete) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $userToDelete->Invest()->delete();

        $userToDelete->Pengajuans()->delete();

        $userToDelete->dompet()->delete();

        // Hapus akun user
        $userToDelete->delete();

        return response()->json(['message' => 'User account deleted successfully']);
    }

    public function chartDashboard()
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $user = auth()->guard('admin-api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = User::select('tipeAkun', DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
        ->groupBy('tipeAkun', 'month')
        ->get()
        ->toArray();

        // $result = array_fill_keys(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'], ['investor' => 0, 'petani' => 0]);
        $result = array_fill_keys($months, ['investor' => 0, 'petani' => 0]);

        foreach ($data as $item) {
            // $monthName = date('F', mktime(0, 0, 0, $item['month'], 10));
            $monthName = $months[$item['month']];
    
            if ($item['tipeAkun'] === 'Investor') {
                $result[$monthName]['investor'] += $item['count'];
            }
    
            if ($item['tipeAkun'] === 'Petani') {
                $result[$monthName]['petani'] += $item['count'];
            }
        }
    
        $result = array_map(function($key, $value) {
            return ['month' => $key] + $value;
        }, array_keys($result), $result);
    
        return response()->json($result);
    }
}
