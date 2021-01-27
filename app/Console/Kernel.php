<?php

namespace App\Console;

use App\Models\MovieDetail;
use App\Models\MovieReviews;
use App\Models\Playing;
use App\Models\Showing;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function (){
            // 每天凌晨运行一次
            // 即将上映
            $showing = Showing::all()->toArray();
            for($i=0; $i<count($showing); $i++){
                $obj = $showing[$i];
                $m_id = $obj['m_id'];
                // 删除电影评论
                MovieReviews::query()->where('m_id','=',$m_id)->delete();
                // 删除电影详情
                MovieDetail::query()->where('m_id','=',$m_id)->delete();
            }
            Showing::query()->truncate();

            // 正在上映
            $playing = Playing::all()->toArray();
            for($i = 0; $i<count($playing); $i++){
                $obj = $playing[$i];
                $m_id = $obj['m_id'];
                // 删除电影评论
                MovieReviews::query()->where('m_id','=',$m_id)->delete();
                // 删除电影详情
                MovieDetail::query()->where('m_id','=',$m_id)->delete();
            }
            Playing::query()->truncate();
        })->weekly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
