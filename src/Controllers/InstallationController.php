<?php

namespace Abedin\WebInstaller\Controllers;

use Abedin\WebInstaller\Lib\Traits\InstallationTrait;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\BufferedOutput;

class InstallationController extends Controller
{
    use InstallationTrait;
     /**
     * Display the instalation permission page.
     */
    public function index()
    {
        $environmentFields = config('installer.environment_fields');
        return match($this->isPublish){
            true => view('vendor.web-installer.install', compact('environmentFields')),
            default => view('joynala.web-installer::install', compact('environmentFields'))
        };
    }

    public function appConfigure(Request $request, $index)
    {
        $formInfos = config('installer.environment_fields.' . $index);
        $rules = [];
        $isFuildsForDB = false;
        foreach($formInfos as $name => $formInfo){
            $isFuildsForDB = $this->isDbCredential($name);
            $rules[$name] = $formInfo['rule'];
        }

        $request->validate($rules);
        $data = $request->all();
        unset($data['_token']);

        if($isFuildsForDB && !$this->checkDatabaseConnection($data)){
            return [
                'status' => 400,
                'message' => 'Sorry, Your database credential is wrong'
            ];
        }

        $this->setupEnv($data);

        return response()->json([
            'status' => 200,
            'massage' => 'enverment setup is successfully.'
        ]);
    }

    public function finalInstall()
    {
        // Ready for some commands run here
        try {
            $this->getReadyToRun();
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'massage' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => 200,
            'massage' => 'enverment setup is successfully.'
        ]);
    }


}
