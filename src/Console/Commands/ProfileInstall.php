<?php

namespace ZhuiTech\LaraBoot\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;

class ProfileInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profile:install
     {name : 配置档案集合名称}
     {--file=* : 安装指定的配置档案}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '安装预定义的配置档案集合';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Application $app
     * @return mixed
     * @throws \Exception
     */
    public function handle(Application $app)
    {
        $name = $this->argument('name');
        $files = $this->option('file');

        /**
         * 检查profile是否存在
         */
        $dir = base_path('profiles/'.$name);
        if (!file_exists($dir) || !is_dir($dir)) {
            throw new \Exception('指定的profile不存在');
        }

        /**
         * 遍历并安装
         */
        $installers = $app->tagged('profile_installers');
        foreach ($installers as $ins) {
            $prof_name = $ins->name();
            $prof_path = $dir . '/' . $prof_name . '.php';
            if (file_exists($prof_path) && (empty($files) || in_array($prof_name, $files))) {
                $messages = [];
                $profile = include $prof_path;

                try {
                    $ins->install($profile, $messages);
                } catch (\Exception $e) {
                    $messages[] = $e->getMessage();
                } finally {
                    foreach ($messages as $msg) {
                        printf("%s:%s %s\n", $name, $prof_name, $msg);
                    }
                }
            }
        }
    }
}
