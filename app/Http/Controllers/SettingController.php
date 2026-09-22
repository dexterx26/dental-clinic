<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        
        // List existing database backups
        $backupFiles = [];
        if (Storage::exists('backups')) {
            $files = Storage::files('backups');
            foreach ($files as $file) {
                $backupFiles[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => round(Storage::size($file) / 1024, 2) . ' KB',
                    'date' => Carbon::createFromTimestamp(Storage::lastModified($file))->toDayDateTimeString(),
                ];
            }
        }

        return view('settings.index', compact('settings', 'backupFiles'));
    }

    public function update(Request $request)
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        AuditLog::log('settings_updated', "Updated clinic system settings.");

        return back()->with('success', 'System settings updated successfully.');
    }

    public function backup()
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $keyName = "Tables_in_" . $dbName;

        $sqlContent = "-- BrightSmile Dental Clinic Management System Backup\n";
        $sqlContent .= "-- Generated at: " . Carbon::now()->toDateTimeString() . "\n";
        $sqlContent .= "-- Database: " . $dbName . "\n\n";
        $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->$keyName;
            
            // Create Table SQL
            $createTableRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createTableRes[0]->{'Create Table'};
            $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sqlContent .= $createSql . ";\n\n";

            // Table Data SQL
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $escapedValues = array_map(function ($val) {
                        if (is_null($val)) return "NULL";
                        return "'" . addslashes((string)$val) . "'";
                    }, array_values($rowArray));

                    $sqlContent .= "INSERT INTO `{$tableName}` VALUES (" . implode(", ", $escapedValues) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }

        $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $fileName = 'backup_' . date('Y_m_d_His') . '.sql';
        Storage::disk('local')->put('backups/' . $fileName, $sqlContent);

        AuditLog::log('backup_created', "Generated full database backup file {$fileName}.");

        return back()->with('success', "Database backup created successfully: {$fileName}");
    }
}
