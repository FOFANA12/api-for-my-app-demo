<?php

namespace App\Console\Commands;

use App\Events\NotifyUserMessage;
use App\Models\User;
use Illuminate\Console\Command;

class NotifySupAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:supadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify SupAdmin';

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
     * @return mixed
     */
    public function handle()
    {
        $user = User::where('email','supadmin@supadmin.com')->first();
        $user->notificationMessages()->create([
            'type' => 'success',
            'data' => json_encode(
                [
                    'from' => 'HADES Consulting',
                    'subject' => 'Sujet notification n° '.str_random(8),
                    'body' => 'Contenu de la notification n° '.str_random(8)
                ]
            ),
            'favoris' => false,
            'read_at' => null,
        ]);
        event(new NotifyUserMessage('new notifycation', $user->id));
    }
}
