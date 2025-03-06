<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\saveOtp;
use App\Models\tam;
use App\Models\tam1;

use Shuchkin\SimpleXLSX;

class OTPController extends Controller
{
    private function generateKey($length)
    {
        return random_bytes($length);
    }

    public function process(Request $request)
    {
        $start_time = microtime(true);
        $action = $request->input('action');
        $message = $request->input('message');
        $cipher = $request->input('cipher');
        $key = $request->input('key');
        $result = null;
    
        if ($action === 'encrypt' && $message) {
            $key = $this->generateKey(strlen($message));
            $cipher = '';
    
            for ($i = 0; $i < strlen($message); $i++) {
                $cipher .= chr(ord($message[$i]) ^ ord($key[$i]));
            }
    
            $result = [
                'cipher' => base64_encode($cipher),
                'key' => base64_encode($key),
            ];
            $end_time = microtime(true);
            $this->Save($message,$result['key'], $result['cipher'], $end_time - $start_time,1);
    
           
            $chartData = $this->getDataForChart();
    
            return view('otp', ['result' => $result, 'chartData' => $chartData]);
        } elseif ($action === 'decrypt' && $cipher && $key) {
            $cipher = base64_decode($cipher);
            $key = base64_decode($key);
            $message = '';
    
            for ($i = 0; $i < strlen($cipher); $i++) {
                $message .= chr(ord($cipher[$i]) ^ ord($key[$i]));
            }
            $end_time = microtime(true);
            $this->Save($message, $request->input('key'),  $request->input('cipher'), $end_time - $start_time,2);
    
            $result = [
                'message' => $message,
            ];
    
            $chartData = $this->getDataForChart();
    
            return view('otp', ['result' => $result, 'chartData' => $chartData]);
        }
    }
    public function Save($message,$key,$cipher,$time,$type)
    {
       
        try {
            $save= new saveOtp();
            $save['plaintext']= $message;
            $save['skey']= $key;
        
            $save['encode']= $cipher;
             $save['time_encode']=$time;
        
           $save->save();
            
        } catch (\Throwable $th) {
            throw $th;
        }
       
    }
    public function getDataForChart()
{
    
    $records = saveOtp::all();
    $data = saveOtp::select('time_encode', 'encode', 'plaintext')
    ->whereNotNull('time_encode') 
    ->get()
    ->sortBy('time_encode')
    ->map(function ($item) {
        return [strlen($item->plaintext), $item->time_encode];
    })->toArray();
    return $data;

}

public function generateInsertCommands(Request $request)
{

    $xlsx = SimpleXLSX::parse("C:\\Users\\OS\\Downloads\\Book1.xls");

    $academies = $xlsx->rows();
    // Chỉ định dòng bắt đầu (ví dụ: dòng thứ 10, bắt đầu từ chỉ số 9)
    $startRow = 110000; // Dòng 10 trong Excel có chỉ số 9 trong mảng (vì mảng bắt đầu từ 0)

    // Cắt mảng từ dòng 10 trở đi
    $academies = array_slice($academies, $startRow);
    $insertCommands = [];
    $insertType = [];
    $chunks = array_chunk($academies, 110000); 
    foreach ($chunks as $chunk) {
        foreach ($chunk as $academy) {
        $insertCommands[] = "INSERT INTO `academy` (`name`, `contact`, `created_at`, `updated_at`, `address`, `avatar`, `brn`, `location_x`, `location_y`, `detail_address`, `description`, `verification`, `business_owner`, `accepted`, `deleted`) VALUES (" .
            "'{$academy[0]}', " . // name
            "'{$academy[5]}', " . // contact
            "'{$academy[2]}', " . // created_at
            "NOW(), " . // updated_at
            "'{$academy[3]}', " . // address
            "'image/Avatar/Academy/academydefault.png', " . // avatar
            "'{$academy[1]}', " . // brn
            "{$academy[6]}, " . // location_x
            "{$academy[7]}, " . // location_y
            "'{$academy[4]}', " . // detail_address
            "'', " . // description
            "0, " . // verification
            "'', " . // business_owner
            "0, " . // accepted
            "0);"; // deleted
        }
        if(count($insertCommands)>=100)
        {
            break;
        }
    }
    $filePath = 'insert_commands.sql';
    file_put_contents('insert_commands.sql', implode("\n", $insertCommands));
    return response()->download($filePath);
}
public function generateInsertCommands2(Request $request)
{

    $xlsx = SimpleXLSX::parse("C:\\Users\\OS\\Downloads\\Book2.xlsx");

    $academies = $xlsx->rows();
    // Chỉ định dòng bắt đầu (ví dụ: dòng thứ 10, bắt đầu từ chỉ số 9)
    $startRow = 110000; // Dòng 10 trong Excel có chỉ số 9 trong mảng (vì mảng bắt đầu từ 0)

    // Cắt mảng từ dòng 10 trở đi
    $academies = array_slice($academies, $startRow);
    $insertCommands = [];
    $insertType = [];
    $chunks = array_chunk($academies, 110000); 
    foreach ($chunks as $chunk) {
        foreach ($chunk as $academy) {

        $insertCommands[] = "  INSERT INTO `detail_type_academy`( `created_at`, `updated_at`, `academy_id`, `category_id`, `type_id`) VALUES (" .
        "NOW(), " . // name
        "NOW(), " .
         "'{$academy[2]}', " .
         "'{$academy[1]}', " .
         "'{$academy[0]}'); ";

    }
    $filePath = 'insert_commands.sql';
    file_put_contents('insert_commands.sql', implode("\n", $insertCommands));
    return response()->download($filePath);
    }
}
public function generateInsertCommands1(Request $request)
{
    $xlsx = SimpleXLSX::parse("C:\\Users\\OS\\Downloads\\Book2.xlsx");
    $academies = $xlsx->rows();

    $existingRecords = Tam::select('id', 'name', 'category_id')
        ->get()
        ->keyBy(function ($record) {
            return $record->name . '-' . $record->category_id;  
        });


        
    $insertCommands = [];
    $not = [];

    $chunks = array_chunk($academies, 1000); 
    foreach ($chunks as $chunk) {
        foreach ($chunk as $academy) {
            $name = $academy[1]; 
            $category_id = $academy[0]; 
            $key = $name . '-' . $category_id;

            if ($existingRecords->has($key)) {
                $insertCommands[] = $existingRecords[$key]->id;
            } else {
                $not[] = $academy; 
                $insertCommands[] = 0;
            }
        }
    }

    $chunks1 = array_chunk($insertCommands, 10000);
    $query = [];

    foreach ($chunks1 as $insert)
    {
        foreach ($insert as $insert1) {
            $query[]= "INSERT INTO `tam1`(`id`) VALUES (".$insert1.")";
        }
    }
    // return response()->json([
    //     'insertCommands' => $insertCommands,
    //     'notFoundRecords' => $not
    // ]);
    return response()->json([
        'query' => $query,
       
    ]);
    }
}
