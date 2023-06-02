<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;


class AuthContoller extends Controller
{   
    public function investorregister()
    {
       
        $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'phone'     => 'required|numeric|digits_between:10,12',
            'password'  => 'required|min:8|confirmed',
            
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create user
        $user = User::create([
            'id'        => Str::uuid()->toString(),
            'name'      => request('name'),
            'email'     => request('email'),
            'phone'     => request('phone'),
            'tipeAkun'  => 'Investor',
            'password'  => bcrypt(request('password')),
            
        ]);
        


        //return response JSON user is created
        if ($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    public function petaniregister()
    {
       
        $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'phone'     => 'required|numeric|digits_between:10,12',
            'password'  => 'required|min:8|confirmed',
            
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create user
        $user = User::create([
            'id'        => Str::uuid()->toString(),
            'name'      => request('name'),
            'email'     => request('email'),
            'phone'     => request('phone'),
            'tipeAkun'  => 'Petani',
            'password'  => bcrypt(request('password')),
        ]);


        //return response JSON user is created
        if ($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

     public function login()
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

        if (! $token = auth()->guard('user-api')->attempt($credentials)) {
            return response()->json(['error' => 'Email atau Password Salah'], 401);
        }

        $user = auth()->guard('user-api')->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('user-api')->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'photo' => 'nullable|image',
            'password' => 'nullable|min:8|confirmed',
            'usia' => 'nullable|numeric',
            'pengalaman' => 'nullable|numeric',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->filled('alamat')) {
            $user->alamat = $request->input('alamat');
        }
        
        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        if ($request->filled('usia')) {
            $user->usia = $request->input('usia');
        }

        if ($request->filled('pengalaman')) {
            $user->pengalaman = $request->input('pengalaman');
        }

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move('image', $imageName);

            // Menambahkan field photo ke data yang akan diubah
            $user->photo = $imageName;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
    
    public function removePhoto()
    {
        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Periksa apakah ada foto profil yang perlu dihapus
        if ($user->photo) {
            // Hapus foto dari penyimpanan
            $imagePath = public_path('image/' . $user->photo);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Hapus referensi foto dari database
            $user->photo = null;
            $user->save();

            return response()->json([
                'message' => 'Foto berhasil dihapus',
                'user' => $user
            ]);
        }

        return response()->json([
            'message' => 'Tidak ditemukan foto untuk pengguna ini',
        ]);
    }

    public function sendResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Jika user tidak ditemukan
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Generate token reset password
        $token = Str::random(60);

        // Simpan token ke dalam tabel users
        $user->reset_password_token = $token;
        $user->save();

        return response()->json([
            'success' => 'true',
            'message' => 'Password was successfully reset',
            'data' => [
                'token' => $token
            ]
        ], Response::HTTP_OK);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
            
        ]);
        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Jika user tidak ditemukan atau token reset password tidak valid
        if (!$user || $user->reset_password_token !== $request->token) {
            return response()->json(['message' => 'Invalid email or token'], Response::HTTP_BAD_REQUEST);
        }

        // Update password user
        $user->password = bcrypt($request->password);
        $user->reset_password_token = null;
        $user->save();

        return response()->json(['message' => 'Password has been reset successfully'], Response::HTTP_OK);
    }


    public function me()
    {
        return response()->json(auth()->guard('user-api')->user());
    }

     public function refresh()
    {
        return $this->respondWithToken(auth()->guard('user-api')->refresh());
    }

    public function logout()
    {
        auth()->guard('user-api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('user-api')->factory()->getTTL() * 60,
        ]);
    }
}
