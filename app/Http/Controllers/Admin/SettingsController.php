<?php
namespace App\Http\Controllers\Admin;
use DB;
use Auth;
use Storage;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class SettingsController extends Controller
{
    public function index(Request $request) 
    {
        if (permission::permitted('settings')=='fail'){ return redirect()->route('denied'); }
        
        $data = table::settings()->where('id', 1)->first();
        
    	return view('admin.settings', compact('data'));
    }
    public function update(Request $request) 
    {

    
        if (permission::permitted('settings-update')=='fail'){ return redirect()->route('denied'); }
       
        $v = $request->validate([
            'country' => 'required|max:100',
            'timezone' => 'required|timezone|max:100',
            'iprestriction' => 'max:1600',
            'time_format' => 'max:1',
            'clock_comment' => 'max:2',
            'rfid' => 'max:2',
            'gps' => 'max:2',
            'app_name' => 'nullable|string|max:255',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'currency' => 'nullable|string|max:10',
        ]);
        $country = $request->country;
        $timezone = $request->timezone;
        $clock_comment = $request->clock_comment;
        $iprestriction = $request->iprestriction;
        $time_format = $request->time_format;
        $rfid = $request->rfid;
        $gps = $request->gps;
        $app_name = $request->app_name;
        $currency = $request->currency;
        
        if ($timezone != null) 
        {
            $t = table::settings()->where('id', 1)->value('timezone');
            $path = base_path('.env');
            
            if(file_exists($path)) 
            {
                file_put_contents($path, str_replace('APP_TIMEZONE='.$t, 'APP_TIMEZONE='.$timezone, file_get_contents($path)));
            }
        }

        $update = [
                'country' => $country,
                'timezone' => $timezone,
                'clock_comment' => $clock_comment,
                'iprestriction' => $iprestriction,
                'time_format' => $time_format,
                'rfid' => $rfid,
                'gps' => $gps,
                'app_name' => $app_name,
                'currency' => $currency,
        ];

        if ($request->hasFile('app_logo')) 
        {
            $oldLogo = table::settings()->where('id', 1)->value('app_logo');

            $path = $request->file('app_logo')->store('logos', 'public');
            $update['app_logo'] = $path;

            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) 
            {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        table::settings()
        ->where('id', 1)
        ->update($update);
        
        return redirect('settings')->with('success', trans("Settings is up to date. Please try re-login for the new settings to take effect."));
    }
}