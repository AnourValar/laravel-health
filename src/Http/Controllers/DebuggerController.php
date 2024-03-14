<?php

namespace AnourValar\LaravelHealth\Http\Controllers;

class DebuggerController
{
    /**
     * Debugger
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function __invoke(\Illuminate\Http\Request $request)
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        $json = json_encode(
            [
                'ip' => $request->ip(),
                'url' => url(''),

                'server' => $request->server->all(),
                'get_loaded_extensions' => get_loaded_extensions(),

                'failed_jobs' => $this->getFailedJobs(),
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return <<<HERE
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8" />
          <title>Debugger</title>
        </head>
        
        <body>
          <div style="overflow-x: auto; border: 2px dotted black; margin: 4px; padding: 4px;">
            <pre>$json</pre>
          </div>
        
          $phpinfo
        </body>
        </html>
        HERE;
    }

    /**
     * @return array
     */
    private function getFailedJobs(): array
    {
        if (config('queue.default') == 'sync') {
            return [];
        }

        $result = \App::make('queue.failer')->all();

        foreach ($result as &$item) {
            $item = (array) $item;
        }

        return $result;
    }
}
