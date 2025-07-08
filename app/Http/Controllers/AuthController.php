<?php

namespace App\Http\Controllers;

use App\Models\ChatChannel;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function data()
    {
        return response()->json([
            'testing data' => 'data'
        ]);
    }

    public function registerGoogle(Request $request)
    {

        $validation = $request->validate([
            'email' => ['required', 'email'],
            'provider' => ['required', Rule::in('google')]
        ]);


        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex ?? '',
            'provider' => $request->provider,
            'email' => $validation['email'],
            'password' => ''
        ]);

        return response()->json([
            'message' => 'Registered successfully',
            'id' => $user
        ]);
    }

    public function registerTraditional(Request $request)
    {

        $validation = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            'confirmationPassword' => ['required', 'same:password'],
            'provider' => ['required', Rule::in('user')]
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'sex' => $request->sex ?? '',
            'provider' => $request->provider,
            'email' => $validation['email'],
            'password' => $validation['password']
        ]);

        return response()->json([
            'message' => 'Registered successfully',
            'id' => $user
        ]);
    }
    public function loginGoogle(Request $request)
    {

        $validation = $request->validate([
            'email' => ['required', 'email'],
            'provider' => ['required', Rule::in('google')]
        ]);


        $user = User::where('email', $validation['email'])->first();


        $user->tokens()->delete(); // delete previous token if exist

        $token = $user->createToken($user->name . '-token')->plainTextToken;


        return response()->json([
            'token' => $token,
            'id' => $user->id,
            'email' => $user->email
        ]);


    }

    public function loginTraditional(Request $request)
    {
        $validation = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            'provider' => ['required', Rule::in('user')]
        ]);

        $user = User::where('email', $validation['email'])->first();

        if (!$user || !Hash::check($validation['password'], $user->password)) {
            return response()->json([
                'error' => 'User not found or wrong password!'
            ]);
        }

        $user->tokens()->delete(); // delete previous token if exist

        $token = $user->createToken($user->name . '-token')->plainTextToken;


        return response()->json([
            'token' => $token,
            'id' => $user->id,
            'email' => $user->email
        ]);

    }

    public function logout(Request $request)
    {
        $user = $request->user('sanctum');

        $user->currentAccessToken()->delete();
    }

    public function findUserByEmail(Request $request)
    {


        $validation = $request->validate([
            'email' => ['required', 'email']
        ]);

        $user = User::where('email', $validation['email'])->first();

        if (!$user) {
            return response()->json(
                [
                    'code' => 404,
                    'error' => 'Email not found'
                ]
            );
        }
        return response()->json([
            'id' => $user->id,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'sex' => $user->sex,
            'provider' => $user->provider
        ]);
    }

    public function findUserById(User $user)
    {
        return response()->json(
            [

                'id' => $user->id,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'sex' => $user->sex,
                'provider' => $user->provider,
                'profilePictureLink' => $user->profile_picture_link

            ]
        );
    }

    public function findUsersByIds(Request $request)
    {
        $users_db = User::findMany($request->ids);

        $users = [];

        foreach ($users_db as $user) {
            $finalUserResult = [

                'id' => $user->id,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'sex' => $user->sex,
                'provider' => $user->provider,
                'profilePictureLink' => $user->profile_picture_link


            ];
            array_push($users, $finalUserResult);
        }



        return response()->json($users);
    }

    public function findUserByName(Request $request)
    {
        $validation = $request->validate([
            'name' => ['required']
        ]);

        $currentUser = $request->user('sanctum');

        $users_db = User::where('first_name', 'like', "{$validation['name']}%")
            ->orWhere('last_name', 'like', "{$validation['name']}%")
            ->get()->all();

        $users = [];


        if (count($users_db) === 0) {
            return response()->json([
                'code' => 4044,
                'error' => 'User not found'
            ]);
        }


        foreach ($users_db as $user) {
            if ($user->id !== $currentUser->id) {
                $finalUserResult = [
                    'id' => $user->id,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'email' => $user->email,
                    'sex' => $user->sex,
                    'provider' => $user->provider,
                    'profilePictureLink' => $user->profile_picture_link
                ];
                array_push($users, $finalUserResult);
            }

        }
        return response()->json($users);
    }


    public function getUsersInContact(Request $request)
    {

        $user = $request->user('sanctum');

        $chatChannels = ChatChannel::where('first_user', $user->id)
            ->orWhere('second_user', $user->id)
            ->get();



        $usersInContact = $chatChannels->map(function ($item, $key) use ($user) {
            $lastMessageTimestamp = Message::where('channel', $item->id)->get()->last();
            return $item->firstUser->id == $user->id ? [
                'user' => [
                    'id' => $item->secondUser->id,
                    'firstName' => $item->secondUser->first_name,
                    'lastName' => $item->secondUser->last_name,
                    'sex' => $item->secondUser->sex,
                    'provider' => $item->secondUser->provider,
                    'profilePictureLink' => $item->secondUser->profile_picture_link
                ],
                'channel' => $item->id,
                'lastMessageTimestamp' => Carbon::parse($lastMessageTimestamp->created_at)->timestamp
            ] : [
                'user' => [
                    'id' => $item->firstUser->id,
                    'firstName' => $item->firstUser->first_name,
                    'lastName' => $item->firstUser->last_name,
                    'sex' => $item->firstUser->sex,
                    'provider' => $item->firstUser->provider,
                    'profilePictureLink' => $item->firstUser->profile_picture_link
                ],
                'channel' => $item->id,
                'lastMessageTimestamp' => Carbon::parse($lastMessageTimestamp->created_at)->timestamp,
            ];
        })->all();


        return response()->json($usersInContact);
    }

    public function updateGoogle(Request $request, User $user)
    {
        $validation = $request->validate([
            'first_name' => ['required'],
            'last_name' => ['required']
        ]);

        if ($user->provider === 'google') {
            $updatedUser = $user->update([
                'first_name' => $validation['first_name'],
                'last_name' => $validation['last_name']
            ]);

            return response()->json($updatedUser);
        }

        return response()->json('Something wrong!');
    }

    public function update(Request $request, User $user)
    {
        $validation = $request->validate([
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required'],
            'password' => ['required']
        ]);

        if ($user->provider !== 'google') {
            $updatedUser = $user->update([
                'first_name' => $validation['first_name'],
                'last_name' => $validation['last_name'],
                'email' => $validation['email'],
                'password' => $validation['password']
            ]);

            return response()->json($updatedUser);
        }

        return response()->json('Something wrong!');
    }

    public function validateToken()
    {
        return auth('sanctum')->user() ? response()->json(true) : response()->json(false);
    }

}
