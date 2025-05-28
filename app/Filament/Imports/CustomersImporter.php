<?php

namespace App\Filament\Imports;

use App\Models\Customers;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CustomersImporter extends Importer
{
    protected static ?string $model = Customers::class;

    // public function getJobConnection(): ?string
    // {
    //     return 'sync';
    // }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
            ->rules(['max:255', 'regex:/^[A-Za-z\s]+$/'])->requiredMapping(),
            ImportColumn::make('email')
            ->rules(['email', 'max:255'])->requiredMapping(),
            ImportColumn::make('contact_no')->rules(['regex:/^(\+?\d{1,4})?\d{7,15}$/'])->label('Contact Number')->requiredMapping(),
            // ImportColumn::make('attributes'),
        ];
    }

    public function resolveRecord(): ?Customers
    {
        // return Customers::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Customers();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customers import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function saveRecord(): void
    {
        $data = $this->data;
        unset($data['name']);
        unset($data['email']);
        $this->record->attributes = json_encode($data); 
        $this->record->segment_id = $this->options['segment_id'];
        $checkCount = DB::table('customers')->where('segment_id',$this->record->segment_id)->where('contact_no',$this->record->contact_no)->count();
        
        unset($data['contact_no']);
        if($checkCount < 1){
            $this->record->save();
        }
    }

    // public function saveRecord(): void
    // {
    //     echo "<pre>";print_r($this);exit;
    //     $data = $this->data;
    //     $dirtyOrClean = 'clean';
    //     $checkCount = DB::table('email_dump')->where('segment_id',Session::get('segment_id'))->where('email',$data['email'])->get()->toArray();
    //     if(count($checkCount) < 1){
    //         $curl = curl_init();
    //         curl_setopt_array($curl, array(
    //             CURLOPT_URL => 'https://api.listclean.xyz/v1/verify/email/'.$data['email'],
    //             CURLOPT_RETURNTRANSFER => true,
    //             CURLOPT_ENCODING => '',
    //             CURLOPT_MAXREDIRS => 10,
    //             CURLOPT_TIMEOUT => 0,
    //             CURLOPT_FOLLOWLOCATION => true,
    //             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //             CURLOPT_CUSTOMREQUEST => 'GET',
    //             CURLOPT_HTTPHEADER => array(
    //                 'X-AUTH-TOKEN: MmM4MTAyYTBkYi0xNzI4NTM5ODU2'
    //             ),
    //         ));
    //         $response = curl_exec($curl);
    //         curl_close($curl);
    //         $response = json_decode($response,true);
    //         if(isset($response['data']['status']) && $response['data']['status'] != ''){
    //             $dumpEmail = [];
    //             $dumpEmail['email'] = $response['data']['email'];
    //             $dumpEmail['status'] = $response['data']['status'];
    //             $dumpEmail['reason'] = $response['data']['remarks'];
    //             $dumpEmail['segment_id'] = Session::get('segment_id');
    //             $dumpEmail['created_at'] = Carbon::now();
    //             DB::table('email_dump')->insert($dumpEmail);
    //             if($dumpEmail['status'] == "dirty"){
    //                 $dirtyOrClean = 'dirty';
    //             }
    //         }
    //     }else{
    //         $checkCount = (array) current($checkCount);
    //         if($checkCount['status'] == "dirty"){
    //             $dirtyOrClean = 'dirty';
    //         }
    //     }

    //     if($dirtyOrClean == 'clean'){
    //         unset($data['name']);
    //         unset($data['email']);
    //         unset($data['contact_no']);
    //         $this->record->attributes = json_encode($data); 
    //         $this->record->segment_id = Session::get('segment_id');
    //         $this->record->save();
    //     }
    // }
}
