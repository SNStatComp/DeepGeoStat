<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

class GeoDataSeeder
{
    public static function importGeoDataIntoDB($command, $file, $table, $tableColumns, $ogr2ogr_options = '')
    {
        $command->info("Preparing data to import it into the database table {$table}.");

        $temp_dir = './database/data/temp';
        $temp_csv = "{$temp_dir}/{$file}.csv";

        // Check if specified zip file can be opened.
        $zip = new \ZipArchive();
        if ($zip->open("./database/data/{$file}.zip") === true) {
            // Create temp directory if it does not exist yet.
            @mkdir($temp_dir);

            // Extract file in zip to the temp directory.
            $zip->extractTo($temp_dir);

            // Extract .gpkg file to a .csv file.
            passthru("ogr2ogr -f CSV {$temp_csv} ./database/data/temp/{$file}.gpkg -lco GEOMETRY=AS_WKT -lco CREATE_CSVT=true -lco GEOMETRY_NAME=geometry -progress {$ogr2ogr_options} 2>&1");
        } else {
            // TODO: Throw exception.
            dd("Failed to find zip file {$file}.");
        }
        $zip->close();

        $command->info("Importing data from {$file} to database {$table}.");

        $db = (DB::connection())->getConfig();
        $db_host = $db['host'];
        $db_port = $db['port'];
        $db_name = $db['database'];
        $db_user = $db['username'];
        $db_password = $db['password'];

        // Use PostgreSQL copy command to enter data from .csv file into database table.
        passthru("psql 'postgresql://{$db_user}:{$db_password}@{$db_host}:{$db_port}/{$db_name}' -c \"\copy {$table} ({$tableColumns}) FROM '".realpath($temp_csv)."' DELIMITER ',' CSV HEADER;\" 2>&1");

        // Delete the temporary directory and all files in it.
        exec("rm -rf {$temp_dir}");

        $command->info("Successfully imported data into database table {$table}.");
    }
}
