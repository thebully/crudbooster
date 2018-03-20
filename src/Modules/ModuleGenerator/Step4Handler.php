<?php

namespace crocodicstudio\crudbooster\Modules\ModuleGenerator;

use crocodicstudio\crudbooster\helpers\Parsers\ControllerConfigParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class Step4Handler
{
    public function showForm($id)
    {
        $controller = DB::table('cms_moduls')->where('id', $id)->first()->controller;

        $data = [];
        $data['id'] = $id;
        if (file_exists(controller_path($controller))) {
            $fileContent = file_get_contents(controller_path($controller));
            $data['config'] = ControllerConfigParser::parse($fileContent);
        }

        return view('CbModulesGen::step4', $data);
    }

    public function handleFormSubmit()
    {
        $id = Request::input('id');
        $module = DB::table('cms_moduls')->where('id', $id)->first();

        $data = Request::all();

        $data['table'] = $module->table_name;

        $script_config = $this->getScriptConfig($data);

        $scripts = implode("\n", $script_config);
        $raw = file_get_contents(controller_path($module->controller));
        $raw = explode("# START CONFIGURATION DO NOT REMOVE THIS LINE", $raw);
        $rraw = explode("# END CONFIGURATION DO NOT REMOVE THIS LINE", $raw[1]);

        $file_controller = trim($raw[0])."\n\n";
        $file_controller .= "            # START CONFIGURATION DO NOT REMOVE THIS LINE\n";
        $file_controller .= $scripts."\n";
        $file_controller .= "            # END CONFIGURATION DO NOT REMOVE THIS LINE\n\n";
        $file_controller .= "            ".trim($rraw[1]);

        file_put_contents(controller_path($module->controller), $file_controller);

        return redirect()->route('AdminModulesControllerGetIndex')->with([
            'message' => trans('crudbooster.alert_update_data_success'),
            'message_type' => 'success',
        ]);
    }

    /**
     * @param $data
     * @return array
     */
    private function getScriptConfig($data)
    {
        $scriptConfig = [];
        $i = 0;
        $data = array_diff_key($data, array_flip(['_token', 'id', 'submit'])); // remove keys
        foreach ($data as $key => $val) {
            if ($val == 'true' || $val == 'false') {
                $value = $val;
            } else {
                $value = "'$val'";
            }

            $scriptConfig[$i] = '            $this->'.$key.' = '.$value.';';
            $i++;
        }

        return $scriptConfig;
    }
}