<?php

namespace App\Http\Controllers\Back\Config;

use App\Http\Requests\Back\Config\GeneralRequest;
use App\Repositories\Back\Config\GeneralRepository;
use App\Http\Controllers\Controller;
use Setting;
class GeneralController extends Controller
{
    protected $message_update;
    protected $message_error;
    protected $generalRepository;

    public function __construct(GeneralRepository $generalRepository)
    {
        $this->message_update = __('Les configurations ont été enregistrées avec succès.');
        $this->message_error = __('Désolé, des erreurs se sont produites lors du traitement.');
        $this->generalRepository = $generalRepository;
    }

    public function get()
    {
        return response()->json(['erreur' => false,'dataConfig' => $this->generalRepository->getConfig()]);

    }

    public function update(GeneralRequest $request)
    {
        if($this->generalRepository->update($request) == true){
            return response()->json(['erreur' => false, 'notify' => ['title' => null, 'text' => $this->message_update, 'color'=>'success']]);
        }else{
            return response()->json(['erreur' => true, 'notify' => ['title' => null, 'text' => $this->message_error, 'color'=>'danger']]);
        }
    }
}
