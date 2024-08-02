<?php

namespace App\Console\Commands;

use App\Models\Part;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class clientAssetGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // make gfx variable
        $gfxes = gfx();

        $vars['xshop-background'] = $gfxes['background'] ?? '#000000';
        $vars['xshop-primary'] = $gfxes['primary'] ?? '#6e0000';
        $vars['xshop-diff'] =  getGrayscaleTextColor($gfxes['primary']) ?? '#6e0000';
        $vars['xshop-diff2'] =  getGrayscaleTextColor($gfxes['secondary']) ?? '#6e0000';
        $vars['xshop-secondary'] = $gfxes['secondary'] ?? '#ff0000';
        $vars['xshop-text'] = $gfxes['text'] ?? '#111111';
        $vars['xshop-border-radius'] = $gfxes['border-radius'] ?? '7px';
        $vars['xshop-shadow'] = $gfxes['shadow'] ?? '2px 2px 4px #777777';


        // prepare client.scss and add gfx variable
        $js = "// PLEASE DO NOT EDIT THIS FILE, \n// IF YOU WANT ADD ANY CODE CREATE NEW JS INTO client-custom \n import axios from 'axios'; \n window.axios = axios; \n \n window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';" . PHP_EOL;
        $variables = "// PLEASE DO NOT EDIT THIS FILE, \n// IF YOU WANT ADD ANY CODE CREATE NEW SCSS INTO client-custom" . PHP_EOL;
        foreach ($vars as $k => $var) {
            $variables .= '$'."$k:$var;" . PHP_EOL;
        }
        $variables .= ":root{" . PHP_EOL;
        foreach ($vars as $k => $var) {
            $variables .= "--$k:$var;" . PHP_EOL;
        }
        $variables .= "}" . PHP_EOL . PHP_EOL;

        // add custom scss and js
        $files = File::allFiles(resource_path() . '/sass/client-custom');

        foreach ($files as $file) {
            if ($file->getType() == 'file' && $file->getExtension() == 'scss') {
                $variables .= '@import "client-custom/' .
                    substr(trim($file->getBasename(), '_'), 0, -5)
                    . '";' . PHP_EOL;
            }

        }
        $files = File::allFiles(resource_path() . '/js/client-custom');

        foreach ($files as $file) {
            if ($file->getType() == 'file' && $file->getExtension() == 'js') {
                $js .= 'import "./client-custom/' . $file->getBasename() . '";' . PHP_EOL;
            }

        }
        // add parts scss & js
        foreach (Part::all() as $part) {
            $variables .= '@import "../views/segments/' . $part->segment . '/'
                . $part->part . '/' . $part->part . '";' . PHP_EOL;
            $js .= 'import "../views/segments/' . $part->segment . '/'
                . $part->part . '/' . $part->part . '.js";' . PHP_EOL;
        }

        // save scss
        file_put_contents(resource_path() . '/sass/client.scss', $variables);
        file_put_contents(resource_path() . '/js/client.js', $js);
    }
}
