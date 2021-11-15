<?php

use Illuminate\Database\Migrations\Migration;

class FixIssuesWithMigrationToLaravel7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            collect(File::allFiles(resource_path('views/vendor')))
                ->filter(function(SplFileInfo $file) {
                    return Str::endsWith($file->getPathname(), 'blade.php') &&
                        !Str::endsWith($file->getPathname(), 'html/message.blade.php') &&
                        !Str::endsWith($file->getPathname(), 'email.blade.php');
                })->each(function(SplFileInfo $file) {
                    File::delete($file->getPathname());
                });
        } catch (Exception $e) {
            //
        }

       try {
           File::delete(base_path('vendor/symfony/translation/TranslatorInterface.php'));
       } catch (Exception $e) {
            //
       }

        try {
            $setting = DB::table('settings')->where('name', 'player.enable_landing')->first();
            if ($setting && (bool) $setting->value) {
                DB::table('settings')->where('name', 'homepage.type')->orWhere('name', 'homepage.value')->delete();
                DB::table('settings')->insert([
                    ['name' => 'homepage.type', 'value' => 'component'],
                    ['name' => 'homepage.value', 'value' => 'Landing Page'],
                ]);
            }
        } catch (Exception $e) {
            //
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
