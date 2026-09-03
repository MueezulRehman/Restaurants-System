<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PlatformSettingsController extends Controller
{
    public function edit()
    {
        abort_unless(Auth::user() instanceof User && Auth::user()->isSuperAdmin(), 403);

        $bank = PlatformSetting::bankDetails();
        $home = [
            'homepage_hero_title' => PlatformSetting::getValue('homepage_hero_title', 'CodeIbex'),
            'homepage_hero_subtitle' => PlatformSetting::getValue('homepage_hero_subtitle', 'One platform for discovering and ordering from independent businesses.'),
            'homepage_banner_image' => PlatformSetting::getValue('homepage_banner_image', ''),
            'homepage_sale_badge_text' => PlatformSetting::getValue('homepage_sale_badge_text', 'Sale live'),
            'homepage_show_sale_badges' => PlatformSetting::getValue('homepage_show_sale_badges', '1') === '1',
        ];
        $platform = [
            'platform_name' => PlatformSetting::getValue('platform_name', 'CodeIbex'),
            'platform_tagline' => PlatformSetting::getValue('platform_tagline', 'Business platform'),
            'platform_email' => PlatformSetting::getValue('platform_email', ''),
            'platform_phone' => PlatformSetting::getValue('platform_phone', ''),
            'platform_address' => PlatformSetting::getValue('platform_address', ''),
            'platform_logo_path' => PlatformSetting::getValue('platform_logo_path', ''),
            'theme_light' => PlatformSetting::getValue('platform_theme_light', '#E7F0FA'),
            'theme_accent' => PlatformSetting::getValue('platform_theme_accent', '#7BA4D0'),
            'theme_primary' => PlatformSetting::getValue('platform_theme_primary', '#2E5E99'),
            'theme_dark' => PlatformSetting::getValue('platform_theme_dark', '#0D2440'),
        ];

        return view('admin.platform.settings', compact('bank', 'home', 'platform'));
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user() instanceof User && Auth::user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'platform_name' => 'required|string|max:100',
            'platform_tagline' => 'nullable|string|max:150',
            'platform_email' => 'nullable|email|max:150',
            'platform_phone' => 'nullable|string|max:30',
            'platform_address' => 'nullable|string|max:500',
            'platform_logo' => 'nullable|image|max:2048',
            'theme_light' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_dark' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'bank_name' => 'nullable|string|max:150',
            'account_title' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'branch' => 'nullable|string|max:150',
            'instructions' => 'nullable|string|max:1000',
            'homepage_hero_title' => 'nullable|string|max:200',
            'homepage_hero_subtitle' => 'nullable|string|max:500',
            'homepage_sale_badge_text' => 'nullable|string|max:50',
            'homepage_show_sale_badges' => 'nullable|boolean',
            'homepage_banner_image' => 'nullable|image|max:4096',
        ]);

        foreach (['platform_name', 'platform_tagline', 'platform_email', 'platform_phone', 'platform_address'] as $key) {
            PlatformSetting::setValue($key, $data[$key] ?? '');
        }

        PlatformSetting::setValue('platform_theme_light', $data['theme_light']);
        PlatformSetting::setValue('platform_theme_accent', $data['theme_accent']);
        PlatformSetting::setValue('platform_theme_primary', $data['theme_primary']);
        PlatformSetting::setValue('platform_theme_dark', $data['theme_dark']);

        PlatformSetting::setValue('bank_name', $data['bank_name'] ?? '');
        PlatformSetting::setValue('bank_account_title', $data['account_title'] ?? '');
        PlatformSetting::setValue('bank_account_number', $data['account_number'] ?? '');
        PlatformSetting::setValue('bank_iban', $data['iban'] ?? '');
        PlatformSetting::setValue('bank_branch', $data['branch'] ?? '');
        PlatformSetting::setValue('bank_instructions', $data['instructions'] ?? '');
        PlatformSetting::setValue('homepage_hero_title', $data['homepage_hero_title'] ?? '');
        PlatformSetting::setValue('homepage_hero_subtitle', $data['homepage_hero_subtitle'] ?? '');
        PlatformSetting::setValue('homepage_sale_badge_text', $data['homepage_sale_badge_text'] ?? 'Sale live');
        PlatformSetting::setValue('homepage_show_sale_badges', $request->boolean('homepage_show_sale_badges') ? '1' : '0');

        if ($request->hasFile('platform_logo')) {
            $oldLogo = PlatformSetting::getValue('platform_logo_path');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            PlatformSetting::setValue('platform_logo_path', $request->file('platform_logo')->store('platform', 'public'));
        }

        if ($request->hasFile('homepage_banner_image')) {
            PlatformSetting::setValue('homepage_banner_image', $request->file('homepage_banner_image')->store('platform', 'public'));
        }

        return back()->with('success', 'Platform settings updated successfully.');
    }
}
