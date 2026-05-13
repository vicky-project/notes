<?php
namespace Modules\Notes\Installations;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Artisan;

class PostInstallation
{
  public function handle(string $moduleName) {
    try {
      $modules = array_merge(["telegram"], [$moduleName]);
      foreach ($modules as $modulename) {
        $module = Module::find($modulename);
        $module->enable();
      }

      Artisan::call("migrate");
    } catch (\Exception $e) {
      logger()->error(
        "Failed to run post installation of notes module: " .
        $e->getMessage(),
      );

      throw $e;
    }
  }
}