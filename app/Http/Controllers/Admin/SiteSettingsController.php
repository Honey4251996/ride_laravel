<?php

/**
 * Site Settings Controller
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Site Settings
 * @author      Trioangle Product Team
 * @version     2.1
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Start\Helpers;
use App\Models\Currency;
use App\Models\SiteSettings;
use App\Models\Language;
use App\Models\Country;
use Validator;

class SiteSettingsController extends Controller
{
	public function __construct()
	{
		$this->helper = new Helpers;
	}

	/**
	 * Load View and Update Site Settings Data
	 *
	 * @return redirect     to site_settings
	 */
	public function index(Request $request)
	{
		if ($request->isMethod('GET')) {
			$data['result'] = SiteSettings::get();

			$data['currency'] = @Currency::codeSelect();
			$data['countries'] = Country::codeSelect();
			$data['default_currency'] = @Currency::defaultCurrency()->first()->code;
			return view('admin.site_settings', $data);
		}

		// Site Settings Validation Rules
		$rules = array(
			'site_name' => 'required',
			'logo' => 'image|mimes:jpg,png,jpeg,gif',
			'page_logo' => 'image|mimes:jpg,png,jpeg,gif',
			'favicon' => 'image|mimes:jpg,png,jpeg,gif',
			'default_currency' => 'required',
			'driver_km' => 'required',
			'admin_contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
			'admin_country_code' => 'required',
			'heat_map' => 'required|In:On,Off',
		);

		if($request->heat_map == 'On') {
			$rules['heat_map_hours'] = 'required|Integer|min:1';
		}

		// Site Settings Validation Custom Names
		$attributes = array(
			'site_name' => 'Site Name',
			'logo' => 'logo Image',
			'logo' => 'Page logo Image',
			'favicon' => 'favicon logo',
			'default_currency' => 'Default Currency',
			'driver_km' => 'Driver Kilo meter',
			'admin_contact' => 'Admin Contact Number',
			'admin_country_code' => 'Country Code',
		);

		$validator = Validator::make($request->all(), $rules,[],$attributes);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		$image = $request->file('logo');
		if ($image) {
			$extension = $image->getClientOriginalExtension();
			$filename = 'logo' . '.' . $extension;

			$success = $image->move('images/logos', $filename);

			if (!$success) {
				return back()->withError('Could not upload Image');
			}
			SiteSettings::where(['name' => 'logo'])->update(['value' => $filename]);
		}

		$page_logo = $request->file('page_logo');
		if ($page_logo) {
			$extension = $page_logo->getClientOriginalExtension();
			$filename = 'page_logo' . '.' . $extension;

			$success = $page_logo->move('images/logos', $filename);

			if (!$success) {
				return back()->withError('Could not upload Image');
			}
			SiteSettings::where(['name' => 'page_logo'])->update(['value' => $filename]);
		}

		$favicon = $request->file('favicon');
		if ($favicon) {
			$extension = $favicon->getClientOriginalExtension();
			$filename = 'favicon' . '.' . $extension;

			$success = $favicon->move('images/logos', $filename);
			if (!$success) {
				return back()->withError('Could not upload Video');
			}
			SiteSettings::where(['name' => 'favicon'])->update(['value' => $filename]);
		}

		Currency::where('status', 'Active')->update(['default_currency' => '0']);
		Currency::where('code', $request->default_currency)->update(['default_currency' => '1']);
          
        Language::where('default_language',1)->update(['default_language' => 0]);
        Language::where('value', $request->default_language)->update(['default_language' => 1]);

		SiteSettings::where(['name' => 'site_name'])->update(['value' => $request->site_name]);
		SiteSettings::where(['name' => 'version'])->update(['value' => $request->version]);
		// SiteSettings::where(['name' => 'payment_currency'])->update(['value' => $request->payment_currency]);
		SiteSettings::where(['name' => 'location_fare'])->update(['value' => $request->location_fare]);
		SiteSettings::where(['name' => 'head_code'])->update(['value' => $request->head_code]);
		SiteSettings::where(['name' => 'driver_km'])->update(['value' => $request->driver_km]);
		SiteSettings::where(['name' => 'admin_contact'])->update(['value' => $request->admin_contact]);
		SiteSettings::where(['name' => 'admin_country_code'])->update(['value' => $request->admin_country_code]);
		SiteSettings::where(['name' => 'heat_map'])->update(['value' => $request->heat_map]);
		SiteSettings::where(['name' => 'heat_map_hours'])->update(['value' => $request->heat_map_hours]);

		$this->helper->flash_message('success', 'Updated Successfully');
		return redirect('admin/site_setting');
	}
}
