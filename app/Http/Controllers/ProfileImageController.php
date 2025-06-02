<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileImageController extends Controller
{

  public function uploadProfilePicture(Request $request)
  {

    $user = $request->user('sanctum');

    $filePath = Storage::disk('images');

    $filePath->putFileAs($user->id, $request->file('image'), $user->id . '.jpg');

    $url = $filePath->url($user->id . '.jpg');

    $user->profile_picture_link = $url;

    $user->save();

    return response()->json($url);
  }

  public function getProfilePicture(Request $request, User $user)
  {
    $url = Storage::disk('images')->url($user->id . '/' . $user->id . '.jpg');

    return response()->json(['link' => $url]);
  }
}
