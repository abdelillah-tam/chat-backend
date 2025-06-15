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

    // return response()->json($request->hasFile('image'));
    $user = $request->user('sanctum');

    $disk = Storage::disk('images');

    $disk->deleteDirectory($user->id);

    $disk->putFileAs($user->id, $request->file('image'), uuid_create() . '.jpg');

    $files = $disk->files($user->id);

    $url = $disk->url($files[0]); // because the folder contains only one image

    $user->profile_picture_link = $url;

    $user->save();

    return response()->json($url);
  }

  public function getProfilePicture(Request $request, User $user)
  {

    $disk = Storage::disk('images');

    $files = $disk->files($user->id);

    if (count($files) > 0) {
      return response()->json(['link' => $disk->url($files[0])]);
    } else {
      return response()->json(['link' => '']);
    }
  }
}
