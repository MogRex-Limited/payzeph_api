<?php

namespace App\Helpers;

use App\Constants\Finance\PlanConstants;
use App\Exceptions\General\InvalidRequestException;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MethodsHelper
{
    /** Returns a random alphanumeric token or number
     * @param int length
     * @param bool  type
     * @return string token
     */
    public static function getRandomToken($length, $typeInt = false)
    {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeAlphabet .= strtolower($codeAlphabet);
        $codeAlphabet .= '0123456789';
        $max = strlen($codeAlphabet);

        if ($typeInt == true) {
            for ($i = 0; $i < $length; $i++) {
                $token .= rand(0, 9);
            }
            $token = intval($token);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $token .= $codeAlphabet[random_int(0, $max - 1)];
            }
        }

        return $token;
    }

    static function generateRandomDigits(int $length, $string = false)
    {
        $digits = '';
        $digits = random_int(pow(10, $length - 1), pow(10, $length) - 1);

        if ($string == true) {
            return (string)$digits;
        } else {
            return (int)$digits;
        }
    }

    /**Puts file in a private storage */
    public static function putFileInPrivateStorage($file, $path, $disk = 'local')
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        Storage::putFileAs($path, $file, $filename, ['disk' => $disk]);

        return "$path/$filename";
    }

    // Returns full public path
    public static function my_asset($path = null)
    {
        return url('/') . env('RESOURCE_PATH') . '/' . $path;
    }

    /**Gets file from public storage */
    public static function getFileFromStorage($fullpath, $storage = 'storage')
    {
        if ($storage == 'storage') {
            return route('read_file', encrypt($fullpath));
        }

        return self::my_asset($fullpath);
    }

    /**Deletes file from public storage */
    public static function deleteFileFromPublicStorage($path)
    {
        if (file_exists($path)) {
            unlink(public_path($path));
        }
    }

    public static function unlinkPath($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**Deletes file from private storage */
    public static function deleteFileFromPrivateStorage($path, $disk = 'local')
    {
        if ((explode('/', $path)[0] ?? '') === 'app') {
            $path = str_replace('app/', '', $path);
        }

        $exists = Storage::disk($disk)->exists($path);
        if ($exists) {
            Storage::delete($path);
        }

        return $exists;
    }

    /**Deletes folder from private storage */
    public static function deleteFolderFromPrivateStorage($path, $disk = 'local')
    {
        if ((explode('/', $path)[0] ?? '') === 'app') {
            $path = str_replace('app/', '', $path);
        }

        $exists = Storage::disk($disk)->exists($path);
        if ($exists) {
            Storage::deleteDirectory($path);
        }

        return $exists;
    }

    /**Downloads file from private storage */
    public static function downloadFileFromPrivateStorage($path, $name)
    {
        $name = $name ?? env('APP_NAME');
        $exists = Storage::disk('local')->exists($path);
        if ($exists) {
            $type = Storage::mimeType($path);
            $ext = explode('.', $path)[1];
            $display_name = $name . '.' . $ext;
            $headers = [
                'Content-Type' => $type,
            ];

            return Storage::download($path, $display_name, $headers);
        }

        return null;
    }

    public static function readPrivateFile($path) {}

    /**Reads file from private storage */
    public static function getFileFromPrivateStorage($fullpath, $disk = 'local')
    {
        if ((explode('/', $fullpath)[0] ?? '') === 'app') {
            $fullpath = str_replace('app/', '', $fullpath);
        }
        if ($disk == 'public') {
            $disk = null;
        }
        $exists = Storage::disk($disk)->exists($fullpath);
        if ($exists) {
            $fileContents = Storage::disk($disk)->get($fullpath);
            $content = Storage::mimeType($fullpath);
            $response = Response::make($fileContents, 200);
            $response->header('Content-Type', $content);

            return $response;
        }

        return null;
    }

    public static function str_limit($string, $limit = 20, $end = '...')
    {
        return Str::limit(strip_tags($string), $limit, $end);
    }

    /**Returns file size */
    public static function bytesToHuman($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /** Returns File type
     * @return Image || Video || Document
     */
    public static function getFileType(string $type)
    {
        $imageTypes = self::imageMimes();
        if (strpos($imageTypes, $type) !== false) {
            return 'Image';
        }

        $videoTypes = self::videoMimes();
        if (strpos($videoTypes, $type) !== false) {
            return 'Video';
        }

        $docTypes = self::docMimes();
        if (strpos($docTypes, $type) !== false) {
            return 'Document';
        }
    }

    public static function imageMimes()
    {
        return 'image/jpeg,image/png,image/jpg,image/svg';
    }

    public static function videoMimes()
    {
        return 'video/x-flv,video/mp4,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi';
    }

    public static function docMimes()
    {
        return 'application/pdf,application/docx,application/doc';
    }

    public static function formatTimeToHuman($time)
    {
        $seconds = Carbon::parse(now())->diffInSeconds(Carbon::parse($time), false);
        if ($seconds < 1) {
            return false;
        }

        return self::formatSecondsToHuman($seconds);
    }

    public static function formatDateTimeToHuman($time, $pattern = 'M d , Y h:i:A')
    {
        return date($pattern, strtotime($time));
    }

    public static function formatSecondsToHuman($seconds)
    {
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$seconds");
        $a = $dtF->diff($dtT)->format('%a');
        $h = $dtF->diff($dtT)->format('%h');
        $i = $dtF->diff($dtT)->format('%i');
        $s = $dtF->diff($dtT)->format('%s');
        if ($a > 0) {
            return $dtF->diff($dtT)->format('%a days, %h hrs, %i mins and %s secs');
        } elseif ($h > 0) {
            return $dtF->diff($dtT)->format('%h hrs, %i mins ');
        } elseif ($i > 0) {
            return $dtF->diff($dtT)->format(' %i mins');
        } else {
            return $dtF->diff($dtT)->format('%s seconds');
        }
    }

    public static function slugify($value)
    {
        return Str::slug($value);
    }

    public static function slugifyReplace($value, $symbol = '-')
    {
        return str_replace(' ', $symbol, $value);
    }

    /**
     * @param $mode = ["encrypt" , "decrypt"]
     * @param $path =
     */
    public static function readFileUrl($mode, $path)
    {
        if (strtolower($mode) == 'encrypt') {
            $path = base64_encode($path);

            return route('web.read_file', $path);
        }

        return base64_decode($path);
    }

    public static function carbon()
    {
        return new Carbon();
    }

    public static function withDir($dir)
    {
        if (!is_dir($dir)) {
            mkdir(trim($dir), 0777, true);
        }
    }

    public static function downloadFileFromUrl($url, $path = null, $return_full_path = false)
    {
        $fileInfo = pathinfo($url);
        $path = $path ?? storage_path('app/downloads');
        self::withDir($path);
        $filename = uniqid() . '.' . $fileInfo['extension'];
        $full_path = $path . '/' . $filename;

        $url_file = fopen($url, 'rb');
        if ($url_file) {
            $newfile = fopen($full_path, 'a+');
            if ($newfile) {
                while (!feof($url_file)) {
                    fwrite($newfile, fread($url_file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($url_file) {
            fclose($url_file);
        }
        if ($newfile) {
            fclose($newfile);

            return $return_full_path ? $full_path : $filename;
        }

        return null;
    }

    public static function int_format($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        $negation = ($number < 0) ? (-1) : 1;
        $coefficient = 10 ** $decimals;
        $number = $negation * floor((string) (abs($number) * $coefficient)) / $coefficient;

        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }

    public static function mkdir($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir);
        }
    }

    public static function isProductionEnv()
    {
        return env('APP_ENV') == 'production';
    }

    public static function dispatchJob(ShouldQueue $job)
    {
        if (self::isProductionEnv()) {
            dispatch($job);
        } else {
            dispatch_sync($job);
        }
    }

    function parsePhoneNumber($value)
    {
        $prefix = "234";
        $str_val = strval($value);
        $str_val = trim($str_val);
        $str_val = str_replace(" ", "", $str_val);

        $value = str_replace(" ", "", $value);

        if ($str_val[0] == "+") {
            $value = str_replace("+", "", $value);
        }

        $prefix_len = strlen(strval($prefix));
        if (substr($value, 0, $prefix_len) == $prefix) {
            $str_val = trim(substr($value, $prefix_len));
        }

        if ($str_val[0] == "0") {
            $value = trim(substr($str_val, 1));
        }

        if (strlen($value) <= 10) {
            $value = $prefix . "" . trim($value);
        }

        return $value;
    }

    static function convertJsonStringToText($inputString)
    {
        $array = json_decode($inputString, true);

        if (is_array($array)) {
            $outputString = implode(", ", $array);
        }
        return $outputString ?? null;
    }

    public static function encrypt($string)
    {
        return encrypt_decrypt("encrypt", $string);
    }

    public static function decrypt($string)
    {
        return encrypt_decrypt("decrypt", $string);
    }

    public static function frequencyDuration($frequency)
    {
        if ($frequency == PlanConstants::DAILY) {
            $duration = "1";
        }

        if ($frequency == PlanConstants::WEEKLY) {
            $duration = "7";
        }

        if ($frequency == PlanConstants::MONTHLY) {
            $duration = "30";
        }

        if ($frequency == PlanConstants::QUARTERLY) {
            $duration = "90";
        }

        if ($frequency == PlanConstants::BI_ANNUALLY) {
            $duration = "180";
        }

        if ($frequency == PlanConstants::YEARLY) {
            $duration = "360";
        }

        return $duration;
    }

    static function validateHash($hash, $throw_exception = true)
    {
        $payload = json_decode(decrypt($hash), true);

        if (!empty($ex = $payload["expires_at"]) && Carbon::parse($ex)->isPast() && $throw_exception) {
            throw new InvalidRequestException("Process has expired, kindly request a new code");
        }

        return $payload["data"];
    }

    static function removeCountryCode($phoneNumber)
    {
        // Remove leading '+' character if present
        if (strpos($phoneNumber, '+') === 0) {
            $phoneNumber = substr($phoneNumber, 1);
        }

        // Check if the number starts with a valid country code
        if (strpos($phoneNumber, '234') === 0) {
            // Replace the country code with '0'
            $phoneNumber = '0' . substr($phoneNumber, 3);
        }

        return $phoneNumber;
    }

    static function parseNumberWithoutZero($phoneNumber)
    {
        // Remove leading '+' character if present
        if (strpos($phoneNumber, '+') === 0) {
            $phoneNumber = substr($phoneNumber, 1);
        }

        // Check if the number starts with a valid country code
        if (strpos($phoneNumber, '234') === 0) {
            $phoneNumber = substr($phoneNumber, 3);
        }

        // Check if the number starts with '0'
        if (strpos($phoneNumber, '0') === 0) {
            $phoneNumber = substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }

    static function parsePhoneNumberByCode($phone, $code = null)
    {
        if (empty($code)) {
            return $phone;
        }

        if ($code[0] != "+") {
            $code = "+$code";
        }
        if ($phone[0] == "+") {
            $phone = str_replace($code, "", $phone);
        }

        if (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }

        if (in_array(strval(substr($phone, 0, 2)), ["07", "08", "09"])) {
            $phone = substr($phone, 1);
        }
        return $code . $phone;
    }
}
