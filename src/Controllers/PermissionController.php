<?php

namespace Abedin\WebInstaller\Controllers;

class PermissionController extends Controller
{
    /**
     * @var array
     */
    protected $results = [];

     /**
     * Display the instalation permission page.
     */
    public function index()
    {
        $permissions = $this->check(config('installer.permissions'));
        return match($this->isPublish){
            true => view('vendor.web-installer.permission', compact('permissions')),
            default => view('joynala.web-installer::permission', compact('permissions'))
        };
    }

    /**
     * Check  folder or file permission.
     *
     * @param array $folderOrFiles
     * @return array
     */
    public function check(array $folderOrFiles): array
    {
        foreach ($folderOrFiles as $path => $permission) {
            if (! ($this->getPermission($path) >= $permission)) {
                $this->addFileAndSetErrors($path, $permission, false);
            } else {
                $this->addFile($path, $permission, true);
            }
        }

        return $this->results;
    }

    private function getPermission($path)
    {
        return substr(sprintf('%o', fileperms(base_path($path))), -4);
    }

    /**
     * Add the path and set the errors.
     *
     * @param $path
     * @param $permission
     * @param $isPermit
     * @return void
     */
    private function addFileAndSetErrors($path, $permission, $isPermit): void
    {
        $this->addFile($path, $permission, $isPermit);
        $this->results['stop'] = true;
    }

    /**
     * Add the file to the list of results.
     *
     * @param $path
     * @param $permission
     * @param $isPermit
     * @return void
     */
    private function addFile($path, $permission, $isPermit): void
    {
        $this->results['items'][] = [
            'path' => $path,
            'number' => $permission,
            'isPermit' => $isPermit,
        ];
    }
}
