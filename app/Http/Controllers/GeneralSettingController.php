<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Str;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $data['getRecord'] = GeneralSetting::find(1);

        return view('general-setting.index', $data);
    }

    public function store(Request $request)
    {
        $save = GeneralSetting::find(1);
        $save->website_name = $request->website_name;
        $save->email = $request->email;
        $save->phone = $request->phone;
        $save->address = $request->address;
        $save->description = $request->description;
        $save->gst_number = $request->gst_number;

        if (! empty($request->file('logo'))) {
            if (! empty($save->logo) && file_exists('upload/general-setting/'.$save->logo)) {
                unlink('upload/general-setting/'.$save->logo);
            }
            $file = $request->file('logo');
            $randomStr = Str::random(30);
            $filename = $randomStr.'.'.$file->getClientOriginalExtension();
            $file->move('upload/general-setting/', $filename);
            $save->logo = $filename;
        }
        $save->save();

        return redirect()->route('general-setting.index')->with('success', 'General Settings created successfully!');

    }
}
