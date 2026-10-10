@php
    $settings = \App\Models\SystemSetting::getSettings();
    $maintenanceMessage = !empty($settings->website_maintenance_message) 
        ? $settings->website_maintenance_message 
        : 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.';
@endphp
@include('errors.maintenance', ['maintenanceMessage' => $maintenanceMessage, 'isWebsite' => true])
