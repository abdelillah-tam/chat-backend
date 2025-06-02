<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message'=> ['required'],
            'message.senderId' => ['required','Integer'],
            'message.receiverId' => ['required', 'Integer'],
            'message.messageText' => ['required']
        ];
    }

    public function messages(){
        return [
            'message.senderId' => 'No id !',
            'message.messageText' => 'Empty message !'
        ];
    }

    
}
