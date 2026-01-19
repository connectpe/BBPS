<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminLoginMail;
class SendingMail
{  
    public static function sendMail($data)
    {
        
        try{
            Mail::to($data['email'])->send(new AdminLoginMail($data));  
            return true;
        }catch(\Exception $e){
            // Log the error or handle it as needed
            \Log::error('Mail sending failed: '.$e->getMessage());      

        }
        
    }
}
