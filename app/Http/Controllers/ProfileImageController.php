<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileImageController extends Controller
{

  public function uploadProfilePicture(Request $request)
  {

    $validation = $request->validate([
      'link' => ['required']
    ]);

    ;
    // return response()->json($request->hasFile('image'));
    $user = $request->user('sanctum');

    $user->profile_picture_link = $validation['link'];

    $user->save();

    return response()->json($user->profile_picture_link);
  }

  public function getProfilePicture(Request $request, User $user)
  {
    return response()->json($user->profile_picture_link);
  }
}
